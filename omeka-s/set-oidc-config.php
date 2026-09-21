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

// Application::init() builds and bootstraps the service container without
// dispatching an HTTP request (that only happens if you call ->run()).
$app = \Omeka\Mvc\Application::init(
    require __DIR__ . '/application/config/application.config.php'
);
$services = $app->getServiceManager();

// ---------------------------------------------------------------------
// 2. The settings you want to change, keyed exactly as Oidc\Settings::KEYS
//    expects them (i.e. the same shape ConfigForm::formDataToSettings()
//    produces — check that class if you're not sure of a key name or the
//    expected value type for a given field).
// ---------------------------------------------------------------------
$desiredSettings = [
    S::BASE_URL           => 'https://sample.curated-collections.cloud.edu.au',
    S::IDP_DISCOVERY_URL        => 'https://foo.bar.registry/oidc',
    S::CLIENT_ID                => 'omeka-s-client-id',
    S::CLIENT_SECRET            => 'seekritt',
    S::ACCESS_GUARD_CLAIM      =>  'isMemberOf',
    S::ACCESS_GUARD_VALUE      =>  'the_groop'
];

// ---------------------------------------------------------------------
// 3. Merge with existing settings so keys you don't mention are preserved,
//    matching what happens when you save the form with only some fields
//    changed.
// ---------------------------------------------------------------------
$settings = $services->get('Omeka\Settings');
$current = [];
foreach (\Oidc\Settings::KEYS as $key) {
    $current[$key] = $settings->get($key, \Oidc\Settings::DEFAULTS[$key] ?? null);
}
$data = array_merge($current, $desiredSettings);

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
