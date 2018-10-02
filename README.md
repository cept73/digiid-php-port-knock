Task
=========================================================================================================

You want to use port 1234 on your server for Remote Desktop Protocol connections (for example). RDP must be unavailable directly to anybody, only after user authentication with Digi-ID. For exclusion, local network may connect without authentication.

https://www.youtube.com/watch?v=pLrQycud5GI

Install
=========================================================================================================

* If you want to work directly from local network, open Firewall and make rule: port = 1234, external IPs = local network

* Change Remote Desktop port from 3389 to port 1234:

reg add HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\Control\Terminal Server\WinStations\RDP-Tcp \
	/v PortNumber /t REG_DWORD /d 1234 /f

* Restart RDP service from Windows server services

* Install web-server (for example, free Open Server) with PHP (include GMP library), MySQL.

* Set some safe password to MySQL user and create new database with any name, which you will later add to config file. All tables will be created automatically when they will be need, so need only some database for write in.

* On local web-server site folder, write: 

git -b tex clone https://github.com/cept73/digiid-php-auth.git

* Copy config.example.php to config.php and change settings: specify port 1234, site name, other parameters.

* Configure router: redirect queries from 443 port to this server. If you want web-site is also be available from http:// redirect from 80 port too. 

* Get DynDNS for web-server (you may find some free services)

* Get SSL certificate for web-server (you may find some free services)

* Create a task for clean IPs list every morning, for example:

SchTasks /Create /SC DAILY /TN "Clean authenticated IPs list" /TR "%SYSTEMROOT%\system32\netsh advfirewall firewall set rule name='RDP DIGIID' new remoteip=127.0.0.1 action=allow" /ST 03:00

User side
=========================================================================================================

When user want to use RDP, he must go throw auth site first.

* If user don't have a DigiByte Wallet yet, he must download it. Links are available from your web-site.
* User scan QR with DigiByte Wallet application on his smart phone.
* If he doesn't registered on this site before, he write name to field and click Register.
* If his address is already allowed by adminstrator (auth >= 1) after previous registration, he passed. If not, he get a message.
* He may click the button to close his port after success auth. He may refresh page (F5) to add his new dynamic address to system and be able to log in again.

Admin side
==========================================================================================================

For allow access to user:
* Go to MySQL manager
* Open database, open table {prefix}user
* Find the line with new user and change 'auth' field to 1

If you don't want allow user to use port 1234 any more you may set auth=null for him..

You may also print for user information to wallet recovery and for remember his PIN: 
[English version](https://github.com/cept73/digiid-php-portknock/blob/master/DigiByte Wallet paper [en].pdf)
[Russian version](https://github.com/cept73/digiid-php-portknock/blob/master/DigiByte Wallet paper [ru].pdf)
