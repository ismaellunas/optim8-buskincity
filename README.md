## Quick Start with Laravel Sail

### Requirements
- Docker Desktop
- Git

### Installation Steps

#### 1. Copy Environment File
```bash
cp .env.sail.example .env
```

#### 2. Install Dependencies

**Option A: With Composer installed locally**
```bash
composer install
```

**Option B: Without Composer (using Docker)**
```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v $(pwd):/var/www/html \
    -w /var/www/html \
    laravelsail/php81-composer:latest \
    composer install --ignore-platform-reqs
```

#### 3. Start Sail & Initialize
```bash
# Start containers
./vendor/bin/sail up -d

# Generate app key
./vendor/bin/sail artisan key:generate

# Run migrations and seed database
./vendor/bin/sail artisan migrate:fresh --seed

# Install frontend dependencies
./vendor/bin/sail yarn install

# Build assets
./vendor/bin/sail yarn build

# Clear caches
./vendor/bin/sail artisan optimize:clear
```

#### 4. Access Application
- **Frontend**: http://localhost
- **Admin**: http://localhost/admin/login

### Create Sail Alias (Optional but Recommended)

Add to your `~/.zshrc` or `~/.bashrc`:
```bash
alias sail='./vendor/bin/sail'
```

Then reload: `source ~/.zshrc`

Now you can use `sail` instead of `./vendor/bin/sail`:
```bash
sail up
sail artisan migrate
sail yarn dev
```

### Daily Development

```bash
# Start containers
sail up -d

# Watch and compile assets
sail yarn dev

# Run migrations
sail artisan migrate

# View logs
sail logs -f

# Stop containers
sail down
```

### Common Commands

```bash
# Container management
sail up -d          # Start containers
sail down           # Stop containers
sail restart        # Restart
sail ps             # Status

# Application
sail artisan <command>
sail composer <command>
sail yarn <command>

# Database
sail artisan migrate
sail artisan migrate:fresh --seed
sail psql            # PostgreSQL shell

# Testing
sail test
sail dusk

# Shell access
sail shell           # App container
sail root-shell      # Root access
```

### Documentation

- **[SAIL_GUIDE.md](./SAIL_GUIDE.md)** - Quick Sail reference
- **[CODEBASE_DOCUMENTATION.md](./CODEBASE_DOCUMENTATION.md)** - Complete documentation

### Laravel Sail
This project uses Laravel Sail for Docker development.  
Documentation: https://laravel.com/docs/9.x/sail

## Security Vulnerabilities

If you discover a security vulnerability within Platform752, please send an e-mail to SDB Agency Team via [webmaster@sdbagency.com](mailto:webmaster@sdbagency.com). All security vulnerabilities will be promptly addressed.
