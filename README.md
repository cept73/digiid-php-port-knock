Install
=========================================================================================================

For example, you want to open port 1234 on server for RDP
(It's may be many more services, RDP is for example)

You don't want it to be available directly to anybody, but
after Digi-ID

* If you want to work directly with RDP from local network,
  open Firewall and make rule with port 1234 with external 
  IPs equals local network

* Change Remote Desktop port from 3389 to port 1234
reg add HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\Control\Terminal Server\WinStations\RDP-Tcp \
	/v PortNumber /t REG_DWORD /d 1234 /f

* Restart RDP service

* Install web-server (for example OpenServer) with PHP 
  (include GMP library), MySQL.

* On local web-server site folder, write:
  git -b tex clone https://github.com/cept73/digiid-php-auth.git

* Copy config.example.php to config.php and change settings
  Specify port 1234, site name, other parameters.

* Redirect 80, 443 ports of the router to server.

* Get DynDNS for web-server (there are some free services)

* Get SSL certificate for web-server (there are some free services)


User
=========================================================================================================

If user want to use RDP, him must go to auth site first.

* If user don't have a DigiByte Wallet, him might download from official site.
* User scan QR with DigiByte Wallet.
*   If he don't use QR on this site before, he write name to field.
*   If his address is allowed by adminstrator (auth >= 1), he passed.
*   If not, he got a message.
* He may click the button to close his port after success auth.
