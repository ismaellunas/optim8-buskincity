@servers(['localhost' => '127.0.0.1'])

@setup
    $env = "deploy";
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__, ".env.$env");
    $dotenv->safeLoad();

    $branch = "master";

    $heroku_app = "platform752";
    $heroku_vars = [
        'APP_NAME',
        'APP_ENV',
        'APP_KEY',
        'APP_DEBUG',
        'DB_CONNECTION',
        'DB_HOST',
        'DB_PORT',
        'DB_DATABASE',
        'DB_USERNAME',
        'DB_PASSWORD',
        'GOOGLE_CLIENT_ID',
        'GOOGLE_CLIENT_SECRET',
        'FACEBOOK_CLIENT_ID',
        'FACEBOOK_CLIENT_SECRET',
        'CLOUDINARY_URL',
        'CLOUDINARY_UPLOAD_PRESET',
        'CLOUDINARY_NOTIFICATION_URL',
        'QUEUE_CONNECTION',
    ];
@endsetup

@story('heroku:deploy-simple')
    install-dependencies
    git-commit-deployment
    heroku:config-set
    heroku:push
    heroku:migration
    heroku:clean-after-deploy
@endstory

@story('heroku:deploy')
    git-restore-and-stash
    install-dependencies
    git-commit-deployment
    heroku:config-set
    heroku:push
    heroku:migration
    heroku:route-list
    heroku:clean-after-deploy
@endstory

@task('heroku:migration')
    heroku run php artisan migrate --force
@endtask

@task('heroku:clean-after-deploy')
    heroku run php artisan optimize:clear
    heroku run rm Envoy.blade.php
    heroku run rm .env.deploy
    heroku maintenance:off
@endtask

@task('install-dependencies')
    composer install
    yarn install
    rm public/js/*
    npm run prod
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
    heroku run php artisan route:list --path="admin" -c
@endtask
