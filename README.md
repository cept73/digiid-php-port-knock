Task
=========================================================================================================

For example, you want to use port 1234 on your server for Remote Desktop Protocol connections. Such connections must be unavailable directly to anybody, only after user authentication with Digi-ID. For exclusion, local network may connect without authentication.

https://www.youtube.com/watch?v=pLrQycud5GI

Server side: Installation
=========================================================================================================

* If you want to work directly from local network without authentication, open Firewall and make rule: port = 1234, external IPs = local network
* Change Remote Desktop port from 3389 to port 1234:

```
reg add HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\Control\Terminal Server\WinStations\RDP-Tcp \
	/v PortNumber /t REG_DWORD /d 1234 /f
```

Also restart RDP service from Windows server services
**If you install web-server on Open Server, then you need user to be always logged in for work. Best case is to install web-server which running as a service. Apache2+PHP7+MySQL is the best solution instead of Open Server. Instruction is comming soon**

* Install web-server with PHP (include GMP library) and MySQL. For example, you may use ready all-in-one free pack: [Open Server](https://ospanel.io)).
* Set some safe password to MySQL user and create new database, which you will specify later in config file. All necessary tables will be created automatically on demand, so you need only some database.
* Go to web-server site folder (for Open Server, C:\OSPanel\domains\localhost), remove all files there, write command to download project: 

```
git clone https://github.com/cept73/digiid-php-port-knock.git .
```

* Copy config.example.php to config.php and change settings: specify 1234 port (which you set before), site name, other parameters.
* Configure router: redirect queries to 443th port -> 443th port of this server. If you want web-site is also be available from http:// redirect from 80 -> to 80 port too. 
* **Important:** get DynDNS for web-server (you may find some free services, for example [hldns](https://hldns.ru))
* **Important:** get SSL certificate for web-server's DynDNS address (you may find some free services, such as [letsencrypt](https://letsencrypt.org) or [sslforfree](https://www.sslforfree.com/))
* Create routers rule which make ability to open web-site outside local network.
* Create a task to clean IPs list every night, for example at 3 o'clock:

```
SchTasks /Create /SC DAILY /TN "Clean authenticated IPs list" \
	/TR "%SYSTEMROOT%\system32\netsh advfirewall firewall set rule name='RDP DIGIID' \
	new remoteip=127.0.0.1 action=allow" /ST 03:00
```

User side
=========================================================================================================

When user wanted to use RDP, he must go throw auth site first.

* User doesn't have a DigiByte Wallet yet? He download it from https://www.digibyte.co/digibyte-wallet-downloads or using links from installed web-site before.
* User opened web-site, scan QR with DigiByte Wallet application on his smart phone.
* If user doesn't registered yet on this site, he write name or some information and click Register.
* If user's address is already allowed by adminstrator (auth >= 1), he passed. If not, he gets a message.
* User may click the button to close his port after success auth. He may also don't click this button and keep page opened to refresh page later (F5) - it's will allow his current dynamic address to log in.

User may also print information for wallet recovery and for remembering his PIN: 
[English version](https://github.com/cept73/digiid-php-portknock/blob/master/DigiByte Wallet paper [en].pdf)
[Russian version](https://github.com/cept73/digiid-php-portknock/blob/master/DigiByte Wallet paper [ru].pdf)

Admin side
==========================================================================================================

To allow access to user:
* Go to MySQL manager (for Open Server: click the button on the flag icon in system tray and select Additional in Menu, then MySQL Manager)
* Open the database, find the table "user" with prefix in name (which specified inside config file)
* Find the line with new user and change 'auth' field to 1

If you don't want to allow user use port 1234 any more, you may set auth=null for him..
