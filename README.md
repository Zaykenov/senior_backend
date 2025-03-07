# Alumni-API

This is the backend API for the Alumni application.

## Prerequisites

- Docker
- Docker Compose

## Getting Started

Follow these steps to run the Alumni API using Docker Compose:

### 1. Clone the Repository

```bash
git clone https://github.com/Zaykenov/senior_backend.git
cd alumni-api
```

### 2. Environment Configuration

Create a `.env` file in the root directory with the necessary environment variables:

```bash
APP_NAME=AlumniAPI
APP_ENV=local
APP_KEY=base64:xCs9Kc1RPgpAvZB+4gNHrj7+GbpsD+HcpAAr6N53NKw=
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
# APP_MAINTENANCE_STORE=database

PHP_CLI_SERVER_WORKERS=4

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=alumni_db
DB_USERNAME=alumni_user
DB_PASSWORD=admindb123

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"
```

### 3. Run with Docker Compose

Start the application and its dependencies:

```bash
docker-compose up --build -d
```

Run these commands to set up the application:
```bash
# Install PHP dependencies
docker compose exec app composer install

# Generate application key
docker compose exec app php artisan key:generate

# Run database migrations
docker compose exec app php artisan migrate

# Seed the database with initial data
docker compose exec app php artisan db:seed
```

### 4. API Access

The API will be available at `http://localhost/`
## API Documentation

API documentation can be accessed at `http://localhost/docs/api` once the application is running.

## Development

### Rebuilding the Container

If you make changes to the codebase or Dockerfile, rebuild the containers:

```bash
docker-compose up --build
```

### Stopping the Application

```bash
docker-compose down
```

## Troubleshooting

If you encounter any issues:

1. Check the container logs:
    ```bash
    docker-compose logs
    ```

2. Make sure your .env file contains the correct configuration variables.

3. Ensure ports are not already in use on your machine.