<?php

declare(strict_types=1);

namespace Interprete;

use Exception;

class SyntaxError extends Exception
{
    public function __construct(string $message, int $offset = 0)
    {
        parent::__construct($message.' around position '.$offset);
    }
}
