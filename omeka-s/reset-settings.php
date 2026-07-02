#!/usr/bin/env php
<?php
/**
 * Reset Omeka S installation-level settings (title, admin email, etc.)
 * and/or the default site to deployment-specific values.
 *
 * WHY THIS APPROACH
 * ------------------
 * Global settings like "installation_title" and "default_site" aren't API
 * resources at all -- there's no /api/settings endpoint. They live in
 * Omeka's plain key/value `setting` table and are only ever read/written
 * through the `Omeka\Settings` service (see
 * application/src/Installation/Task/AddDefaultSettingsTask.php, which is
 * how the installer sets "installation_title" in the first place, and
 * application/src/Form/SettingForm.php, which defines "default_site" as
 * just a setting whose value is a site's numeric ID). So the same
 * bootstrap-and-call-the-real-service pattern from reset-admin.php applies
 * here too -- just a different service.
 *
 * Renaming the actual default *site* (its title/slug) is a normal Doctrine
 * entity update on Omeka\Entity\Site -- same mechanism reset-admin.php
 * uses for the User entity.
 *
 * Usage (run from the Omeka S root, alongside bootstrap.php):
 *   php reset-settings.php \
 *     --installation-title="My Institution Archive" \
 *     --administrator-email=admin@example.org \
 *     --site-title="My Institution Archive" \
 *     --site-slug=archive \
 *     --find-site-by-slug=build-site \
 *     [--time-zone=Australia/Sydney] [--locale=en_US]
 *
 * All flags are optional and independent -- pass only what you want to change.
 * --find-site-by-slug identifies the placeholder site created at build time
 * (same idea as --find-by-email in reset-admin.php); if omitted, the script
 * falls back to "the one site in this installation" and errors out loudly
 * if that assumption doesn't hold, rather than guessing.
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only\n");
    exit(1);
}

$options = getopt('', [
    'installation-title::',
    'administrator-email::',
    'time-zone::',
    'locale::',
    'site-title::',
    'site-slug::',
    'find-site-by-slug::',
]);

require __DIR__ . '/bootstrap.php';
$app = \Laminas\Mvc\Application::init(
    require __DIR__ . '/application/config/application.config.php'
);
$services = $app->getServiceManager();

/** @var \Omeka\Settings\Settings $settings */
$settings = $services->get('Omeka\Settings');

// --- Global installation settings -------------------------------------
$settingMap = [
    'installation-title' => 'installation_title',
    'administrator-email' => 'administrator_email',
    'time-zone' => 'time_zone',
    'locale' => 'locale',
];
foreach ($settingMap as $flag => $key) {
    if (!empty($options[$flag])) {
        $settings->set($key, $options[$flag]);
        echo "Set {$key} = {$options[$flag]}\n";
    }
}

// --- Default site: rename it and/or point default_site at it -----------
if (!empty($options['site-title']) || !empty($options['site-slug']) || !empty($options['find-site-by-slug'])) {
    /** @var \Doctrine\ORM\EntityManager $entityManager */
    $entityManager = $services->get('Omeka\EntityManager');
    $repo = $entityManager->getRepository('Omeka\Entity\Site');

    $site = null;
    if (!empty($options['find-site-by-slug'])) {
        $site = $repo->findOneBy(['slug' => $options['find-site-by-slug']]);
    }
    if (!$site && !empty($options['site-slug'])) {
        // Maybe already renamed on a previous boot.
        $site = $repo->findOneBy(['slug' => $options['site-slug']]);
    }
    if (!$site) {
        $sites = $repo->findAll();
        if (count($sites) !== 1) {
            fwrite(STDERR, sprintf(
                "Couldn't find the placeholder site, and there isn't exactly one site to assume (found %d). Refusing to guess; pass --find-site-by-slug.\n",
                count($sites)
            ));
            exit(1);
        }
        $site = $sites[0];
    }

    if (!empty($options['site-title'])) {
        $site->setTitle($options['site-title']);
    }
    if (!empty($options['site-slug'])) {
        $site->setSlug($options['site-slug']);
    }
    $entityManager->flush();

    echo "Site #{$site->getId()} is now titled '{$site->getTitle()}' (slug: {$site->getSlug()}).\n";
}

echo "Done.\n";
