<?php

declare(strict_types=1);

use Rector\Config\Level\CodeQualityLevel;
use Rector\Config\Level\DeadCodeLevel;
use Rector\Config\Level\TypeDeclarationLevel;
use Rector\Config\RectorConfig;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;

$typeCoverageLevel = count(TypeDeclarationLevel::RULES);
$deadCodeLevel = count(DeadCodeLevel::RULES);
$codeQualityLevel = count(CodeQualityLevel::RULES);

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/bootstrap/app.php',
        __DIR__ . '/database',
        __DIR__ . '/public',
    ])
    ->withCache(__DIR__ . '/storage/framework/rector')
    ->withSkip([
        AddOverrideAttributeToOverriddenMethodsRector::class,
    ])
    ->withPhpSets()
    ->withTypeCoverageLevel($typeCoverageLevel)
    ->withDeadCodeLevel($deadCodeLevel)
    ->withCodeQualityLevel($codeQualityLevel);
