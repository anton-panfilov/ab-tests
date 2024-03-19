<?php

namespace AP\ABTest\IntIdBased\Option\Bool;

class FalseBoolOption extends AbstractBoolOption
{
    public function run(): bool
    {
        return false;
    }
}
