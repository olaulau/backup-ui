#!/bin/bash

exec > >(tee -a /home/laulau/.tmp/borg-ui_DEV.log) 2>&1
mkdir /home/laulau/.tmp/borg-ui_DEV.lock || exit 0

echo ""
echo ""
echo "--------------------------"
date

PROJECT_DIR=`dirname "${0%/*}"`
cd $PROJECT_DIR
source script/dev.conf.sh

for dest in "${DESTS[@]}"
do
	echo "DEST: $dest"
	time rsync \
	--verbose --progress --itemize-changes --stats \
	--recursive --times --delete \
	--exclude-from ~/.gitignore --exclude .git --exclude tmp --exclude-from ./.gitignore --exclude vendor  \
	-e "ssh -p $DEST_PORT" "$SRC" "$dest"
done

rm -rf /home/laulau/.tmp/borg-ui_DEV.lock
date
