## Installation steps

### Requirements
- Php 8.1
- RedisCLI

### Laravel
- cp .env.example .env
- cp .env.dusk.local.example .env.dusk.local
- cp .env.sail.example .env.sail
- composer install
- sail up -d
- sail artisan key:generate
- sail artisan migrate:fresh --seed
- yarn && yarn build
- sail artisan optimize:clear
### InertiaJs
### Vue3

## Security Vulnerabilities

If you discover a security vulnerability within Platform752, please send an e-mail to SDB Agency Team via [webmaster@sdbagency.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.
