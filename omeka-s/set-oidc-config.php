<?php
/**
 * Update the Oidc module's configuration from the command line, without
 * going through the admin "Configure" web form.
 *
 * Usage:
 *   php set-oidc-config.php
 *
 * Run this from anywhere; it locates the Omeka S install via OMEKA_PATH
 * below. Adjust that path for your server.
 *
 * WHY THIS APPROACH (rather than calling Omeka\Settings->set() directly):
 * Module::handleConfigForm() does not write raw POST values into settings.
 * It converts form data to a settings array via ConfigForm::formDataToSettings(),
 * then passes that array to Oidc\Service\Configurator::applyValidated(),
 * which validates it and throws InvalidConfigurationException on bad input.
 * Calling Configurator::applyValidated() here reuses that same validation,
 * so you can't accidentally save a broken OIDC config (e.g. missing issuer
 * URL) the way you could with a raw Settings->set() call.
 */

use Oidc\Settings as S;


require __DIR__ . '/bootstrap.php';

$options = getopt('', [
    'base-url::',
    'idp-discovery-url::',
    'client-id::',
    'client-secret::',
    'access-guard-claim::',
    'access-guard-value::',
]);

// Application::init() builds and bootstraps the service container without
// dispatching an HTTP request (that only happens if you call ->run()).

$app = \Omeka\Mvc\Application::init(
    require __DIR__ . '/application/config/application.config.php'
);
$services = $app->getServiceManager();

// command-line options mapped to settings values

$settingMap = [
    'base-url'           => S::BASE_URL,
    'idp-discovery-url'  => S::IDP_DISCOVERY_URL,
    'client-id'          => S::CLIENT_ID,
    'client-secret'      => S::CLIENT_SECRET,
    'access-guard-claim' => S::ACCESS_GUARD_CLAIM,
    'access-guard-value' => S::ACCESS_GUARD_VALUE,
];

// Make $targetSettings with the new values

$targetSettings = [];

foreach ($settingMap as $flag => $key) {
    if (!empty($options[$flag])) {
        $targetSettings[$key] = $options[$flag];
    }
}

$settings = $services->get('Omeka\Settings');
$current = [];
foreach (\Oidc\Settings::KEYS as $key) {
    $current[$key] = $settings->get($key, \Oidc\Settings::DEFAULTS[$key] ?? null);
}
$data = array_merge($current, $targetSettings);



// ---------------------------------------------------------------------
// 4. Validate and apply, exactly as the config form does.
// ---------------------------------------------------------------------
try {
    $services->get(\Oidc\Service\Configurator::class)->applyValidated($data);
    echo "OIDC settings updated successfully.\n";
} catch (\Oidc\Exception\InvalidConfigurationException $e) {
    fwrite(STDERR, "Validation failed: " . $e->getMessage() . "\n");
    fwrite(STDERR, $e->getTraceAsString());
    exit(1);
}
