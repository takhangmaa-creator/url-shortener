#!/bin/sh
set -e

if [ ! -d "vendor" ]; then
  composer install --no-interaction --prefer-dist
fi

# Install npm dependencies if needed
if [ ! -d "node_modules" ]; then
  npm install
fi

# Build Vite assets AFTER volume is mounted
npm run build

php artisan key:generate --force
php artisan migrate --force

php-fpm
