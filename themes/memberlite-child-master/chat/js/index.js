//TODO Make sure that server room management events are received and handled properly. Including a room dissappearing.
var activeChannel;
var activeChannelMeta = {};

var client;
var typingMembers = new Set();

var activeChannelPage;

var userContext = {};

$(document).ready(function() {

    ( function () {
      window.addEventListener( "message", ( event ) => {
        var name = event.data.name;

      console.log("Chat Received Message From Parent...");
      console.log(event);

        switch(name) {
  				case 'pte_chat_message':
            pte_handle_chat_start(event);
  				break;
  				case 'pte_channel_deleted':
            pte_set_chat("disabled");
            activeChannelMeta = {"name": "pte_channel_stop"}
            pte_parent_message_send();
            clearCurrentChannel();
  				break;
  			}
      });
    } () );

    $("#message-body-input").emojioneArea({
      placeholder: "Message...",
      search: false,
    	pickerPosition: "top",
    	filtersPosition: "bottom",
      tones: true,
      inline: true,
      hidePickerOnBlur: true,
      autocomplete: false,
      attributes: {
          spellcheck : true
      },
      events: {
        keyup: function (event, key) {
            if (key.keyCode == 13) {  //TODO consider multiline with ENTER support -- means send button and ctrl+enter for send
              var body = this.getText();
              if (body) {
                var eArea = this;
                activeChannel.sendMessage(body).then(function() {
                  eArea.setText("").setFocus();
                  $('#channel-messages').scrollTop($('#channel-messages ul').height());
                  $('#channel-messages li.last-read').removeClass('last-read');
                });
              }
            } else if (activeChannel) {
              activeChannel.typing();
            }
        }
      }
    });

    logIn();

    function pte_set_chat(state){

    	if (state == 'disabled') {
        $('#channel').hide();
    	} else {
        $('#channel').show();
    	}
    }

    function pte_handle_chat_start(event){
      //TODO Handle Switching between profile and vault views - should not reload chat...
      var data = event.data.data;
      var channelId = data.channel_id;
      if (channelId) {
        activeChannelMeta = data;
        joinChannel(channelId);
        pte_set_chat("enabled")
      } else {
        pte_set_chat("disabled")
        activeChannelMeta = {"name": "pte_channel_stop"}
        pte_parent_message_send();
        clearCurrentChannel();
      }
    }


function joinChannel(sid) {
  $('#channel-messages ul').empty();

  client.getChannelBySid(sid)
  .then(function(channel) {

    activeChannel = channel;
    setActiveChannel(activeChannel);

  }).catch(function() {

    }).then(function(channel) {

    }).catch(function(channel) {

    });


}


  var isUpdatingConsumption = false;
  $('#channel-messages').on('scroll', function(e) {
    var $messages = $('#channel-messages');

    if ($('#channel-messages ul').height() - 50 < $messages.scrollTop() + $messages.height()) {
      activeChannel.getMessages(1).then(messages => {
        var newestMessageIndex = messages.length ? messages[0].index : 0;
        if (!isUpdatingConsumption && activeChannel.lastConsumedMessageIndex !== newestMessageIndex) {
          isUpdatingConsumption = true;
          activeChannel.updateLastConsumedMessageIndex(newestMessageIndex).then(function() {
            isUpdatingConsumption = false;
          });
        }
      });
    }

    var self = $(this);
    if($messages.scrollTop() < 50 && activeChannelPage && activeChannelPage.hasPrevPage && !self.hasClass('loader')) {
      self.addClass('loader');
      var initialHeight = $('ul', self).height();
      activeChannelPage.prevPage().then(page => {
        page.items.reverse().forEach(prependMessage);
        activeChannelPage = page;
        var difference = $('ul', self).height() - initialHeight;
        self.scrollTop(difference);
        self.removeClass('loader');
      });
    }
  });

});


function logIn() {

//TODO ASAP BEFORE SHIP. NEED TO go back to original mechanism or change out the "request" mechanism for token management

  $.getJSON( '../pte_chat/token.php', {
    device: 'browser'
  }, function(data) {

    userContext = {identity: data.identity};

    Twilio.Chat.Client.create(data.token, { logLevel: 'info' })
      .then(function(createdClient) {
        client = createdClient;
        client.on('tokenAboutToExpire', () => {
          $.getJSON( '../pte_chat/token.php', {
            device: 'browser'
          }, function(data) {
            if (data.token) {
              console.log('Got new token!');
              client.updateToken(data.token);
              userContext = {identity: data.identity};
            } else {
              console.error('Failed to get a token ');
              throw new Error(data);
            }
          });
        });

        client.on('channelJoined', function(channel) {
          channel.on('messageAdded', updateUnreadMessages);
        });

      })
      .catch(function(err) {
        throw err;
      })


  });

}
function updateUnreadMessages(message) {
  var channel = message.channel;
  if (channel !== activeChannel) {  //TODO Manage new chat events

    console.log("Updating Unread Messsage...");
    console.log(message);

  }
}

function updateMessages() {
  $('#channel-messages ul').empty();
  activeChannel.getMessages(30).then(function(page) {
    page.items.forEach(addMessage);
  });
}

function removeMessage(message) {
  $('#channel-messages li[data-index=' + message.index + ']').remove();
}

function updateMessage(args) {
  var $el = $('#channel-messages li[data-index=' + args.message.index + ']');
  $el.empty();
  createMessage(args.message, $el);
}


function createMessage(message, $el) {
    if (userContext.identity == message.author) {
      createMessageOwner(message, $el);
    } else {
      createMessageUser(message, $el);
    }
}


function createMessageUser(message, $el) {

  message.getMember()
  .then(function(member) {
      member.getUser()
      .then(function(user) {

        var attributes = user.attributes;
        var imageHandle = "https://storage.googleapis.com/pte_media_store_1/" + attributes.image_handle;

        var time = message.timestamp;
        var minutes = time.getMinutes();
        var ampm = Math.floor(time.getHours()/12) ? 'PM' : 'AM';

        if (minutes < 10) { minutes = '0' + minutes; }

        var html = '<div class="pte_container">';
            html += '<div class="pte_line_user">';
            html += '<div class="pte_topic_icon_holder">';
            html += '<img src="' + imageHandle + '" class="pte_topic_icon">';
            html += '</div>';
            html += '<div class="pte_topic_body">';
            html += '<span style="font-size: 12px; font-weight: bold;">' + user.friendlyName + '</span></br>';
            html +=  message.body;
            html += '<span class="timestamp"  style="font-size: 10px;">' + ' ' + (time.getHours()%12) + ':' + minutes + '' + ampm + ''  + '</span>';
            html += '</div>';
            html += '<div style="clear:both;">';
            html += '<p class="last-read">New Messages</p>';
            html += '<p class="members-read"/>';
            html += '</div>';
            html += '</div>';

            $el.append(html);

      }).catch(function() {
        // If it doesn't exist, let's create it
      });



  }).catch(function() {
    // If it doesn't exist, let's create it
  });



}


function createMessageOwner(message, $el) {

    var time = message.timestamp;
    var minutes = time.getMinutes();
    var ampm = Math.floor(time.getHours()/12) ? 'PM' : 'AM';

    if (minutes < 10) { minutes = '0' + minutes; }

  var html = '<div class="pte_container">';
      html += '<div class="pte_line_owner">';
      html += '<div class="pte_message_line">';
      html += message.body;
      html += '<span class="timestamp"  style="font-size: 10px;">' + ' ' + (time.getHours()%12) + ':' + minutes + '' + ampm + ''  + '</span>';
      html += '</div>';
      html += '<div class="edit-body"></div>';
      html += '<p class="last-read">New Messages</p>';
      html += '<p class="members-read"/>';
      html += '</div>';
      html += '</div>';

      $el.append(html);

      $el.on('click', function(e) {
        e.preventDefault();
        var row = $(this);
        var messageLine = row.find('.pte_message_line');
        var editLine =  row.find('.edit-body');
        messageLine.hide();
        editLine.show();
      });

      var textEditor = $('<textarea class="pte_message_editor">' + message.body + '</textarea></br>');

      var saveButton = $('<i class="far fa-save pte_message_editor_button" style="font-size: 15px;" title="Save"></i>')
        .on('click', function(e) {
          message.updateBody(textEditor.val());
        });

      var deleteButton = $('<i class="far fa-trash-alt pte_message_editor_button" title="Delete"></i>')
        .on('click', function(e) {
          e.preventDefault();
          message.remove();
        });

      var cancelButton = $('<i class="far fa-times-circle pte_message_editor_button" style="font-size: 15px;" title="Cancel"></i>')
        .on('click', function(e) {
          setTimeout(function(){   //If all else fails, try a timer.
            var messageLine = $el.find('.pte_message_line');
            var editLine =  $el.find('.edit-body');
            messageLine.show();
            editLine.hide();
        }, 0);
        });

      var editBody = $el.find('.edit-body').append(textEditor).append(saveButton).append(deleteButton).append(cancelButton);

}

function prependMessage(message) {
  var $messages = $('#channel-messages');
  var $el = $('<li/>').attr('data-index', message.index);
  createMessage(message, $el);
  $('#channel-messages ul').prepend($el);
}

function addMessage(message) {
  var $messages = $('#channel-messages');
  var initHeight = $('#channel-messages ul').height();

  var $el = $('<li/>').attr('data-index', message.index);

  createMessage(message, $el);

  $('#channel-messages ul').append($el);

  if (initHeight - 50 < $messages.scrollTop() + $messages.height()) {
    $messages.scrollTop($('#channel-messages ul').height());
  }

  if ($('#channel-messages ul').height() <= $messages.height() &&
      message.index > message.channel.lastConsumedMessageIndex) {
    message.channel.updateLastConsumedMessageIndex(message.index);
  }
}

function pte_parent_message_send(){
    var name = activeChannelMeta.name;
    window.parent.postMessage({ name, activeChannelMeta }, "*" );
}

function clearCurrentChannel(){
  if (activeChannel) {
    activeChannel.removeListener('messageAdded', addMessage);
    activeChannel.removeListener('messageRemoved', removeMessage);
    activeChannel.removeListener('messageUpdated', updateMessage);
    activeChannel.removeListener('updated', updateActiveChannel);
    activeChannel.removeListener('memberUpdated', updateMember);
  }
}

function setActiveChannel(channel) {

  clearCurrentChannel();

  activeChannel = channel;

  activeChannelMeta.name = "pte_channel_started";
  pte_parent_message_send();

  activeChannel.on('updated', updateActiveChannel);

  channel.getMessages(30).then(function(page) {

    activeChannelPage = page;
    page.items.forEach(addMessage);

    channel.on('messageAdded', addMessage);
    channel.on('messageUpdated', updateMessage);
    channel.on('messageRemoved', removeMessage);
    channel.on('memberUpdated', updateMember);

    var newestMessageIndex = page.items.length ? page.items[page.items.length - 1].index : 0;
    var lastIndex = channel.lastConsumedMessageIndex;
    if (false || lastIndex && lastIndex !== newestMessageIndex) {    //TODO figure out the last read and other member ready features and make them work. For now, scroll to last item
      var $li = $('li[data-index='+ lastIndex + ']');
      var top = $li.position() && $li.position().top;
      $li.addClass('last-read');
      $('#channel-messages').scrollTop(top + $('#channel-messages').scrollTop());
    } else {
      $('#channel-messages').scrollTop($('#channel-messages ul').height());
    }

    if ($('#channel-messages ul').height() <= $('#channel-messages').height()) {
      channel.updateLastConsumedMessageIndex(newestMessageIndex).then();  //was updatemembers
    }


  })

  channel.on('typingStarted', function(member) {
    member.getUser().then(user => {
      typingMembers.add(user.friendlyName || member.identity);
      updateTypingIndicator();
    });
  });

  channel.on('typingEnded', function(member) {
    member.getUser().then(user => {
      typingMembers.delete(user.friendlyName || member.identity);
      updateTypingIndicator();
    });
  });

  $('#message-body-input').focus();
}

function clearActiveChannel() {

}

function updateActiveChannel(channel) {

  console.log("Updating Active Channel...");
  console.log(channel);


}

function updateMember(obj){
  console.log("Updating member...");
  console.log(obj);
}

function updateTypingIndicator() {
  var message = 'Typing: ';
  var names = Array.from(typingMembers).slice(0,3);

  if (typingMembers.size) {
    message += names.join(', ');
  }

  if (typingMembers.size > 3) {
    message += ', and ' + (typingMembers.size-3) + 'more';
  }

  if (typingMembers.size) {
    message += '...';
  } else {
    message = '';
  }
  $('#typing-indicator span').text(message);
}
