#!/bin/bash

exec > >(tee -a /home/laulau/.tmp/borg-ui_DEV.log) 2>&1
date

PROJECT_DIR=`dirname "${0%/*}"`
cd $PROJECT_DIR

mkdir /home/laulau/.tmp/borg-ui_DEV.lock || exit 0

source script/dev.conf.sh

for dest in "${DESTS[@]}"
do
	echo "DEST: $dest"
	time rsync \
	--verbose --progress --itemize-changes --stats \
	--recursive --times --delete \
	--exclude-from ~/.gitignore --exclude .git --exclude tmp --exclude conf/conf.ini \
	-e "ssh -p $DEST_PORT" "$SRC" "$dest"
	echo "after" >&2
	
	# --exclude-from ./.gitignore
	# --dry-run \
done

rm -rf /home/laulau/.tmp/borg-ui_DEV.lock
