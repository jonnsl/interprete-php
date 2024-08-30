<?php

use PhpCsFixer\Finder;
use PhpCsFixer\Config;

$rules = [
    '@Symfony' => true,
    'yoda_style' => false,
    'no_empty_comment' => false,
    'no_empty_phpdoc' => false,
    'declare_strict_types' => true,
    'global_namespace_import' => ['import_classes' => true, 'import_constants' => false, 'import_functions' => false],
];

$finder = (new Finder())->in(__DIR__);

return (new Config())
    ->setFinder($finder)
    ->setRules($rules)
    ->setUsingCache(true);
