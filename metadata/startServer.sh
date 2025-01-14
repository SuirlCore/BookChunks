#!/bin/bash

# Open a single Konsole window with two split terminals
konsole --new-tab \
    --noclose --hold -e bash -c "ngrok http --url=reliably-apt-buzzard.ngrok-free.app 80" &
sleep 1

qdbus org.kde.konsole /Sessions/1 split-session-left-right
qdbus org.kde.konsole /Sessions/2 sendText "sudo tail -f /var/log/mysql/mariadb.log\n"
