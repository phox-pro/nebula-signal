<?php

namespace Phox\Nebula\Signal\Implementation;

class SignalVariable
{
    public function __construct(
        protected string $name,
        protected mixed $value
    ) {
        //
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }
}