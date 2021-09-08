<?php

namespace Tests\Unit;

use Phox\Nebula\Http\Implementation\Request;
use Phox\Nebula\Plasma\Notion\Abstracts\Star;
use Phox\Nebula\Signal\Implementation\Signal;
use Phox\Nebula\Signal\Implementation\StarsContainer;
use Phox\Nebula\Signal\SignalContainer;
use Phox\Nebula\Signal\TestCase;

class SignalTest extends TestCase
{
    public function testDefaultSignalLogic(): void
    {
        $_REQUEST['foo'] = 'bar';
        $_SERVER['REQUEST_URI'] = '/test/path';

        $this->container()->singleton($this);

        $controller = new #[Signal('test')] class extends Star {
            #[Signal('path')]
            public function path(SignalTest $signalTest, Request $request) {
                $signalTest->assertEquals('bar', $request->getValues()->get('foo'));
            }
        };

        $this->container()->get(StarsContainer::class)->register($controller);

        $this->nebula->run();
    }

    public function testSignalWithVariables(): void
    {
        $_SERVER['REQUEST_URI'] = '/test/path/5';

        $controller = new #[Signal('test')] class($this) extends Star {
            public function __construct(private SignalTest $signalTest) {}

            #[Signal('path/{id}')]
            public function path(SignalContainer $signalContainer) {
                $this->signalTest->assertTrue($signalContainer->getSignalVariables()->has('id'));
                $this->signalTest->assertEquals(5, $signalContainer->getSignalVariables()->get('id')->getValue());
            }
        };

        $this->container()->get(StarsContainer::class)->register($controller);

        $this->nebula->run();
    }

    public function testSignalWithStarActionParams(): void
    {
        $_SERVER['REQUEST_URI'] = '/test/path/5/john';

        $controller = new #[Signal('test')] class($this) extends Star {
            public function __construct(private SignalTest $signalTest) {}

            #[Signal('path/{id}/{name: [a-z]+}')]
            public function path(string $name, $id) {
                $this->signalTest->assertEquals(5, $id);
                $this->signalTest->assertEquals('john', $name);
            }
        };

        $this->container()->get(StarsContainer::class)->register($controller);

        $this->nebula->run();
    }
}