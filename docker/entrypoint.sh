#!/bin/sh
set -e

cd /var/www/html

ROLE="${1:-$APP_ROLE}"

# config.php is gitignored, so generate it from environment on every start
# unless the user explicitly disables it and provides their own file.
if [ ! -f config.php ] || [ "${GENERATE_CONFIG:-1}" = "1" ]; then
    php docker/configure.php
fi

for dir in cached cms/cached cms/debmes debmes saverestore modules templates objects rc; do
    mkdir -p "$dir"
    chown -R www-data:www-data "$dir" 2>/dev/null || true
done

# MariaDB may still be initializing on the first boot.
until php -r '$m = @new mysqli(getenv("DB_HOST"), getenv("DB_USER"), getenv("DB_PASSWORD"), getenv("DB_NAME"), (int)(getenv("DB_PORT") ?: 3306)); exit($m->connect_errno ? 1 : 0);'; do
    echo "Waiting for MySQL/MariaDB at ${DB_HOST}:${DB_PORT:-3306}..."
    sleep 2
done

case "$ROLE" in
    web)
        exec apache2-foreground
        ;;
    cycle)
        exec php cycle.php
        ;;
    *)
        exec "$@"
        ;;
esac
