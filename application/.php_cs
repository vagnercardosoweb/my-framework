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
  ->setRiskyAllowed(true)
  ->setRules([
    '@PhpCsFixer' => true,
    '@PSR1' => true,
    '@PSR2' => true,
    '@Symfony' => true,
    'psr4' => true,
    'align_multiline_comment' => true, // psr-5
    'array_indentation' => true,
    'array_syntax' => ['syntax' => 'short'],
    'cast_spaces' => ['space' => 'none'],
    'concat_space' => ['spacing' => 'one'],
    'compact_nullable_typehint' => true,
    'declare_equal_normalize' => ['space' => 'single'],
    'general_phpdoc_annotation_remove' => [
      'annotations' => [
        // 'author',
        // 'package',
      ],
    ],
    'increment_style' => ['style' => 'post'],
    'list_syntax' => ['syntax' => 'long'],
    'no_short_echo_tag' => true,
    'phpdoc_to_comment' => false,
    'phpdoc_add_missing_param_annotation' => ['only_untyped' => false],
    'phpdoc_align' => true,
    'phpdoc_no_empty_return' => false,
    'phpdoc_order' => true, // psr-5
    'phpdoc_no_useless_inheritdoc' => false,
    'protected_to_private' => false,
    'ordered_imports' => [
      'sort_algorithm' => 'alpha',
      'imports_order' => ['class', 'const', 'function'],
    ],
    'phpdoc_summary' => false,
  ])
  ->setFinder($finder)
;
