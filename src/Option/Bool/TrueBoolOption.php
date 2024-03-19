<?php

namespace AP\ABTest\IntIdBased\Option\Bool;

class TrueBoolOption extends AbstractBoolOption
{
    public function run(): bool
    {
        return true;
    }
}
