<?php

namespace AP\ABTest\IntIdBased\XOptions\DataModel;

use AP\ABTest\IntIdBased\XOptions\Exception\BadElement;
use Stringable;

class Option
{
    public function __construct(
        public mixed               $element,
        public readonly int        $weight,
        protected readonly ?string $label = null,
    )
    {
        if (!is_string($label) && !is_scalar($element) && !($element instanceof Stringable)) {
            throw new BadElement(
                message: "if label do not set an element should be scalar or should to implement interface Stringable"
            );
        }
    }

    public function getLabel(): string
    {
        return is_string($this->label) ?
            $this->label : (string)$this->element;
    }
}
