#!/bin/bash
set -e

echo "Waiting for database to be ready..."
# Wait for database (simple loop)
until php -r "new PDO('mysql:host=' . (getenv('DATABASE_HOST') ?: 'db'), getenv('DATABASE_USER'), getenv('DATABASE_PASSWORD'));" 2>/dev/null; do
  echo "Database is unavailable - sleeping"
  sleep 2
done

echo "Database is up - executing migrations"
php migration.php

echo "Executing seeds"
php seed.php

echo "Starting Apache"
exec apache2-foreground
