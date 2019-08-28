#!/bin/sh
sudo apt update
sudo apt upgrade
sudo apt clean
sudo apt install unzip php-cli php-curl git nmap socat iptraf autossh librtlsdr-dev
sudo wget https://github.com/fg8oj/aisguadeloupe/raw/master/aisdeco2 -O /usr/local/bin/aisdeco2
sudo chmod +x /usr/local/bin/aisdeco2 
sudo wget https://raw.githubusercontent.com/fg8oj/aisguadeloupe/master/aisdeco2.service -O /etc/systemd/system/aisdeco2.service
sudo systemctl daemon-reload
sudo systemctl enable aisdeco2
sudo systemctl start aisdeco2
wget https://github.com/fg8oj/aisguadeloupe/archive/master.zip
unzip master.zip

