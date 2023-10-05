#!/bin/bash

## usage :
## ~/bin/borg_client.sh <server_name> <user_name> <repo_name>

echo "-----------------------"
echo "begin : `date`"

# Check if Lock File exists, if not create it and set trap on exit
if { set -C; 2>/dev/null > ~/bin/borgmatic.lock; }
then
    trap "rm -f ~/bin/borgmatic.lock" EXIT
else
    echo "Lock file exists… exiting"
    exit
fi

if [ $# -ne 3 ]
then
    echo "invalid parameter count."
    exit 1
fi
server_name="$1"
user_name="$2"
repo_name="$3"
config_file=`realpath ~/.config/borgmatic/borgmatic_$server_name.yaml`

#validate-borgmatic-config --config $config_file
#borgmatic --config $config_file rcreate --encryption none

borgmatic --config $config_file create --verbosity 1 --list --stats
borgmatic --config $config_file prune  --verbosity 1 --list --stats
borgmatic --config $config_file check  --verbosity 1 --progress

#borgmatic --config $config_file list
#borgmatic --config $config_file info

ssh $user_name@$server_name 'chmod -R 2770 $HOME/borg/$repo_name/'

url="$server_name/borg-ui/cache/update/$user_name/$repo_name"
curl -L $url
url="$server_name/borg-ui_DEV/cache/update/$user_name/$repo_name"
curl -L $url

echo "end : `date`"
echo ""
echo ""
