@echo on

copy .env.example .env
call composer install
call php artisan migrate
call yarn
call npm run dev