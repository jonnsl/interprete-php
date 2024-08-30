<?php

declare(strict_types=1);

namespace Interprete;

class Token
{
    public const STRING = 1;
    public const BOOLEAN_OPERATOR = 2;
    public const IDENTIFIER = 3;
    public const NUMBER = 4;
    public const MATH_OPERATOR = 5;
    public const COMPARISON_OPERATOR = 6;
    public const OPEN_PAREN = 7;
    public const CLOSE_PAREN = 8;
    public const QUESTION_MARK = 9;
    public const COLON = 10;

    protected ?string $value;

    public int $type;

    /**
     */
    public function __construct(int $type, ?string $value = null)
    {
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * Return the value associated with this token.
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Checks if this token is a binary operator.
     */
    public function isBinaryOperator(): bool
    {
        return in_array($this->value, ['or', '||', 'and', '&&', '=', '!=', '<', '>', '>=', '<=', '+', '-', '*', '/']);
    }
}
