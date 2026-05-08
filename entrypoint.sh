#!/bin/bash
set -e

echo "Waiting for database to be ready..."
# Wait for database (robust PHP check)
until php -r "
try {
    \$host = getenv('DATABASE_HOST') ?: 'db';
    \$port = getenv('DATABASE_PORT') ?: '3306';
    \$user = getenv('DATABASE_USER');
    \$pass = getenv('DATABASE_PASSWORD');
    new PDO(\"mysql:host=\$host;port=\$port\", \$user, \$pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    exit(0);
} catch (Exception \$e) {
    exit(1);
}" 2>/dev/null; do
  echo "Database is unavailable - sleeping"
  sleep 2
done

echo "Database is up - executing migrations"
php migration.php

echo "Executing seeds"
php seed.php

echo "Starting Apache"
exec apache2-foreground
