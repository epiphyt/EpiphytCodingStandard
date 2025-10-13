<?php
/**
 * Automatically register dependent standards for PHPCS.
 */

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Util\Standards;

$paths = [__DIR__];
$phpCompatibilityPath = __DIR__ . '/vendor/phpcompatibility/php-compatibility';

if (is_dir($phpCompatibilityPath) && !in_array('PHPCompatibility', (Standards::getInstalledStandardDetails()['name'] ?? []))) {
    $paths[] = $phpCompatibilityPath;
}

$phpCompatibilityParagoniePath = __DIR__ . '/vendor/phpcompatibility/phpcompatibility-paragonie';
$phpCompatibilityWpPath = __DIR__ . '/vendor/phpcompatibility/phpcompatibility-wp';

if (is_dir($phpCompatibilityWpPath) && is_dir($phpCompatibilityParagoniePath) && !in_array('PHPCompatibilityWP', (Standards::getInstalledStandardDetails()['name'] ?? []))) {
    $paths[] = $phpCompatibilityParagoniePath;
    $paths[] = $phpCompatibilityWpPath;
}

$phpcsextraPath = __DIR__ . '/vendor/phpcsstandards/phpcsextra';

if (is_dir($phpcsextraPath) && !in_array('PHPCSExtra', (Standards::getInstalledStandardDetails()['name'] ?? []))) {
    $paths[] = $phpcsextraPath;
}

$phpcsutilsPath = __DIR__ . '/vendor/phpcsstandards/phpcsutils';

if (is_dir($phpcsutilsPath) && !in_array('PHPCSUtils', (Standards::getInstalledStandardDetails()['name'] ?? []))) {
    $paths[] = $phpcsutilsPath;
}

$wpcsPath = __DIR__ . '/vendor/wp-coding-standards/wpcs';

// Only add it if it exists and isn't already loaded.
if (is_dir($wpcsPath) && !in_array('WordPress', (Standards::getInstalledStandardDetails()['name'] ?? []))) {
    $paths[] = $wpcsPath;
}

if (!empty($paths)) {
    Config::setConfigData('installed_paths', implode(',', $paths), true);
}
