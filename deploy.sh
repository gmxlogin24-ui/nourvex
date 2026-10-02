#!/bin/bash
set -e
SSH_USER=sspxqmqp
HOST=198.187.29.251
PORT=21098
PHPBIN=/opt/alt/php84/usr/bin/php
composer install --no-dev --optimize-autoloader
rsync -az --delete --chmod=D755,F644 -e "ssh -p $PORT" \
  --exclude=.git --exclude=node_modules --exclude=tests --exclude=.env \
  --exclude=storage/logs --exclude=storage/framework --exclude=storage/app/public \
  --exclude=public/hot --exclude=deploy.sh ./ $SSH_USER@$HOST:~/campulsy/
ssh -p $PORT $SSH_USER@$HOST "PHPBIN=$PHPBIN bash -s" <<'REMOTE'
cd ~/campulsy
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs storage/app/public bootstrap/cache
chmod -R 775 storage bootstrap/cache
rsync -a --chmod=D755,F644 --exclude=.htaccess public/ ~/public_html/
sed -i "s#__DIR__.'/../#__DIR__.'/../campulsy/#g" ~/public_html/index.php
[ -L ~/public_html/storage ] || ln -s ~/campulsy/storage/app/public ~/public_html/storage
$PHPBIN artisan migrate --force
$PHPBIN artisan optimize:clear
$PHPBIN artisan optimize
REMOTE
composer install
echo "DEPLOYED"
