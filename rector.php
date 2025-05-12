<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;

return RectorConfig::configure()
    ->withRootFiles()
    ->withPhpSets()
    ->withRules([AddOverrideAttributeToOverriddenMethodsRector::class])
    ->withPreparedSets(
        //deadCode: true,
        //codeQuality: true,
        //typeDeclarations: true,
        //typeDeclarations: true,
        //privatization: true,
        //instanceOf: true,
        //strictBooleans: true,
    );
