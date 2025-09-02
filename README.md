## Installation steps

### Requirements
- Php 8.1
- Docker
- NodeJS

### Laravel
- cp .env.example .env
- cp .env.dusk.local.example .env.dusk.local
- cp .env.sail.example .env.sail
- composer install (Expect to receive an error at the end because the translation service expects to query on our non existent database)
- sail up -d
- sail composer install
- sail artisan key:generate

**⚠️ IMPORTANT: Database Migration Warning**
- **DO NOT** run `sail artisan migrate:fresh --seed` if your database server is not localhost
- Only run migrations when you have a local development database or are sure about the database connection
- Check your `.env` file to verify `DB_HOST` is set to `localhost` or `127.0.0.1`

- sail artisan migrate:fresh --seed (Only if using local database)
- sail yarn install
- sail yarn build
- sail artisan optimize:clear

### InertiaJs
### Vue3

## Security Vulnerabilities

If you discover a security vulnerability within Platform752, please send an e-mail to SDB Agency Team via [webmaster@sdbagency.com](mailto:webmaster@sdbagency.com). All security vulnerabilities will be promptly addressed.
