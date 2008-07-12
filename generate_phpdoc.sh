#!/bin/sh

if [ -z $1 ]
then
	ver='edge'
else
	ver=$1
fi

phpdoc -o HTML:Smarty:HandS -d Krai/,script/demo/,tutorials/ -f Krai.php,README,COPYING,INSTALL,MIGRATION,CHANGELOG -t doc -ti "Krai Framework Documentation" -dn Krai -i *.phtml,*wiki_parser/ --quiet on --sourcecode on --parseprivate on -ric README,COPYING,INSTALL,CHANGELOG,MIGRATION
mv doc krai-doc-$ver
tar czf krai-doc-$ver.tar.gz krai-doc-$ver/
rm -r krai-doc-$ver
