var chatClient = {};

$(function() {

  var pteBase = "https://proteamedge.com/wp-content/themes/memberlite-child-master/pte_chat/";
  var pteAssets = pteBase + "assets/";

  var $chatWindow = $('#messages');


  var currentChannel = "";

  var username;

  ( function () {
    window.addEventListener( "message", ( event ) => {
      var name = event.data.name;

      if (event.data.name == 'pte_chat_message') {

        console.log("Chat Window: Received message...");


        pte_handle_chat_window_message(event);
        return;
      }
    });
  } () );


  $("#chat-input").emojioneArea({
    placeholder: "Message...",
    search: false,
  	pickerPosition: "top",
  	filtersPosition: "bottom",
    tones: true,
    autocomplete: true,
    inline: true,
    hidePickerOnBlur: true,
    textcomplete: {
        maxCount  : 8,
        placement : 'top'
    },
    attributes: {
        spellcheck : true
    },
    events: {
      keyup: function (event, key) {
        //console.log(key.keyCode);

        var $input = $('#chat-input');

          if (key.keyCode == 13) {
            currentChannel.sendMessage($input.val());
            $input.val('');
          }
      }
    }
  });

  function pte_handle_chat_window_message(event){

    var data = event.data.data;
    var channelId = data.channel_id;
    if (channelId) {
      console.log("Chat Window: Start Chat on Channel...", channelId);
      joinChannel(channelId);
    }
  }


  // Helper function to print info messages to the chat window
  function print(infoMessage, asHtml) {
    var $msg = $('<div class="info">');
    if (asHtml) {
      $msg.html(infoMessage);
    } else {
      $msg.text(infoMessage);
    }
    $chatWindow.append($msg);
  }

  function printMessage(fromUser, message, timestamp, msgsid) {
      var $user = $('<span class="username" style="font-size: 17px;font-weight: 500;">').text(fromUser + ':');
      if (fromUser == username) {
          $user.addClass('me');
          $chatWindow.append('<div class="chat chat-right chatupdate"><div class="chat-avatar"><a href="#" class="avatar"><img alt="John Doe" src="' + pteAssets + 'img/user.jpg" class="img-fluid rounded-circle"></a></div><div class="chat-body" style="margin-left: 5px;"><div class="chat-bubble"><div class="chat-content changemsg letfchatview" data-msgsid="'+ msgsid +'" data-msgs="'+ message +'" data-senduser="me" style="border-radius: 14px 20px 20px 2px !important;"><span class="chat-time" style="color: #080808;font-size: initial;">'+ fromUser +'</span><hr class="hrclass"><p>'+ message +'</p><span class="chat-time">'+ timestamp +'</span></div></div></div>');
      }else{
          $chatWindow.append('<div class="chat chat-left chatupdate"><div class="chat-avatar"><a href="#" class="avatar"><img alt="John Doe" src="' + pteAssets + 'img/user.jpg" class="img-fluid rounded-circle">   </a></div><div class="chat-body"><div class="chat-bubble"><div class="chat-content" style="max-width: 96%;" data-msgsid="'+ msgsid +'" data-msgs="'+ message +'" data-senduser="other"><span class="chat-time" style="color: #080808;font-size: initial;">'+ fromUser +'</span><hr class="hrclass"><p>'+ message +'</p><span class="chat-time">'+ timestamp +'</span></div></div></div>');
      }
      $chatWindow.scrollTop($chatWindow[0].scrollHeight);
      $('.chat-wrap-inner').scrollTop($chatWindow[0].scrollHeight);
  }

  // Alert the user they have been assigned a random username
  console.log('Chat: Logging in...');

  // Get an access token for the current user, passing a username (identity)
  // and a device ID - for browser-based apps, we'll always just use the
  // value "browser"
  $.getJSON( pteBase + 'token.php', {
    device: 'browser'
  }, function(data) {


    // Initialize the Chat client
    Twilio.Chat.Client.create(data.token).then(client => {
      console.log('Chat: Created client...');

      chatClient = client;
      console.log(chatClient);

    }).catch(error => {
      console.error(error);
    });
  });

  function joinChannelCall(_channel) {

    console.log('Chat: Calling join channel...');

    return _channel.join()
      .then(function(joinedChannel) {
        console.log('Joined channel...');

        return joinedChannel;
      })
      .catch(function(err) {
        if (_channel.status == 'joined') {
          return _channel;
        }
        console.error(
          "Couldn't join channel because -> " + err
        );
      });
  }

  function joinChannel(sid) {

    console.log('Chat: Attempting to join channel...', sid);

    chatClient.getChannelBySid(sid)
    .then(function(channel) {
      currentChannel = channel;
      console.log('Chat: Current channel...', sid);
      console.log(currentChannel);
      currentChannel.on('messageAdded', function(message) {
        console.log("Chat Window: chat message received...");

        //console.log(message);

        var currentTime = message.timestamp.toLocaleTimeString();
        printMessage(message.author, message.body, currentTime, message.sid);
      });

      joinChannelCall(currentChannel);


    }).catch(function() {
      // If it doesn't exist, let's create it
      }).then(function(channel) {

      }).catch(function(channel) {

      });
  }

    // Listen for new messages sent to the channe


  // Send a new message to the general channel

});
