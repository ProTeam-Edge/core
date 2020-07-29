#! /bin/bash
apt update
apt install -y openssh-client

mkdir ~/.ssh
mv id_rsa ~/.ssh
touch ~/.ssh/known_hosts

ssh-keyscan 34.105.28.87 >> ~/.ssh/known_hosts

cat ~/.ssh/known_hosts
echo "should've listed some things"

ssh -o StrictHostKeyChecking=no -v jmanni@34.105.28.87 uptime 'bash -s cd_pull.bash'
