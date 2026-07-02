#!/usr/bin/env php
<?php
/**
 * Reset the Omeka S global admin user's email, name and password to
 * deployment-specific values (e.g. sourced from Docker secrets).
 *
 * WHY THIS APPROACH
 * ------------------
 * Neither the REST API nor the PHP API (Omeka\ApiManager) ever exposes a
 * "password" field for users — Omeka S's own installer works around this
 * by loading the User entity straight out of Doctrine and calling
 * setPassword() on it directly (see
 * application/src/Installation/Task/CreateFirstUserTask.php in omeka-s).
 * This script does the same thing for email/name/password, so it uses
 * exactly the same code path Omeka S itself uses, hashed the same way
 * (password_hash() with PASSWORD_DEFAULT), no raw SQL involved, and no
 * API key / login session required (that's the other blocker: you can't
 * get a global-admin API key until an admin already exists with known
 * credentials — chicken and egg).
 *
 * INTENDED FLOW
 * -------------
 * 1. At image build time, your existing install step creates a throwaway
 *    global admin (e.g. build-admin@internal.invalid / random password).
 * 2. At container start, this script runs once, finds that placeholder
 *    user, and overwrites it with the real values pulled from Docker
 *    secrets. Safe to run on every container start: if the placeholder
 *    is already gone and the target email already exists, it no-ops.
 *
 * USAGE (run from the Omeka S root, alongside bootstrap.php)
 *   php reset-admin.php \
 *     --email=admin@example.org \
 *     --name="Site Admin" \
 *     --password="correct-horse-battery-staple" \
 *     --find-by-email=build-admin@internal.invalid \
 *     [--role=global_admin]
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only\n");
    exit(1);
}

$options = getopt('', ['email:', 'name:', 'password:', 'find-by-email::', 'role::']);
foreach (['email', 'name', 'password'] as $required) {
    if (empty($options[$required])) {
        fwrite(STDERR, "Missing --$required\n");
        exit(1);
    }
}

$targetEmail = $options['email'];
$targetName = $options['name'];
$targetPassword = $options['password'];
$placeholderEmail = $options['find-by-email'] ?? null;
$role = $options['role'] ?? 'global_admin';

require __DIR__ . '/bootstrap.php';
$app = \Laminas\Mvc\Application::init(
    require __DIR__ . '/application/config/application.config.php'
);
$services = $app->getServiceManager();

/** @var \Doctrine\ORM\EntityManager $entityManager */
$entityManager = $services->get('Omeka\EntityManager');
$repo = $entityManager->getRepository('Omeka\Entity\User');

$user = null;

if ($placeholderEmail !== null) {
    $user = $repo->findOneBy(['email' => $placeholderEmail]);
}

if (!$user) {
    // Placeholder not found (or none given). Two possibilities:
    // already reset on a previous boot, or we need to fall back to
    // "the one global admin" heuristic.
    $already = $repo->findOneBy(['email' => $targetEmail]);
    if ($already) {
        echo "Admin already has email {$targetEmail}; nothing to do.\n";
        exit(0);
    }

    $admins = $repo->findBy(['role' => $role]);
    if (count($admins) !== 1) {
        fwrite(STDERR, sprintf(
            "Couldn't find placeholder admin '%s', target email '%s' doesn't exist yet, ".
            "and there isn't exactly one '%s' user to assume (found %d). Refusing to guess.\n",
            $placeholderEmail ?? '(none given)',
            $targetEmail,
            $role,
            count($admins)
        ));
        exit(1);
    }
    $user = $admins[0];
}

$user->setEmail($targetEmail);
$user->setName($targetName);
$user->setPassword($targetPassword); // hashes internally via password_hash()
$user->setRole($role);
$user->setIsActive(true);

$entityManager->flush();

echo "Admin user #{$user->getId()} reset to {$targetEmail}.\n";
