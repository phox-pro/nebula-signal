<?php

namespace Phox\Nebula\Signal;

use Phox\Nebula\Atom\Implementation\Application;
use Phox\Nebula\Atom\Implementation\Exceptions\StateExistsException;
use Phox\Nebula\Atom\Implementation\StateContainer;
use Phox\Nebula\Atom\Notion\Abstracts\Provider;
use Phox\Nebula\Atom\Notion\Interfaces\IDependencyInjection;
use Phox\Nebula\Http\Implementation\Request;
use Phox\Nebula\Http\Implementation\States\HttpState;
use Phox\Nebula\Plasma\Implementation\StarResolver;
use Phox\Nebula\Signal\Implementation\Signal;
use Phox\Nebula\Signal\Implementation\SignalVariable;
use Phox\Nebula\Signal\Implementation\StarsContainer;
use Phox\Nebula\Signal\Implementation\States\SignalState;

class SignalProvider extends Provider
{
    /**
     * @throws StateExistsException
     */
    public function __invoke(StateContainer $stateContainer, IDependencyInjection $dependencyInjection)
    {
        $signalState = new SignalState();
        $signalState->listen([$this, 'onSignalState']);

        $dependencyInjection->singleton(new StarsContainer());
        $stateContainer->addAfter($signalState, HttpState::class);
    }

    public function onSignalState(StarsContainer $container, StarResolver $starResolver, Request $request, IDependencyInjection $dependencyInjection): void
    {
        $path = $request->getServerValue('REQUEST_URI');
        $method = $request->getServerValue('REQUEST_METHOD');

        $signalContainer = $container->resolve($path, $method);

        if (!is_null($signalContainer)) {
            $dependencyInjection->singleton($signalContainer);

            $starResolver->setStar($signalContainer->getStar());
            $starResolver->setAction($signalContainer->getAction());

            $signalVariables = $signalContainer->getSignalVariables();
            $variables = array_map(fn(SignalVariable $variable): mixed => $variable->getValue(), $signalVariables->all());

            $starResolver->setParams($variables);
        }
    }
}