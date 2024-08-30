<?php

declare(strict_types=1);

namespace Interprete;

use Interprete\Nodes\BinaryNode;
use Interprete\Nodes\NameNode;
use Interprete\Nodes\NumberNode;
use Interprete\Nodes\StringNode;
use Interprete\Nodes\TernaryNode;

class Parser
{
    protected const LEFT_ASSOCIATIVE = 1;
    protected const RIGHT_ASSOCIATIVE = 2;

    private array $ternaryOperators;
    private array $binaryOperators;

    public function __construct()
    {
        $this->ternaryOperators = [
            '?' => ['precedence' => 10, 'associativity' => self::RIGHT_ASSOCIATIVE],
        ];

        $this->binaryOperators = [
            'or' => ['precedence' => 20, 'associativity' => self::LEFT_ASSOCIATIVE],
            '||' => ['precedence' => 20, 'associativity' => self::LEFT_ASSOCIATIVE],
            'and' => ['precedence' => 30, 'associativity' => self::LEFT_ASSOCIATIVE],
            '&&' => ['precedence' => 30, 'associativity' => self::LEFT_ASSOCIATIVE],
            '=' => ['precedence' => 40, 'associativity' => self::LEFT_ASSOCIATIVE],
            '!=' => ['precedence' => 40, 'associativity' => self::LEFT_ASSOCIATIVE],
            '<' => ['precedence' => 40, 'associativity' => self::LEFT_ASSOCIATIVE],
            '>' => ['precedence' => 40, 'associativity' => self::LEFT_ASSOCIATIVE],
            '>=' => ['precedence' => 40, 'associativity' => self::LEFT_ASSOCIATIVE],
            '<=' => ['precedence' => 40, 'associativity' => self::LEFT_ASSOCIATIVE],
            '+' => ['precedence' => 50, 'associativity' => self::LEFT_ASSOCIATIVE],
            '-' => ['precedence' => 50, 'associativity' => self::LEFT_ASSOCIATIVE],
            '*' => ['precedence' => 60, 'associativity' => self::LEFT_ASSOCIATIVE],
            '/' => ['precedence' => 60, 'associativity' => self::LEFT_ASSOCIATIVE],
        ];
    }

    public function parse(TokenStream $stream)
    {
        $node = $this->parseExpression($stream);

        if (!$stream->isEOF()) {
            $token = $stream->current();
            throw new SyntaxError(sprintf('Unexpected token "%s" of value "%s"', $token->type, $token->getValue()));
        }

        return $node;
    }

    protected function parseExpression(TokenStream $stream, int $precedence = 0)
    {
        $expr = $this->parsePrimary($stream);
        while (($token = $stream->current()) && $token->isBinaryOperator()) {
            $op = $this->binaryOperators[$token->getValue()];
            if ($op['precedence'] < $precedence) {
                break;
            }

            $stream->next();

            $isLeftAssociative = self::LEFT_ASSOCIATIVE === $op['associativity'];
            $right = $this->parseExpression($stream, $isLeftAssociative ? $op['precedence'] + 1 : $op['precedence']);
            $expr = new BinaryNode($token->getValue(), $expr, $right);
        }

        $expr = $this->parseTernary($stream, $expr, $precedence);

        return $expr;
    }

    protected function parseTernary(TokenStream $stream, $expr, int $precedence)
    {
        while (($token = $stream->current()) && $token->type === Token::QUESTION_MARK) {
            $op = $this->ternaryOperators['?'];
            if ($op['precedence'] < $precedence) {
                break;
            }

            // The only ternary operator (?) is right associative, hence...
            $subPrecedence = $op['precedence'];

            // Parse the if branch expression.
            if ($stream->current()->type !== Token::QUESTION_MARK) {
                $stream->next();
                $ifBranch = null;
            } else {
                $stream->next();
                $ifBranch = $this->parseExpression($stream, $subPrecedence);
            }

            // Parse the else branch expression.
            $stream->expect(Token::COLON, ':', 'Ternary else expected');
            $elseBranch = $this->parseExpression($stream, $subPrecedence);

            // Create the ternary operator node
            $expr = new TernaryNode($expr, $ifBranch, $elseBranch);
        }

        return $expr;
    }

    protected function parsePrimary(TokenStream $stream)
    {
        $token = $stream->current();

        if ($token->type === Token::OPEN_PAREN) {
            $stream->next();
            $expr = $this->parseExpression($stream);
            $stream->expect(Token::CLOSE_PAREN, ')', 'An opened parenthesis is not properly closed');

            return $expr;
        }

        return $this->parsePrimaryExpression($stream);
    }

    protected function parsePrimaryExpression(TokenStream $stream)
    {
        $token = $stream->current();
        switch ($token->type) {
            case Token::IDENTIFIER:
                $stream->next();

                return new NameNode($token->getValue());

            case Token::NUMBER:
                $stream->next();

                return new NumberNode($token->getValue());

            case Token::STRING:
                $stream->next();

                return new StringNode($token->getValue());

            default:
                throw new SyntaxError(sprintf('Unexpected token "%s" of value "%s"', $token->type, $token->getValue()), $token->cursor, $stream->getExpression());
        }
    }
}
