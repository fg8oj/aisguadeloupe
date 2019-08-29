#!/bin/sh
sudo tee /etc/modprobe.d/rtl-sdr-blacklist.conf >/dev/null << _EOF 
blacklist dvb_usb_rtl28xxu
blacklist e4000
blacklist rtl2832
blacklist rtl2830
_EOF
sudo mv no-rtl.conf /etc/modprobe.d/
sudo apt update
sudo apt upgrade -y 
sudo apt clean
sudo apt install -y unzip php-cli php-curl git nmap socat iptraf autossh librtlsdr-dev tcpdump telnet cmake build-essential
git clone git://git.osmocom.org/rtl-sdr.git
cd rtl-sdr/
mkdir build
cd build
cmake ../ -DINSTALL_UDEV_RULES=ON -DDETACH_KERNEL_DRIVER=ON
make
sudo make install
sudo ldconfig
cd ~
sudo cp ./rtl-sdr/rtl-sdr.rules /etc/udev/rules.d/
git clone https://github.com/dgiardini/rtl-ais 
cd rtl-ais
make
cd ~
sudo cp rtl-ais/rtl_ais /usr/local/bin/rtl_ais
sudo wget https://raw.githubusercontent.com/fg8oj/aisguadeloupe/master/ais.service -O /etc/systemd/system/ais.service
sudo systemctl daemon-reload
sudo systemctl enable ais
sudo systemctl start ais
wget https://github.com/fg8oj/aisguadeloupe/archive/master.zip
unzip master.zip

