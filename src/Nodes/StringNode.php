<?php

declare(strict_types=1);

namespace Interprete\Nodes;

class StringNode
{
    public $value;

    public function __construct($value)
    {
        $this->value = $value;
    }
}
