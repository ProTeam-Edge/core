//TODO Make sure that server room management events are received and handled properly. Including a room dissappearing.

var chatIsActive = false;
var activeChannel;
var activeChannelMeta = {};
var activeChannelPage;
var activeChannelUserDescriptors = {};

var activeVideoRoom = false;

var currentCancelButtonId;

var chatWindowState = 'closed';
var muteState = 'muted';

var client = false;
var typingMembers = new Set();

var userContext = {};
var userChannels = [];
var userChannelsDom = [];

var chatWindowSelection;

var domProcessor;
var domProcessorFirstTime = 1000;
var domProcessorSubsequentTimes = 3000;

var Video = Twilio.Video;
var Chat = Twilio.Chat;


var imageBase = "https://storage.googleapis.com/pte_media_store_1/";

  $(document).ready(function() {

      jQuery.fn.reverse = [].reverse;

      ( function () {
        //Pass messages from chat parent to iframe
        pte_set_chat("disabled");

        window.addEventListener( "message", ( event ) => {

          var name = event.data.name;
          var data = event.data.data;

          switch(name) {

            case 'pte_chat_window_closed':
              chatWindowState = 'closed';
            break;
            case 'pte_chat_window_open':
              chatWindowState = 'open';
            break;

            case "pte_video_room_event_notifications":   //actually receive here. Naming confusing but full round trip.

            //console.log('Received a Video Room Notification');
            //console.log(data);

            if (data.notifications_operation == "local_participant_joined_video_room") {
                participantConnected(data.channel_id, data.notifications_participant_id);
            } else if (data.notifications_operation == "local_participant_exited_video_room") {
                participantDisconnected(data.channel_id, data.notifications_participant_id);
            }

            break;
            case 'pte_video_rooms_initial_list':
              console.log('Draw Initial Rooms');
              var data = event.data.data;
              alpn_wait_for_ready(10000, 250,  //Interaction table
                function(){ //Something to check
                  if (typeof client.connectionState != "undefined" && client.connectionState == 'connected') {
                    return true;
                  }
                  return false;
                },
                function(){ //Handle Success
                  pte_handle_draw_video_rooms(data.channel_data);
                },
                function(){ //Handle Error
                  console.log("Could Not Find Client"); //TODO Handle Error
                });

    				break;
    				case 'pte_chat_message':
              pte_handle_chat_start(event);
    				break;
            case 'pte_join_audio_current_channel':
              pte_join_audio_current_channel(event);
            break;
            case 'pte_mute_current_channel':
              pte_handle_mute(event);
            break;
            case 'pte_unmute_current_channel':
              pte_handle_unmute(event);
            break;
            case 'pte_insert_new_object':
              var data = event.data;
              console.log(data);
              pte_insert_new_object(data);
            break;
    				case 'pte_channel_deleted':
              pte_set_chat("disabled");
              var activeChannelMetaSnapshot = jQuery.extend(true, [], activeChannelMeta);  //Snapshot of array
              activeChannelMetaSnapshot.name = "pte_channel_stop";
              pte_parent_message_send(activeChannelMetaSnapshot);
              clearCurrentChannel();
    				break;
    			}
        });
      } () );

      logIn();
      setupEmojiOneArea($("#message-body-input"));

      var isUpdatingConsumption = false;
      $('#channel-messages').on('scroll', function(e) {
        var $messages = $('#channel-messages');
        if (activeChannel) {
        if ($('#channel-messages ul').height() - 50 < $messages.scrollTop() + $messages.height()) {
          activeChannel.getMessages(1).then(messages => {
            var newestMessageIndex = messages.items.length ? messages.items[0].index : 0;
            if (!isUpdatingConsumption && activeChannel.lastConsumedMessageIndex !== newestMessageIndex) {
              isUpdatingConsumption = true;
              activeChannel.updateLastConsumedMessageIndex(newestMessageIndex).then(function() {
                jQuery("li.pte_chat_list_item[data-cid='" + activeChannel.uniqueName + "']").remove();
                $('#pte_chat_no_chats_message').show();
                pte_clear_channel_updates(activeChannel.uniqueName);
                isUpdatingConsumption = false;
              });
            }
          });
        }
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

    function pte_set_chat(state){
    	if (state == 'disabled') {
        $('#pte_chat_messages_area').hide();
        chatIsActive = 'disabled';
        if ($("#message-body-input").data("emojioneArea")){
          $("#message-body-input").data("emojioneArea").setText("");
        }
    	} else {
        $('#pte_chat_messages_area').show();
        chatIsActive = 'enabled';
  	   }
    }

    function pte_leave_current_audio_room(){
      //console.log(activeVideoRoom);
      if (typeof activeVideoRoom != "undefined" && activeVideoRoom.state == "connected") {  //leave room
        var roomName = activeVideoRoom.name;  //channelId
        var participantId = userContext.identity;
        var activeChannelMetaSnapshot = jQuery.extend(true, [], activeChannelMeta);  //Snapshot of array
        activeChannelMetaSnapshot.name = "pte_audio_ended";
        pte_parent_message_send(activeChannelMetaSnapshot);
        try {
          activeVideoRoom.disconnect();
        } catch (e) {
          console.log("Error disconnecting");
          console.log(e);
        }
      }
    }

    function pte_join_audio_current_channel(event){
      var data = event.data.data;
      if (typeof activeChannel != "undefined" && activeChannel.sid && chatIsActive == 'enabled') {
        if (typeof activeVideoRoom != "undefined" && activeVideoRoom.name && activeVideoRoom.state == 'connected') {
          console.log('Leaving Room..');
          const activeVideoRoomName = activeVideoRoom.name;
          pte_leave_current_audio_room();
          if (activeVideoRoomName == activeChannel.sid) {
            console.log('Same Button Pressed...');
            return;
          }
        }
        var activeChannelMetaSnapshot = jQuery.extend(true, [], activeChannelMeta);  //Snapshot of array
        activeChannelMetaSnapshot.name = "pte_start_audio_wait";
        pte_parent_message_send(activeChannelMetaSnapshot);
        pte_start_audio(activeChannel.sid);
      } else {
        console.log("Can't Join Audio Room...");
      }
    }

    function participantConnected (roomName, participantId) {
      var videoRooms = [];
      videoRooms[roomName] = [];  //draw room, no participants, if not already drawn
      pte_handle_draw_video_rooms(videoRooms);
      client.getUserDescriptor(participantId).then(function(userDescriptor){
        var userAttributes = userDescriptor.attributes;
        var userFullName = userAttributes.full_name;
        var userImageHandle = userAttributes.image_handle;
        pte_add_video_room_participant(roomName, userFullName, userImageHandle, participantId);
      });
    }


    function participantDisconnected(roomName, participantId) {
      console.log("Participant Disconnected - ", participantId, " - ", roomName);
      var roomElement = $("li.pte_video_list_item[data-chid='" + roomName + "']");
      roomElement.find("img.pte_chat_activity_icon_image[data-uid='" + participantId + "']").fadeOut(250, function(){
        $(this).remove();
        roomElement = $("li.pte_video_list_item[data-chid='" + roomName + "']"); //reread
        var roomParticipants = roomElement.find("img.pte_chat_activity_icon_image_participant");
        if (!roomParticipants.length) {
          roomElement.fadeOut(250, function(){
            $(this).remove();
            $("#pte_chat_no_video_message").fadeIn(250);
          });
        } else {
          $("#pte_chat_no_video_message").hide();
        }
      });
    }

    function roomJoined(room) {
      window.room = room;
      //console.log("ROOM JOINED");
      //console.log(room);
      var roomName = room.name;
      var localParticipant = room.localParticipant;
      var localParticipantId = localParticipant.identity;

      var targetUserAttributes, activeChannelMetaSnapshot;
      var videoRooms = [];
      videoRooms[roomName] = [];  //draw room, no participants
      pte_handle_draw_video_rooms(videoRooms);

      activeChannel.getMembers().then(function (members) {   //Notify all room members regardless of if they are in the room or not.
        members.forEach(function(member){
          member.getUserDescriptor().then(function(targetUserDescriptor){
            targetUserAttributes = targetUserDescriptor.attributes;
            activeChannelMetaSnapshot = jQuery.extend(true, [], activeChannelMeta);  //Snapshot of array
            activeChannelMetaSnapshot.name = "pte_send_notification_by_sync";
            activeChannelMetaSnapshot.notifications_operation = "local_participant_joined_video_room";
            activeChannelMetaSnapshot.notifications_member_sync_id = targetUserAttributes.sync_id;
            activeChannelMetaSnapshot.notifications_participant_id = localParticipantId;
            pte_parent_message_send(activeChannelMetaSnapshot);
          });
        });
      });

      room.on('trackSubscribed', function(track, publication, participant) {
        //console.log("Track Subscribed");
        var participantId = participant.identity;
        var newMedia = $("<div class='pte_media_holder' data-uid='" + participantId + "'></div>")
        newMedia.append(track.attach());
        $("#pte_all_media").append(newMedia);
      });


      room.on('trackUnsubscribed', function(track, publication, participant) {
        track.detach().forEach(function(mediaElement) {
          $(mediaElement).parent().remove();
        });
      });

      room.once('disconnected', function(localRoom, error) {
        //console.log('HANDLING DISCONNECT');
        if (error) {
          console.log('Unexpectedly disconnected:', error);
        }
        var track;
        localRoom.localParticipant.tracks.forEach(function(localAudioTrackPublication) {  //stop and detach local track (microphone/speaker).
          track = localAudioTrackPublication.track;
          track.stop();
          track.detach();
        });
        var activeChannelMetaSnapshot = jQuery.extend(true, [], activeChannelMeta);  //Snapshot of array
        activeChannelMetaSnapshot.name = "pte_handle_mute_button";
        pte_parent_message_send(activeChannelMetaSnapshot);

        activeChannelMetaSnapshot = jQuery.extend(true, [], activeChannelMeta);  //Snapshot of array
        activeChannelMetaSnapshot.name = "pte_send_notification_by_sync";
        activeChannelMetaSnapshot.notifications_operation = "local_participant_exited_video_room";
        activeChannelMetaSnapshot.notifications_participant_id = userContext.identity;
        activeChannel.getMembers().then(function (members) {   //Notify all room members regardless of if they are in the room or not.
          members.forEach(function(member){
            member.getUserDescriptor().then(function(targetUserDescriptor){
              targetUserAttributes = targetUserDescriptor.attributes;
              activeChannelMetaSnapshot.notifications_member_sync_id = targetUserAttributes.sync_id;
              pte_parent_message_send(activeChannelMetaSnapshot);
            });
          });
        });
      });
    }

    function pte_handle_mute(event){
      console.log('Handling Mute');

      if (activeVideoRoom != "undefined" && activeVideoRoom.state == "connected") {
        activeVideoRoom.localParticipant.audioTracks.forEach(trackPublication => {
          trackPublication.track.disable();
          console.log("Disabling Track");
          console.log(trackPublication.track);
        });
        var activeChannelMetaSnapshot = jQuery.extend(true, [], activeChannelMeta);  //Snapshot of array
        activeChannelMetaSnapshot.name = "pte_handle_mute_button";
        pte_parent_message_send(activeChannelMetaSnapshot);
      }
    }

    function pte_handle_unmute(event){
      console.log('Handling Unmute');
      if (activeVideoRoom != "undefined" && activeVideoRoom.state == "connected") {
        activeVideoRoom.localParticipant.audioTracks.forEach(trackPublication => {
          trackPublication.track.enable();
          console.log("Enabling Track");
          console.log(trackPublication.track);
          var activeChannelMetaSnapshot = jQuery.extend(true, [], activeChannelMeta);  //Snapshot of array
          activeChannelMetaSnapshot.name = "pte_handle_unmute_button";
          pte_parent_message_send(activeChannelMetaSnapshot);
        });

      }
    }

    function pte_start_audio(roomChannelSid) {
      console.log('Starting Audio');
      var token = userContext.token;
      if ('mediaDevices' in navigator && 'getUserMedia' in navigator.mediaDevices) {
        Video.createLocalTracks({
          audio: true
        }).then(localTracks => {
          localTracks.forEach(function(track) {
              track.disable();  //enter room muting my track
          });

          return Video.connect(token, {
            name: roomChannelSid,
            tracks: localTracks
          }).catch(error => {
            console.log('Error Creating Video Room');
            console.log(error);
          });
        }).then(room => {
          activeVideoRoom = room;
          var activeChannelMetaSnapshot = jQuery.extend(true, [], activeChannelMeta);  //Snapshot of array
          activeChannelMetaSnapshot.name = "pte_audio_started";
          pte_parent_message_send(activeChannelMetaSnapshot);
          roomJoined(activeVideoRoom);
        });
      }
    }

    function pte_clear_channel_updates(channelId){
      var newDom, newDomId;
      if (channelId) {
        delete userChannels[channelId + "_"];
        if (userChannelsDom.length) {
          for (var key in userChannelsDom) {
            newDom = $(userChannelsDom[key]);
            newDomId = newDom.data('cid');
            if (newDomId == channelId) {
              delete userChannelsDom[key];
            }
          }
        }
      }
    }

    function pte_handle_chat_start(event){

      console.log('CHAT WINDOW -- Handling Chat Start/STOP');
      //TODO Handle Switching between profile and vault views - should not reload chat...
      var data = event.data.data;
      var newChannelId = data.channel_id;

      if (newChannelId) {
        if (typeof activeChannel != "undefined" && activeChannel && activeChannel.sid == newChannelId) {
          console.log("Same Channel. Do Nothing");
          return;
        }
        if (typeof activeVideoRoom != "undefined") {
          pte_leave_current_audio_room();
        }
        activeChannelMeta = data;
        joinChannel(newChannelId);
        pte_set_chat("enabled");
      } else {
        pte_set_chat("disabled");
        pte_leave_current_audio_room();
        activeChannelMeta = {"name": "pte_channel_stop"};
        var activeChannelMetaSnapshot = jQuery.extend(true, [], activeChannelMeta);  //Snapshot of array
        pte_parent_message_send(activeChannelMetaSnapshot);
        clearCurrentChannel();
      }
    }


function joinChannel(sid) {

  console.log('CHAT WINDOW - JOINING CHAT', sid);

  clearCurrentChannel();

  alpn_wait_for_ready(10000, 250,
    function(){ //Something to check
      if (typeof client.connectionState != "undefined" && client.connectionState == 'connected') {
        return true;
      }
      return false;
    },
    function(){ //Handle Success
      client.getChannelBySid(sid).then(function(channel) {
        activeChannel = channel;
        setActiveChannel(activeChannel);
      }).catch(function() {

      });
    },
    function(){ //Handle Error
      console.log("Could Not Find Client"); //TODO Handle Error
    });
}

function pte_handle_internal_link(el) {
  console.log("Handle Internal Link...");
  var $el = $(el);
  var topicId = $el.data('topic_id');
  var ownerId = $el.data('owner_id');
  var operation = $el.data('op');

  switch(operation) {
    case "vault_item":
      var vaultId = $el.data('vault_id');
      var activeChannelMetaSnapshot = jQuery.extend(true, [], activeChannelMeta);  //Snapshot of array
      activeChannelMetaSnapshot.name = "pte_handle_link";
      activeChannelMetaSnapshot.topic_id = topicId;
      activeChannelMetaSnapshot.channel_owner_id = ownerId;
      activeChannelMetaSnapshot.vault_id = vaultId;
      activeChannelMetaSnapshot.link_operation= "vault_item";
      pte_parent_message_send(activeChannelMetaSnapshot);
    break;
  }
}

function pte_send_chat(){
  var textAreaObj = $("#message-body-input").data("emojioneArea");
  var body = textAreaObj.getText().trim();

  activeChannel.sendMessage(body, {'message_type': 'message'}).then(function() {
    textAreaObj.setText("").setFocus();
    $("#buttercup_easter_egg").addClass('pte_button_disabled');
    $('#channel-messages').scrollTop($('#channel-messages ul').height());
    $('#channel-messages li.last-read').removeClass('last-read');
  });
}

function setupEmojiOneArea($el, container = ""){

  var emojioneEl = $el.emojioneArea({
  container: container,
  placeholder: "Message...",
  search: false,
  pickerPosition: "top",
  filtersPosition: "bottom",
  tones: true,
  autocompleteTones: true,
  textcomplete: {
    maxCount  : 8,
    placement : 'top'
  },
  hidePickerOnBlur: true,
  autocomplete: true,
  attributes: {
      spellcheck : true
  },
  events: {
    blur: function(editor, event){
      // console.log("BLUR");
      // selectionRange = saveSelection();
      // console.log(selectionRange);
    },
    focus: function(editor, event){
      // console.log("FOCUS");
      // console.log(selectionRange);
      // restoreSelection(selectionRange);
    },
    paste: function(editor, text, html){
      // console.log("PASTE");
      // console.log(editor);
      // console.log(text);
      // console.log(html);
    },
    keyup: function (event, key) {
      var body = this.getText();
      var elId = $el.attr("id");
      var sendButton = $("#buttercup_easter_egg");
        if (body && elId == "message-body-input") {
          sendButton.removeClass('pte_button_disabled');
        } else {
          sendButton.addClass('pte_button_disabled');
        }
        if ((key.keyCode == 10 || key.keyCode == 13) && (key.ctrlKey || event.metaKey == '⌘-')) {
          if (body) {
            var eArea = this;
            if (elId == "message-body-input") { //Add
              pte_send_chat();
            } else {//Update
              var itemEditingIndex = $el.closest("li").data('index');
              jQuery("i.pte_chat_save_button[data-li='" + itemEditingIndex + "']").click();
            }
          }
        } else if (activeChannel) {
          activeChannel.typing();
        }
    }
  }
});

return emojioneEl;
}

function logIn() {

  $.getJSON( '../chat/token.php', {

    device: 'browser'

  }, function(data) {

    if (!data.identity) {
        console.log("HANDLE CHAT LOGGED OUT - MAIN"); //exit to login

    }

    userContext = {identity: data.identity, token: data.token};

    Chat.Client.create(data.token, { logLevel: 'info' })
      .then(function(createdClient) {
        client = createdClient;
        client.on('tokenAboutToExpire', () => {
          console.log('CHAT WINDOW - TOKEN ABOUT TO EXPIRE');
          $.getJSON( '../chat/token.php', {
            device: 'browser'
          }, function(data1) {
            if (!data1.identity) {
                console.log("HANDLE CHAT LOGGED OUT - EXPIRED");  //exit to login

            }
            if (data1.token) {
              console.log('Got new token!');
              client.updateToken(data1.token);
              userContext = {identity: data1.identity};
            } else {
              console.error('Failed to get a token ');
              throw new Error(data1);
            }
          });
        });

        client.getSubscribedChannels().then(function(paginator) {
          //console.log('Getting Channels..');
          //console.log(paginator);
          var channel, channelState, uniqueId, sid;
          for (i = 0; i < paginator.items.length; i++) {
            channel = paginator.items[i];
            channelState = channel.state;
            uniqueId = channelState.uniqueName;
            if (uniqueId == parseInt(uniqueId)) {
              userChannels[uniqueId + "_"] = channel;   //Ensures one array element per topic
            }
          }
          pte_process_chat_channels();
          //setup dom processor timer
          setTimeout(function(){
            pte_process_new_chat_dom();
          }, domProcessorFirstTime);

          clearInterval(domProcessor);
          domProcessor = setInterval(function(){
            pte_process_new_chat_dom();
          }, domProcessorSubsequentTimes);

          client.on('messageAdded', function(message) {
            var messageState = message.state;
            var messageChannel = message.channel;
            var messageAuthor = messageState.author;
            var messageTopic = messageChannel.uniqueName;
            if (messageAuthor != userContext.identity) {
              pte_handle_chat_channel(messageChannel);
            }
          });

          client.on('messageRemoved', function(message) {
            var messageState = message.state;
            var messageChannel = message.channel;
            var messageAuthor = messageState.author;
            var messageTopic = messageChannel.uniqueName;
            if (messageAuthor != userContext.identity) {
              pte_handle_chat_channel(messageChannel);
            }
          });

          client.on('channelUpdated', function(channelUpdate) {
            console.log("Channel Updated Called");
            console.log(channelUpdate);
          });

          client.on('channelJoined', function(channel) {
            console.log('Channel Joined Called');
            var body = "Joined";
            channel.sendMessage(body, {'message_type': 'message'}).then(function() {
            });
            var channelFriendlyName = channel.friendlyName;
            if (isJson(channelFriendlyName)) {
              var contactData = JSON.parse(channelFriendlyName);
              if (userContext.identity == contactData.owner_id) {
                var showIdentity = contactData.contact_id;
              } else {
                var showIdentity = contactData.owner_id;
              }
              client.getUserDescriptor(showIdentity).then(function(userDescriptor){
                var userAttributes = userDescriptor.attributes;
                var channelFriendlyName = userAttributes.full_name;
                var channelImageHandle = userAttributes.image_handle;
                //console.log(" Contact for ", channelFriendlyName);
                pte_add_edit_chat_panel(channel.uniqueName, channelFriendlyName, 2, channelImageHandle, contactData.owner_id);
              });
            } else {
              //console.log(" Topic for ", channelFriendlyName);
              channel.getAttributes().then(function(attributes){
                pte_add_edit_chat_panel(channel.uniqueName, channel.friendlyName, 2, attributes.image_handle, attributes.topic_owner_id);
              });
            }
          });

          client.on('channelLeft', function(channel) {
            console.log('Channel Left');
            console.log(channel);

            //TODO if current topic selected, redraw topic without connected.

          });

          client.on('channelRemoved', function(channel) {
            console.log('Channel Removed');
            console.log(channel);
          });

        });  //End Get Unreads

        client.on('connectionError', function(channel) {
          console.log("Connection Error");
          console.log(channel);

          
          //var channelState = channel.state;
          //var uniqueId = (typeof channelState.uniqueName != "undefined") && channelState.uniqueName ? channelState.uniqueName : channelState.friendlyName;


        });


        client.on('connectionStateChanged', function(channelState) {
          console.log("Channel State Changed");
          if (channelState == "denied") {
            console.log("DENIED LOGIN BEFORE????");
            logIn();
            console.log("DENIED LOGIN AFTER????");
          }
        });

      })
      .catch(function(err) {
        throw err;
      })


  });

}

function pte_add_video_room_participant(key, userFullName, userImageHandle, participantId) {

  var roomElement, roomParticipant, imagePath, imageHandle;

  alpn_wait_for_ready(10000, 250,    //handles timing issues  where room was arriving after user
    function(){ //Something to check
      roomElement = $("li.pte_video_list_item[data-chid='" + key + "']");
      if (roomElement.length) {
        return true;
      }
      return false;
    },
    function(){ //Handle Success
      roomParticipant = roomElement.find("img.pte_chat_activity_icon_image[data-uid='" + participantId + "']");
      if (roomParticipant.length) {
        //console.log("PARTICIPANT ALREADY DRAWN");
        return;
      }
      imagePath = imageBase + userImageHandle;
      imageHandle = "<img data-uid='" + participantId + "' class='pte_chat_activity_icon_image pte_chat_activity_icon_image_participant' src='" + imagePath + "'>"
      roomElement.find("div#pte_chat_activity_icons_holder").append(imageHandle);
    },
    function(){ //Handle Error
      console.log("DID NOT FIND ROOM, ", key)
    });
}

function pte_handle_draw_video_rooms(videoRooms){   //create DOM entries for all rooms in channel_data with participants

  var currentChannel, participants, participantCount, channelName, channelImageHandle, participantId, participantStatus, currentChannelData, participantData, userStatus;
  var userState, userAttributes, userFriendlyName, userImageHandle, userFullName, roomParticipants, roomEntries, channelTopicId, channelOwnerId;
//Draw Rooms
  if (Object.keys(videoRooms)) {
      var loggedUserId = userContext.identity;
      for (var key in videoRooms) {
        participants = videoRooms[key];
        try {
          client.getChannelBySid(key).then(function(channel){
            $("#pte_chat_no_video_message").hide();
            var channelUniqueId = channel.uniqueName;
            //console.log("Got Channel - ", channelUniqueId);
            var channelFriendlyName = channel.friendlyName;
            var channelImageHandle = "";
            if (channelUniqueId == parseInt(channelUniqueId)) { //ensures topic id integer is here
              if (isJson(channelFriendlyName)) {
                var contactData = JSON.parse(channelFriendlyName);
                if (userContext.identity == contactData.owner_id) {
                  var showIdentity = contactData.contact_id;
                } else {
                  var showIdentity = contactData.owner_id;
                }
                client.getUserDescriptor(showIdentity).then(function(userDescriptor){
                  var userAttributes = userDescriptor.attributes;
                  var channelFriendlyName = userAttributes.full_name;
                  var channelImageHandle = userAttributes.image_handle;
                  //console.log(" Contact for ", channelFriendlyName);
                  pte_add_edit_video_panel(key, channelFriendlyName, channelImageHandle, channelUniqueId, contactData.owner_id);
                });
              } else {
                //console.log(" Topic for ", channelFriendlyName);
                channel.getAttributes().then(function(attributes){
                  pte_add_edit_video_panel(key, channelFriendlyName, attributes.image_handle, channelUniqueId, attributes.topic_owner_id);
                });
              }
            }
            participants.forEach(function(participant) {
              participantStatus = participant.status;
              participantId = participant.identity;
              client.getUserDescriptor(participantId).then(function(userDescriptor){
                var userAttributes = userDescriptor.attributes;
                var userFullName = userAttributes.full_name;
                var userImageHandle = userAttributes.image_handle;
                pte_add_video_room_participant(key, userFullName, userImageHandle, participantId);
              });
            });
          });
        } catch (e) {
          console.log(e);
        }
    }
  }
}

function pte_add_edit_video_panel(channelUniqueId, channelFriendlyName, channelImageHandle, channelTopicId, channelOwnerId){
  //console.log("Adding Video Panel");
  var el, rowHtml, participantIcons, participantAbout, room;
  var imagePath;
  var imageHandle = "";
  var vidPanel = $("li.pte_video_list_item[data-chid='" + channelUniqueId + "']");
  if (vidPanel.length) { //already drawn
    return;
  }
  channelFriendlyName = channelFriendlyName ? channelFriendlyName : "Not Specified";
  if (channelImageHandle) {
    imagePath = imageBase + channelImageHandle;
    imageHandle = "<img class='pte_chat_activity_icon_image' src='" + imagePath + "'>"
  }
  el = $('<li/>').addClass('pte_video_list_item').attr('data-cid', channelTopicId).attr('data-chid', channelUniqueId).attr('data-co', channelOwnerId)
  rowHtml =  "<div class='pte_item_row_container'>";
  rowHtml += "<div class='pte_chat_activity_icon'>" +  imageHandle + "</div>";
  rowHtml += "<div class='pte_chat_activity_friendlyname' onclick='pte_select_new_topic($(this))'>" + channelFriendlyName + "</div>";
  rowHtml += "<div class='pte_chat_activity_unreads'><div id='pte_unread_count' class='pte_unread_count'></div></div>";
  rowHtml += "</div>";
  rowHtml +=  "<div class='pte_item_row_container_icons'>";
  rowHtml += "<div class='pte_chat_activity_icon'></div>";
  rowHtml += "<div id='pte_chat_activity_icons_holder' class='pte_chat_activity_icons_holder'></div>";
  rowHtml += "</div>";
  el.append(rowHtml);

  $("ul#pte_audio_activity_list").append(el);
}

function pte_process_new_chat_dom(){
  //console.log("Processing new chat dom");
  if (userChannelsDom.length) {
    var userChannelsDomCopy = jQuery.extend(true, [], userChannelsDom);  //Snapshot of array
    userChannelsDom = [];
    var currentCount, currentDomId, newDom, newDomCount, newDomId, chatActivity, chatActivityList, handled, updated;
    chatActivity = $('#pte_chat_activity_list');
    for (var key in userChannelsDomCopy) {
      chatActivityList = $('#pte_chat_activity_list li');
      newDom = $(userChannelsDomCopy[key]);
      newDomId = newDom.data('cid');
      newDomCount = newDom.data('mc');
      if (chatActivityList.length) {
        //Update means delete and reinsert
        chatActivityList.each(function(cKey, cValue){
          $cValue = $(cValue);
          currentDomId = $cValue.data('cid');
          if (newDomId == currentDomId) {
            $cValue.remove();
            chatActivityList = $('#pte_chat_activity_list li');
            return false;
          }
        });
        handled = false;
        chatActivityListReverse = chatActivityList.reverse();
        chatActivityListReverse.each(function(cKey, cValue){
          $cValue = $(cValue);
          currentDomId = $cValue.data('cid');
          currentCount = $cValue.data('mc');
          if (+newDomCount < +currentCount) {
            newDom.insertAfter($cValue);
            handled = true;
            return false;  //exit loop
          }
        });
        if (!handled) {
          chatActivity.prepend(newDom);
        }
      } else {
        chatActivity.append(newDom);
      }
    }
    var totalChatUnreads = 0;
    chatActivityList = $('#pte_chat_activity_list li');
    if (chatActivityList.length) {
      $('#pte_chat_no_chats_message').hide();
      chatActivityList.each(function(cKey, cValue){
        $cValue = $(cValue);
        totalChatUnreads += $cValue.data('mc');
      });
    }
    var activeChannelMetaSnapshot = jQuery.extend(true, [], activeChannelMeta);  //Snapshot of array
    activeChannelMetaSnapshot.name = "pte_update_chat_total";
    activeChannelMetaSnapshot.chat_total_unreads = totalChatUnreads;
    pte_parent_message_send(activeChannelMetaSnapshot);
  }
}

function pte_select_new_topic($el){
  console.log("Selecting New Topic");
  var selectedTopicLI = $el.closest("LI");
  var topicId = selectedTopicLI.data('cid');
  var channelOwnerId = selectedTopicLI.data('co');

  var activeChannelMetaSnapshot = jQuery.extend(true, [], activeChannelMeta);  //Snapshot of array
  activeChannelMetaSnapshot.name = "pte_handle_link";
  activeChannelMetaSnapshot.topic_id = topicId;
  activeChannelMetaSnapshot.channel_owner_id = channelOwnerId;
  activeChannelMetaSnapshot.link_operation= "topic_same";
  pte_parent_message_send(activeChannelMetaSnapshot);
}

function pte_add_edit_chat_panel(channelUniqueId, channelFriendlyName, messageCount, channelImageHandle = "", channelOwnerId = 0){
  var el, rowHtml;
  channelFriendlyName = channelFriendlyName ? channelFriendlyName : "Not Specified";
  if (channelUniqueId == activeChannel.uniqueName) {
    return;
  }
  var imageHandle = "";
  if (channelImageHandle) {
    var imagePath = "https://storage.googleapis.com/pte_media_store_1/" + channelImageHandle;
    imageHandle = "<img class='pte_chat_activity_icon_image' src='" + imagePath + "'>"
  }
  el = $('<li/>').addClass('pte_chat_list_item').attr('data-cid', channelUniqueId).attr('data-mc', messageCount).attr('data-co', channelOwnerId);
  rowHtml = "<div class='pte_item_row_container'>";
  rowHtml += "<div class='pte_chat_activity_icon'>" +  imageHandle + "</div>";
  rowHtml += "<div class='pte_chat_activity_friendlyname' onclick='pte_select_new_topic($(this));'>" + channelFriendlyName + "</div>";
  rowHtml += "<div class='pte_chat_activity_unreads'><div class='pte_unread_count'>" + messageCount + "</div></div>";
  rowHtml += "</div>";
  el.append(rowHtml);
  userChannelsDom.push(el);
}

function pte_handle_chat_channel(channel){

   // console.log("Handling Channel");
   // console.log(channel);


  var channelState = channel.state;
  var channelUniqueId = channel.uniqueName;
  var channelFriendlyName = channel.friendlyName;
  var channelImageHandle = "";
  if (channelUniqueId == parseInt(channelUniqueId)) { //TopicId
  if (isJson(channelFriendlyName)) {
    var contactData = JSON.parse(channelFriendlyName);
    if (userContext.identity == contactData.owner_id) {
      var showIdentity = contactData.contact_id;
    } else {
      var showIdentity = contactData.owner_id;
    }
    channel.getUnconsumedMessagesCount().then(function(messageCount){
      if (messageCount > 0) {   //Unreads
        client.getUserDescriptor(showIdentity).then(function(userDescriptor){
          var userAttributes = userDescriptor.attributes;
          var channelFriendlyName = userAttributes.full_name;
          var channelImageHandle = userAttributes.image_handle;
          pte_add_edit_chat_panel(channelUniqueId, channelFriendlyName, messageCount, channelImageHandle, contactData.owner_id);
        });
      }
    });

  } else {
      channel.getUnconsumedMessagesCount().then(function(messageCount){
        if (messageCount > 0) {   //Unreads
          channel.getAttributes().then(function(attributes){
            pte_add_edit_chat_panel(channelUniqueId, channelFriendlyName, messageCount, attributes.image_handle);
          });
        }
      });
  }
}
}

function pte_process_chat_channels(){
  console.log("PROCESSING CHAT CHANNELS");
  var cleanKey, currentChannel;
  var userChannelsCopy = jQuery.extend(true, [], userChannels);
  userChannels = [];

  for (var key in userChannelsCopy) {
    currentChannel = userChannelsCopy[key];
    pte_handle_chat_channel(currentChannel);
  }
}

function updateUnreadMessages(message) {
  var channel = message.channel;
  if (channel !== activeChannel) {  //TODO Manage new chat events

    console.log("Updating Unread Messsage...");
    console.log(message);

  }
}

function pte_insert_new_object(data) {

  data.message_type = 'object';
  activeChannel.sendMessage('object', data).then(function() {
    $('#channel-messages').scrollTop($('#channel-messages ul').height());
    $('#channel-messages li.last-read').removeClass('last-read');
  });

}



function updateMessages() {
  console.log("UPDATE MESSAGES -- DIS CULPRIT");
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

  var messageAttributes = (typeof message.attributes != "undefined") ? message.attributes : {};
  var messageType = (typeof messageAttributes.message_type != "undefined") ? messageAttributes.message_type : 'message';
  var messageData = (typeof messageAttributes.data != "undefined") ? messageAttributes.data : '{}';

  switch(messageType) {
    case "message":
      createStandardMessage(message, $el);
    break;
    case "object":
      createObject(message, $el);
    break;
  }
}

function pte_handle_object_action(lineId) {
  activeChannel.getMessages(1, lineId).then(function(page) {
    if (typeof page.items[0] != "undefined") {
      var messageData = page.items[0].attributes.data;
      messageData.operation = "vault_item";
      var activeChannelMetaSnapshot = jQuery.extend(true, [], activeChannelMeta);  //Snapshot of array
      messageData.current_user_id = userContext.identity ;
      activeChannelMetaSnapshot.name = "pte_handle_object_action";
      activeChannelMetaSnapshot.object_action_data = messageData;
      pte_parent_message_send(activeChannelMetaSnapshot);
    } else {
      return false;
    }
  });
}

function createObject(message, $el) {
  var messageAttributes = message.attributes;
  var objectData = messageAttributes.data;
  var objectType = objectData.object_type;
  var fileName = objectData.file_name ? objectData.file_name : "-- No Name Specified --";
  var fileAbout = objectData.file_about? objectData.file_about : " --";
  var objectBody = '';
  var lineIndex = $el.data('index');
  switch(objectType) {
    case "vault_item":
      objectBody +=  "<div class='pte_object_container'>";
      objectBody += "<div class='pte_object_container_icon'>";
      objectBody += "<i class='far fa-file-pdf pte_chat_object_icon' onclick='pte_handle_object_action(" + lineIndex + ");' title='Vault Item iLink'></i>";
      objectBody += "</div>";
      objectBody += "<div class='pte_object_container_body'>";
      objectBody += "<div class='pte_chat_object_title'  onclick='pte_handle_object_action(" + lineIndex + ");'>" + fileName + "</div>";
      objectBody += "<div class='pte_chat_object_about'>" + fileAbout + "</div>";
      objectBody += "</div>";
      objectBody += "</div>";
    break;
  }

  var user = activeChannelUserDescriptors[message.author];
  var attributes = user.attributes;
  var imageHandle = "https://storage.googleapis.com/pte_media_store_1/" + attributes.image_handle;
  var time = message.timestamp;
  var minutes = time.getMinutes();
  var ampm = Math.floor(time.getHours()/12) ? 'PM' : 'AM';

  if (minutes < 10) { minutes = '0' + minutes; }

  if (userContext.identity == message.author) {
    var editableClass = ' pte_flex_item_editable';
    var editableString = '<div class="pte_flex_edit_body"><div class="pte_chat_edit_title">Editing...</div>' + objectBody + '</div>';
    var containerTitle = 'Select Message to Delete';
  } else {
    var editableClass = ' pte_flex_item_not_editable';
    var editableString = '';
    var containerTitle = '';
  }

  var html =  '<div class="pte_flex_container">';
      html += '<div class="pte_flex_user_icon">';
      html += '<img src="' + imageHandle + '" class="pte_topic_icon' + editableClass + '" title="' +  containerTitle + '">';
      html += '</div>';
      html += '<div class="pte_flex_body">';
      html += '<span class="' + editableClass + '" title="' +  containerTitle + '">' + user.friendlyName + '</span></br>';
      html +=  objectBody;
      html += '<span class="timestamp"  style="font-size: 10px;">' + ' ' + (time.getHours()%12) + ':' + minutes + '' + ampm + ''  + '</span>';
      html += '</div>';
      html += editableString;
      html += '<p class="last-read">New Messages</p>';
      html += '<p class="members-read"/>';
      html += '</div>';
      var initHeight = $('#channel-messages ul').height();
      $el.append(html);

      if (userContext.identity == message.author) {  //as the user, I can edit and delete my messages.
        $el.find('.pte_flex_item_editable').on('click', function(e) {
          var row = $el;
          var messageLine = row.find('.pte_flex_body');
          var editLine =  row.find('.pte_flex_edit_body');
          var messageEditor = row.find('.pte_message_editor');
          var newCancelButtonId = row.find('.pte_chat_cancel_button').data('li');
          if (currentCancelButtonId && currentCancelButtonId != newCancelButtonId) {
            var previousCancelButton = jQuery("i.pte_chat_cancel_button[data-li='" + currentCancelButtonId + "']")
            previousCancelButton.click();
          }
          messageLine.hide();
          editLine.show();
          currentCancelButtonId = newCancelButtonId;
        });
        var deleteButton = $('<i class="far fa-trash-alt pte_message_editor_button" title="Delete"></i>')
          .on('click', function(e) {
            e.preventDefault();
            message.remove();
          });
        var cancelButton = $('<i class="far fa-times-circle pte_message_editor_button pte_chat_cancel_button" style="font-size: 15px;" title="Cancel" data-li="' + lineIndex + '"></i>')
          .on('click', function(e) {
            setTimeout(function(){   //If all else fails, try a timer.
              var messageLine = $el.find('.pte_flex_body');
              var editLine =  $el.find('.pte_flex_edit_body');
              messageLine.show();
              editLine.hide();
            }, 0);
          });
        var editBody = $el.find('.pte_flex_edit_body').append(deleteButton).append(cancelButton);
      }
}


function createStandardMessage(message, $el) {

  var user = activeChannelUserDescriptors[message.author];

  var lineIndex = $el.data('index');
  var attributes = user.attributes;
  var imageHandle = "https://storage.googleapis.com/pte_media_store_1/" + attributes.image_handle;

  var time = message.timestamp;
  var minutes = time.getMinutes();
  var ampm = Math.floor(time.getHours()/12) ? 'PM' : 'AM';

  if (minutes < 10) { minutes = '0' + minutes; }

  if (userContext.identity == message.author) {
    var editableClass = ' pte_flex_item_editable';
    var editableString = '<div class="pte_flex_edit_body"></div>';
    var containerTitle = 'Select Message to Edit or Delete';
  } else {
    var editableClass = ' pte_flex_item_not_editable';
    var editableString = '';
    var containerTitle = '';
  }

  var messageBody = message.body;
  if (typeof messageBody != "undefined" && messageBody) {
    messageBody = messageBody.linkify();
  } else {
    messageBody = "";
  }

  var html =  '<div class="pte_flex_container">';
      html += '<div class="pte_flex_user_icon">';
      html += '<img src="' + imageHandle + '" class="pte_topic_icon' + editableClass + '" title="' +  containerTitle + '">';
      html += '</div>';
      html += '<div class="pte_flex_body">';
      html += '<span class="' + editableClass + '" style="font-size: 12px; font-weight: bold;" title="' +  containerTitle + '">' + user.friendlyName + '</span></br>';
      html +=  messageBody;
      html += '&nbsp;<span class="timestamp">' + ' ' + (time.getHours()%12) + ':' + minutes + '' + ampm + ''  + '</span>';
      html += '</div>';
      html += editableString;
      html += '<p class="last-read">New Messages</p>';
      html += '<p class="members-read"/>';
      html += '</div>';
      var initHeight = $('#channel-messages ul').height();
      $el.append(html);

      if (userContext.identity == message.author) {  //as the user, I can edit and delete my messages.
        $el.find('.pte_flex_item_editable').on('click', function(e) {
          var row = $el;
          var messageLine = row.find('.pte_flex_body');
          var editLine =  row.find('.pte_flex_edit_body');
          var messageEditor = row.find('.pte_message_editor');
          var newCancelButtonId = row.find('.pte_chat_cancel_button').data('li');
          if (currentCancelButtonId && currentCancelButtonId != newCancelButtonId) {
            var previousCancelButton = jQuery("i.pte_chat_cancel_button[data-li='" + currentCancelButtonId + "']")
            previousCancelButton.click();
          }
          messageLine.hide();
          editLine.show();
          currentCancelButtonId = newCancelButtonId;
          if (!messageEditor.data('emojioneArea')) {
            var emojiEl = setupEmojiOneArea(messageEditor, "");
          }
        });

        var textEditor = $('<textarea class="pte_message_editor">' + message.body + '</textarea>');

        var saveButton = $('<i class="far fa-save pte_message_editor_button pte_chat_save_button" style="font-size: 15px;" title="Save" data-li="' + lineIndex + '"></i>')
          .on('click', function(e) {
            var thisEl = $(this);
            var relatedEmojiOneArea = thisEl.closest(".pte_flex_edit_body").find('textarea.pte_message_editor');
            if (typeof relatedEmojiOneArea[0] != "undefined") {
              var thisEditor = relatedEmojiOneArea[0].emojioneArea;
              message.updateBody(thisEditor.getText().trim());
            }
          });

        var deleteButton = $('<i class="far fa-trash-alt pte_message_editor_button" title="Delete"></i>')
          .on('click', function(e) {
            e.preventDefault();
            message.remove();
          });

        var cancelButton = $('<i class="far fa-times-circle pte_message_editor_button pte_chat_cancel_button" style="font-size: 15px;" title="Cancel" data-li="' + lineIndex + '"></i>')
          .on('click', function(e) {
            setTimeout(function(){   //If all else fails, try a timer.
              var messageLine = $el.find('.pte_flex_body');
              var editLine =  $el.find('.pte_flex_edit_body');
              messageLine.show();
              editLine.hide();
            }, 0);
          });

        var editBody = $el.find('.pte_flex_edit_body').append(textEditor).append(saveButton).append(deleteButton).append(cancelButton);
      }
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

  var messageChannel = message.channel.uniqueName;
  var currentChannel = activeChannel.uniqueName;

  if (messageChannel == currentChannel) {

    var $el = $('<li/>').attr('data-index', message.index);
    createMessage(message, $el);
    $('#channel-messages ul').append($el);

    //Handles when a message is added by the other person.
    if (initHeight - 50 < $messages.scrollTop() + $messages.height()) {
      $messages.scrollTop($('#channel-messages ul').height());
    }
    if ($('#channel-messages ul').height() <= $messages.height() &&
        message.index > message.channel.lastConsumedMessageIndex) {
          message.channel.updateLastConsumedMessageIndex(message.index);
    }
  }
}

function pte_parent_message_send(activeChannelMetaSnapshot){
    var name = activeChannelMetaSnapshot.name;
    window.parent.postMessage({ name, activeChannelMetaSnapshot }, "*" );
}

function clearCurrentChannel(){
  if (activeChannel) {

    activeChannel.removeListener('updated', updateActiveChannel);

    activeChannel.removeListener('messageAdded', addMessage);
    activeChannel.removeListener('messageRemoved', removeMessage);
    activeChannel.removeListener('messageUpdated', updateMessage);

    activeChannel.removeListener('memberUpdated', updateMember);
    activeChannel.removeListener('memberJoined', memberJoined);
    activeChannel.removeListener('memberLeft', memberLeft);

    activeChannel.removeListener('removed', userRemoved);
  }

  activeChannel = false;
}

function setActiveChannel(channel) {
  var messagesPerPage = 30;

  channel.getUnconsumedMessagesCount().then(function(messageCount){
    var activeChannelMetaSnapshot = jQuery.extend(true, [], activeChannelMeta);  //Snapshot of array
    activeChannelMetaSnapshot.name = "pte_channel_started";
    activeChannelMetaSnapshot.message_count = messageCount;
    pte_parent_message_send(activeChannelMetaSnapshot);
  });

  channel.on('updated', updateActiveChannel);

  channel.on('messageAdded', addMessage);
  channel.on('messageUpdated', updateMessage);
  channel.on('messageRemoved', removeMessage);

  channel.on('removed', userRemoved);

  channel.on('typingStarted', function(member) {
      typingMembers.add(activeChannelUserDescriptors[member.identity].friendlyName);
      updateTypingIndicator();
  });
  channel.on('typingEnded', function(member) {
      typingMembers.delete(activeChannelUserDescriptors[member.identity].friendlyName);
      updateTypingIndicator();
  });

  channel.getUserDescriptors().then(function(userDescriptors){

    userDescriptors.items.forEach(function(item) {
      activeChannelUserDescriptors[item.identity] = item;
    });

    channel.getMessages(messagesPerPage).then(function(page) {
      activeChannelPage = page;
      $('#channel-messages ul').empty();
      page.items.forEach(addMessage);
      var newestMessageIndex = page.items.length ? page.items[page.items.length - 1].index : 0;
      var lastIndex = channel.lastConsumedMessageIndex;
      if (lastIndex && lastIndex !== newestMessageIndex) {
        var $li = $('li[data-index='+ lastIndex + ']');
        var top = $li.position() && $li.position().top;
        $li.addClass('last-read');
        $('#channel-messages').scrollTop(top + $('#channel-messages').scrollTop());
      } else {
        $('#channel-messages').scrollTop($('#channel-messages ul').height());
      }

      if ($('#channel-messages ul').height() <= $('#channel-messages').height()) {
        channel.updateLastConsumedMessageIndex(newestMessageIndex).then(function(){
          console.log('Updating LCM - get Messages');
          jQuery("li.pte_chat_list_item[data-cid='" + activeChannel.uniqueName + "']").remove();
          $('#pte_chat_no_chats_message').show();

        });
      }


      $("#message-body-input").data("emojioneArea").setText('').setFocus();


      return channel.getMembers();

    }).then(function(members) {

      channel.on('memberUpdated', updateMember);
      channel.on('memberJoined', memberJoined);
      channel.on('memberLeft', memberLeft);

      members.forEach(member => {
        member.getUser().then(user => {
          user.on('updated', () => {
            updateMember.bind(null, member, user);
          });
        });
      });
    });
  });
  //return channel.getMembers();
}

function clearActiveChannel() {

}

function updateActiveChannel(channel) {

  console.log("Updating Member Event No Action...");
  // console.log(channel);

}

function updateMember(memberStatus, user) {
  console.log("Updating Member Event No Action...");
//  console.log(channel);
}

function memberJoined(obj){
  //Update activeChannelUserDescriptors

  console.log("Member Joined Event No Action...");
  //console.log(obj);
}

function memberLeft(obj){
  //Update activeChannelUserDescriptors

  console.log("Member Left Event No Action...");
  //console.log(obj);
}

function userRemoved(obj){
  //Update activeChannelUserDescriptors

  console.log("User Removed Event No Action...");
  //console.log(obj);
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


function isJson(item) {
    item = typeof item !== "string"
        ? JSON.stringify(item)
        : item;
    try {
        item = JSON.parse(item);
    } catch (e) {
        return false;
    }
    if (typeof item === "object" && item !== null) {
        return true;
    }
    return false;
}

function alpn_wait_for_ready(waitPeriod, tryFrequency, checkCondition, callback, errorHandler){
	//in milliseconds. Checks every tryFrequency for checkCondition. If true, then callback. Keeps trying up to waitPeriod. Fails to errorHandler.
	var tryTimes = parseInt(waitPeriod / tryFrequency);
	if (checkCondition()) {
		callback();
		return;
	}
	var tryCount = 1;
	var alpn_timer = setInterval(function(){
		if (checkCondition()) {
			clearInterval(alpn_timer);
			callback();
		}
		tryCount++;
		if (tryCount >= tryTimes) {
			clearInterval(alpn_timer);
			errorHandler();
		}
	}, tryFrequency);
}

if(!String.linkify) {
  String.prototype.linkify = function() {

      var teHtml;
      // teamedge://
      var pte_pattern = /\b(?:teamedge):\/\/[a-z0-9-+&@#\/%?=~_|!:,.;]*[a-z0-9-+&@#\/%=~_|]/gim;
      // http://, https:// only
      var urlPattern = /\b(?:https?):\/\/[a-z0-9-+&@#\/%?=~_|!:,.;]*[a-z0-9-+&@#\/%=~_|]/gim;
      // www. sans http:// or https://
      var pseudoUrlPattern = /(^|[^\/])(www\.[\S]+(\b|$))/gim;
      // Email addresses
      var emailAddressPattern = /[\w.]+@[a-zA-Z_-]+?(?:\.[a-zA-Z]{2,6})+/gim;

      var body = this;

      // for (const item of this.matchAll(pte_pattern)) {
      //   const originalUrl = item[0];
      //   const urlParts = new URL(originalUrl);
      //   var urlPathname = urlParts.pathname;
      //   var urlProtocol = urlParts.protocol;
      //   const searchParams = new URLSearchParams(urlParts.search);
      //   var ownerId = searchParams.get('owner_id');
      //   var topicId = searchParams.get('topic_id');
      //   switch(urlPathname) {
      //     case "//vault_item":   //actually receive here. Naming confusing but full round trip.
      //       var vaultId = searchParams.get('vault_id');
      //       var vaultFileName = searchParams.get('vault_file_name');
      //       vaultFileName = (typeof vaultFileName != "undefined" && vaultFileName) ? vaultFileName : "File";
      //       teHtml = "<span class='pte_chat_internal_link' data-op='vault_item' data-tid = '" + topicId + "' data-oid = '" + ownerId + "' data-vid = '" + vaultId + "'  onclick='pte_handle_internal_link(this);'><i class='far fa-file-pdf'></i> " + vaultFileName + "</span>";
      //       body = body.replace(originalUrl, teHtml);
      //     break;
      //   }
      // }
      return body
          .replace(urlPattern, '<a class="pte_chat_httpx_link" target="_blank" href="$&">$&</a>')
          .replace(pseudoUrlPattern, '$1<a class="pte_chat_httpx_link" target="_blank" href="https://$2">$2</a>')
          .replace(emailAddressPattern, '<a class="pte_chat_mailto_link" href="mailto:$&">$&</a>');
  };
}

window.addEventListener('beforeunload', function(event){
  //console.log ("BEFORE UNLOAD");
  pte_leave_current_audio_room();
});
