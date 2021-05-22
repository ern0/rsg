#!/bin/bash
clear

TUTOR=`ls -1 kontent/tutorial-* | tail -n1 | cut -d/ -f2`
#TUTOR=tutorial-03.txt

for i in {1..8}; do
	curl "localhost:8080/rsg.php?text=$TUTOR&plain=1"
done
