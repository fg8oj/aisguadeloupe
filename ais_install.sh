#!/bin/sh
wget https://raw.githubusercontent.com/fg8oj/aisguadeloupe/master/ais.service -O /etc/systemd/system/ais.service
sudo systemctl daemon-reload
sudo systemctl enable ais
sudo systemctl start ais
