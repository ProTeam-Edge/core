<?php
  include('/var/www/html/proteamedge/public/wp-blog-header.php');
  $pteRoot = "https://proteamedge.com/wp-content/themes/memberlite-child-master/pte_chat/";



 ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css?family=Fira+Sans:400,500,600,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="<?php echo $pteRoot;?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $pteRoot;?>assets/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $pteRoot;?>assets/css/emojionearea.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $pteRoot;?>assets/css/style.css">

    <script type="text/javascript" src="<?php echo $pteRoot;?>assets/js/jquery-3.2.1.min.js"></script>
    <script type="text/javascript" src="<?php echo $pteRoot;?>assets/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="<?php echo $pteRoot;?>assets/js/emojionearea.min.js"></script>

    <script type="text/javascript" src="https://media.twiliocdn.com/sdk/js/client/v1.11/twilio.min.js"></script>
    <script type="text/javascript" src="//media.twiliocdn.com/sdk/js/common/v0.1/twilio-common.min.js"></script>
    <script type="text/javascript" src="//media.twiliocdn.com/sdk/js/video/releases/1.14.0/twilio-video.js"></script>
    <script type="text/javascript" src="https://media.twiliocdn.com/sdk/js/chat/v1.0/twilio-chat.js"></script>

    <script type="text/javascript" src="<?php echo $pteRoot;?>assets/js/app.js"></script>
    <script type="text/javascript" src="<?php echo $pteRoot;?>assets/js/chat.js"></script>

    <?php

    require_once '../vendor/autoload.php';

    use Twilio\Jwt\ClientToken;
    use Twilio\Rest\Client;

    $channelId = isset($_GET['channel_id']) ? $_GET['channel_id'] : '';
    $userId = isset($_GET['user_id']) ? $_GET['user_id'] : '';

    ?>
</head>

<body>
<div class="main-wrapper">
    <div class="page-wrapper" style="margin-left: 0px;padding-top: 0px;">
        <div class="chat-main-row">
            <div class="chat-main-wrapper">
                <div class="col-lg-9 message-view task-view">
                    <div class="chat-window" style="max-height: 350px;">
                        <div class="fixed-header" style='padding: 3px 20px; background-color: rgb(240, 240, 240)'>
                            <div class="navbar">
                                <div class="user-details mr-auto">
                                    [ICONS]
                                </div>

                                <ul class="nav custom-menu">
                                    <li class="nav-item mute_audio" style="">
                                        <a class="nav-link" href="#" id="mute_audio_call" title="Mute Audio" style="height: 32px;"><i class="material-icons" style="font-size: 32px; color: #4499d7;">mic</i></a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="chat-contents">
                            <div class="chat-content-wrap">
                                <div class="chat-wrap-inner" style="background-color: white;">
                                    <div class="chat-box">
                                        <div class="chats" id="messages" style="padding: 5px 10px;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="chat-footer" style="padding: 5px 5px 3px 5px;">
                            <div class="message-bar">
                                    <div class="message-area">
                                        <div class="input-group">
                                            <textarea class="form-control" id="chat-input" placeholder="Message..." style="display: none; padding: 3px 6px; overflow: hidden; background-color: rgb(250, 250, 250); height: 50px;"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>

</html>
