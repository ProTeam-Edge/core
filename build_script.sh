#! /bin/bash
apt update
apt install -y openssh-client

mkdir ~/.ssh
touch ~/.ssh/known_hosts

ssh-keyscan -H 34.105.28.87 >> ~/.ssh/known_hosts

ssh -i my-ssh-key jmanni@34.105.28.87 'echo "hello world"'