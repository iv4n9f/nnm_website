#!/bin/sh
set -e
if [ -f .env ]; then
  export $(grep -v '^#' .env | xargs)
fi
php migrate.php >/dev/null
echo "Init complete"
