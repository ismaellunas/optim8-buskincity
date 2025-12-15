release: php artisan package:discover --ansi && php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan view:cache
web: vendor/bin/heroku-php-apache2 public/
worker: php artisan schedule:work
