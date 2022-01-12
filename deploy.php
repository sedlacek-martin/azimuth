<?php
namespace Deployer;

require 'recipe/common.php';

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
    ->hostname('www192.your-server.de')
//    ->hostname(getenv('SERVER_HOSTNAME'))
    ->user('azimutu')
    ->port(222)
    ->forwardAgent(true)
    ->multiplexing(false)
    ->set('deploy_path', '/usr/home/azimutu/public_html/gama')
    ->set('keep_releases', 3);
    
// Tasks

task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:writable',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
    'success'
]);

// If deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

