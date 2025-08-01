<?php

namespace JesseGall\Delegator;

class Reference
{

    public function __construct(
        public string $property,
        public mixed &$value,
    ) {}

}