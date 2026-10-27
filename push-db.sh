#!/bin/bash
source ~/.campulsy.env
read -p "Local DB name: " LDB
read -p "Local DB user [root]: " LUSER; LUSER=${LUSER:-root}
read -p "Server DB name: " SDB
read -p "Server DB user: " SUSER
read -sp "Server DB password: " SPASS; echo
mariadb-dump -u "$LUSER" -p "$LDB" | ssh -p $PORT $SSH_USER@$HOST "mysql -u $SUSER -p'$SPASS' $SDB"
rsync -az -e "ssh -p $PORT" storage/app/public/ $SSH_USER@$HOST:~/campulsy/storage/app/public/
echo "DB + uploads pushed"
