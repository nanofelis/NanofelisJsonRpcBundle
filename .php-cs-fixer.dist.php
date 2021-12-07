<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setRules(array(
        '@Symfony'             => true,
        '@Symfony:risky'       => true,
        'declare_strict_types' => true,
        'array_syntax'         => ['syntax' => 'short'],
        'protected_to_private' => false,
    ))
    ->setRiskyAllowed(true)
    ->setFinder($finder);
