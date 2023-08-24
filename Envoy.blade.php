@servers(['localhost' => '127.0.0.1'])

@setup
    function logSuccess($message) { return "echo '\033[0;32m" .$message. "\033[0m';\n"; }
    function logWarn($message)    { return "echo '\033[0;31m" .$message. "\033[0m';\n"; }
    function logInfo($message)    { return "echo '\033[1;33m" .$message. "\033[0m';\n"; }
    function logLine($message)    { return "echo '" .$message. "';\n"; }

    $deployEnvironments = ['staging', 'prod'];

    if (empty($env)) {
        $env = "local";
    }

    if ($env == "local") {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    } else {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__, ".env.$env");
    }

    $dotenv->safeLoad();

    $branch = $_ENV['HEROKU_BRANCH'] ?? 'main';
    $git_remote = $_ENV['HEROKU_REMOTE'] ?? 'remote_is_empty';
    $theme = $_ENV['THEME_ACTIVE'] ?? 'biz';

    $heroku_vars = [
        'APP_DEBUG',
        'APP_DOMAIN',
        'APP_ENV',
        'APP_HTTPS_IS_ON',
        'APP_ID',
        'APP_KEY',
        'APP_NAME',
        'APP_URL',
        'CACHE_DRIVER',
        'DB_CONNECTION',
        'ERROR_REPORTING',
        'FOLDER_PREFIX',
        'MAIL_ENCRYPTION',
        'MAIL_FROM_ADDRESS',
        'MAIL_FROM_NAME',
        'MAIL_HOST',
        'MAIL_MAILER',
        'MAIL_PASSWORD',
        'MAIL_PORT',
        'MAIL_USERNAME',
        'QUEUE_CONNECTION',
        'REDIS_CLIENT',
        'REDIS_SCHEME',
        'SCOUT_DRIVER',
        'SESSION_DRIVER',
        'SESSION_SECURE_COOKIE',
        'TELESCOPE_ENABLED',
        'THEME_ACTIVE',
        'VITE_APP_NAME',
    ];
@endsetup

@story('heroku:deploy')
    check-deploy-environment
    git-restore-and-stash
    git-checkout
    heroku:maintenance-on
    heroku:push
    heroku:migration
    heroku:generate-css
    heroku:route-list
    heroku:restart
    heroku:clean-after-deploy
    heroku:maintenance-off
    git-push
@endstory

@story('heroku:deploy-full')
    check-deploy-environment
    git-restore-and-stash
    git-checkout
    heroku:maintenance-on
    heroku:config-set
    heroku:push
    heroku:migration
    heroku:generate-css
    heroku:route-list
    heroku:restart
    heroku:clean-after-deploy
    heroku:maintenance-off
    git-push
@endstory

@task('heroku:migration')
    @if (! $skipMigration)
        heroku run -r {{ $git_remote }} -- php artisan migrate --force
    @endif
@endtask

@task('heroku:clean-after-deploy')
    heroku run -r {{ $git_remote }} php artisan optimize:clear
    heroku run -r {{ $git_remote }} rm Envoy.blade.php
@endtask

@task('heroku:restart')
    heroku restart -r {{ $git_remote }} worker
@endtask

@task('install-dependencies')
    composer install
    yarn install
@endtask

@task('git-restore-and-stash')
    git restore public/css/app.css
    git restore public/mix-manifest.json
    git stash -u
@endtask

@task('git-checkout')
    git checkout {{ $branch }}
@endtask

@task('git-commit-changes-before-deployment')
    git add . && git diff --staged --quiet || git commit -m "Deploy on {{date("Y-m-d H:i:s")}}"
@endtask

@task('git-push')
    git push origin {{ $branch }}
@endtask

@task('heroku:maintenance-on')
    heroku maintenance:on -r {{ $git_remote }}
@endtask

@task('heroku:maintenance-off')
    heroku maintenance:off -r {{ $git_remote }}
@endtask

@task('heroku:config-set')
    @foreach ($_ENV as $key => $value)
        @if (in_array($key, $heroku_vars))
            heroku config:set {{ $key }}={{ $value }} -r {{ $git_remote }}
        @endif
    @endforeach
@endtask

@task('heroku:generate-css')
    heroku run -r {{ $git_remote }} php artisan generate:theme-css
@endtask

@task('heroku:push')
    git push {{ $git_remote }} {{ $branch }}
@endtask

@task('heroku:postgresql-credentials')
    heroku pg:credentials:url
@endtask

@task('heroku:route-list')
    heroku run -r {{ $git_remote }} -- php artisan route:list --path="admin"
@endtask

@task('sail:fresh')
    sail artisan db:wipe
    sail artisan migrate
    sail artisan db:seed
    sail artisan module:seed Space
    sail artisan module:seed Ecommerce
    sail artisan module:seed Booking
@endtask

@task('sail:queue')
    sail artisan queue:work
@endtask

@task('sail:schedule')
    sail artisan schedule:work
@endtask

@task('sail:init-npm')
    sail yarn
@endtask

@story('sail:init')
    sail:init-npm
    sail:fresh
@endstory

@task('art:seed-basic')
    php artisan db:seed --class=DatabaseBasicSeeder
    php artisan module:seed --class=SpaceDatabaseBasicSeeder Space
    php artisan module:seed --class=EcommerceDatabaseBasicSeeder Ecommerce
    php artisan module:seed Booking
    php artisan module:seed --class=FormBuilderDatabaseBasicSeeder FormBuilder
@endtask

@task('check-deploy-environment')
    @if (! in_array($env, $deployEnvironments))
        {{ logWarn("Env is not for deployment") }}
        exit;
    @endif
@endtask
