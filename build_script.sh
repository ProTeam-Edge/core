#! /bin/bash
apt update
apt install -y openssh-client

mkdir ~/.ssh
touch ~/.ssh/known_hosts

ssh-keyscan 34.105.28.87 >> ~/.ssh/known_hosts

cat ~/.ssh/known_hosts
echo "should've listed some things"

#ssh -v jmanni@34.105.28.87 'bash -s cd_pull.bash'
#ssh -i unprotected_key -v jmanni@34.105.28.87 'cd /var/www/html/proteamedge/public/wp-content && git pull origin dev' 
#ssh -i unprotected_key -v jmanni@34.105.28.87 'bash -s cd_pull.bash'
ssh -o StrictHostKeyChecking=no -l jondmanni 34.105.28.87 "bballfreak4891; bash -s cd_pull.bash"
