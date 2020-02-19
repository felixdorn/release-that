#! /bin/bash
# This is kinda hacky, yes.

echo "Using $(pwd)"
echo "This script will globally install humbug/box:^3.8."
echo "With this trick, we reduce our build by 80%."
echo "It allow us to remove more than 11MO overhead in the binary"

export PATH="$PATH:$HOME/.config/composer/vendor/bin/"

composer global require "humbug/box:^3"

composer update -n --no-suggest --optimize-autoloader --no-dev --prefer-dist --no-progress

box compile --with-docker

echo "Release-that build was successful."

composer update -n --no-suggest --dev

