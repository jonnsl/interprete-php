<?php

declare(strict_types=1);

namespace Tests\Unit;

use Interprete\Interpreter;
use Interprete\Lexer;
use Interprete\Nodes\BinaryNode;
use Interprete\Nodes\NameNode;
use Interprete\Nodes\NumberNode;
use Interprete\Nodes\StringNode;
use Interprete\Nodes\TernaryNode;
use Interprete\Parser;
use Interprete\Token;
use Interprete\TokenStream;
use PHPUnit\Framework\TestCase;

class InterpreterTest extends TestCase
{
    public function testInterpreter()
    {
        $input = 'variable < 10';
        $result = Interpreter::evaluate($input, ['variable' => '10']);
        $this->assertEquals(false, $result);

        $input = 'variable < 10';
        $result = Interpreter::evaluate($input, ['variable' => '9']);
        $this->assertEquals(true, $result);

        $input = 'variable < 10';
        $result = Interpreter::evaluate($input, ['variable' => null]);
        $this->assertEquals(false, $result);

        $input = 'variable < 10';
        $result = Interpreter::evaluate($input, ['variable' => '']);
        $this->assertEquals(true, $result);

        $input = 'variable = (a ? b + 1 : b - 1)';
        $result = Interpreter::evaluate($input, ['variable' => 1, 'a' => true, 'b' => 0]);
        $this->assertEquals(true, $result);

        $input = 'variable = (a ? b + 1 : b - 1)';
        $result = Interpreter::evaluate($input, ['variable' => 1, 'a' => false, 'b' => 0]);
        $this->assertEquals(false, $result);
    }

    public function testTokenStream()
    {
        $input = 'variable = 0';
        $lexemes = Lexer::tokenize($input);
        $stream = new TokenStream($lexemes);

        $this->assertEquals(new Token(Token::IDENTIFIER, 'variable'), $stream->current());
        $stream->next();
        $this->assertEquals(new Token(Token::COMPARISON_OPERATOR, '='), $stream->current());
        $stream->next();
        $this->assertEquals(new Token(Token::NUMBER, '0'), $stream->current());
        $stream->next();
        $this->assertTrue($stream->isEOF());
    }

    public function testParser()
    {
        $input = 'variable = "string"';
        $lexemes = Lexer::tokenize($input);
        $ast = (new Parser())->parse(new TokenStream($lexemes));
        $this->assertEquals(new BinaryNode('=', new NameNode('variable'), new StringNode('string')), $ast);

        $input = 'variable = (a ? b + 1 : b - 1)';
        $lexemes = Lexer::tokenize($input);
        $ast = (new Parser())->parse(new TokenStream($lexemes));
        $ifBranch = new BinaryNode('+', new NameNode('b'), new NumberNode('1'));
        $elseBranch = new BinaryNode('-', new NameNode('b'), new NumberNode('1'));
        $ternary = new TernaryNode(new NameNode('a'), $ifBranch, $elseBranch);
        $this->assertEquals(new BinaryNode('=', new NameNode('variable'), $ternary), $ast);
    }

    public function testLexer()
    {
        $input = 'foo_bar = "string"';
        $lexemes = iterator_to_array(Lexer::tokenize($input));
        $this->assertEquals([
            new Token(Token::IDENTIFIER, 'foo_bar'),
            new Token(Token::COMPARISON_OPERATOR, '='),
            new Token(Token::STRING, 'string'),
        ], $lexemes);

        $input = 'variable = "string"';
        $lexemes = iterator_to_array(Lexer::tokenize($input));
        $this->assertEquals([
            new Token(Token::IDENTIFIER, 'variable'),
            new Token(Token::COMPARISON_OPERATOR, '='),
            new Token(Token::STRING, 'string'),
        ], $lexemes);

        $input = 'variable = ""';
        $lexemes = iterator_to_array(Lexer::tokenize($input));
        $this->assertEquals([
            new Token(Token::IDENTIFIER, 'variable'),
            new Token(Token::COMPARISON_OPERATOR, '='),
            new Token(Token::STRING, ''),
        ], $lexemes);

        $input = 'variable = "string with spaces"';
        $lexemes = iterator_to_array(Lexer::tokenize($input));
        $this->assertEquals([
            new Token(Token::IDENTIFIER, 'variable'),
            new Token(Token::COMPARISON_OPERATOR, '='),
            new Token(Token::STRING, 'string with spaces'),
        ], $lexemes);

        $input = 'variable = "string with \\"quotes\\" inside"';
        $lexemes = iterator_to_array(Lexer::tokenize($input));
        $this->assertEquals([
            new Token(Token::IDENTIFIER, 'variable'),
            new Token(Token::COMPARISON_OPERATOR, '='),
            new Token(Token::STRING, 'string with "quotes" inside'),
        ], $lexemes);

        $input = 'variable = "\\\\"';
        $lexemes = iterator_to_array(Lexer::tokenize($input));
        $this->assertEquals([
            new Token(Token::IDENTIFIER, 'variable'),
            new Token(Token::COMPARISON_OPERATOR, '='),
            new Token(Token::STRING, '\\'),
        ], $lexemes);

        $input = 'variable = 0';
        $lexemes = iterator_to_array(Lexer::tokenize($input));
        $this->assertEquals([
            new Token(Token::IDENTIFIER, 'variable'),
            new Token(Token::COMPARISON_OPERATOR, '='),
            new Token(Token::NUMBER, '0'),
        ], $lexemes);

        $input = 'variable + 0';
        $lexemes = iterator_to_array(Lexer::tokenize($input));
        $this->assertEquals([
            new Token(Token::IDENTIFIER, 'variable'),
            new Token(Token::MATH_OPERATOR, '+'),
            new Token(Token::NUMBER, '0'),
        ], $lexemes);

        $input = 'variable - 0';
        $lexemes = iterator_to_array(Lexer::tokenize($input));
        $this->assertEquals([
            new Token(Token::IDENTIFIER, 'variable'),
            new Token(Token::MATH_OPERATOR, '-'),
            new Token(Token::NUMBER, '0'),
        ], $lexemes);

        $input = 'variable * 0';
        $lexemes = iterator_to_array(Lexer::tokenize($input));
        $this->assertEquals([
            new Token(Token::IDENTIFIER, 'variable'),
            new Token(Token::MATH_OPERATOR, '*'),
            new Token(Token::NUMBER, '0'),
        ], $lexemes);

        $input = 'variable / 0';
        $lexemes = iterator_to_array(Lexer::tokenize($input));
        $this->assertEquals([
            new Token(Token::IDENTIFIER, 'variable'),
            new Token(Token::MATH_OPERATOR, '/'),
            new Token(Token::NUMBER, '0'),
        ], $lexemes);

        $input = 'variable >= 0';
        $lexemes = iterator_to_array(Lexer::tokenize($input));
        $this->assertEquals([
            new Token(Token::IDENTIFIER, 'variable'),
            new Token(Token::COMPARISON_OPERATOR, '>='),
            new Token(Token::NUMBER, '0'),
        ], $lexemes);

        $input = '0';
        $lexemes = iterator_to_array(Lexer::tokenize($input));
        $this->assertEquals([
            new Token(Token::NUMBER, '0'),
        ], $lexemes);

        $input = '0,0';
        $lexemes = iterator_to_array(Lexer::tokenize($input));
        $this->assertEquals([
            new Token(Token::NUMBER, '0,0'),
        ], $lexemes);

        $input = 'var var var === 0';
        $lexemes = iterator_to_array(Lexer::tokenize($input));
        $this->assertEquals([
            new Token(Token::IDENTIFIER, 'var'),
            new Token(Token::IDENTIFIER, 'var'),
            new Token(Token::IDENTIFIER, 'var'),
            new Token(Token::COMPARISON_OPERATOR, '='),
            new Token(Token::COMPARISON_OPERATOR, '='),
            new Token(Token::COMPARISON_OPERATOR, '='),
            new Token(Token::NUMBER, '0'),
        ], $lexemes);

        $input = 'variable = -123';
        $lexemes = iterator_to_array(Lexer::tokenize($input));
        $this->assertEquals([
            new Token(Token::IDENTIFIER, 'variable'),
            new Token(Token::COMPARISON_OPERATOR, '='),
            new Token(Token::NUMBER, '-123'),
        ], $lexemes);

        $input = '(1 + 3) * variable = -123';
        $lexemes = iterator_to_array(Lexer::tokenize($input));
        $this->assertEquals([
            new Token(Token::OPEN_PAREN),
            new Token(Token::NUMBER, '1'),
            new Token(Token::MATH_OPERATOR, '+'),
            new Token(Token::NUMBER, '3'),
            new Token(Token::CLOSE_PAREN),
            new Token(Token::MATH_OPERATOR, '*'),
            new Token(Token::IDENTIFIER, 'variable'),
            new Token(Token::COMPARISON_OPERATOR, '='),
            new Token(Token::NUMBER, '-123'),
        ], $lexemes);

        $input = 'a > 0 OR b < 1';
        $lexemes = iterator_to_array(Lexer::tokenize($input));
        $this->assertEquals([
            new Token(Token::IDENTIFIER, 'a'),
            new Token(Token::COMPARISON_OPERATOR, '>'),
            new Token(Token::NUMBER, '0'),
            new Token(Token::BOOLEAN_OPERATOR, 'OR'),
            new Token(Token::IDENTIFIER, 'b'),
            new Token(Token::COMPARISON_OPERATOR, '<'),
            new Token(Token::NUMBER, '1'),
        ], $lexemes);

        $input = 'a > 0 AND b < 1';
        $lexemes = iterator_to_array(Lexer::tokenize($input));
        $this->assertEquals([
            new Token(Token::IDENTIFIER, 'a'),
            new Token(Token::COMPARISON_OPERATOR, '>'),
            new Token(Token::NUMBER, '0'),
            new Token(Token::BOOLEAN_OPERATOR, 'AND'),
            new Token(Token::IDENTIFIER, 'b'),
            new Token(Token::COMPARISON_OPERATOR, '<'),
            new Token(Token::NUMBER, '1'),
        ], $lexemes);

        $input = 'a ? b : c';
        $lexemes = iterator_to_array(Lexer::tokenize($input));
        $this->assertEquals([
            new Token(Token::IDENTIFIER, 'a'),
            new Token(Token::QUESTION_MARK),
            new Token(Token::IDENTIFIER, 'b'),
            new Token(Token::COLON),
            new Token(Token::IDENTIFIER, 'c'),
        ], $lexemes);
    }
}
