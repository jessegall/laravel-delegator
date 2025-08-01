<?php

namespace JesseGall\Delegator;

class PropertyLinker
{

    /**
     * @param string $property
     * @param object $source
     * @param object $target
     * @return void
     */
    public function linkProperty(string $property, object $source, object $target): void
    {
        if ($reference = &$this->read($property, $source)) {
            $this->write($property, $target, $reference);
        }
    }

    /**
     * @param object $source
     * @param object $target
     * @param string[] $except
     * @return void
     */
    public function linkProperties(object $source, object $target, array $except = []): void
    {
        $properties = $this->resolvePropertyNames($source, $except);

        foreach ($properties as $property) {
            $this->linkProperty($property, $source, $target);
        }
    }

    /**
     * @param string $property
     * @param object $target
     * @return mixed
     */
    public function &read(string $property, object $target): Reference|null
    {
        $reader = function () use ($property) {
            if (! isset($this->{$property})) {
                return null;
            }

            $value = &$this->{$property};

            return new Reference($property, $value);
        };

        $reference = $reader->call($target);

        return $reference;
    }

    /**
     * @param string $property
     * @param object $target
     * @param Reference $reference
     * @return void
     */
    public function write(string $property, object $target, Reference $reference): void
    {
        $writer = function () use ($property, $reference) {
            $this->{$property} = &$reference->value;
        };

        $writer->call($target);
    }

    /**
     * @param object $source
     * @param array $except
     * @return string[]
     */
    public function resolvePropertyNames(object $source, array $except = []): array
    {
        $resolver = function () use ($source) {
            return array_keys(get_object_vars($source));
        };

        $properties = $resolver->call($source);

        return array_values(array_diff($properties, $except));
    }

}