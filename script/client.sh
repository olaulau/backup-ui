#!/bin/bash

## usage :
## ~/bin/client.sh <client_name> <server_name>

echo "-----------------------"
echo "begin : `date`"

client_name="$1"
remove_user_name=$client_name
server_name="$2"
config_file=`realpath ~/.config/borgmatic/borgmatic_$server_name.yaml`

#validate-borgmatic-config --config $config_file
#borgmatic --config $config_file rcreate --encryption none

borgmatic --config $config_file create  --verbosity 1 --list --stats
borgmatic --config $config_file prune   --verbosity 1 --list --stats
borgmatic --config $config_file check   --verbosity 1 --progress

borgmatic --config $config_file list
borgmatic --config $config_file info

ssh $remove_user_name@$server_name "chmod -R u+rwx,g+rwxs,o-rwx /home/$remove_user_name/borg/"

echo "end : `date`"

echo ""
echo ""
