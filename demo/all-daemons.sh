#!/bin/bash

TMPDIR='/tmp/imaginarium'
SCREENRC="$TMPDIR/daemon.rc"
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

mkdir `dirname $SCREENRC` -p

echo -n '' > "$SCREENRC"
echo 'caption always "%{= 45}%{+b w}Screen: %n | %h %=%t %c"' >> "$SCREENRC"
echo 'hardstatus alwayslastline "%-Lw%{= BW}%50>%n%f* %t%{-}%+Lw%<"' >> "$SCREENRC"
echo 'startup_message off' >> "$SCREENRC"

test -d "$TMPDIR" || mkdir "$TMPDIR" -p
if [ ! -d "$TMPDIR" ]
 then
  echo "NOT CREATED!!!!!!!"
  exit 1
fi
# RUN DAEMONS
find "$STORAGE"/* -maxdepth 0 -type d | while read i
do
    echo '' >> "$SCREENRC"
    NAME=`basename $i`
    COMMAND="$TMPDIR/$NAME.sh"
    echo '#!/bin/bash' > "$COMMAND"
    chmod 0755 "$COMMAND"
    echo 'while true; do' >> "$COMMAND"
    echo "LOGDIR_=\"$LOGDIR/\`date '+%Y/%m/%d/%H-%M'\`\"" >> "$COMMAND";
    echo "mkdir -p \"\$LOGDIR_\"" >> "$COMMAND"
    echo "su "$PHPUSER" -s \"$PHP\" $DIR/Gearman.php \"$NAME\" >> \"\$LOGDIR_/$NAME.log\"" >> "$COMMAND"
    echo 'done' >> "$COMMAND"
    echo "screen -t '$NAME' $COMMAND" >> "$SCREENRC"
done

screen -c "$SCREENRC" -S imaginarium -d -m
if [[ $? = 0 ]]
 then
  sleep 1
  #rm -r "$TMPDIR"
  exit 0
 else
  echo "NOT RUN SCREEN!"
  exit 1
fi
