#!/bin/sh

function help()
{
  echo "Krai Framework application component generator"
  echo "Usage: generate.sh [-d application_root] [-g module -m module_name]|[-g action -m module_name -a action_name]|[-g view -m module_name -v view_name]"
}

function module()
{
  if [ -z $1 ]
  then
    echo "Error: Missing parameter in module generator."
    help
    return 1
  fi

  if cd includes/modules 2>/dev/null
  then
    echo "Directory `pwd`/$1.module"
    mkdir $1.module
    if cd $1.module 2>/dev/null
    then
      echo "Directory `pwd`/actions"
      mkdir actions
      echo "Directory `pwd`/views"
      mkdir views
      echo "File `pwd`/$1.module.php"
      touch $1.module.php
      return 0
    else
      echo "Error: Unable to generate module directory."
      help
      return 1
    fi
  else
    echo "Error: Could not change to modules directory."
    help
    return 1
  fi
}

function action()
{
  if [ -z $1 ]
  then
    echo "Error: Missing module parameter in action generator."
    help
    return 1
  fi

  if [ -z $2 ]
  then
    echo "Error: Missing action parameter in action generator."
    help
    return 1
  fi

  if cd includes/modules/$1.module 2>/dev/null
  then
    if cd actions 2>/dev/null
    then
      echo "File `pwd`/$2.action.php"
      touch $2.action.php
      return 0
    else
      echo "Actions directory missing. Generating."
      echo "Directory `pwd`/actions"
      mkdir actions
      if cd actions 2>/dev/null
      then
        echo "File `pwd`/$2.action.php"
        touch $2.action.php
        return 0
      else
        echo "Error: Unable to generate actions directory."
        help
        return 1
      fi
    fi
  else
    echo "Module missing. Generating."
    module $1
    if cd includes/modules/$1.module 2>/dev/null
    then
      if cd actions 2>/dev/null
      then
        echo "File `pwd`/$2.action.php"
        touch $2.action.php
        return 0
      else
        echo "Actions directory missing. Generating."
        echo "Directory `pwd`/actions"
        mkdir actions
        if cd actions 2>/dev/null
        then
          echo "File `pwd`/$2.action.php"
          touch $2.action.php
          return 0
        else
          echo "Error: Unable to generate actions directory."
          help
          return 1
        fi
      fi
    else
      echo "Error: Unable to generate module."
      help
      return 1
    fi
  fi
}

function view()
{
  if [ -z $1 ]
  then
    echo "Error: Missing module parameter in view generator."
    help
    return 1
  fi

  if [ -z $2 ]
  then
    echo "Error: Missing view parameter in view generator."
    help
    return 1
  fi

  if cd includes/modules/$1.module 2>/dev/null
  then
    if cd views 2>/dev/null
    then
      echo "File `pwd`/$2.phtml"
      touch $2.phtml
      return 0
    else
      echo "Views directory missing. Generating."
      echo "Directory `pwd`/views"
      mkdir views
      if cd views 2>/dev/null
      then
        echo "File `pwd`/$2.phtml"
        touch $2.phtml
        return 0
      else
        echo "Error: Unable to generate views directory."
        help
        return 1
      fi
    fi
  else
    echo "Module missing. Generating."
    module $1
    if cd includes/modules/$1.module 2>/dev/null
    then
      if cd views 2>/dev/null
      then
        echo "File `pwd`/$2.phtml"
        touch $2.phtml
        return 0
      else
        echo "Views directory missing. Generating."
        echo "Directory `pwd`/views"
        mkdir views
        if cd views 2>/dev/null
        then
          echo "File `pwd`/$2.phtml"
          touch $2.phtml
          return 0
        else
          echo "Error: Unable to generate views directory."
          help
          return 1
        fi
      fi
    else
      echo "Error: Unable to generate module."
      help
      return 1
    fi
  fi
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

while getopts ":g:m:a:v:d:" options; do
  case $options in
    g) generate=$OPTARG;;
    m) modname=$OPTARG;;
    a) actname=$OPTARG;;
    v) viewname=$OPTARG;;
    d) basedir=$OPTARG;;
    h) help; exit;;
    \?) help; exit;;
    *) help; exit;;
  esac
done

if [ -z $basedir ]
then
  basedir=`pwd`
fi

case $generate in
  module) change_basedir $basedir
          module $modname;;
  view)   change_basedir $basedir
          view $modname $viewname;;
  action) change_basedir $basedir
          action $modname $actname;;
  *) help; exit;;
esac
