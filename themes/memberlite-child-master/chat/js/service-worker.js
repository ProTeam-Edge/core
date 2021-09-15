importScripts('https://www.gstatic.com/firebasejs/8.6.3/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.6.3/firebase-messaging.js');

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

  var messaging = firebase.messaging();


 messaging.setBackgroundMessageHandler(payload => {
   console.log('sw '+payload)
  console.log('[firebase-messaging-sw.js] Received background message ', payload);
  var author=payload.data.author;
  if(typeof author !== 'undefined'){
// Customize notification here
var notificationTitle = 'New message from '+payload.data.author+'';
var notificationOptions = {
    body: payload.data.twi_body,
};

return self.registration.showNotification(notificationTitle,
    notificationOptions);
  }
  else
  {
     var twi_body = payload.data.twi_body;
     if(typeof twi_body !== 'undefined'){
       // Customize notification here
var notificationTitle = 'Message from https://alct.pro/';
var notificationOptions = {
    body: payload.data.twi_body,
};

return self.registration.showNotification(notificationTitle,
    notificationOptions);
     }
  }

})

self.addEventListener('notificationclick', function(event) {
  let url = 'https://alct.pro/missioncontrol/';
  event.notification.close(); // Android needs explicit close.
  event.waitUntil(
      clients.matchAll({type: 'window'}).then( windowClients => {
          // Check if there is already a window/tab open with the target URL
          for (var i = 0; i < windowClients.length; i++) {
              var client = windowClients[i];
              // If so, just focus it.
              if (client.url === url && 'focus' in client) {
                  return client.focus();
              }
          }
          // If not, then open the target URL in a new window/tab.
          if (clients.openWindow) {
              return clients.openWindow(url);
          }
      })
  );
});
