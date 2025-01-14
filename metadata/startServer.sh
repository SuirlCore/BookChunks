#!/bin/bash

# Open Konsole and create a new session
konsole --noclose --hold -e bash -c "ngrok http --url=reliably-apt-buzzard.ngrok-free.app 80" &
sleep 1 # Allow the first session to initialize

# Use qdbus to split the session
qdbus org.kde.konsole /konsole/MainWindow_1 org.kde.konsole.MainWindow.splitViewLeftRight

# Send the second command to the new (right) split
qdbus org.kde.konsole /Sessions/2 sendText "sudo tail -f /var/log/mysql/mariadb.log\n"
