<?php

namespace AP\ABTest\IntIdBased\ByGroup\DataModel;

use AP\Structure\Collection\AbstractCollection;
use AP\Structure\Collection\ObjectsCollection;

class GroupsCollection extends ObjectsCollection
{
    public function __construct(array|AbstractCollection $data = [])
    {
        parent::__construct(Group::class, data: $data);
    }

    /**
     * @return Group[]
     */
    public function all(): array
    {
        return parent::all();
    }
}
