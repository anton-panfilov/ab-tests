<?php

namespace AP\ABTest\IntIdBased\ByGroup\DataModel;

class Group
{
    public function __construct(
        public readonly int  $group,
        public readonly int  $periods_count,
        public readonly bool $active_now,
        public readonly ?Period $active_period = null,
    )
    {
    }
}
