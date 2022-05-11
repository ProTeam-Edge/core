<?php

$hostName = gethostname();
$domainName = (substr($hostName, 0, 16) == "pte-dev-staging-") ? "alct.pro" : "wiscle.com";

define('PTE_STANDARD_COLOR_COUNT', 10);

define('PTE_HOST_DOMAIN_NAME', $domainName);
define('PTE_ROOT_URL', "https://{$domainName}/wp-content/themes/memberlite-child-master/");
define('PTE_BASE_URL', "https://{$domainName}/");
define('PTE_ROOT_PATH', '/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/');
define('PTE_ROOT_DIST', '/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/dist/');
define('PTE_ROOT_DIST_FONTS', '/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/dist/assets/fonts/');
define('PTE_IMAGES_ROOT_URL', 'https://storage.googleapis.com/pte_media_store_1/');

define('WSC_PREVIEWS_PATH', '/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/wsc_preview/');
define('WSC_PREVIEWS_URL', "https://{$domainName}/wp-content/themes/memberlite-child-master/wsc_preview/");

define('PTE_DATE_FORMAT_STRING', 'MMM D, YYYY, h:mma');   //javascript formatting.
define('PTE_DATE_FORMAT_STRING_PHP', 'M j, Y, g:i a');   //php formatting.

//GoogleDrive

define('GOOGLE_STORAGE_KEY', '/var/www/html/proteamedge/private/proteam-edge-cf8495258f58.json');

//database

define('DB_HOST_RW', 'vit-all.mdb0001643.db.skysql.net:5001');
define('DB_USERNAME_RW', 'DB00004543');
define('DB_PASSWORD_RW', '71,nnfSKezJcvfKKYLOgLD,gN');

define('DB_HOST_RO', 'vit-all.mdb0001643.db.skysql.net:5001');
define('DB_USERNAME_RO', 'DB00004543');
define('DB_PASSWORD_RO', '71,nnfSKezJcvfKKYLOgLD,gN');

//transloadit
define('TRANSLOADIT_KEY', '0f89b090056541ff8ed17c5136cd7499');
define('TRANSLOADIT_SECRET', 'dc9f8e71f6dd1b7ab3c6093e850e12d11141ecb8');

//sendgrid
define('SENDGRID_KEY', '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');

// Twilio

define('ACCOUNT_SID', 'ACa3cfb8ff4e9f2b263e37a00f35c3e1ae');
//define('NOTIFYSSID', 'ISe2ce4eeed597d0b555132fa36d43f6be');
define('NOTIFYSSID', 'IS883be23baad9c7c9c47cdf834924a5e9');
define('FCMCREDENTIALSID', 'CRb88746a8b7fce85b786f1c5ed76e3645');
define('AUTHTOKEN', '3571d56833a50968498e466dddd50ed4');
define('APPID', 'APcac98d559ccc8b1eba822b0219c6eb9e');
define('APIKEY', 'SK79bc191d001b636bba388d0da7d9c40d');
define('SECRETKEY', '90EJrypSfVoJBvCFOgtIzw7wa16TirF9');
define('CHATSERVICESID', 'ISd4ca1551946f4360a7dfb215ad84e1d0');
define('SYNCSERVICEID', 'ISa94e325859c77093fdf5d805d8d3d1fa');
define('CHANNELSSID', 'CHb368c3a4f22548b2966c7333adf45dc1');
define('MESSAGINGSERVICEID', 'MG5aa06d950ae9fd9fba6ed78abd176007');
define('PUSHCREDENTIALSIDDEV', 'CR3f54eee0897fd59ac836fd9cec38e5f0');

define('alctApikeyssid', 'SKda65299852f60bada6e45acfec3d22fc');
define('alctApisecretkey', 'aN9qEfq2fzCpZpuQltfzgAHGuDnf5vrO');

define('CALLURL', "https://{$domainName}/wp-content/themes/memberlite-child-master/pte_chat");

define('FAX_DOCUMO_API_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VySWQiOiIxYzA3MDUxMS04NDcxLTQ5ZDItOWVmYS0yOTdmMWNjY2QwMDQiLCJpYXQiOjE1OTYwNjExNzR9.LjQuTk6iEL0oh6989vKNNoJZQLXfxT3O5EQA69IMzEU');
define('FAX_DOCUMO_ACCOUNT_ID', '7f2e187d-252d-4ada-8611-35933ac2923d');

//moralis

define('MORALIS_APPID', 'z40qQMDZOc7HYcIZEgSDyXRWyTgiUp5Ino0I6yww');
define('MORALIS_MK', 'JwJXP1eaPNNyRmNVWIvioqWgJeLF9ZDyC8Ei0JUW');
define('MORALIS_SERVER_URL', 'https://h2yoot4zcjng.usemoralis.com:2053');
define('MORALIS_API_KEY', 'va4yCbZSKEwurHgMOKvszRzWW1NUGEOalGvHsyxxCI2mNkA3t4jSo8dHpDPdoADR');


//TWITTER

define('TWITTER_CONSUMER_KEY', 'dI0TPoyC70xVZEpC0UJwJehfa');
define('TWITTER_CONSUMER_SECRET', 'xr4Rs0JJr5LRKtNp5RThrwJsK4m4gWuneVsMMpoE2fjkO7ckYR');
define('TWITTER_BEARER_TOKEN', 'AAAAAAAAAAAAAAAAAAAAAIw3QgEAAAAAaV7jwWq68aGfcIzCQ8vNmUcEm1A%3D0EM4EHij7KlFe372yhDj29QPeD9stePZA6nnRoWz7znML0vZwk');
define('TWITTER_ACCESS_TOKEN', '56469651-1qKL1X48zGUtU1sxCOj9r6403qNdGDCmFO54heNSU');
define('TWITTER_ACCESS_TOKEN_SECRET', 'ibOs7dTpJXA6s3LtlSYhUJcmO0WuZXlYG6AY2ExoiH2Hb');
define('TWITTER_OAUTH_CALLBACK', "https://{$domainName}/wp-content/themes/memberlite-child-master/wsc_twitter_oauth_handler.php");



?>
