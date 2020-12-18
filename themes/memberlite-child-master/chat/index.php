<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="../fa/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="./css/vendor/emojionearea.css">
  <link rel="stylesheet" type="text/css" href="./css/main.css">
</head>
<body>
  <div id="channel">
    <div id="channel-body">
      <div id='channel-toolbar'>
        <div id='channel-toolbar-left'><i class="far fa-user-friends" style="line-height: 40px; font-size: 24px; color: #444;"></i></div>
        <div id='channel-toolbar-right'></div>
        <div style='clear: both;'></div>
      </div>
      <div id="channel-chat">
        <div id="channel-messages"><ul></ul></div>
        <div id="channel-message-send">
          <div id="typing-indicator"><span></span></div>
          <textarea id="message-body-input"></textarea>
        </div>
      </div>
    </div>
  </div>
  <script src="https://code.jquery.com/jquery-2.1.4.js"></script>

  <!--

  <script src="https://media.twiliocdn.com/sdk/js/chat/v3.3/twilio-chat.min.js"></script>
<script src="https://media.twiliocdn.com/sdk/js/video/releases/1.14.0/twilio-video.js"></script>
 -->

 <script src="https://media.twiliocdn.com/sdk/js/chat/v3.3/twilio-chat.min.js"></script>
<script src="https://media.twiliocdn.com/sdk/js/video/releases/1.14.0/twilio-video.js"></script>

  <script type="text/javascript" src="./js/vendor/emojionearea.min.js"></script>
  <script type="text/javascript" src="./js/index.js"></script>
</body>
</html>
