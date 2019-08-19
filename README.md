# AIS Guadeloupe

## Routeur de trafic AIS pour affichage des navires autour des îles de Guadeloupe et détection de la propagation. Installation sur Raspberry PI https://ais.radioamateur.gp/

### Installation :

apt update && apt install unzip php-cli php-curl

cd /home/pi/
wget https://github.com/fg8oj/aisguadeloupe/archive/master.zip
unzip master.zip 

crontab -e
@reboot php /home/pi/aisguadeloupe-master/decode.ais.php

### Edition de vos paramétres : 

nano /home/pi/aisguadeloupe-master/decode.ais.php

$station='FG5ZBX'; <--- Votre indicatif radioamateur ou pseudo

$station_email='info@fg8oj.com'; <--- Votre email pour recevoir les alertes propagations ou coupures de votre récéption

$station_lon=-61.311395; <--- latitude et longitude  de votre station

$station_lat=16.251922;  <--- 
