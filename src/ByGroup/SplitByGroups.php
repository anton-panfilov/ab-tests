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
    abstract protected function settingsDataLayer(): DataLayerInterface;
    abstract protected function makeOptions(mixed $settings): OptionsCollection;

    public function searchPeriod(int $group, int $timestamp): ?Period
    {
        return $this->settingsDataLayer()->searchPeriod(group: $group, timestamp: $timestamp);
    }

    public function getPeriods(int $group): PeriodsCollection
    {
        return $this->settingsDataLayer()->getPeriods(group: $group);
    }

    public function getGroups(): GroupsCollection
    {
        return $this->settingsDataLayer()->getGroups();
    }

    private static function makeShape(int $startTs, ?int $endTs): AbstractShape
    {
        return is_null($endTs) ?
            Shape::vp($startTs) :
            Shape::s($startTs, $endTs);
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

        $periods  = $this->getPeriods(group: $group);
        $newShape = self::makeShape(
            startTs: $startTs,
            endTs: $endTs
        );
        try {
            $this->settingsDataLayer()->transactionStart();
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

                        $this->settingsDataLayer()->insertPeriod(
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

            $id = $this->settingsDataLayer()->insertPeriod(
                group: $group,
                settings: $settings,
                startTs: $startTs,
                endTs: $endTs
            );

            $this->settingsDataLayer()->transactionCommit();

            return $id;

        } catch (Throwable $e) {
            $this->settingsDataLayer()->transactionRollback();
            throw $e;
        }
    }

    public function get(int $id): void
    {
        $this->settingsDataLayer()->removePeriod(id: $id);
    }

    public function remove(int $id): void
    {
        $this->settingsDataLayer()->removePeriod(id: $id);
    }
}
