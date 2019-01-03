Task
=========================================================================================================

Linux: server DigiID firewall

How it's works:

1️⃣ Open on server for everybody only two ports: 80 (http), 443 (https). Close all others.

2️⃣ When user enter url of server, he arrive to web-site with DigiID authentication

3️⃣ If he is there for a first time, system prompts for registration. User may enter secret words (which set by admin before) and became "friendly user". Also admin may change theirs state throw mysql.

4️⃣ After successfull log in for  good users IPs, system will  open other ports (list can be tuned throw firewall)

5️⃣ When user end his work, he may click Close connection and system close door for his IP.

6️⃣ If he didnt close and gone, system clear list of current logged users daily at 1 o'clock

Windows: for example, you want to use port 1234 on your server for Remote Desktop Protocol connections. Such connections must be unavailable directly to anybody, only after user authentication with Digi-ID. For exclusion, local network may connect without authentication.

If you like this project, donate some digibytes to DPZ9BncvaCRx7vMXN6dAQNnXzTP6JVahqj

https://www.youtube.com/watch?v=pLrQycud5GI

Linux: Installation
=========================================================================================================

Step-by-step:

1️⃣ Clone project `git clone https://github.com/cept73/digiid-php-port-knock.git /var/www/html`

2️⃣ (not mandatory) You may specify other settings in `/var/www/html/install/install` script in SETTINGS section (specify db name, db user,  ..):
```
# SETTINGS
....
....
# /SETTINGS
```
I prefer do it throw mcedit: `apt-get install mcedit` and `mcedit /var/www/html/install/install`

3️⃣ Run: `/var/www/html/install/install`. System at some stage also open configuration file, correct and save it to continue. 

4️⃣ Prepare domain

Get some **domain name** (if you have not yet). You may buy it or get it free, for example on https://hldns.ru

Get some **SSL certificate**. Easy way to get free 3 months certificate (renew it every 3 months): 
- Go to https://zerossl.com/free-ssl/#crt
- Enter domain name, accept ZeroSSL TOS and Lets Encrypt SA, go next step
- Next
- Copy specified text to file with specified name in folder /var/www/html/.well-known/...
- Copy first certificate to /etc/ssl/certs/ssl-cert-snakeoil.pem
- Copy second certificate (private key) to /etc/ssl/private/ssl-cert-snakeoil.key 
(if you select other names, also correct /etc/apache2/sites-available/digiid-ssl.conf params SSLCertificateFile, SSLCertificateKeyFile)

5️⃣ Run: `/var/www/html/install/install-end`

Check is it works and remove install folder (/var/www/html/install)

Windows Server: Installation
=========================================================================================================

* If you want to work directly from local network without authentication, open Firewall and make rule: port = 1234, external IPs = local network
* Change Remote Desktop port from 3389 to port 1234:

```
reg add HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\Control\Terminal Server\WinStations\RDP-Tcp \
	/v PortNumber /t REG_DWORD /d 1234 /f
```

Also restart RDP service from Windows server services
**If you install web-server on Open Server, then you need OR run web-server as-service OR user to be always logged in for work. Best case is to install web-server which running as a service. Apache2+PHP7+MySQL is the best solution instead of Open Server.**

* Install web-server with PHP (include GMP library) and MySQL. As quick solution, you may use ready all-in-one free pack: [Open Server](https://ospanel.io)).
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
[English version](https://github.com/cept73/digiid-php-portknock/blob/master/DigiByte_Wallet_paper_[en].pdf)
[Russian version](https://github.com/cept73/digiid-php-portknock/blob/master/DigiByte_Wallet_paper_[ru].pdf)

Admin side
==========================================================================================================

To allow access to user:
* Go to MySQL manager (for Open Server: click the button on the flag icon in system tray and select Additional in Menu, then MySQL Manager). For linux recommend to use mysql-workbench
* Open the database, find the table "user" with prefix in name (which specified inside config file)
* Find the line with new user and change 'auth' field to 1

If you don't want to allow user communicate with server any more, you may set auth=null for him..
