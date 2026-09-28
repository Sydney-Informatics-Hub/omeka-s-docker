<?php
/**
 * Update the Oidc module's configuration from the command line, without
 * going through the admin "Configure" web form.
 *
 * Usage:
 *   php set-oidc-config.php --config=config_file
 *
 * config_file is JSON with keys matching the values in Settings.php:
 * the oidc_roles_map should be a JSON object mapping oidc claims
 * to Omeka S roles
*/

use Oidc\Settings as S;


require __DIR__ . '/bootstrap.php';

$options = getopt('', [ 'config::']);

$app = \Omeka\Mvc\Application::init(
    require __DIR__ . '/application/config/application.config.php'
);
$services = $app->getServiceManager();

$config_str = file_get_contents($options['config']);

try {
    $config = json_decode($config_str, true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
    fwrite(STDERR, "JSON parse failed: " . $e->getMessage() . "\n");
    exit(1);
}

$settings = $services->get('Omeka\Settings');
$current = [];
foreach (\Oidc\Settings::KEYS as $key) {
    $current[$key] = $settings->get($key, \Oidc\Settings::DEFAULTS[$key] ?? null);
}
$data = array_merge($current, $config);


// ---------------------------------------------------------------------
// 4. Validate and apply, exactly as the config form does.
// ---------------------------------------------------------------------
try {
    $services->get(\Oidc\Service\Configurator::class)->applyValidated($data);
    echo "OIDC settings updated successfully.\n";
} catch (\Oidc\Exception\InvalidConfigurationException $e) {
    fwrite(STDERR, "Validation failed: " . $e->getMessage() . "\n");
    exit(1);
}
