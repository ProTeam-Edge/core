<?php

$hostName = gethostname();
$domainName = (substr($hostName, 0, 16) == "pte-dev-staging-") ? "alct.pro" : "wiscle.com";

define('PTE_STANDARD_COLOR_COUNT', 10);

define('PTE_HOST_DOMAIN_NAME', $domainName);
define('PTE_ROOT_URL', "https://{$domainName}/wp-content/themes/memberlite-child-master/");
define('PTE_BASE_URL', "https://{$domainName}/");
define('PTE_ROOT_PATH', '/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/');
define('PTE_IMAGES_ROOT_URL', 'https://storage.googleapis.com/pte_media_store_1/');

define('PTE_DATE_FORMAT_STRING', 'MMM D, YYYY, h:mma');   //javascript formatting.
define('PTE_DATE_FORMAT_STRING_PHP', 'M j, Y, g:i a');   //php formatting.


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



?>
