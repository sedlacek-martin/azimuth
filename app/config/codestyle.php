<?php

// https://mlocati.github.io/php-cs-fixer-configurator/#version:2.18
return [
    'directory' => [
        0 => './src',
    ],
    'rules' => [
        '@PSR1' => true,
        '@PSR2' => true,
        'array_syntax' => [
            'syntax' => 'short',
        ],
        'binary_operator_spaces' => [
            'align_equals' => false,
            'align_double_arrow' => false,
        ],
        'cast_spaces' => true,
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
        'concat_space' => [
            'spacing' => 'one',
        ],
        'linebreak_after_opening_tag' => true,
        'no_blank_lines_after_class_opening' => true,
        'no_blank_lines_after_phpdoc' => true,
        'no_extra_blank_lines' => true,
        'no_trailing_comma_in_singleline_array' => true,
        'no_whitespace_in_blank_line' => true,
        'no_spaces_around_offset' => true,
        'no_unused_imports' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'no_whitespace_before_comma_in_array' => true,
        'normalize_index_brace' => true,
        'phpdoc_indent' => true,
        'phpdoc_to_comment' => true,
        'phpdoc_trim' => true,
        'single_quote' => true,
        'ternary_to_null_coalescing' => true,
        'trailing_comma_in_multiline_array' => true,
        'trim_array_spaces' => true,
        'method_argument_space' => [
            'ensure_fully_multiline' => false,
        ],
        'no_break_comment' => false,
        'blank_line_before_statement' => true,
        'single_blank_line_before_namespace' => true,
        'fully_qualified_strict_types' => true,
        'ordered_imports' => true,
        'magic_method_casing' => true,
        'phpdoc_line_span' => [
            'method' => 'multi',
            'const' => 'single',
            'property' => 'single',
        ],
        'class_attributes_separation' => [
            'elements' => [
                0 => 'method',
                1 => 'property',
            ],
        ],
    ],
];
