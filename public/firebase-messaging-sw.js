importScripts('https://www.gstatic.com/firebasejs/8.3.0/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.3.0/firebase-messaging.js');

// Initialize the Firebase app in the service worker by passing in the
// messagingSenderId.
firebase.initializeApp({
  apiKey: "AIzaSyCL0m8D9ZUR29mKiaYbZXjbbWJaQxeJtlE",
  authDomain: "pruebas-33641.firebaseapp.com",
  projectId: "pruebas-33641",
  storageBucket: "pruebas-33641.appspot.com",
  messagingSenderId: "688870553131",
  appId: "1:688870553131:web:e064e35fe33b06978fdc6e",
  measurementId: "G-YRHSYZXRYX"
});

// Retrieve an instance of Firebase Messaging so that it can handle background
// messages.
const messaging = firebase.messaging();

messaging.setBackgroundMessageHandler(function(payload) {
  console.log('[firebase-messaging-sw.js] Received background message ', payload);
  // Customize notification here
  const notificationTitle = 'Background Message Title';
  const notificationOptions = {
    body: 'Background Message body.',
    icon: './images/logo.png'    
  };

  return self.registration.showNotification(notificationTitle,
      notificationOptions);
});