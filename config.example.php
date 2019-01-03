<?php
/*
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

// COPY TO config.php AND CHANGE PARAMS

// define your absolute url
define('DIGIID_SERVER_URL', '/'); // https is must!

// site language
define('DIGIID_LANG', 'en');

// if empty, no log
define('DIGIID_DEBUG_PATH', '');

// define database credentials
define('DIGIID_SITE_NAME', 'DigiID auth');
define('DIGIID_DB_HOST', 'localhost');
define('DIGIID_DB_NAME', 'digiid_db');
define('DIGIID_DB_USER', 'digiid_user');
define('DIGIID_DB_PASS', '111222333');
define('DIGIID_TBL_PREFIX', 'digiid_');
define('DIGIID_SECRET', 'mysecret'); // if not empty, who specified this secret, automatically will be admin
define('DIGIID_SITE_NAME', 'site_name');

// Google Analytics
define('DIGIID_GOOGLE_ANALYTICS_TAG', ''); // for example UA-123456789-1

// For Linux. Where hold IPs. Allow ALL ports (at current version)
define('DIGIID_IPS_PATH', '/var/www/html/ips');

// For Windows. Which port to open/close
define('DIGIID_OPENCLOSE_PORT', 1234);
define('DIGIID_OPENCLOSE_PROFILE', 'public');
