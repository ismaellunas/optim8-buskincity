@servers(['localhost' => '127.0.0.1'])

@setup
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__, '.env.deploy');
    $dotenv->safeLoad();
    $now = new DateTime();
    $herokuConfigKeys = [
        'APP_DEBUG',
        'APP_ENV',
        'APP_KEY',
        'APP_NAME',
        'APP_URL',
    ];
    $herokuApp = "bysdb-starter-kit";
    $branch = "master";
@endsetup

@task('heroku-set-config-vars')
    echo "Heroku: set config variables";
    @foreach ($_ENV as $key => $value)
        @if (in_array($key, $herokuConfigKeys))
            heroku config:set {!! $key !!}={!! $value !!} -a {{ $herokuApp }}
        @endif
    @endforeach
@endtask
