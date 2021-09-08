<?php

namespace Phox\Nebula\Signal;

use Phox\Nebula\Plasma\Notion\Abstracts\Star;
use Phox\Nebula\Signal\Implementation\Signal;
use Phox\Nebula\Signal\Implementation\SignalVariables;

class SignalContainer
{
    protected Star $star;
    protected ?string $action;
    protected string $method = Signal::METHOD_ANY;
    protected SignalVariables $signalVariables;

    public function __construct()
    {
        $this->signalVariables = new SignalVariables();
    }

    /**
     * @return Star
     */
    public function getStar(): Star
    {
        return $this->star;
    }

    /**
     * @param Star $star
     */
    public function setStar(Star $star): void
    {
        $this->star = $star;
    }

    /**
     * @return string|null
     */
    public function getAction(): ?string
    {
        return $this->action;
    }

    /**
     * @param string|null $action
     */
    public function setAction(?string $action): void
    {
        $this->action = $action;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    /**
     * @return SignalVariables
     */
    public function getSignalVariables(): SignalVariables
    {
        return $this->signalVariables;
    }

    /**
     * @param SignalVariables $signalVariables
     */
    public function setSignalVariables(SignalVariables $signalVariables): void
    {
        $this->signalVariables = $signalVariables;
    }
}