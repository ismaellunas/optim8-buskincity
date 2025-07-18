@servers(['localhost' => '127.0.0.1'])

@setup
    if (empty($env)) {
        $env = "deploy";
    }

    if ($env == "local") {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    } else {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__, ".env.$env");
    }

    $dotenv->safeLoad();

    $branch = "main";
    $theme = $_ENV['THEME_ACTIVE'] ?? 'biz';
    $git_remote = $_ENV['GIT_REMOTE'] ?? 'heroku';

    $heroku_app = $_ENV['HEROKU_APP'];
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
    git-restore-and-stash
    git-checkout
    git-commit-deployment
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
    git-restore-and-stash
    git-checkout
    git-commit-deployment
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
        heroku run -a {{ $heroku_app }} php artisan migrate --force
    @endif
@endtask

@task('heroku:clean-after-deploy')
    heroku run -a {{ $heroku_app }} php artisan optimize:clear
    heroku run -a {{ $heroku_app }} rm Envoy.blade.php
@endtask

@task('heroku:restart')
    heroku restart -a {{ $heroku_app }} worker
@endtask

@task('install-dependencies')
    composer install
    yarn install
@endtask

@task('git-restore-and-stash')
    rm -rf public/build
    git restore public/css/app.css
    git restore public/js/app.js
    git restore public/mix-manifest.json
    git stash -u
@endtask

@task('git-checkout')
    git checkout {{ $branch }}
    git fetch origin master
    git merge master
@endtask

@task('git-commit-deployment')
    git add . && git diff --staged --quiet || git commit -m "Deploy on {{date("Y-m-d H:i:s")}}"
@endtask

@task('git-push')
    git push origin {{ $branch }}
@endtask

@task('heroku:maintenance-on')
    heroku maintenance:on -a {{ $heroku_app }}
@endtask

@task('heroku:maintenance-off')
    heroku maintenance:off -a {{ $heroku_app }}
@endtask

@task('heroku:config-set')
    @foreach ($_ENV as $key => $value)
        @if (in_array($key, $heroku_vars))
            heroku config:set {{ $key }}={{ $value }} -a {{ $heroku_app }}
        @endif
    @endforeach
@endtask

@task('heroku:generate-css')
    heroku run -a {{ $heroku_app }} php artisan generate:theme-css
@endtask

@task('heroku:push')
    git push {{ $git_remote }} {{ $branch }}
@endtask

@task('heroku:postgresql-credentials')
    heroku pg:credentials:url
@endtask

@task('heroku:route-list')
    heroku run -a {{ $heroku_app }} php artisan route:list --path="admin"
@endtask

@task('nwatch')
    npm run watch-poll
@endtask

@task('nwatch-theme')
    npm run watch-poll --theme={{ $theme }}
@endtask

@task('sail:fresh')
    sail artisan db:wipe
    sail artisan migrate
    sail artisan db:seed
    sail artisan module:seed Space
    sail artisan module:seed Ecommerce
    sail artisan module:seed Booking
@endtask

@task('sail:watch')
    sail npm run watch-poll
@endtask

@task('sail:watch-theme')
    sail npm run watch-poll --theme={{ $theme }}
@endtask

@task('sail:dev')
    sail npm run dev
    sail npm run dev --theme={{ $theme }}
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
