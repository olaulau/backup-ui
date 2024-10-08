#!/bin/bash

## usage :
## ~/bin/borg_client.sh <server_name> <user_name> <repo_name>

echo ""
echo ""
echo "-----------------------"
echo "begin : `date`"

# check if lock file exists, if not create it and set trap on exit
if { set -C; 2>/dev/null > ~/bin/borgmatic.lock; }
then
    trap "rm -f ~/bin/borgmatic.lock" EXIT
else
    echo "Lock file exists… exiting"
    exit
fi

# parameters
if [ $# -ne 3 ]
then
    echo "invalid parameter count."
    exit 1
fi
server_name="$1"
user_name="$2"
repo_name="$3"

# do borg stuff
config_file=`realpath ~/.config/borgmatic/borgmatic_${server_name}_${user_name}_${repo_name}.yaml`
#borgmatic --config $config_file config validate
#borgmatic --config $config_file rcreate --encryption none

borgmatic --config $config_file create	--verbosity 1 --list --files --stats
borgmatic --config $config_file prune	--verbosity 1 --list --stats
borgmatic --config $config_file compact	--verbosity 1 --progress
borgmatic --config $config_file check	--verbosity 1 --progress

#borgmatic --config $config_file rinfo
#borgmatic --config $config_file list
#borgmatic --config $config_file info --archive latest
echo ""

# force permissions
ssh $user_name@$server_name "chmod -R 2770 ~/borg/$repo_name/"
echo ""

# query borg-ui to update his cache for this repo
echo "pushing cache update"
url="$server_name/backup-ui/cache/update/borg/$user_name/$repo_name"
echo "=> $url"
curl --location $url

# also query _DEV URL
echo ""
url="$server_name/backup-ui_DEV/cache/update/borg/$user_name/$repo_name"
echo "=> $url"
curl --location $url
echo ""

echo "end : `date`"
