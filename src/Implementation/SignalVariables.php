<?php

namespace Phox\Nebula\Signal\Implementation;

use Phox\Structures\ObjectCollection;

/**
 * @extends ObjectCollection<SignalVariable>
 */
class SignalVariables extends ObjectCollection
{
    public function __construct()
    {
        parent::__construct(SignalVariable::class);
    }
}