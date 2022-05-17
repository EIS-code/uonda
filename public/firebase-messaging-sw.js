// Give the service worker access to Firebase Messaging.
// Note that you can only use Firebase Messaging here. Other Firebase libraries
// are not available in the service worker.importScripts('https://www.gstatic.com/firebasejs/7.23.0/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-messaging.js');

/*
Initialize the Firebase app in the service worker by passing in the messagingSenderId.
*/
var apiKey              = "{{ env('FCM_WEB_API_KEY', '') }}",
    authDomain          = "{{ env('FCM_WEB_AUTH_DOMAIN', '') }}",
    projectId           = "{{ env('FCM_WEB_PROJECT_ID', '') }}",
    storageBucket       = "{{ env('FCM_WEB_STORAGE_BUCKET', '') }}",
    messagingSenderId   = "{{ env('FCM_SENDER_ID', '') }}",
    appId               = "{{ env('FCM_WEB_APP_ID', '') }}",
    measurementId       = "{{ env('FCM_WEB_MEASUREMENT_ID', '') }}";

var firebaseConfig = {
    apiKey: apiKey,
    authDomain: authDomain,
    projectId: projectId,
    storageBucket: storageBucket,
    messagingSenderId: messagingSenderId,
    appId: appId,
    measurementId: measurementId
};

firebase.initializeApp(firebaseConfig);

// Retrieve an instance of Firebase Messaging so that it can handle background
// messages.
const messaging = firebase.messaging();

/* messaging.setBackgroundMessageHandler(function (payload) {
    console.log("Message received.", payload);

    const title = "Hello world is awesome";

    const options = {
        body: "Your notificaiton message .",
        icon: "/firebase-logo.png",
    };

    return self.registration.showNotification(
        title,
        options,
    );
}); */
