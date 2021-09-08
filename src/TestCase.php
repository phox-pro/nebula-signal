<?php

namespace Phox\Nebula\Signal;

use Phox\Nebula\Atom\Implementation\ProvidersContainer;
use Phox\Nebula\Http\HttpProvider;
use Phox\Nebula\Plasma\TestCase as PlasmaTestCase;

class TestCase extends PlasmaTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $providersContainer = $this->container()->get(ProvidersContainer::class);

        $providersContainer->addProvider(new HttpProvider());
        $providersContainer->addProvider(new SignalProvider());
    }
}