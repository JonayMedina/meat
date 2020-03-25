<?php
namespace Deployer;

$TELEGRAM_TOKEN_HERE = "485201242:AAG8KbvaQZBEoqmEetwvH7o1e65xItYccdg";
$TELEGRAM_CHATID_HERE = -469107537;

require 'recipe/symfony4.php';
require 'recipe/telegram.php';

/** Project name */
set('application', 'Meat House');

/** Symfony shared dirs */
set('shared_dirs', ['var/logs', 'public/uploads', 'public/media', 'public/assets', 'public/bundles', 'node_modules']);

/** Symfony shared files */
set('shared_files', ['.env.local']);

/** Symfony writable dirs */
set('writable_dirs', ['var/cache', 'var/logs', 'public/uploads', 'public/media']);

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

set('keep_releases', 2);

/** Hosts */
host('staging')
    ->hostname('172.105.152.192')
    ->user('tribal')
    ->set('branch', 'staging')
    ->port(22)
    ->identityFile('~/pem/procasa-dev.pem')
    ->stage('staging')
    ->set('deploy_path', '/var/www/staging');

/** Tasks */
task('build', function () {
    run('cd {{release_path}} && build');
});

/** [Optional] if deploy fails automatically unlock. */
after('deploy:failed', 'deploy:unlock');

/** Migrate database before symlink new release. */
before('deploy:symlink', 'database:migrate');

/** Notify via Telegram */
after('success', 'telegram:notify:success');
