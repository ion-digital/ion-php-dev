#!/usr/bin/env sh

TARGET=$1
if [ $TARGET = "" ]; then
   TARGET="build"
fi

 ./vendor/bin/phing -f ./build.xml $TARGET
