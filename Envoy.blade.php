@servers(['localhost' => '127.0.0.1'])

@setup
    $env = "deploy";
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__, ".env.$env");
    $dotenv->safeLoad();

    $branch = "master";

    $heroku_app = "bysdb-starter-kit";
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
    ];
@endsetup

@story('heroku:deploy')
    install-dependencies
    heroku:config-set
    heroku:push
    heroku:migration
    heroku:clean-after-deploy
@endstory

@task('heroku:migration')
    heroku run php artisan migrate --force
@endtask

@task('heroku:clean-after-deploy')
    heroku run php artisan optimize:clear
    heroku run rm Envoy.blade.php
    heroku run rm .env.deploy
@endtask

@task('install-dependencies')
    composer install
    npm install
    npm run prod
@endtask

@task('heroku:config-set')
    @foreach ($_ENV as $key => $value)
        @if (in_array($key, $heroku_vars))
            heroku config:set {{ $key }}={{ $value }} -a {{ $heroku_app }}
        @endif
    @endforeach
@endtask

@task('heroku:push')
    git push heroku master
@endtask
