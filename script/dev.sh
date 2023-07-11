#!/bin/bash

source script/dev.conf.sh

for dest in "${DESTS[@]}"
do
	time rsync \
	--verbose --progress --itemize-changes --stats \
	--recursive --times --delete \
	--exclude-from ~/.gitignore --exclude .git   \
	-e "ssh -p $DEST_PORT" "$SRC" "$dest"
	
	# --exclude-from ./.gitignore
	# --dry-run \
done
exit
