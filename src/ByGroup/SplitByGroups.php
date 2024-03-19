<?php

namespace AP\ABTest\IntIdBased\ByGroup;

use AP\ABTest\IntIdBased\ByGroup\DataModel\GroupsCollection;
use AP\ABTest\IntIdBased\ByGroup\DataModel\Period;
use AP\ABTest\IntIdBased\ByGroup\DataModel\PeriodsCollection;
use AP\ABTest\IntIdBased\ByGroup\Exception\BadPeriod;
use AP\ABTest\IntIdBased\ByGroup\Exception\CanNotReplace;
use AP\ABTest\IntIdBased\XOptions\DataModel\OptionsCollection;
use AP\Geometry\Int1D\Exception\Infinity;
use AP\Geometry\Int1D\Exception\NoIntersectsException;
use AP\Geometry\Int1D\Geometry\Exclude;
use AP\Geometry\Int1D\Helpers\Shape;
use AP\Geometry\Int1D\Shape\AbstractShape;
use Throwable;

abstract class SplitByGroups
{
    abstract protected function dataLayer(): DataLayerInterface;

    abstract protected function makeOptions(mixed $settings): OptionsCollection;

    protected function modifySettings(mixed $settings): mixed
    {
        return $settings;
    }

    public function searchPeriod(int $group, int $timestamp): ?Period
    {
        return $this->dataLayer()->searchPeriod(group: $group, timestamp: $timestamp);
    }

    public function getPeriods(int $group): PeriodsCollection
    {
        return $this->dataLayer()->getPeriods(group: $group);
    }

    public function getGroups(): GroupsCollection
    {
        return $this->dataLayer()->getGroups();
    }

    public function get(int $id): ?Period
    {
        return $this->dataLayer()->getPeriod(id: $id);
    }

    private static function makeShape(int $startTs, ?int $endTs): AbstractShape
    {
        return is_null($endTs) ?
            Shape::vp($startTs) :
            Shape::s($startTs, $endTs);
    }

    public function remove(int $id): void
    {
        $this->dataLayer()->removePeriod(id: $id);
    }

    /**
     * @throws BadPeriod
     * @throws CanNotReplace
     * @throws Throwable
     */
    public function saveSettings(
        int   $group,
        mixed $settings,
        int   $startTs,
        ?int  $endTs = null,
        bool  $replace = true,
    ): int
    {
        $nowTimestamp = time();
        if ($startTs < $nowTimestamp) {
            $startTs = $nowTimestamp;
        }
        if (!is_null($endTs) && $startTs > $endTs) {
            throw new BadPeriod("`endTs` can't be before `startTs`");
        }

        $settings = $this->modifySettings(settings: $settings);

        $periods  = $this->getPeriods(group: $group);
        $newShape = self::makeShape(
            startTs: $startTs,
            endTs: $endTs
        );
        try {
            $this->dataLayer()->transactionStart();
            foreach ($periods->all() as $period) {
                $currentShape = self::makeShape(
                    startTs: $period->startTs,
                    endTs: $period->endTs
                );
                try {
                    $updatedCurrentShapes = Exclude::exclude(
                        exclude: $newShape,
                        original: $currentShape
                    );
                    if (!$replace) {
                        throw new CanNotReplace();
                    }
                    $this->remove(id: $period->id);
                    foreach ($updatedCurrentShapes->all() as $shape) {
                        try {
                            $min = $shape->min()->value;
                        } catch (Infinity) {
                            $min = null;
                        }

                        try {
                            $max = $shape->max()->value;
                        } catch (Infinity) {
                            $max = null;
                        }

                        $this->dataLayer()->insertPeriod(
                            group: $period->group,
                            settings: $period->settings,
                            startTs: $min,
                            endTs: $max
                        );
                    }
                } catch (NoIntersectsException) {
                    // do nothing for NoIntersects case
                }
            }

            $id = $this->dataLayer()->insertPeriod(
                group: $group,
                settings: $settings,
                startTs: $startTs,
                endTs: $endTs
            );

            $this->dataLayer()->transactionCommit();

            return $id;

        } catch (Throwable $e) {
            $this->dataLayer()->transactionRollback();
            throw $e;
        }
    }
}
