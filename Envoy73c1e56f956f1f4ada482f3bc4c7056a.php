<?php $value = isset($value) ? $value : null; ?>
<?php $key = isset($key) ? $key : null; ?>
<?php $_ENV = isset($_ENV) ? $_ENV : null; ?>
<?php $now = isset($now) ? $now : null; ?>
<?php $dotenv = isset($dotenv) ? $dotenv : null; ?>
<?php $__container->servers(['localhost' => '127.0.0.1']); ?>

<?php
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, '.env.deploy');
$dotenv->safeLoad();
$now = new DateTime();
?>

<?php $__container->startTask('heroku-set-config-vars'); ?>
    echo "Heroku: set config variables";
    echo <?php echo $now->format('d-m-y'); ?>;
    echo <?php echo $_ENV['APP_NAME']; ?>


    <?php for ($_ENV as $key => $value): ?>
    echo <?php echo $key; ?> <?php echo $value; ?>

    <?php endfor; ?>
<?php $__container->endTask(); ?>
