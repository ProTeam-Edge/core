#! /bin/bash
apt update
apt install -y openssh-client

mkdir ~/.ssh
touch ~/.ssh/known_hosts

ssh-keyscan 34.105.28.87 >> ~/.ssh/known_hosts

cat ~/.ssh/known_hosts
echo "should've listed some things"

ssh -i unprotected_key -V -t jmanni@34.105.28.87 'cd /var/www/html/proteamedge/public/wp-content && echo "hello"'
