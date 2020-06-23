# AIS Guadeloupe

## Routeur de trafic AIS pour affichage des navires autour des îles de Guadeloupe et détection de la propagation. Installation sur Raspberry PI https://ais.radioamateur.gp/

### Installation :

apt-get update && sudo apt-get upgrade && apt install unzip php-cli php-curl git nmap socat iptraf autossh && apt install rtl-sdr librtlsdr-dev libusb-1.0-0-dev  && 
apt install -y build-essential debhelper dh-systemd libusb-1.0-0-dev pkg-config libncurses5-dev git libfftw3-bin libfftw3-dev

cd /home/pi/ && 
wget https://github.com/fg8oj/aisguadeloupe/archive/master.zip && 
unzip master.zip 

crontab -e
@reboot rtl_ais -l 161.966M -r 162.015M -g 48 -T -h 127.0.0.1 -P 4000
@reboot php /home/pi/aisguadeloupe-master/decode.ais.php

### Edition de vos paramétres : 

nano /home/pi/aisguadeloupe-master/decode.ais.php

$station='FG5ZBX'; <--- Votre indicatif radioamateur ou pseudo

$station_email='info@fg8oj.com'; <--- Votre email pour recevoir les alertes propagations ou coupures de votre récéption

$station_lon=-61.311395; <--- latitude et longitude  de votre station

$station_lat=16.251922;  <--- 

### Rebootez le raspberry

reboot

Après quelques minutes, votre station doit apparaitre sur https://ais.radioamateur.gp/
