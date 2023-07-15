#!/bin/bash

## usage :
## ~/bin/borg_client.sh <server_name> <repo_name> 

echo "-----------------------"
echo "begin : `date`"

server_name="$1"
repo_name="$2"
remove_user_name=$repo_name
config_file=`realpath ~/.config/borgmatic/borgmatic_$server_name.yaml`

#validate-borgmatic-config --config $config_file
#borgmatic --config $config_file rcreate --encryption none

borgmatic --config $config_file create  --verbosity 1 --list --stats
borgmatic --config $config_file prune   --verbosity 1 --list --stats
borgmatic --config $config_file check   --verbosity 1 --progress

borgmatic --config $config_file list
borgmatic --config $config_file info

ssh $remove_user_name@$server_name "chmod -R u+rwx,g+rwxs,o-rwx /home/$remove_user_name/borg/"

url="https://$server_name/borg-ui_DEV/cache/update/$repo_name"
curl $url

echo "end : `date`"

echo ""
echo ""
