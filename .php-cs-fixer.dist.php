<?php

$finder = (new PhpCsFixer\Finder())
    ->in([
        __DIR__.'/src',
        __DIR__.'/tests',
        __DIR__.'/examples',
    ]);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        'function_declaration' => [
            'closure_function_spacing' => 'none',
            'closure_fn_spacing' => 'none',
        ],
        'ordered_imports' => ['imports_order' => ['class', 'function', 'const'], 'sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        'blank_line_between_import_groups' => false,
        'no_extra_blank_lines' => ['tokens' => ['use']],
        'native_function_casing' => true,
        'native_constant_invocation' => ['strict' => false],
        'native_function_invocation' => ['include' => ['@all'], 'scope' => 'namespaced', 'strict' => true],
        'ordered_class_elements' => ['order' => ['use_trait', 'public', 'protected', 'private']],
        'single_line_empty_body' => true,
    ])
    ->setFinder($finder);
