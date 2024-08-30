# Interprete-php

Simple expression evaluator in php

* Math operators + - * /
* Boolean operators (or || and &&)
* Comparisons (= != < > <= >=)

## Installation

```bash
composer install --save interprete
```

## Examples

```php
use Interprete\Interpreter;

$input = 'variable < 10';
$result = Interpreter::evaluate($input, ['variable' => '10']);
$this->assertEquals(false, $result);
```

## License

This project is licensed under the MIT License - see [LICENSE](LICENSE) for details.
