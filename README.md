# aisguadeloupe

apt update && apt install unzip php-cli php-curl

cd /home/pi/
wget https://github.com/fg8oj/aisguadeloupe/archive/master.zip
unzip master.zip 

crontab -e
@reboot php /home/pi/aisguadeloupe-master/decode.ais.php
