#!/bin/bash
set -e

mkdir -p /overlay/storage/{dokuwiki,work}
mount -t overlay \
      -o lowerdir=/overlay/original,upperdir=/overlay/storage/dokuwiki,workdir=/overlay/storage/work \
         overlay /var/www/html

docker-php-entrypoint apache2-foreground
