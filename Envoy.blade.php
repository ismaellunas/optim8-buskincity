@servers(['localhost' => '127.0.0.1'])

@setup
    $env = "deploy";
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__, ".env.$env");
    $dotenv->safeLoad();

    $branch = "master";
    $theme = $_ENV['THEME_ACTIVE'] ?? 'biz';

    $heroku_app = $_ENV['HEROKU_APP'];
    $heroku_vars = [
        'APP_DEBUG',
        'APP_ENV',
        'APP_ID',
        'APP_KEY',
        'APP_NAME',
        'APP_URL',
        'CACHE_DRIVER',
        'CLOUDINARY_NOTIFICATION_URL',
        'CLOUDINARY_UPLOAD_PRESET',
        'CLOUDINARY_URL',
        'DB_CONNECTION',
        'DB_DATABASE',
        'DB_HOST',
        'DB_PASSWORD',
        'DB_PORT',
        'DB_USERNAME',
        'FACEBOOK_CLIENT_ID',
        'FACEBOOK_CLIENT_SECRET',
        'GOOGLE_CLIENT_ID',
        'GOOGLE_CLIENT_SECRET',
        'IPREGISTRY_API_KEY',
        'MAIL_MAILER',
        'MAIL_HOST',
        'MAIL_PORT',
        'MAIL_USERNAME',
        'MAIL_PASSWORD',
        'MAIL_ENCRYPTION',
        'MAIL_FROM_ADDRESS',
        'MAIL_FROM_NAME',
        'QUEUE_CONNECTION',
        'RECAPTCHA_SECRET_KEY',
        'RECAPTCHA_SITE_KEY',
        'SCOUT_DRIVER',
        'STRIPE_PK',
        'STRIPE_SK',
        // 'REDIS_CLIENT',
        // 'REDIS_URL',
    ];
@endsetup

@story('heroku:deploy')
    git-restore-and-stash
    install-dependencies
    git-commit-deployment
    heroku:push
    heroku:migration
    heroku:route-list
    heroku:restart
    heroku:clean-after-deploy
@endstory

@story('heroku:deploy-full')
    git-restore-and-stash
    install-dependencies
    git-commit-deployment
    heroku:config-set
    heroku:push
    heroku:migration
    heroku:route-list
    heroku:restart
    heroku:clean-after-deploy
@endstory

@task('heroku:migration')
    heroku run php artisan migrate --force
@endtask

@task('heroku:clean-after-deploy')
    heroku run php artisan optimize:clear
    heroku run rm Envoy.blade.php
    heroku maintenance:off
@endtask

@task('heroku:restart')
    heroku restart worker
@endtask

@task('install-dependencies')
    composer install
    yarn install
    rm public/js/*
    npm run prod
    npm run prod --theme={{ $theme }}
@endtask

@task('git-restore-and-stash')
    git restore public/css/app.css
    git restore public/js/app.js
    git restore public/mix-manifest.json
    git stash -u
    git pull
@endtask

@task('git-commit-deployment')
    git add . && git diff --staged --quiet || git commit -m "Deploy on {{date("Y-m-d H:i:s")}}"
@endtask

@task('heroku:config-set')
    heroku maintenance:on
    @foreach ($_ENV as $key => $value)
        @if (in_array($key, $heroku_vars))
            heroku config:set {{ $key }}={{ $value }} -a {{ $heroku_app }}
        @endif
    @endforeach
@endtask

@task('heroku:push')
    git push heroku master
@endtask

@task('heroku:postgresql-create-free')
    heroku addons:create heroku-postgresql:hobby-dev
@endtask

@task('heroku:postgresql-credentials')
    heroku pg:credentials:url
@endtask

@task('heroku:route-list')
    heroku run php artisan route:list --path="admin"
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
