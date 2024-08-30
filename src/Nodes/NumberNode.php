<?php

declare(strict_types=1);

namespace Interprete\Nodes;

class NumberNode
{
    public $value;

    public function __construct($value)
    {
        $this->value = $value;
    }
}
