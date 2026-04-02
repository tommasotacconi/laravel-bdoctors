<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__.'/app',
        __DIR__.'/bootstrap',
        __DIR__.'/config',
        __DIR__.'/database',
        __DIR__.'/routes',
    ]); // ⚠️ adjust to your project

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'control_structure_braces' => false,
    ])
    ->setFinder($finder);
