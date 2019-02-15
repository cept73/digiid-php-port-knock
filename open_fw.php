<?php
/*
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*/
// Detect server operation system
$fwlib = (strtoupper(php_uname('s')) == 'LINUX') ? 'firewall-linux' : 'firewall';
session_start();
require_once dirname(__FILE__) . "/config.php";
require_once dirname(__FILE__) . "/classes/$fwlib.php";

// Using port and network profile (public, private, domain, any)
$fw = new firewall (DIGIID_OPENCLOSE_PORT, DIGIID_OPENCLOSE_PROFILE);

// Run from command line?
if (!empty($argv)) foreach ($argv as $param) {
	if (substr($param,0,3) == 'off') {
		if (!substr($param,3)) die ($param);
		$fw->del_ip (substr($param,3));
		exit; 
	}
}

// No user logged in
if (empty($_SESSION['user']['address']) || empty($_SESSION['user']['info'])) {
	header ('location:' . DIGIID_SERVER_URL);
	//echo json_encode (array ('error' => 'Что-то пусто'));
	exit;
}

// Find signal to stop
$signal_off = isset($_REQUEST['off']);
if ($signal_off) {

	// Delete IP address, change to 127.0.0.1 if empty
	$fw->del_ip ();
	unset ($_SESSION['user']);

	if (!isset($_REQUEST['silent']))
		echo json_encode (array('result' => 'Остановлено'));

	return;
}

// No auth permission
if (intval($_SESSION['user']['info']['auth']) < 1) {
	unset ($_SESSION['user']);
	header ('location:' . DIGIID_SERVER_URL);
	//echo json_encode (array ('error' => 'Вход станет доступен после проверки администратором'));
	exit;
}

// Add user IP address
$fw->add_ip ();

if (!isset($_REQUEST['silent']))
	echo json_encode (array('result' => 'Вход выполнен. Можете заходить'));
