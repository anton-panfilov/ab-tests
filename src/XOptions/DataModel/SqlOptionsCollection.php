<?php

namespace AP\ABTest\IntIdBased\XOptions\DataModel;

use AP\Structure\Collection\AbstractCollection;
use AP\Structure\Collection\ObjectsCollection;

class SqlOptionsCollection extends ObjectsCollection
{
    public function __construct(array|AbstractCollection $data = [])
    {
        parent::__construct(
            class: SqlOption::class,
            data: $data
        );
    }

    /**
     * @return SqlOption[]
     */
    public function all(): array
    {
        return parent::all();
    }
}
