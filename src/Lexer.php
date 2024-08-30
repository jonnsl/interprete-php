<?php

declare(strict_types=1);

namespace Interprete;

use Exception;
use Generator;

class Lexer
{
    /**
     * A generator function wich produces a stream of lexemes for a given input.
     */
    public static function tokenize(string $input): Generator
    {
        $input = str_replace(["\t", "\r", "\n"], ' ', $input);
        $length = strlen($input);
        $offset = 0;

        while ($offset < $length) {
            if ($input[$offset] === ' ') {
                ++$offset;
                continue;
            }

            if (preg_match('/"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"/As', $input, $matches, 0, $offset)) {
                $offset += strlen($matches[0]);
                yield new Token(Token::STRING, stripcslashes(substr($matches[0], 1, -1)));
            } elseif (preg_match('/AND|OR|&&|\\|\\|/A', $input, $matches, 0, $offset)) {
                $offset += strlen($matches[0]);
                yield new Token(Token::BOOLEAN_OPERATOR, $matches[0]);
            } elseif (preg_match('/[_a-zA-Z][_a-zA-Z0-9]*/A', $input, $matches, 0, $offset)) {
                $offset += strlen($matches[0]);
                yield new Token(Token::IDENTIFIER, $matches[0]);
            } elseif (preg_match('/-?[0-9]*,[0-9]+|-?[0-9]+/A', $input, $matches, 0, $offset)) {
                $offset += strlen($matches[0]);
                yield new Token(Token::NUMBER, $matches[0]);
            } elseif (preg_match('/\\+|-|\\*|\//A', $input, $matches, 0, $offset)) {
                $offset += strlen($matches[0]);
                yield new Token(Token::MATH_OPERATOR, $matches[0]);
            } elseif (preg_match('/<=|<|>=|>|=|!=|<>/A', $input, $matches, 0, $offset)) {
                $offset += strlen($matches[0]);
                yield new Token(Token::COMPARISON_OPERATOR, $matches[0]);
            } elseif ($input[$offset] === '(') {
                ++$offset;
                yield new Token(Token::OPEN_PAREN);
            } elseif ($input[$offset] === ')') {
                ++$offset;
                yield new Token(Token::CLOSE_PAREN);
            } elseif ($input[$offset] === '?') {
                ++$offset;
                yield new Token(Token::QUESTION_MARK);
            } elseif ($input[$offset] === ':') {
                ++$offset;
                yield new Token(Token::COLON);
            } else {
                throw new Exception('Unexpected "'.substr($input, $offset).'" at position: '.$offset);
            }
        }
    }
}
