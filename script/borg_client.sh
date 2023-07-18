#!/bin/bash

## usage :
## ~/bin/borg_client.sh <server_name> <repo_name>

echo "-----------------------"
echo "begin : `date`"

# Check is Lock File exists, if not create it and set trap on exit
if { set -C; 2>/dev/null > ~/bin/borgmatic.lock; }
then
    trap "rm -f ~/bin/borgmatic.lock" EXIT
else
    echo "Lock file exists… exiting"
    exit
fi

if [ $# -ne 2 ]
then
	echo "invalid parameter count."
	exit 1
fi
server_name="$1"
repo_name="$2"
remote_user_name=$repo_name
config_file=`realpath ~/.config/borgmatic/borgmatic_$server_name.yaml`

#validate-borgmatic-config --config $config_file
#borgmatic --config $config_file rcreate --encryption none

borgmatic --config $config_file create --verbosity 1 --list --stats
borgmatic --config $config_file prune  --verbosity 1 --list --stats
borgmatic --config $config_file check  --verbosity 1 --progress

borgmatic --config $config_file list
borgmatic --config $config_file info

ssh $remote_user_name@$server_name "chmod -R u+rwx,g+rwxs,o-rwx /home/$remote_user_name/borg/"

url="https://$server_name/borg-ui/cache/update/$repo_name"
curl $url
url="https://$server_name/borg-ui_DEV/cache/update/$repo_name"
curl $url

echo "end : `date`"

echo ""
echo ""
