<?php

namespace Phox\Nebula\Signal\Implementation;

use Attribute;
use Phox\Structures\Collection;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Signal
{
    public const METHOD_GET = 'GET';
    public const METHOD_POST = 'POST';
    public const METHOD_ANY = 'ANY';

    public function __construct(protected string $pattern, protected ?string $method = null)
    {
        //
    }

    /**
     * @return string
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * @return ?string
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }
}