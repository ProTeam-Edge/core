#! /bin/bash
apt update
apt install -y openssh-client

mkdir ~/.ssh
mv id_rsa ~/.ssh
touch ~/.ssh/known_hosts

ssh-keyscan 34.105.28.87 >> ~/.ssh/known_hosts

cat ~/.ssh/known_hosts
echo "should've listed some things"

ssh -v jmanni@34.105.28.87 'bash -s cd_pull.bash'
