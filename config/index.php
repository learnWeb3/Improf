<?php

define("MINUTE", 60);
define("HOUR", MINUTE * 60);
define("DAY", HOUR * 24);

define('JWT_SECRET', "aaabbbccc");
define("JWT_EXP_TIME", DAY * 2);
define("JWT_ISS", "Coop");

define('ROOT_PATH', '/Coop');
define("PROTOCOL", strtolower(preg_replace("/\/\d+.\d+/", "", $_SERVER['SERVER_PROTOCOL'])));
define('ABSOLUTE_ROOT_PATH', PROTOCOL . "://" . $_SERVER['HTTP_HOST'] . ROOT_PATH);

define("UPLOAD_IMG_PATH", ROOT_PATH . "/public/img");
define('UPLOAD_IMG_SIZE_LIMIT', 10000000);

define('DB_HOST', "localhost");
define('DB_USERNAME', "root");
define('DB_PASSWORD', "root");
define('DB_CHARSET', "utf8");
define('DB_NAME', "improf");

define("PAGINATION_DEFAULT_OFFSET", 0);
define("PAGINATION_DEFAULT_LIMIT", 10);

define("CREDIT_APPOINTMENT_COST", 1);
define("DEFAULT_USER_CREDIT_BALANCE", 10);
define("CREDIT_FORMATION_CREATION", 2);

define("EMAIL_ACCOUNT", "improf.noreply@gmail.com");
define("ZOOM_CLIENT_ID", "******************");
define("ZOOM_CLIENT_SECRET", "******************");
define("ZOOM_REDIRECT_URI", "http://localhost:8000/Improf/zoom/callback");

define("FACEBOOK_CLIENT_ID", "*****************");
define("FACEBOOK_CLIENT_SECRET", "****************");
define("FACEBOOK_REDIRECT_URI", "http://localhost:8000/Improf/facebook/callback");
