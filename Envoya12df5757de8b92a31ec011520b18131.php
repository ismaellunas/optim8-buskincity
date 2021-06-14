<?php $now = isset($now) ? $now : null; ?>
<?php $__container->servers(['localhost' => '127.0.0.1']); ?>

<?php
    require __DIR__.'/vendor/autoload.php';
    (new \Dotenv())->load(__DIR__, '.env.deploy');

    $now = new Datetime();
?>

<?php $__container->startTask('heroku-set-config-vars'); ?>

    echo "Heroku: set config variables {env("APP_NAME")}"
    if [ -f .env.deploy ]
    fi

<?php $__container->endTask(); ?>
