#!/bin/sh
cat <<EOF >no-rtl.conf
blacklist dvb_usb_rtl28xxu
blacklist rtl2832
blacklist rtl2830
EOF
sudo tee /etc/modprobe.d/rtl-sdr-blacklist.conf >/dev/null << _EOF 
blacklist dvb_usb_rtl28xxu
blacklist e4000
blacklist rtl2832
_EOF
sudo mv no-rtl.conf /etc/modprobe.d/
sudo apt update
sudo apt upgrade
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
sudo wget https://github.com/fg8oj/aisguadeloupe/raw/master/aisdeco2 -O /usr/local/bin/aisdeco2
sudo chmod +x /usr/local/bin/aisdeco2 
sudo wget https://raw.githubusercontent.com/fg8oj/aisguadeloupe/master/aisdeco2.service -O /etc/systemd/system/aisdeco2.service
sudo systemctl daemon-reload
sudo systemctl enable aisdeco2
sudo systemctl start aisdeco2
wget https://github.com/fg8oj/aisguadeloupe/archive/master.zip
unzip master.zip

