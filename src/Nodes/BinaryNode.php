<?php

declare(strict_types=1);

namespace Interprete\Nodes;

class BinaryNode
{
    public $value;
    public $left;
    public $rigth;

    public function __construct($value, $left, $rigth)
    {
        $this->value = $value;
        $this->left = $left;
        $this->rigth = $rigth;
    }
}
