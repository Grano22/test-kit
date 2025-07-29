<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'no_unused_imports' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_empty_comment' => true,
        'no_useless_return' => true,
        'no_superfluous_phpdoc_tags' => true,
        'single_quote' => true,
        'standardize_increment' => true,
        'cast_spaces' => ['space' => 'none'],
        'no_whitespace_in_blank_line' => true,
        'phpdoc_trim_consecutive_blank_line_separation' => true,
        'binary_operator_spaces' => ['default' => 'single_space'],
        'no_empty_phpdoc' => true,
        'heredoc_to_nowdoc' => true,
        'heredoc_indentation' => ['indentation' => 'same_as_start'],
        'phpdoc_no_access' => true,
        'type_declaration_spaces' => true,
        'method_chaining_indentation' => true,
        'no_blank_lines_after_phpdoc' => true,
        'phpdoc_trim' => true,
        'class_attributes_separation' => [
            'elements' => [
                'const' => 'none',
                'method' => 'one',
                'property' => 'none',
                'trait_import' => 'none'
            ]
        ],
        'yoda_style' => [
            'equal' => false,
            'identical' => false,
            'less_and_greater' => false
        ],
    ])
    ->setFinder($finder)
;