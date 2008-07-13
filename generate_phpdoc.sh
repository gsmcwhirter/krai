#!/bin/sh

function gendoc()
{
	if [ -z $2 ]
	then
		ver='edge'
	else
		ver=$2
	fi

	phpdoc -c $1
	mv doc krai-doc-$ver
	tar czf krai-doc-$ver.tar.gz krai-doc-$ver/
	rm -r krai-doc-$ver
}


if [ -z $2 ]
then
	echo "Missing configuration parameter. Using default."
	conf=phpdoc_archive.ini
else
	conf=$2
fi

gendoc $conf $1
