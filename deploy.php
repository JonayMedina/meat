<?php
namespace Deployer;

$TELEGRAM_TOKEN_HERE = "485201242:AAG8KbvaQZBEoqmEetwvH7o1e65xItYccdg";
$TELEGRAM_CHATID_HERE = -469107537;

require 'recipe/symfony4.php';
require 'recipe/telegram.php';
require 'recipe/sentry.php';

/** Project name */
set('application', 'Meat House');

/** Symfony shared dirs */
set('shared_dirs', ['var/log', 'public/uploads', 'public/media', 'public/assets', 'public/bundles', 'node_modules', 'config/jwt']);

/** Symfony shared files */
set('shared_files', ['.env.local']);

/** Symfony writable dirs */
set('writable_dirs', ['var/cache', 'var/log', 'public/uploads', 'public/media']);

/** Project repository */
set('repository', 'git@bitbucket.org:bakingthecookie/meathouse-ecommerce.git');

/** [Optional] Allocate tty for git clone. Default value is false. */
set('git_tty', true);

/** Set default Stage */
set('default_stage', 'staging');

/** Set deployer timeout */
set('default_timeout', 30000000000);

/** Set Telegram data */
set('telegram_token', $TELEGRAM_TOKEN_HERE);
set('telegram_chat_id', $TELEGRAM_CHATID_HERE);
set('telegram_text', '_{{user}}_ deploying `{{branch}}` to *{{target}}*');
set('telegram_success_text', '_{{user}}_ has correctly deployed the `{{branch}}` branch to *{{target}}*');

/** Set Sentry data */
set('sentry', [
    'organization' => 'praga-web-studio-jn',
    'projects' => [
        'meat-house'
    ],
    'token' => '9fc5435b49934c86a539c8002a3146762a72139530a94fd28873316a6fde14f1',
]);

set('keep_releases', 2);

/** Hosts */
host('staging')
    ->hostname('meathouse.tribalworldwide.gt')
    ->user('tribal')
    ->set('branch', 'develop')
    ->port(22)
    ->identityFile('~/pem/procasa-dev.pem')
    ->stage('staging')
    ->set('deploy_path', '/var/www/staging');

host('production')
    ->hostname('172.26.0.134')
    ->user('ubuntu')
    ->set('branch', 'production')
    ->port(22)
    ->identityFile('~/pem/meathouse.pem')
    ->stage('production')
    ->set('deploy_path', '/var/www/production');

/** Tasks */
task('build', function () {
    run('cd {{release_path}} && build');
});

/** [Optional] if deploy fails automatically unlock. */
after('deploy:failed', 'deploy:unlock');

/** Migrate database before symlink new release. */
before('deploy:symlink', 'database:migrate');

/** Notify via Telegram */
// after('success', 'telegram:notify:success');

/** Restart fpm */
task('reload:php-fpm', function () {
    run('sudo /etc/init.d/php-fpm restart'); // Using SysV Init scripts
});

/** Restart supervisor */
task('reload:php-fpm', function () {
    run('sudo /etc/init.d/supervisor restart'); // Using SysV Init scripts
});

/** Restart nginx */
task('reload:php-fpm', function () {
    run('sudo /etc/init.d/nginx restart'); // Using SysV Init scripts
});

/** Notify to sentry */
after('deploy', 'deploy:sentry');
