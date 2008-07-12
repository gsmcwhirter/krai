#!/bin/sh

function help()
{
  echo "Krai Framework application skeleton generator"
  echo "Usage: newapp.sh [-l] [-w] [-p] -d application_root -n application_name"
  echo "    -l  Chmod log files a+w"
  echo "    -w  Use the demo application code instead of the skeleton"
  echo "    -p  Use a pear-style package of the framework (copy only application and skeleton)"
  echo "    -d  The path of the directory in which the application directory will live"
  echo "    -n  The name of the directory in which the application will live"
}

function change_basedir()
{
  if [ -z $1 ]
  then
    echo "Error: Missing basedir value."
    help
    exit
  fi

  if cd $1 2>/dev/null
  then
    echo "Using application basedir: `pwd`"
  else
    echo "Error: Failed to change to application basedir: $basedir"
    help
    exit
  fi
}

while getopts ":n:d:lwp" options; do
  case $options in
    n) name=$OPTARG;;
    d) basedir=$OPTARG;;
    l) chmodlog=0;;
    w) usedemo=0;;
    p) usepear=0;;
    h) help; exit;;
    \?) help; exit;;
    *) help; exit;;
  esac
done

if [ -z $basedir ]
then
  basedir=`pwd`
  echo "Warning: No application root specified. Using current working directory: $basedir"
#  help
fi

if [ -z $name ]
then
  echo "Error: Application name is required."
  help
  exit
fi

echo "Script name: $0"
scrdir=`readlink -fv $0`
echo "Script directory is: $scrdir"
fwdir=`dirname $scrdir`/..
echo "Framework directory is: $fwdir"

change_basedir $basedir

if [ -d $name ]
then
  echo "Warning: Application directory already exists."
else
  mkdir $name
fi

if cd $name 2>/dev/null
then
  echo "Changing to application directory."
  if [ -z $usepear ]
  then
    echo "Copying framework files."
    cp -ruT $fwdir/Krai Krai
    cp -ruT $fwdir/Krai.php Krai.php
  else
    echo "Ignoring framework files."
  fi

  if [ -z $usedemo ]
  then
    echo "Copying application skeleton files."
    cp -ru $fwdir/script/defaults/* .
  else
    echo "Copying demo application files."
    cp -ru $fwdir/script/demo/* .
  fi

  if [ -d script ]
  then
    echo "Warning: script directory already exists."
  else
    mkdir script
  fi

  echo "Copying component generator to script directory."
  cp -uT $fwdir/script/generate.sh script/generate.sh

  if [ -z $chmodlog ]
  then
    echo "Not chmodding the log files."
  else
    echo "Chmodding the log files."
    chmod a+w log/*.log
  fi

else
  echo "Error: Unable to change to application directory."
  help
  exit
fi
