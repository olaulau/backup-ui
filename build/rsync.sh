#!/bin/bash

source build/rsync.conf.sh

time rsync \
--verbose --progress --itemize-changes --stats \
--recursive --times --delete \
--exclude-from ~/.gitignore --exclude .git   \
-e "ssh -p $DEST_PORT" "$SRC" "$DEST"

# --exclude-from ./.gitignore
# --dry-run \
