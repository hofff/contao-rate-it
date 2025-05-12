<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector;

return RectorConfig::configure()
    ->withRootFiles()
    ->withPhpSets()
    ->withRules([
        DeclareStrictTypesRector::class,
        AddOverrideAttributeToOverriddenMethodsRector::class,
    ])
    ->withPreparedSets(
        //deadCode: true,
        //codeQuality: true,
        //typeDeclarations: true,
        //typeDeclarations: true,
        //privatization: true,
        //instanceOf: true,
        //strictBooleans: true,
    );
