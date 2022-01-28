<?php
namespace Deployer;

use function Clue\StreamFilter\fun;

require 'recipe/common.php';

const HOSTNAME = 'www192.your-server.de';

// Project name
set('application', 'Azimuth');

// Project repository
set('repository', 'https://github.com/sedlacek-martin/azimuth.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', false);

set('default_timeout', 2000);
set('http_user', 'azimutu');

// Shared files/dirs between deploys
add('shared_files', ['app/config/parameters.yml', 'web/.htaccess']);
add('shared_dirs', ['var/logs', 'var/sessions', 'web/media', 'web/uploads', 'web/json', 'app/Resources/translations']);

set('clear_paths', ['.github/', 'doc/', '.gitignore', 'docker/']);

// Writable dirs by web server
//add('writable_dirs', []);

set('allow_anonymous_stats', false);

createHost('prod');
createHost('beta');

// Hosts
function createHost($env) {

    host($env)
        ->hostname(HOSTNAME)
        ->user('azimutu')
        ->port(222)
        ->forwardAgent(true)
        ->multiplexing(false)
        ->set('deploy_path', "/usr/home/azimutu/public_html/{$env}")
        ->set('keep_releases', 3);

// Tasks
    task( "deploy_$env", [
        'deploy:info',
        'deploy:prepare',
        'deploy:lock',
        'deploy:release',
        'deploy:update_code',
        'deploy:shared',
        "index_file_$env",
        "composer_$env",
        "app_version_$env",
        "assets_$env",
        "translations_$env",
        "migration_$env",
        'deploy:writable',
        'deploy:symlink',
        'deploy:unlock',
        'cleanup',
        'success'
    ]);

    task("index_file_$env", function () use ($env) {
        run("mv /usr/home/azimutu/public_html/{$env}/release/web/app_{$env}.php /usr/home/azimutu/public_html/{$env}/release/web/app.php");
        run("rm /usr/home/azimutu/public_html/{$env}/release/web/app_*");
    });

    task("composer_$env", function () use ($env) {
        cd('{{release_path}}');
        run("php /usr/home/azimutu/public_html/tools/composer.phar install --no-interaction -d /usr/home/azimutu/public_html/{$env}/release");
        run("php /usr/home/azimutu/public_html/tools/composer.phar dump-autoload --optimize --classmap-authoritative --no-interaction -d /usr/home/azimutu/public_html/{$env}/release");
    });

    task("app_version_$env", function () use ($env) {
        cd("/usr/home/azimutu/public_html/{$env}/release/app/config");
        run('current_date_time="`date +%Y%m%d%H%M%S`" && printf "parameters: \n  application_version: $current_date_time" > app_version.yml');
    });

    task("assets_$env", function () use ($env) {
        run("/usr/home/azimutu/public_html/{$env}/release/bin/console assets:install --symlink web --env={$env}");
        run("/usr/home/azimutu/public_html/{$env}/release/bin/console ckeditor:install");
        run("/usr/home/azimutu/public_html/{$env}/release/bin/console assetic:dump --env={$env}");
    });

    task("translations_$env", function () use ($env) {
        cd("/usr/home/azimutu/public_html/{$env}/release/vendor/jms/translation-bundle/JMS/TranslationBundle/Resources/views");
        run("ln -s Translate translate");
        cd("/usr/home/azimutu/public_html/{$env}/release/");
        run("/usr/home/azimutu/public_html/{$env}/release/bin/console translation:extract en --config=cocorico");
    });

    task("migration_$env", function () use ($env) {
        run("/usr/home/azimutu/public_html/{$env}/release/bin/console doctrine:migrations:migrate -n");
    });

// If deploy fails automatically unlock.
    after('deploy:failed', 'deploy:unlock');
}

