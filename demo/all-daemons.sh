#!/bin/bash

SCREENRC='/tmp/screenrc/daemon.rc'
DIR='/PATH/TO/PROJECT/ROOT/DIR'
PHP='/usr/bin/php'
LOGDIR='/var/log/imaginarium-daemon'
PHPUSER='www-data'

# /for example only!
DIR=`dirname "$0"`
DIR=`cd "$DIR/../"; pwd`
PHP='/usr/bin/php7.0'
LOGDIR='/tmp'
# /for example only!

STORAGE="$DIR/storage"

mkdir `dirname $SCREENRC -p`

echo -n '' > "$SCREENRC"
echo 'caption always "%{= 45}%{+b w}Screen: %n | %h %=%t %c"' >> "$SCREENRC"
echo 'hardstatus alwayslastline "%-Lw%{= BW}%50>%n%f* %t%{-}%+Lw%<"' >> "$SCREENRC"
echo 'startup_message off' >> "$SCREENRC"

# RUN DAEMONS
find "$STORAGE"/* -maxdepth 0 -type d | while read i
do
    echo '' >> "$SCREENRC"
    #echo "chdir $STORAGE/$i" >> "$SCREENRC"
    NAME=`basename $i`
    DO="LOGDIR_=\"$LOGDIR/\`date '+%Y/%m/%d/%H-%M'\`\"; mkdir -p \"\$LOGDIR_\"; su "$PHPUSER" -s /bin/bash \"$PHP\" $DIR/Gearman.php \"$NAME\" >> \"\$LOGDIR_/$NAME.log\"; "
    echo "screen -t \"$NAME\" while true; do $DO; done" >> "$SCREENRC"
done

`screen -c $SCREENRC -S imaginarium -d -m`
rm $SCREENRC
