//TODO Make sure that server room management events are received and handled properly. Including a room dissappearing.

var inMessageTextArea = false;
var security = {};

var chatIsActive = false;
var activeChannel;
var activeChannelMeta = {};
var activeChannelPage;
var activeChannelUserDescriptors = {};
var speechEvents = {};

var groupMessagesWithin = 10000; //milliseconds
var previousMessageTime = new Date() - (groupMessagesWithin + 1000);  //expire it to start
var previousMessagAuthor = 0;

var activeVideoRoom = false;

var currentCancelButtonId;

var fileUploaders = [];

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
var siteBase = "https://wiscle.com/wp-content/themes/memberlite-child-master/";
var siteRoot = "/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/";

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
              security = data.security;
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


      wsc_setup_chat();
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
                pte_clear_channel_updates(activeChannel.uniqueName);
                isUpdatingConsumption = false;
                wsc_handle_no_chats();
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


    function wsc_setup_chat(){
      logIn();
    }

    function pte_set_chat(state){
    	if (state == 'disabled') {
        $('#pte_chat_messages_area').hide();
        chatIsActive = 'disabled';
        // if ($("#message-body-input").data("emojioneArea")){
        //   //$("#message-body-input").data("emojioneArea").setText("");
        // }
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
          console.log("LEAVING...");
          speechEvents.stop();
          activeVideoRoom.disconnect();
          activeVideoRoom = false;
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

      var token = (typeof userContext.token != "undefined" && userContext.token) ? userContext.token : false;

      if (token && 'mediaDevices' in navigator && 'getUserMedia' in navigator.mediaDevices) {
        navigator.mediaDevices.getUserMedia({
          audio: true
        }).then(function(mediaStream) {
          var options = {'interval': '100'};
          speechEvents = hark(mediaStream, options);
          speechEvents.on('speaking', function() {
            console.log('speaking');
          });
          speechEvents.on('stopped_speaking', function() {
            console.log('stopped_speaking');
          });
          var localTracks = mediaStream.getTracks();
          localTracks.forEach(function(track) {
              track.enabled = false;  //enter room muting my track
              //console.log(track);
          });
          return Video.connect(token, {
            name: roomChannelSid,
            tracks: localTracks
          });
        }).then(function(room) {
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

      var startWithAudio = false;

      console.log('CHAT WINDOW -- Handling Chat Start/STOP');
      //TODO Handle Switching between profile and vault views - should not reload chat...
      var data = event.data.data;
      var newChannelId = data.channel_id;

      if (newChannelId) {
        if (typeof activeChannel != "undefined" && activeChannel && activeChannel.sid == newChannelId) {
          console.log("Same Channel. Do Nothing");
          return;
        }
        if (activeVideoRoom) {
          startWithAudio = true;
          pte_leave_current_audio_room();
        }
        activeChannelMeta = data;
        joinChannel(newChannelId, startWithAudio);
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


function joinChannel(sid, startWithAudio = false) {

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
        if (startWithAudio) {
          var activeChannelMetaSnapshot = jQuery.extend(true, [], activeChannelMeta);  //Snapshot of array
          activeChannelMetaSnapshot.name = "pte_start_audio_wait";
          pte_parent_message_send(activeChannelMetaSnapshot);
          pte_start_audio(activeChannel.sid);
        }
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
  var body = textAreaObj.getText().trim() ? textAreaObj.getText().trim() : "";

  var previewImage = jQuery("div#wsc_message_preview img.wsc_paste_preview_image");
  var fileId = previewImage.data('fid') ? previewImage.data('fid') : '';
  var isReady = (previewImage.data('wsc-ready') == 'true') ? true : false;
  var fileName = fileId && isReady ? fileId  + ".webp" : '';

  textAreaObj.setText("").setFocus();
  $("#buttercup_easter_egg").addClass('pte_button_disabled');

  activeChannel.sendMessage(body, {
    'message_type': 'message',
    'file_id' : fileId,
    'file_name': fileName,
    'title': '',
    'description': ''
  }).then(function(newMessageIndex) {

    $('#channel-messages').scrollTop($('#channel-messages ul').height());
    $('#channel-messages li.last-read').removeClass('last-read');

    var imageSource = $('div#wsc_message_preview img.wsc_paste_preview_image').attr('src');
    if (imageSource) {
      var newMessageImageContainer = $('#channel-messages li[data-index=' + newMessageIndex + '] div.wsc_chat_preview_panel_outer');
      var previewTitle = '';
      var previewDescription = '';
      var previewPanel = "<div class='wsc_chat_preview_panel_outer'><div class='wsc_preview_image_inner'><div class='wsc_preview_image_inner_left'><img onclick='wsc_open_image_window(this);' class='wsc_preview_image wsc_clickable' src='" + imageSource + "'></div><div class='wsc_preview_image_inner_right'><div class='wsc_preview_image_title'>" + previewTitle + "</div><div class='wsc_preview_image_description'>" + previewDescription + "</div></div></div></div>"
      newMessageImageContainer.html(previewPanel);
    }
    wsc_close_message_preview();
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
      inMessageTextArea = false;
      // console.log("BLUR");
      // selectionRange = saveSelection();
      // console.log(selectionRange);
    },
    focus: function(editor, event){
      inMessageTextArea = true;
      // console.log("FOCUS");
      // console.log(selectionRange);
      // restoreSelection(selectionRange);
    },
    paste: function(editor, clipboardText, clipboardHTML){

        navigator.permissions.query({ name: "clipboard-read" }).then((result) => {
          if (result.state == "granted" || result.state == "prompt") {
              navigator.clipboard.read().then((data) => {
                // console.log("CLIPBOARD");
                // console.log(data);
                for (let i = 0; i < data.length; i++) {
                  if (!data[i].types.includes("image/png")) {
                    console.log("Clipboard contains non-image data. Unable to access it.");
                  } else {
                    data[i].getType("image/png").then((fileBlob) => {
                      var fileId = pte_UUID();
                      var fileName = fileId + ".png";
                      var fileType = fileBlob.type;

                      if (fileId && fileName && fileBlob) {
                        var imageData = {
                          file_name: fileName,
                          file_id: fileId,
                          type: fileType,
                          blob: fileBlob,
                          image_url: true,
                          message: false
                        };
                        wsc_chat_preview_upload(imageData);
                        var imageSource = URL.createObjectURL(fileBlob);
                        jQuery("div#wsc_message_preview").html("<img data-fid='" + fileId + "' class='wsc_paste_preview_image' src='" + imageSource + "'><div id='wsc_message_preview_close' onclick='wsc_close_message_preview();'>✕</div>").fadeIn();

                        $("#buttercup_easter_egg").removeClass('pte_button_disabled');
                      }
                    });
                }
              }
            })
              .catch(function(error) {
                console.log("Invalid item on clipboard");
              });
          }
        });

    },
    keydown: function (event, key) {
      var isCliboardPreview = jQuery("div#wsc_message_preview").html() ? true : false;
      var body = this.getText();
      var elId = $el.attr("id");
      var sendButton = $("#buttercup_easter_egg");
        if ((body || isCliboardPreview) && elId == "message-body-input") {
          sendButton.removeClass('pte_button_disabled');
        } else {
          sendButton.addClass('pte_button_disabled');
        }
        if ((key.keyCode == 10 || key.keyCode == 13) && (key.ctrlKey || event.metaKey == '⌘-' )) {
          if (body || isCliboardPreview) {
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

function wsc_close_message_preview(){
  jQuery("div#wsc_message_preview").fadeOut(250, function(){
    jQuery("div#wsc_message_preview").html("");
    var messageEditor = $("#message-body-input").data("emojioneArea");
    var sendButton = $("#buttercup_easter_egg");
    var body = messageEditor.getText();
    if (body) {
      sendButton.removeClass('pte_button_disabled');
    } else {
      sendButton.addClass('pte_button_disabled');
    }
  });
}

function wsc_send_parent_logged_out(){
  var activeChannelMetaSnapshot = jQuery.extend(true, [], activeChannelMeta);  //Snapshot of array
  activeChannelMetaSnapshot.name = "pte_channel_logged_out";
  pte_parent_message_send(activeChannelMetaSnapshot);
}

function logIn() {

  $.getJSON( '../chat/token.php', {

    device: 'browser'

  }, function(data) {

    console.log("LOGIN");
    console.log(data);

    if (!data.identity) {
        console.log("HANDLE CHAT LOGGED OUT - CHAT"); //exit to login
        wsc_send_parent_logged_out();
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
              console.log(data1);
              client.updateToken(data1.token);
              userContext = {identity: data1.identity};
            } else {
              console.error('Failed to get a token ');
              throw new Error(data1);
            }
          });
        });
        //firebase addition for handling browser notifications
        if (firebase && firebase.messaging()) {
          firebase.messaging().requestPermission().then(() => {
            firebase.messaging()
            .getToken({vapidKey:"BDypbWx3yzZhri6Kz3ooioxhSIoEmFi5yzz6r7X-tJ9wCSjRJ7TPjW9MMpoVhAD04-GY5hy1uIHNzkJ10E9-NE8"})
            .then((fcmToken) => {
              jQuery.ajax({
              	url: "https://wiscle.com/wp-content/themes/memberlite-child-master/api_handler/saveFcm.php",
              	type: "POST",
              	data:{token: fcmToken, userId: userContext.identity},
              	success: function(html){
                    console.log("SAVED FCM");
              	 }
               });
              client.setPushRegistrationId('fcm', fcmToken);
            }).catch((err) => {
                console.log("CANT GET TOKEN");
                console.log(err);
            });
          }).catch((err) => {
            console.log('firebase error 1');
            console.log(err);
          });
        } else {
          console.log('firebase error 2');
          console.log(err);
        }

        client.getSubscribedChannels().then(function(paginator) {
          console.log('Getting Channels..');
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
            // console.log("Channel Updated Called");
            // console.log(channelUpdate);
          });

          client.on('channelJoined', function(channel) {
            console.log('Channel Joined Called');
            // console.log(channel);

            var channelFriendlyName = channel.friendlyName;
            var channelState = channel.state;

            // console.log("Channel State");
            // console.log(channelState);

            var lastConsumedMessageIndex = channelState.lastConsumedMessageIndex;
            var lastMessage = channelState.lastMessage;
            var lastMessageIndex = lastMessage.index;

            console.log(lastMessage);
            console.log(lastConsumedMessageIndex);

            if (lastConsumedMessageIndex < lastMessage) {
              console.log("ADDED BECAUSE UNREADS");

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
            } else {
              console.log("DID NOT ADD THIS BECAUSE CURRENT");
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

      //  });

        client.on('connectionError', function(channel) {
          console.log("Connection Error -- Retrying");
          console.log(channel);

          // var activeChannelMetaSnapshot = jQuery.extend(true, [], activeChannelMeta);  //Snapshot of array
          // activeChannelMetaSnapshot.name = "pte_channel_stop";
          // pte_parent_message_send(activeChannelMetaSnapshot);
          pte_set_chat("disabled");
          clearCurrentChannel();
          wsc_setup_chat();

        });


        client.on('connectionStateChanged', function(channelState) {
          console.log("Channel State Changed");
          console.log(channelState);

          if (channelState == "denied") {
            console.log("DENIED LOGIN");
            //alert("About to try Chat Drop fix.");
            pte_set_chat("disabled");
            clearCurrentChannel();
            wsc_setup_chat();
          }
        });


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

function wsc_handle_no_chats() {
  if (!$('#pte_chat_activity_list li').length) {
    if ($('#pte_chat_no_chats_message').css('display') == 'none') {
      $('#pte_chat_no_chats_message').show();
      var activeChannelMetaSnapshot = jQuery.extend(true, [], activeChannelMeta);  //Snapshot of array
      activeChannelMetaSnapshot.name = "pte_update_chat_total";
      activeChannelMetaSnapshot.chat_total_unreads = "--";
      pte_parent_message_send(activeChannelMetaSnapshot);
    }
  }
}

function pte_process_new_chat_dom(){
  // console.log("Processing new chat dom");
  // console.log(userChannelsDom);
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
  } else {
    wsc_handle_no_chats();
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
  if (typeof activeChannel != "undefined" && typeof activeChannel.uniqueName != "undefined" && channelUniqueId == activeChannel.uniqueName) {
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
    channel.getUnconsumedMessagesCount()
    .then(function(messageCount){
      if (messageCount > 0) {   //Unreads
        client.getUserDescriptor(showIdentity).then(function(userDescriptor){
          console.log("ADDING DUE TO UNREADS");
          console.log(showIdentity);
          console.log(userDescriptor);
          var userAttributes = userDescriptor.attributes;
          var channelFriendlyName = userAttributes.full_name;
          var channelImageHandle = userAttributes.image_handle;

          pte_add_edit_chat_panel(channelUniqueId, channelFriendlyName, messageCount, channelImageHandle, contactData.owner_id);
        })
        .catch(function(err){
          console.log("Handling exception for getUserDescriptor");
          pte_set_chat("disabled");
          clearCurrentChannel();
          wsc_setup_chat();
        });
      }
    }).catch(function(err){
      console.log("Handling exception for getUnconsumedMessagesCount 1");
      pte_set_chat("disabled");
      clearCurrentChannel();
      wsc_setup_chat();
    });

  } else {
      channel.getUnconsumedMessagesCount().then(function(messageCount){
        if (messageCount > 0) {   //Unreads
          channel.getAttributes().then(function(attributes){
            console.log("HERE");
            console.log(channel);
            pte_add_edit_chat_panel(channelUniqueId, channelFriendlyName, messageCount, attributes.image_handle);
          });
        }
      }).catch(function(err){
        console.log("Handling exception for getUnconsumedMessagesCount 2");
        pte_set_chat("disabled");
        clearCurrentChannel();
        wsc_setup_chat();
      });
  }
} else {
    console.log("ERROR? No Topic ID");
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

  var imageHandle = imageBase + attributes.image_handle;
  var time = message.timestamp;
  var minutes = time.getMinutes();
  var hours = time.getHours();
  var displayHours = (hours % 12) ? hours % 12 : 12;
  var ampm = Math.floor(hours / 12) ? 'PM' : 'AM';

  var messageMonth = time.getMonth() + 1;
  var messageDay = time.getDate();
  var messageYear = time.getFullYear().toString().slice(-2);

  var isTimeLapsedSinceLastMessage = ((time - previousMessageTime) > groupMessagesWithin) ? true : false;
  var isLoggedInMember = (userContext.identity == message.author) ? true : false;
  var isSameAuthor = (previousMessagAuthor && message.author == previousMessagAuthor) ? true : false;
  var isPrepend = (typeof message.wsc_prepend != "undefined" && message.wsc_prepend) ? true : false;
  var isUpdating = (typeof message.wsc_updated != "undefined" && message.wsc_updated) ? true : false;

  previousMessagAuthor = message.author;
  previousMessageTime = time;

  if (minutes < 10) { minutes = '0' + minutes; }

  if (isLoggedInMember) {
    var editButtonHtml = "<i class='far fa-pencil-alt pte_flex_item_editable wsc_chat_edit_button' title='Edit Message'></i>";
    var editableString = '<div class="pte_flex_edit_body"></div>';
  } else {
    var editButtonHtml = "";
    var editableString = '';
  }

  var html =  '<div class="pte_flex_container">';

    if (isUpdating || isPrepend || !isSameAuthor || ( isSameAuthor && isTimeLapsedSinceLastMessage)) {
      html += '<div class="pte_flex_user_icon">';
      html += '<img src="' + imageHandle + '" class="pte_topic_icon">';
      html += '</div>';
    } else {
      html += '<div class="pte_flex_user_icon">';
      html += "&nbsp;";
      html += '</div>';
    }

      html += '<div class="pte_flex_body">';

      if (isPrepend || !isSameAuthor || ( isSameAuthor && isTimeLapsedSinceLastMessage)) {
        html += '<span style="font-size: 12px; font-weight: bold;">' + user.friendlyName + '</span></br>';
      }

      html +=  objectBody;

      html += '<div class="timestamp">' + messageMonth + '/' + messageDay + '/' + messageYear +  ' ' + displayHours + ':' + minutes + '' + ampm + ''  + editButtonHtml + '</div>';

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
  var imageHandle = imageBase + attributes.image_handle;

  var time = message.timestamp;
  var minutes = time.getMinutes();
  var hours = time.getHours();
  var displayHours = (hours % 12) ? hours % 12 : 12;
  var ampm = Math.floor(hours / 12) ? 'PM' : 'AM';

  var messageMonth = time.getMonth() + 1;
  var messageDay = time.getDate();
  var messageYear = time.getFullYear().toString().slice(-2);

  var isTimeLapsedSinceLastMessage = ((time - previousMessageTime) > groupMessagesWithin) ? true : false;
  var isLoggedInMember = (userContext.identity == message.author) ? true : false;
  var isSameAuthor = (previousMessagAuthor && message.author == previousMessagAuthor) ? true : false;
  var isPrepend = (typeof message.wsc_prepend != "undefined" && message.wsc_prepend) ? true : false;

  previousMessagAuthor = message.author;
  previousMessageTime = time;

  if (minutes < 10) { minutes = '0' + minutes; }

  if (isLoggedInMember) {
    var editButtonHtml = "<i class='far fa-pencil-alt pte_flex_item_editable wsc_chat_edit_button' title='Edit Message'></i>";
    var editableString = '<div class="pte_flex_edit_body"></div>';
  } else {
    var editButtonHtml = "";
    var editableString = '';
  }

  var messageAttributes = message.attributes;
  var isUpdating = (typeof message.wsc_updated != "undefined" && message.wsc_updated) ? true : false;
   //console.log("DRAWING");
  // console.log(messageAttributes);

  var messageFileId = (typeof messageAttributes.file_id != "undefined" && messageAttributes.file_id) ? messageAttributes.file_id : false;
  var messagePreviewFileName = (typeof messageAttributes.file_name != "undefined" && messageAttributes.file_name) ? messageAttributes.file_name : false;
  var previewTitle = (typeof messageAttributes.title != "undefined" && messageAttributes.title) ? messageAttributes.title.substr(0, 100) : '';
  var previewDescription = (typeof messageAttributes.description != "undefined" && messageAttributes.description) ? messageAttributes.description.substr(0, 250) : '';

  var previewPanel = "<div class='wsc_chat_preview_panel_outer'></div>";
  if (messagePreviewFileName || previewTitle || previewDescription) {  //preview ready
    var imageSource = messagePreviewFileName ? imageBase + messagePreviewFileName : imageBase + "f326a01e-no_chat_image.webp";
    var previewPanel = "<div class='wsc_chat_preview_panel_outer'><div class='wsc_preview_image_inner'><div class='wsc_preview_image_inner_left'><img onclick='wsc_open_image_window(this);' class='wsc_preview_image wsc_clickable' src='" + imageSource + "'></div><div class='wsc_preview_image_inner_right'><div class='wsc_preview_image_title'>" + previewTitle + "</div><div class='wsc_preview_image_description'>" + previewDescription + "</div></div></div></div>"
  }

  var messageBody = message.body;
  if (typeof messageBody != "undefined" && messageBody) {
    messageBody = messageBody.linkify(message);
  } else {
    messageBody = "";
  }

  var html =  '<div class="pte_flex_container">';
      if (isUpdating || isPrepend || !isSameAuthor || ( isSameAuthor && isTimeLapsedSinceLastMessage)) {
        html += '<div class="pte_flex_user_icon">';
        html += '<img src="' + imageHandle + '" class="pte_topic_icon">';
        html += '</div>';
      } else {
        html += '<div class="pte_flex_user_icon">';
        html += "&nbsp;";
        html += '</div>';
      }
      html += '<div class="pte_flex_body">';
      if (isPrepend || !isSameAuthor || ( isSameAuthor && isTimeLapsedSinceLastMessage)) {
        html += '<span style="font-size: 12px; font-weight: bold;">' + user.friendlyName + '</span></br>';
      }
      html +=  messageBody;
      html +=  previewPanel;
      html += '<div class="timestamp">' + messageMonth + '/' + messageDay + '/' + messageYear +  ' ' + displayHours + ':' + minutes + '' + ampm + ''  + editButtonHtml + '</div>';
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
              var messageAttributes = message.attributes;
              message.wsc_updated = true;
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
  message.wsc_prepend = true;
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
          jQuery("li.pte_chat_list_item[data-cid='" + activeChannel.uniqueName + "']").remove();
        });
      }
      $("#message-body-input").data("emojioneArea").setFocus();

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

function wsc_get_new_uppy_instance(){
 var localInstance = new Uppy.Core({
      id: "wsc_file_uploader",
      debug: true,
      autoProceed: true,
      allowMultipleUploads: false,
      restrictions: {
          maxFileSize: 1024 * 1024 * 5,
          maxNumberOfFiles: 1,
          minNumberOfFiles: 1
      }
    })
    .use(Uppy.Transloadit, {
             service: 'https://api2.transloadit.com',
             waitForEncoding: true,
             importFromUploadURLs: false,
             alwaysRunAssembly: false,
             signature: null,
             limit: 1,
             params: {
                auth: { key: '0f89b090056541ff8ed17c5136cd7499' },
                template_id: '3f8a70152574422891ac0b9358ae9c8d'
              }
     })
        .on('transloadit:complete', (assembly) => {
           console.log("TLI Chat Handle Preview Upload Complete...");

          if (typeof assembly.results != "undefined" && typeof assembly.results.resize_image != "undefined") {
            var results = assembly.results.resize_image[0];
            var currentMessage = (typeof localInstance.wsc_message != "undefined" && localInstance.wsc_message) ? localInstance.wsc_message : false;
            if (currentMessage) {
              var currentMessageAttributes = currentMessage.attributes;
              currentMessageAttributes.file_name = results.name;
              currentMessage.updateAttributes(currentMessageAttributes);
            } else {
              var resultFileId = results.basename;
              //console.log("Looking for: ", resultFileId);
              var previewImage = jQuery("img.wsc_paste_preview_image[data-fid='" + resultFileId + "']");
              previewImage.data("wsc-ready", 'true');
                var messageAttributes, fileId, fileName;
                activeChannel.getMessages(30).then(function(page) {
                  page.items.forEach(function(message){
                    messageAttributes = message.attributes;
                    fileId = (typeof messageAttributes.file_id != "undefined" && messageAttributes.file_id) ? messageAttributes.file_id : "";
                    fileName = (typeof messageAttributes.file_name != "undefined" && messageAttributes.file_name) ? messageAttributes.file_name : "";
                    if (!fileName && fileId && resultFileId && fileId == resultFileId) {
                      messageAttributes.file_name = fileId + ".webp";
                      message.updateAttributes(messageAttributes);
                      throw("Found and UPDATED Message with File Name");
                    }
                  });
                }).catch(function(e){
                  //console.log(e);
                });
            }

            if (results.original_name) {
              jQuery.ajax({
                url: siteBase + 'wsc_delete_tmp_file.php',
                type: 'POST',
                data: {
                  security: security,
                  file_name: results.original_name
                },
                dataType: "json",
                success: function(json) {
                },
                error: function() {
                  console.log('problem deleting temp file');
                }
              });
            }
        } else {
          //Do nothing failed?
          console.log("Image Upload Failed. Ignore");
        }
      });

      return localInstance;
}

function wsc_chat_preview_upload(imageData) {

  // console.log("PREVIEW UPLOAD");
  // console.log(imageData);

    var availableUploader = false;
    var message = (typeof imageData.message != "undefined" && imageData.message) ? imageData.message : false;

    try {
      fileUploaders.forEach(function(uppyUploader){
        if (uppyUploader.getState().allowNewUpload) {
          availableUploader = uppyUploader = wsc_get_new_uppy_instance();
          throw Exception;
        } else {
          availableUploader = wsc_get_new_uppy_instance();
          fileUploaders.push(availableUploader);
          throw Exception;
        }
      });
    } catch (e) {
    }
    if (!availableUploader) {  //first time
      availableUploader = wsc_get_new_uppy_instance();
      fileUploaders.push(availableUploader);
    }
    availableUploader.wsc_message = message;
    availableUploader.addFile({
      name: imageData.file_name,
      type: imageData.type,
      data: imageData.blob,
      isRemote: false
    });
}

 function wsc_open_image_window(el) {
   var $el = jQuery(el);
   var imageSource = $el.attr("src");
   window.open(imageSource, '_blank');
 }

function wsc_create_message_preview_url(data) {

   // console.log("Creating Message Preview URL");
   // console.log(data);

  var siteUrl = (typeof data.site_url != "undefined" && data.site_url) ? data.site_url : false;
  var topicId = (typeof data.topic_id != "undefined" && data.topic_id) ? data.topic_id : false;
  var message = (typeof data.message != "undefined" && data.message) ? data.message : false;
  var fileId = (typeof data.file_id != "undefined" && data.file_id) ? data.file_id : false;

  if (siteUrl && topicId && message && siteUrl.substr(0,7) != 'http://') {

    jQuery.ajax({
      url: siteBase + 'wsc_handle_chat_preview_url.php',
      type: 'POST',
      data: {
        security: security,   //global sent from main on chat start
        site_url: siteUrl,
        file_id: fileId
      },
      dataType: "json",
      success: function(json) {
        // console.log("BACK");
        // console.log(json);

        var fileName = json.file_name;
        var imageUrl = json.image_url;
        var previewTitle = json.title;
        var previewDescription = json.description;
        var messageIndex = message.index;

        if (fileName && imageUrl) { //Handle with image
          try {
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function(){
                if (this.readyState == 4 && this.status == 200){
                  console.log("BLOBBY");
                  console.log(this.response);
                    if (this.response.size) {
                      var fileType = this.response.type;
                      var fileBlob= this.response;
                      var acceptArray = ["image/jpeg", "image/png", "image/webp"];
                      if (acceptArray.includes(fileType)) {
                        if (fileId && fileName && fileBlob) {
                          var imageData = {
                            file_name: fileName,
                            file_id: fileId,
                            type: fileType,
                            blob: fileBlob,
                            image_url: imageUrl,
                            message: message
                          };
                          var messageAttributes = message.attributes;
                          messageAttributes.file_id = fileId;
                          messageAttributes.file_name = fileName;
                          messageAttributes.title = previewTitle;
                          messageAttributes.description = previewDescription;
                          message.updateAttributes(messageAttributes);
                          wsc_chat_preview_upload(imageData);

                          var imageSource = URL.createObjectURL(fileBlob);
                          previewTitle = messageAttributes.title.substr(0, 100);
                          previewDescription = messageAttributes.description.substr(0, 250);

                          jQuery("div#channel-messages li[data-index='" + messageIndex + "'] div.wsc_chat_preview_panel_outer").html("<div class='wsc_preview_image_inner'><div class='wsc_preview_image_inner_left'><img  onclick='wsc_open_image_window(this);' class='wsc_preview_image wsc_clickable' src='" + imageSource + "'></div><div class='wsc_preview_image_inner_right'><div class='wsc_preview_image_title'>" + previewTitle + "</div><div class='wsc_preview_image_description'>" + previewDescription + "</div></div></div>");
                        }
                    }
                }
              }
            }
            var tempFile = siteBase + "tmp/" + fileName;
            xhr.open('GET', tempFile);
            xhr.responseType = 'blob';
            xhr.send();

          } catch (e) {
            console.log("Error Getting Preview File");
            console.log(e);
          }
        } else {
          //No img
          var messageAttributes = message.attributes;
          messageAttributes.file_id = fileId;
          messageAttributes.file_name = "";
          messageAttributes.title = previewTitle;
          messageAttributes.description = previewDescription;
          message.updateAttributes(messageAttributes);
          previewTitle = messageAttributes.title.substr(0, 100);
          previewDescription = messageAttributes.description.substr(0, 250);
          var imageSource = imageBase + "f326a01e-no_chat_image.webp";
          jQuery("div#channel-messages li[data-index='" + messageIndex + "'] div.wsc_chat_preview_panel_outer").html("<div class='wsc_preview_image_inner'><div class='wsc_preview_image_inner_left'><img class='wsc_preview_image' src='" + imageSource + "'></div><div class='wsc_preview_image_inner_right'><div class='wsc_preview_image_title'>" + previewTitle + "</div><div class='wsc_preview_image_description'>" + previewDescription + "</div></div></div>");
        }
      },
      error: function() {
        console.log('problemo - save chat image');
      //TODO
      }
    });
  } else {
    console.log("PREVIEW URL ERROR, DATA MISSING");
    console.log(data);
  }

}

if(!String.linkify) {

  String.prototype.linkify = function(message) {

      var messageAttributes = message.attributes;

      var fileId = (typeof messageAttributes.file_id != "undefined" && messageAttributes.file_id) ? messageAttributes.file_id : false;
      var messagePreviewFileName = (typeof messageAttributes.file_name != "undefined" && messageAttributes.file_name) ? messageAttributes.file_name : false;

      // wiscle://
      var pte_pattern = /\b(?:wiscle):\/\/[a-z0-9-+&@#\/%?=~_|!:,.;]*[a-z0-9-+&@#\/%=~_|]/gim;

      var urlPattern = /(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.[^\s]{2,}|www\.[a-zA-Z0-9]+\.[^\s]{2,})/gi;
      // Email addresses
      var emailAddressPattern = /[\w.]+@[a-zA-Z_-]+?(?:\.[a-zA-Z]{2,6})+/gim;

      var body = this;

      body = body.replace(urlPattern, (match) => {
        match = match.trim();
        if (match.includes("www.")) {
            var sPos = match.indexOf("www.");
            match = match.substr(0, sPos) + match.substr(sPos + 4);
        }
        if (match.substr(0, 7) == "http://") {
          match = "https://" + match.substr(7);
        }
        if (match.substr(0, 8) != "https://") {
          match = "https://" + match;
        }
        if (!fileId && (userContext.identity == message.author)) {
          fileId = pte_UUID();
          messageAttributes.file_id = fileId;  //update right away to avoid regenerating elsewhere?
          message.updateAttributes(messageAttributes);
          var previewData = {
            site_url: match,
            message: message,
            topic_id: activeChannel.uniqueName,
            file_id: fileId
          };
          wsc_create_message_preview_url(previewData);
        }
        return '<a class="pte_chat_httpx_link" target="_blank" href="' + match + '">' + match + '</a>';
      });

      body = body.replace(emailAddressPattern, (match) => {
        match = match.trim();
        return '<a class="pte_chat_mailto_link" target="_blank" href="mailto:' + match + '">' + match + '</a>';
      });

      return body;
  };
}

function pte_UUID() { // Public Domain/MIT
    var d = new Date().getTime();//Timestamp
    var d2 = (performance && performance.now && (performance.now()*1000)) || 0;//Time in microseconds since page-load or 0 if unsupported
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        var r = Math.random() * 16;//random number between 0 and 16
        if(d > 0){//Use timestamp until depleted
            r = (d + r)%16 | 0;
            d = Math.floor(d/16);
        } else {//Use microseconds since page-load if supported
            r = (d2 + r)%16 | 0;
            d2 = Math.floor(d2/16);
        }
        return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
    });
}


window.addEventListener('beforeunload', function(event){
  //console.log ("BEFORE UNLOAD");
  pte_leave_current_audio_room();
});
