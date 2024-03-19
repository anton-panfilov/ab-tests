<?php

namespace AP\ABTest\IntIdBased\ByGroup\DataModel;

use AP\Structure\Collection\AbstractCollection;
use AP\Structure\Collection\ObjectsCollection;

class PeriodsCollection extends ObjectsCollection
{
    public function __construct(array|AbstractCollection $data = [])
    {
        parent::__construct(Period::class, data: $data);
    }

    /**
     * @return Period[]
     */
    public function all(): array
    {
        return parent::all();
    }
}
