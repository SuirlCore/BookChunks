#!/bin/bash

# Open Konsole and run the first command
konsole --hold --noclose -e "ngrok http --url=reliably-apt-buzzard.ngrok-free.app 80" &
sleep 1 # Allow time for the first tab/session to initialize

# Use qdbus to split the current view
qdbus org.kde.konsole /konsole/MainWindow_1 org.kde.konsole.MainWindow.splitViewLeftRight

# Send the second command to the right pane (new session)
qdbus org.kde.konsole /Sessions/2 sendText "sudo tail -f /var/log/mysql/mariadb.log\n"
