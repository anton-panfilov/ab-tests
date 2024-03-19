<?php

namespace AP\ABTest\IntIdBased\ByGroup\DataModel;

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
}
