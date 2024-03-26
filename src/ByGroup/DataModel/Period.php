<?php

namespace AP\ABTest\IntIdBased\ByGroup\DataModel;

use AP\Geometry\Int1D\Geometry\Intersects;
use AP\Geometry\Int1D\Helpers\Shape;

class Period
{
    public function __construct(
        public readonly int $id,
        public readonly int $group,
        public mixed        $settings,
        public int          $startTs,
        public ?int         $endTs = null,
    )
    {
    }

    public function isActive(int $currentTimestamp)
    {
        return Intersects::intersectsShapes(
            shape1: Shape::p($currentTimestamp),
            shape2: Shape::make(
                min: $this->startTs,
                max: $this->endTs
            ),
        );
    }
}
