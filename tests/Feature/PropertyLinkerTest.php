<?php

namespace Tests\Feature;

use AllowDynamicProperties;
use JesseGall\Delegator\PropertyLinker;
use Orchestra\Testbench\TestCase;

class PropertyLinkerTest extends TestCase
{

    public function test_CanResolvePropertyNames()
    {
        $linker = new PropertyLinker();
        $source = new PropertyLinkerTestSource();
        $target = new PropertyLinkerTestTarget();

        $properties = $linker->resolvePropertyNames($source);

        $this->assertContains('property', $properties);
        $this->assertContains('privateProperty', $properties);
        $this->assertContains('overlappingProperty', $properties);
        $this->assertContains('overlappingPrivateProperty', $properties);
    }

    public function test_TheLinkedPropertyOfTheSourceIsReferencedByTheTarget()
    {
        $linker = new PropertyLinker();
        $source = new PropertyLinkerTestSource();
        $target = new PropertyLinkerTestTarget();

        $linker->linkProperty('property', $source, $target);

        $this->assertTrue(isset($target->property));
        $this->assertSame($source->property, $target->property);
    }

    public function test_WhenTheLinkedPropertyOfTheSourceIsChanged_TheTargetIsUpdated()
    {
        $linker = new PropertyLinker();
        $source = new PropertyLinkerTestSource();
        $target = new PropertyLinkerTestTarget();

        $linker->linkProperty('property', $source, $target);

        $source->property = 'new value';

        $this->assertSame('new value', $target->property);
    }

    public function test_WhenTheLinkedPropertyOfTheTargetIsChanged_TheSourceIsUpdated()
    {
        $linker = new PropertyLinker();
        $source = new PropertyLinkerTestSource();
        $target = new PropertyLinkerTestTarget();

        $linker->linkProperty('property', $source, $target);

        $target->property = 'new value';

        $this->assertSame('new value', $source->property);
    }

    public function test_CanLinkPrivatePropertyFromSource()
    {
        $linker = new PropertyLinker();
        $source = new PropertyLinkerTestSource();
        $target = new PropertyLinkerTestTarget();

        $linker->linkProperty('privateProperty', $source, $target);

        $this->assertTrue(isset($target->privateProperty));
        $this->assertEquals('private property', $target->privateProperty);
    }

    public function test_CanLinkOverlappingProperty()
    {
        $linker = new PropertyLinker();
        $source = new PropertyLinkerTestSource();
        $target = new PropertyLinkerTestTarget();

        $linker->linkProperty('overlappingProperty', $source, $target);

        $this->assertSame($source->overlappingProperty, $target->overlappingProperty);
    }

    public function test_CanLinkSourcePropertyToPrivateTargetProperty()
    {
        $linker = new PropertyLinker();
        $source = new PropertyLinkerTestSource();
        $target = new PropertyLinkerTestTarget();

        $linker->linkProperty('overlappingPrivateProperty', $source, $target);

        $this->assertSame($source->overlappingPrivateProperty, $target->getOverlappingPrivateProperty());
    }

    public function test_LinkingUndefinedPropertyIsHandledGracefully()
    {
        $linker = new PropertyLinker();
        $source = new PropertyLinkerTestSource();
        $target = new PropertyLinkerTestTarget();

        $linker->linkProperty('undefinedProperty', $source, $target);

        $this->assertFalse(isset($target->undefinedProperty));
    }

    public function test_CanLinkMultipleProperties()
    {
        $linker = new PropertyLinker();
        $source = new PropertyLinkerTestSource();
        $target = new PropertyLinkerTestTarget();

        $linker->linkProperties($source, $target);

        $this->assertSame('property', $target->property);
        $this->assertSame('private property', $target->privateProperty);
        $this->assertSame('overlapping property', $target->overlappingProperty);
        $this->assertSame('overlapping private property', $target->getOverlappingPrivateProperty());
    }

}

class PropertyLinkerTestSource
{
    public string $property = 'property';
    private string $privateProperty = 'private property';

    public string $overlappingProperty = 'overlapping property';
    public string $overlappingPrivateProperty = 'overlapping private property';

    public string $undefinedProperty;
}


class PropertyLinkerTestTarget
{
    public string $overlappingProperty = 'overlapping property';
    private string $overlappingPrivateProperty = 'overlapping property';

    public function getOverlappingPrivateProperty(): string
    {
        return $this->overlappingPrivateProperty;
    }
}