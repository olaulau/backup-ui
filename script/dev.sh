#!/bin/bash

exec > >(tee -a $HOME/.tmp/borg-ui_DEV.log) 2>&1
mkdir $HOME/.tmp/borg-ui_DEV.lock || exit 0

echo ""
echo ""
echo "--------------------------"
date

PROJECT_DIR=`dirname "${0%/*}"`
cd $PROJECT_DIR
source script/dev.conf.sh

for i in "${!DESTS[@]}"
do
	dest=${DESTS[i]}
	port=${PORTS[i]}
	echo "DEST: $dest : $port"
	time rsync \
	--verbose --progress --itemize-changes --stats \
	--recursive --times --delete \
	--exclude-from ~/.gitignore --exclude .git --exclude tmp --exclude-from ./.gitignore --exclude vendor  \
	-e "ssh -p $port" "$SRC" "$dest"
done

rm -rf /home/laulau/.tmp/borg-ui_DEV.lock
date
