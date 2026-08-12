<?php
/**
 * Automatically register dependent standards for PHPCS.
 */

use Composer\InstalledVersions;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Util\Standards;

$autoloaders = [
    __DIR__ . '/vendor/autoload.php', // standalone development of the standard
    __DIR__ . '/../../autoload.php',  // installed in a project: vendor/epiphyt/coding-standard/../../autoload.php = vendor/autoload.php
];

foreach ($autoloaders as $autoloader) {
    if (file_exists($autoloader)) {
        require_once $autoloader;
        break;
    }
}

/**
 * Get the absolute path of an installed Composer package.
 *
 * Resolves the path independently of the current working directory, no matter
 * whether the standard is developed standalone (dependencies live in its own
 * vendor directory), installed as a dependency of a project (dependencies are
 * siblings inside the project's vendor directory) or installed globally.
 *
 * @param string $package Package name, e.g. "slevomat/coding-standard"
 * @return string Absolute path to the package, or an empty string if not found
 */
$getPackagePath = static function ($package) {
    $candidates = [];

    if (class_exists(InstalledVersions::class) && InstalledVersions::isInstalled($package)) {
        $candidates[] = (string) InstalledVersions::getInstallPath($package);
    }

    $candidates[] = __DIR__ . '/vendor/' . $package; // standalone development of the standard
    $candidates[] = dirname(__DIR__, 2) . '/' . $package; // installed in a project

    foreach ($candidates as $candidate) {
        if ($candidate !== '' && is_dir($candidate)) {
            return (string) realpath($candidate);
        }
    }

    return '';
};

/**
 * Dependent standards, mapped to one of the standards they provide.
 *
 * The standard name is used to check whether the project already provides it,
 * in which case the project's version takes precedence.
 */
$dependencies = [
    'phpcompatibility/php-compatibility' => 'PHPCompatibility',
    'phpcompatibility/phpcompatibility-paragonie' => 'PHPCompatibilityParagonieRandomCompat',
    'phpcompatibility/phpcompatibility-wp' => 'PHPCompatibilityWP',
    'phpcsstandards/phpcsextra' => 'Universal',
    'phpcsstandards/phpcsutils' => 'PHPCSUtils',
    'slevomat/coding-standard' => 'SlevomatCodingStandard',
    'wp-coding-standards/wpcs' => 'WordPress',
];

$installed = Config::getConfigData('installed_paths');

if (!is_string($installed)) {
    $installed = '';
}

$paths = array_filter(explode(',', $installed));
$paths[] = __DIR__;
$installedStandards = Standards::getInstalledStandardDetails(true);

foreach ($dependencies as $package => $standard) {
    // Only add it if it isn't already provided by the project.
    if (isset($installedStandards[$standard])) {
        continue;
    }

    $path = $getPackagePath($package);

    if ($path !== '') {
        $paths[] = $path;
    }
}

$paths = array_unique(array_filter($paths));

if (!empty($paths)) {
    Config::setConfigData('installed_paths', implode(',', $paths), true);
}
