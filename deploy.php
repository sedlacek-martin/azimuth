<?php
namespace Deployer;

use function Clue\StreamFilter\fun;

require 'recipe/common.php';

$env = 'prod2';
$hostname = 'www192.your-server.de';

// Project name
set('application', 'Azimuth');

// Project repository
set('repository', 'https://github.com/sedlacek-martin/azimuth.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', false);

set('default_timeout', 2000);
set('http_user', 'azimutu');

// Shared files/dirs between deploys
add('shared_files', ['app/config/parameters.yml']);
add('shared_dirs', ['var/log', 'var/sessions', 'web/media', 'web/uploads' ]);

// Writable dirs by web server
//add('writable_dirs', []);

set('allow_anonymous_stats', false);


// Hosts

host('prod')
    ->hostname($hostname)
    ->user('azimutu')
    ->port(222)
    ->forwardAgent(true)
    ->multiplexing(false)
    ->set('deploy_path', "/usr/home/azimutu/public_html/{$env}")
    ->set('keep_releases', 3);
    
// Tasks

task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'composer',
    'assets',
    'deploy:shared',
    'deploy:writable',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
    'success'
]);

task('composer', function () use ($env) {
    cd('{{release_path}}');
    run("composer prod --no-interaction -d /usr/home/azimutu/public_html/{$env}/release");
    run("composer dump-autoload --optimize --classmap-authoritative --no-interaction -d /usr/home/azimutu/public_html/{$env}/release");
});

task('migration', function () use ($env) {
    run("/usr/home/azimutu/public_html/{$env}/release/bin/console doctrine:migrations:migrate -n");
});

task('assets', function () use ($env) {
    run("/usr/home/azimutu/public_html/{$env}/release/bin/console assets:install --symlink");
    run("/usr/home/azimutu/public_html/{$env}/release/bin/console assetic:dump");
});

// If deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

