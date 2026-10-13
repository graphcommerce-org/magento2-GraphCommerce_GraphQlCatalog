<?php
/**
 * Unit test bootstrap: the Magento vendor autoloader of the installation that holds the package
 * (MAGENTO_ROOT, else the project two levels up), plus this package's own PSR-4 map.
 */
declare(strict_types=1);

$root = getenv('MAGENTO_ROOT') ?: null;
foreach ([$root, dirname(__DIR__, 4)] as $candidate) {
    if ($candidate && is_file($candidate . '/vendor/autoload.php')) {
        $loader = require $candidate . '/vendor/autoload.php';
        break;
    }
}
if (!isset($loader)) {
    fwrite(STDERR, "No Magento installation found: set MAGENTO_ROOT\n");
    exit(1);
}
$package = json_decode((string)file_get_contents(dirname(__DIR__, 2) . '/composer.json'), true);
foreach ($package['autoload']['psr-4'] as $prefix => $path) {
    $loader->addPsr4($prefix, dirname(__DIR__, 2) . '/' . $path, true);
}
// The interface the DI compile generates: declared here with core's shape when no generated code exists.
if (!interface_exists(\Magento\GraphQl\Model\Query\ContextExtensionInterface::class)) {
    require __DIR__ . '/generated/ContextExtensionInterface.php';
}
