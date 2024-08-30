<?php

declare(strict_types=1);

namespace Interprete\Nodes;

class NameNode
{
    public $value;

    public function __construct($value)
    {
        $this->value = $value;
    }
}
