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
<div id="pte_all_media"></div>
  <div id="channel">
      <div id="channel-body">
        <div id="channel-chat">

          <div id="pte_chat_activity_area">
            <div class="pte_chat_activity_title">Chat</div>
            <div id="pte_chat_no_chats_message">- No Unread -</div>
            <ul id="pte_chat_activity_list"></ul>

            <div class="pte_chat_activity_title">Audio</div>
            <div id="pte_chat_no_video_message" >- No Active Audio -</div>
            <ul id="pte_audio_activity_list"></ul>
          </div>

          <div id="pte_chat_messages_area">
            <div id="channel-messages"><ul></ul></div>
            <div id="channel-message-send">
              <div class="pte_typing_bar">
                <div id="typing-indicator" class="pte_typing_area"><span></span></div>
                <div class="pte_send_area">
                  <button id="buttercup_easter_egg" class="pte_button_disabled" onclick="pte_send_chat(this);" title="Or, press CTRL+Enter">Send</button>
                </div>
              </div>
              <textarea id="message-body-input"></textarea>
            </div>
          </div>

        </div>
      </div>
    </div>

  <script src="https://code.jquery.com/jquery-2.1.4.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.textcomplete/1.8.5/jquery.textcomplete.min.js"></script>

  <script type="text/javascript" src="./js/twilio-chat.min.js"></script>
  <script type="text/javascript" src="./js/twilio-video.min.js"></script>
  <script type="text/javascript" src="./js/hark.bundle.js"></script>

  <!-- The core Firebase JS SDK is always required and must be listed first -->
<script src="https://www.gstatic.com/firebasejs/8.6.3/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.6.3/firebase-messaging.js"></script>

<script>
  // Your web app's Firebase configuration
  var firebaseConfig = {
    apiKey: "AIzaSyDf6aIUvgp5g7nXMwVzbFZ1yTnTCzo4l-Q",
    authDomain: "alctpro-26fc9.firebaseapp.com",
    projectId: "alctpro-26fc9",
    storageBucket: "alctpro-26fc9.appspot.com",
    messagingSenderId: "1009836905958",
    appId: "1:1009836905958:web:18dd9a7ceb5b0bae057227"
  };
  // Initialize Firebase
  firebase.initializeApp(firebaseConfig);
</script>

  <script type="text/javascript" src="./js/vendor/emojionearea.min.js"></script>
  <script type="text/javascript" src="./js/index.js"></script>

</body>
</html>
