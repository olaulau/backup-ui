#!/bin/bash

cd ~/borg/

cd lock.exclusive/ 2> /dev/null
if [[ $? -eq 1 ]]
then
        # echo "no borg lock.exclusive directory found."
        exit
fi

if [[ `ls -1 | wc -l` -eq 0 ]]
then
        echo "no file found in lock.exclusite directory."
        exit
fi

lock_filename=`/bin/ls -1 | head -n1`
if [[ ! $lock_filename =~ ^(.+)@(.+)\.(.+)-(.+)$ ]]
then
        echo "regex did not match for lock.exclusite filename."
        exit
fi

PID=${BASH_REMATCH[3]}
# echo "killing $PID ..."
kill $PID
