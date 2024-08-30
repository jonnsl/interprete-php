<?php

declare(strict_types=1);

namespace Interprete;

use Exception;
use Interprete\Nodes\BinaryNode;
use Interprete\Nodes\NameNode;
use Interprete\Nodes\NumberNode;
use Interprete\Nodes\StringNode;
use Interprete\Nodes\TernaryNode;

class Interpreter
{
    public static function evaluate(string $input, array $constants = [])
    {
        $lexemes = Lexer::tokenize($input);
        $ast = (new Parser())->parse(new TokenStream($lexemes));

        return self::evaluateNode($ast, $constants);
    }

    protected static function evaluateNode($node, array $constants)
    {
        if ($node instanceof BinaryNode) {
            $left = self::evaluateNode($node->left, $constants);
            $rigth = self::evaluateNode($node->rigth, $constants);
            switch ($node->value) {
                // Booleans
                case 'or':
                case '||':
                    return $left || $rigth;
                case 'and':
                case '&&':
                    return $left && $rigth;

                    // Math
                case '+':
                    return $left + $rigth;
                case '-':
                    return $left - $rigth;
                case '*':
                    return $left * $rigth;
                case '/':
                    return $left / $rigth;
            }

            if ($left === null || $rigth === null) {
                return false;
            }

            switch ($node->value) {
                // Comparisons
                case '=':
                    return $left === $rigth;
                case '!=':
                    return $left !== $rigth;
                case '<':
                    return $left < $rigth;
                case '>':
                    return $left > $rigth;
                case '>=':
                    return $left >= $rigth;
                case '<=':
                    return $left <= $rigth;
            }

            throw new Exception('Unkown binary operator');
        } elseif ($node instanceof NameNode) {
            return isset($constants[$node->value]) ? $constants[$node->value] : null;
        } elseif ($node instanceof StringNode) {
            return $node->value;
        } elseif ($node instanceof NumberNode) {
            return self::parseNumber($node->value);
        } elseif ($node instanceof TernaryNode) {
            $condition = self::evaluateNode($node->condition, $constants);

            return self::evaluateNode($condition ? $node->ifBranch : $node->elseBranch, $constants);
        }

        throw new Exception('Unkown node type');
    }

    /**
     * Parse a string into a flot or int.
     */
    protected static function parseNumber(string $input): float|int
    {
        $input = ltrim($input, '0');
        if ($input === '') {
            return 0;
        }

        $input = str_replace(',', '.', $input);
        $number = floatval($input);

        return (float) ((int) $number) === $number ? (int) $number : $number;
    }
}
