#!/bin/bash

# Web server and MajorDoMo cycles run as this user.
# Override with: WEB_USER=apache ./install-linux.sh
WEB_USER="${WEB_USER:-www-data}"

folders=( cached cms debmes modules templates objects rc saverestore)

for i in "${folders[@]}"
do
    mkdir -p $i
    # Directories must belong to the web user, otherwise cycles cannot
    # create files there. 777/666 are not needed: rwx for the owner is enough.
    chown "$WEB_USER":"$WEB_USER" "$i" 2>/dev/null
    find "$i/" -exec chown "$WEB_USER":"$WEB_USER" {} + 2>/dev/null
    find "$i/" -type d -exec chmod 775 {} \;
    find "$i/" -type f -exec chmod 664 {} \;
done

echo "don't forget to run next command with sudo to enable voice in linux"
echo "sudo usermod -a -G audio www-data"
