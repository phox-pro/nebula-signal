<?php

namespace Phox\Nebula\Signal\Implementation;

use LogicException;
use Phox\Nebula\Atom\Implementation\Functions;
use Phox\Nebula\Plasma\Notion\Abstracts\Star;
use Phox\Nebula\Signal\SignalContainer;
use Phox\Structures\Collection;
use ReflectionClass;
use ReflectionMethod;

class StarsContainer
{
    /** @var Collection<Collection<SignalContainer>> */
    protected Collection $signals;

    public function __construct()
    {
        $this->signals = new Collection(Collection::class);
    }

    public function register(Star $star): void
    {
        $reflection = new ReflectionClass($star::class);

        $classSignals = $this->getClassSignals($reflection);

        if (is_callable($star)) {
            $this->registerMethod($star, $classSignals);
        }

        $this->registerActions($reflection, $star, $classSignals);
    }

    public function get(string $pattern, ?string $method = null): ?SignalContainer
    {
        $method ??= Signal::METHOD_ANY;

        /** @var Collection<SignalContainer>|null $signals */
        $signals = $this->signals->tryGet($pattern);

        if (is_null($signals)) {
            return null;
        }

        return $signals->tryGet($method) ?? $signals->tryGet(Signal::METHOD_ANY);
    }

    /**
     * @param string $pattern
     * @return Collection<SignalContainer>|null
     */
    public function getContainer(string $pattern): ?Collection
    {
        return $this->signals->tryGet($pattern);
    }

    public function resolve(string $uri, ?string $method = null): ?SignalContainer
    {
        $uri = trim(parse_url($uri, PHP_URL_PATH), '/');
        $method ??= Signal::METHOD_ANY;

        foreach ($this->signals as $pattern => $signal) {
            $variables = $this->getVariablesFromPattern($pattern);
            $pattern = $this->handlePatternByVariables($pattern, $variables);

            if (preg_match('/^' . str_replace('/', '\/', $pattern) . '$/', $uri, $vars) > 0) {
                $signalContainer = $signal->tryGet($method) ?? $signal->tryGet(Signal::METHOD_ANY);

                return is_null($signalContainer) ? null : $this->handleFoundSignalContainer($signalContainer, $variables, $vars);
            }
        }

        return null;
    }

    protected function getClassSignals(ReflectionClass $reflection): Collection
    {
        $signals = $reflection->getAttributes(Signal::class);
        $classSignals = new Collection(Signal::class);

        foreach ($signals as $signal) {
            $classSignals->add($signal->newInstance());
        }

        return $classSignals;
    }

    protected function registerActions(ReflectionClass $reflection, Star $star, Collection $classSignals): void
    {
        $actions = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($actions as $action) {
            $signals = $action->getAttributes(Signal::class);

            foreach ($signals as $signal) {
                /** @var Signal $methodSignal */
                $methodSignal = $signal->newInstance();

                $this->registerMethod($star, $classSignals, $methodSignal, $action->getName());
            }
        }
    }

    protected function registerMethod(Star $star, Collection $signals, ?Signal $methodSignal = null, ?string $action = null): void
    {
        /** @var Signal $signal */
        foreach ($signals as $signal) {
            $pattern = implode('/', [$signal->getPattern(), $methodSignal?->getPattern()]);
            $method = $methodSignal?->getMethod() ?? $signal->getMethod();

            $this->registerAction($star, $pattern, $method, $action);
        }
    }

    protected function registerAction(Star $star, string $pattern, ?string $method = null, ?string $action = null): void
    {
        $this->signals->has($pattern) ?: $this->signals->set($pattern, new Collection(SignalContainer::class));
        $method ??= Signal::METHOD_ANY;

        $collection = $this->signals->get($pattern);
        $collection->has($method) ?: $collection->set($method, $this->createContainer($star, $method, $action));
    }

    protected function createContainer(Star $star, string $method, ?string $action = null): SignalContainer
    {
        $container = new SignalContainer();

        $container->setStar($star);
        $container->setMethod($method);
        is_null($action)
            ? (is_callable($star) ?: throw new LogicException())
            : $container->setAction($action);

        return $container;
    }

    protected function getVariablesFromPattern(string $pattern): array
    {
        $variables = [];

        preg_match_all("/{([a-zA-Z0-9_\-]+)(?::([^}]+))?}/", $pattern, $matches);

        if ($matches[0] !== []) {
            foreach ($matches[1] as $key => $variable) {
                $variables[trim($variable)] = trim($matches[2][$key]);
            }
        }

        return $variables;
    }

    protected function handlePatternByVariables(string $pattern, array $variables): string
    {
        foreach ($variables as $var => $rule) {
            $pattern = preg_replace(
                '/{\s*(' . $var . ')((?::\s)?[^}]+)?}/',
                '(?<$1>' . ($rule == '' ? '[a-zA-Z0-9_\-]+' : $rule) . ')',
                $pattern
            );
        }

        return $pattern;
    }

    protected function handleFoundSignalContainer(SignalContainer $signalContainer, array $variables, array $variablesValues): SignalContainer
    {
        $signalVariables = new SignalVariables();

        foreach ($variables as $key => $variable) {
            $signalVariables->set($key, new SignalVariable($key, $variablesValues[$key]));
        }

        $signalContainer->setSignalVariables($signalVariables);

        return $signalContainer;
    }
}