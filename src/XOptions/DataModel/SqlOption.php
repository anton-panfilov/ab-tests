<?php

namespace AP\ABTest\IntIdBased\XOptions\DataModel;

class SqlOption
{
    public function __construct(
        public readonly string $label,
        public readonly string $label_sql,
        public readonly string $case_sql,
    )
    {
    }
}
