<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in([__DIR__.'/src', __DIR__.'/tests', __DIR__.'/features/bootstrap'])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'declare_strict_types' => true,
        'final_class' => true,
        // Test names read as sentences: it_parses_euros_into_cents_without_floats.
        'php_unit_method_casing' => ['case' => 'snake_case'],
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
