<?php

define('PTE_STANDARD_COLOR_COUNT', 10);

define('PTE_ROOT_URL', 'https://proteamedge.com/wp-content/themes/memberlite-child-master/');
define('PTE_ROOT_PATH', '/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/');
define('PTE_IMAGES_ROOT_URL', 'https://storage.googleapis.com/pte_media_store_1/');

define('PTE_DATE_FORMAT_STRING', 'MMM D, YYYY, h:mma');   //javascript formatting.
define('PTE_DATE_FORMAT_STRING_PHP', 'M j, Y, g:i a');   //php formatting.


//database

define('DB_HOST_RW', 'sky0001654.mdb0001643.db.skysql.net:5003');
define('DB_USERNAME_RW', 'DB00002069');
define('DB_PASSWORD_RW', 'U5lp93,RmjZbSs7199kn1X4');

define('DB_HOST_RO', 'sky0001654.mdb0001643.db.skysql.net:5003');
define('DB_USERNAME_RO', 'DB00002069');
define('DB_PASSWORD_RO', 'U5lp93,RmjZbSs7199kn1X4');

//transloadit
define('TRANSLOADIT_KEY', '0f89b090056541ff8ed17c5136cd7499');
define('TRANSLOADIT_SECRET', 'dc9f8e71f6dd1b7ab3c6093e850e12d11141ecb8');

//sendgrid
define('SENDGRID_KEY', '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');

// Twilio

define('ACCOUNT_SID', 'ACa3cfb8ff4e9f2b263e37a00f35c3e1ae');
define('AUTHTOKEN', '3571d56833a50968498e466dddd50ed4');
define('APPID', 'APcac98d559ccc8b1eba822b0219c6eb9e');
define('APIKEY', 'SK79bc191d001b636bba388d0da7d9c40d');
define('SECRETKEY', '90EJrypSfVoJBvCFOgtIzw7wa16TirF9');
define('CHATSERVICESID', 'ISd4ca1551946f4360a7dfb215ad84e1d0');
define('SYNCSERVICEID', 'ISa94e325859c77093fdf5d805d8d3d1fa');
define('CHANNELSSID', 'CHb368c3a4f22548b2966c7333adf45dc1');
define('MESSAGINGSERVICEID', 'MG5aa06d950ae9fd9fba6ed78abd176007');
define('CALLURL', 'https://proteamedge.com/wp-content/themes/memberlite-child-master/pte_chat');


define('FAX_DOCUMO_API_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VySWQiOiIxYzA3MDUxMS04NDcxLTQ5ZDItOWVmYS0yOTdmMWNjY2QwMDQiLCJpYXQiOjE1OTYwNjExNzR9.LjQuTk6iEL0oh6989vKNNoJZQLXfxT3O5EQA69IMzEU');
define('FAX_DOCUMO_ACCOUNT_ID', '7f2e187d-252d-4ada-8611-35933ac2923d');

//Importance
define('IMP_NETWORK_TOTAL', 15);
define('IMP_NETWORK_VIP', 1.0);
define('IMP_NETWORK_GENERAL', 0.7);

define('IMP_TOPIC_TOTAL', 15);
define('IMP_TOPIC_VIT', 1.0);
define('IMP_TOPIC_GENERAL', 0.7);

define('IMP_REVISIT_TOTAL', 15);

define('IMP_INTERACTION_TYPE_TOTAL', 15);
define('IMP_INTERACTION_TYPE_LOW', 0.2);
define('IMP_INTERACTION_TYPE_MED', 0.5);
define('IMP_INTERACTION_TYPE_HIGH', 1.0);

define('IMP_REQUIRES_ATTENTION_TOTAL', 40);
define('IMP_REQUIRES_ATTENTION_STEP', 1.0);
define('IMP_REQUIRES_ATTENTION_INFO', 0.75);

define('IMP_EASING_TOTAL', 0.3);

define('IMP_REQUIRES_ATTENTION_INFO_SECONDS', 30);

?>
