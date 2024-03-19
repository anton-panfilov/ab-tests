<?php

namespace AP\ABTest\IntIdBased\XOptions\DataModel;

use AP\Structure\Collection\AbstractCollection;
use AP\Structure\Collection\ObjectsCollection;

class OptionsCollection extends ObjectsCollection
{
    public function __construct(array|AbstractCollection $data = [])
    {
        parent::__construct(
            class: Option::class,
            data: $data
        );
    }

    /**
     * @return Option[]
     */
    public function all(): array
    {
        return parent::all();
    }
}
