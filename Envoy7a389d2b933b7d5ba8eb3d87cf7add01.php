<?php $now = isset($now) ? $now : null; ?>
<?php $dotenv = isset($dotenv) ? $dotenv : null; ?>
<?php $__container->servers(['localhost' => '127.0.0.1']); ?>

<?php
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, '.env.deploy');
$dotenv->safeLoad();
$now = new Datetime();
?>

<?php $__container->startTask('heroku-set-config-vars'); ?>
    echo "Heroku: set config variables";
    echo <?php echo $now; ?>;
<?php $__container->endTask(); ?>
