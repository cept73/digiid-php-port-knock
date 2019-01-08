<?php
/*
Copyright 2018 Sergey Taranov
Copyright 2014 Daniel Esteban

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

// Require config.php
if (!file_exists("config.php")) die ("Config.php file is not exists");

session_start ();

// Require users operations
require_once dirname(__FILE__) . "/classes/users.php";

// Current stored value
$user_addr = $user_info = false;
// He is already specified QR
if (isset($_SESSION['user']['address'])) {
	// Load all we already know about user
	$user_addr = $_SESSION['user']['address'];
	// Check, maybe user updated by admin
	$user = new token_user ($user_addr);
	$user_info = $_SESSION['user']['info'] = $user->get_info();
}

// Lang pack
$lang = defined('DIGIID_LANG') ? DIGIID_LANG : 'en';
$path_prefix = dirname(__FILE__) . "/lang_";
if (!file_exists("{$path_prefix}{$lang}.php")) die ("Language pack don't exists");
require_once "{$path_prefix}{$lang}.php";

// Steps:
// 1 - Scan QR first. 
// 2 - Wait details for registration. 
// 3 - already logged in
$step = ($user_addr && $user_info) ? 3 : (isset($_SESSION['user']) ? 2 : 1);

if ($step == 1) {
	// DigiID is required for login (do not modify)
	// DAO could be replace by your CMS/FRAMEWORK database classes
	require_once dirname(__FILE__) . "/classes/DigiID.php";
	require_once dirname(__FILE__) . "/classes/DAO.php";
	$digiid = new DigiID();

	// generate a nonce
	$nonce = $digiid->generateNonce();

	// build uri with nonce, nonce is optional, but we pre-calculate it to avoid extracting it later
	$digiid_uri = $digiid->buildURI(DIGIID_SERVER_URL . '/callback.php', $nonce);

	// Insert nonce + IP in the database to avoid an attacker go and try several nonces
	// This will only allow one nonce per IP, but it could be easily modified to allow severals per IP
	// (this is deleted after an user successfully log in the system, so only will collide if two or more users try to log in at the same time)
	$dao = new DAO();
	$result = $dao->insert($nonce, @$_SERVER['REMOTE_ADDR']);
	if (!$result) die ('dao');
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<title><?= (DIGIID_SITE_NAME != '') ? DIGIID_SITE_NAME : $text['site_name'] ?></title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<base href="<?= DIGIID_SERVER_URL ?>">
	<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<link rel="stylesheet" type="text/css" href="css/main.css">
<?php if (!empty(DIGIID_GOOGLE_ANALYTICS_TAG)) : ?><!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=<?= DIGIID_GOOGLE_ANALYTICS_TAG ?>"></script>
	<script>window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date()); gtag('config', '<?= DIGIID_GOOGLE_ANALYTICS_TAG ?>');</script>
<?php endif ?>
</head>
<body>
	
	<div class="limiter">
		<div class="container-login">
			<div class="wrap-login">
<?php if ($step == 1) : ?>
				<div id="step1" class="login-form step">
					<div class="bigscreen-padding hidden-xs"></div>
					<span class="login-form-title" style="padding-bottom: 20px">
						<?= $text['scan'] ?>:
					</span>
					<div class="center">
						<a href="<?= $digiid_uri ?>"><div><img id="qr" alt="<?= $text['click_qr'] ?>" border="0" /></div></a>
						<p class="comment"><?= $text['throw_digibyte'] ?>:</p>
						<p class="applications">
							<a href="https://itunes.apple.com/us/app/digibyte/id1378061425" target="_blank"><img src="images/appstore.png" height="32px" /></a>
							<a href="https://play.google.com/store/apps/details?id=io.digibyte" target="_blank"><img src="images/android.png" height="32px" /></a>
						</p>
						<p class="comment">
							<a href="https://www.youtube.com/watch?v=pLrQycud5GI" class="link"><?= $text['know_more'] ?></a>
						</p>
					</div>
				</div>
<?php elseif ($step == 2) : ?>
				<div id="step2" class="login-form step">
					<div class="bigscreen-padding hidden-xs"></div>
					<form id="regform" action="<?= DIGIID_SERVER_URL ?>/register.php" method="post">
					<span class="login-form-title" style="padding-bottom: 42px;">
						<?= $text['unknown_device'] ?>
						<?= $user_addr ? "<span class=\"info\">$user_addr</span>" : '' ?>
					</span>
					<div class="wrap-input100">
						<input class="input100" type="text" name="fio" required="true">
						<span class="focus-input100"></span>
						<span class="label-input100"><?= $text['your_name'] ?>:</span>
					</div>
					<div class="wrap-input100">
						<input class="input100" type="text" name="secret" required="false">
						<span class="focus-input100"></span>
						<span class="label-input100"><?= $text['secret'] ?>:</span>
					</div>
					<div class="container-login-form-btn">
						<input type="submit" class="login-form-btn main" value="<?= $text['register'] ?>" />
					</div>
					<p class="address" style="padding-bottom: 42px"><?= $text['contact_admin'] ?></p>
					</form>

					<form action="<?= DIGIID_SERVER_URL ?>/logout.php" method="post">
					<div class="container-login-form-btn" style="margin-top:5px">
						<input type="submit" class="login-form-btn" value="<?= $text['cancel_query'] ?>" />
					</div>
					</form>
				</div>
<?php elseif ($step == 3) : ?>
				<div id="step3" class="login-form step" style="padding-bottom: 42px;">
					<div class="bigscreen-padding hidden-xs"></div>
					<p class="login-form-title"><?= intval($user_info['auth']) > 0 ? $text['access_approved'] : $text['access_guest'] ?></p>
					<p class="info"><?= $text['your_ip_is'] ?>: <strong><?= $_SERVER["REMOTE_ADDR"] ?></strong></p>
					<div class="container-login-form-btn" style="margin-top:5px; padding: 0 30px">
					<form action="<?= DIGIID_SERVER_URL ?>/logout.php" method="post">
						<input type="submit" class="login-form-btn" value="<?= $text['close_access'] ?>" />
					</form>
					</div>
				</div>
<?php endif ?>

				<div class="login-more" style="background-image: url(images/bg-01.default.jpg);">
				</div>
			</div>
		</div>
	</div>
	
	<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
	<script src="js/digiQR.min.js"></script>
<?php if ($step == 1) : ?>
    <script>var nonce='<?= $nonce ?>'; $("#qr").attr("src", DigiQR.id("<?= $digiid_uri ?>",300,2,0.5));
        const PRESS_TO_UPDATE = "<?= $text['press_to_update_qr'] ?>";</script>
	<script src="js/startpage.js"></script>
<?php elseif ($step == 3) : ?>
	<script>$.ajax({'url': 'open_fw.php'});</script>
<?php endif ?>
</body>
</html>