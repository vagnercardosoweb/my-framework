<?php

require_once __DIR__.'/vendor/autoload.php';

$finder = PhpCsFixer\Finder::create()
    ->notPath(__DIR__.'/resources')
    ->notPath(__DIR__.'/storage')
    ->notPath(__DIR__.'/vendor')
    ->in(dirname(__DIR__))
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true)
;

return PhpCsFixer\Config::create()
    ->setUsingCache(false)
    ->setRules([
        '@PhpCsFixer' => true,
        '@Symfony' => true,
        'phpdoc_order' => false,
        'phpdoc_summary' => false,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => true,
    ])
    ->setFinder($finder)
;
