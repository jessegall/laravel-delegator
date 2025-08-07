<?php

namespace Tests\Feature;

use JesseGall\Delegator\Delegates;
use JesseGall\Delegator\PropertyLinker;
use Orchestra\Testbench\TestCase;

class DelegatesTest extends TestCase
{

    public function test_DelegatorProxiesProperties()
    {
        $delegator = new DelegatorTestTarget(
            new DelegatorTestSource()
        );

        $delegator->count = 1;
        $this->assertEquals(1, $delegator->delegate->count);

        $delegator->count = 2;
        $this->assertEquals(2, $delegator->delegate->count);
    }

    public function test_DelegatorForwardsMethodCalls()
    {
        $delegator = new DelegatorTestTarget(
            new DelegatorTestSource()
        );

        $delegator->incrementCount();
        $this->assertEquals(1, $delegator->delegate->count);

        $delegator->incrementCount();
        $this->assertEquals(2, $delegator->delegate->count);
    }


    public function test_WhenSettingUndefinedPropertyThroughTargetLinkIsCreated()
    {
        $linker = new PropertyLinker();
        $source = new PropertyLinkerTestSource();
        $target = new PropertyLinkerTestTarget();

        $linker->linkProperties($source, $target);

        $target->undefinedProperty = 'new value';

        $this->assertTrue(isset($target->undefinedProperty));
        $this->assertSame($source->undefinedProperty, $target->undefinedProperty);
    }
}

class DelegatorTestSource
{
    public int $count = 0;
    public array $array = [];

    public function incrementCount(): void
    {
        $this->count++;
    }

}

class DelegatorTestTarget extends DelegatorTestSource
{
    use Delegates;

    public function __construct(object $delegate)
    {
        $this->delegateTo($delegate);
    }

}