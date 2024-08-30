<?php

declare(strict_types=1);

namespace Interprete;

use Generator;

class TokenStream
{
    private Generator $tokens;

    public function __construct(Generator $tokens)
    {
        $this->tokens = $tokens;
    }

    /**
     * Move forward to next element.
     *
     * @throws SyntaxError
     */
    public function next(): void
    {
        if ($this->tokens->current() === null) {
            throw new SyntaxError('Unexpected end of input');
        }
        $this->tokens->next();
    }

    /**
     * Return the current element.
     */
    public function current(): ?Token
    {
        return $this->tokens->current();
    }

    /**
     * Tests a token.
     *
     * @param array|int   $type    The type to test
     * @param string|null $value   The token value
     * @param string|null $message The syntax error message
     */
    public function expect($type, $value = null, $message = null): void
    {
        $token = $this->current();
        if ($token === null) {
            $message = sprintf('"%s" expected%s', $type, $value ? sprintf(' with value "%s"', $value) : '');
            throw new SyntaxError('Unexpected end of input. '.$message);
        } elseif ($token->type !== $type) {
            $message = $message ?: sprintf('Unexpected token "%s" of value "%s" ("%s" expected%s)', $token->type, $token->value, $type, $value ? sprintf(' with value "%s"', $value) : '');

            throw new SyntaxError($messagePrefix ? $messagePrefix.' '.$message : $message, $token->offset);
        }
        $this->next();
    }

    /**
     * Checks if the this stream has reached its end.
     */
    public function isEOF(): bool
    {
        return $this->tokens->valid() === false;
    }
}
