<?php

declare(strict_types=1);

namespace Interprete\Nodes;

class TernaryNode
{
    public $condition;
    public $ifBranch;
    public $elseBranch;

    public function __construct($condition, $ifBranch, $elseBranch)
    {
        $this->condition = $condition;
        $this->ifBranch = $ifBranch;
        $this->elseBranch = $elseBranch;
    }
}
