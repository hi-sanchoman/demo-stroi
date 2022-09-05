importScripts('https://www.gstatic.com/firebasejs/9.9.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.9.0/firebase-messaging-compat.js');

// import { onBackgroundMessage } from "https://www.gstatic.com/firebasejs/9.9.0/firebase-messaaging-sw.js";


const firebaseApp = firebase.initializeApp({
  apiKey: "AIzaSyB6DH-DmDVQOZHHEQH1aQ5A6SjQcKJvXqA",
  authDomain: "oks-oasis.firebaseapp.com",
  projectId: "oks-oasis",
  storageBucket: "oks-oasis.appspot.com",
  messagingSenderId: "223102235039",
  appId: "1:223102235039:web:bfa087202a00421bfe8daa",
  measurementId: "G-MZR602M5D2"
});

const isSupported = firebase.messaging.isSupported();

if (isSupported) {
  const messaging = firebase.messaging();

  messaging.onBackgroundMessage(({ notification: { title, body, image } }) => {
    console.log('[firebase-messaging-sw.js] Received background message ', title, body, image);

    self.registration.showNotification(title, { body, icon: image || '/images/launcher.png' });
  });
}

// // on background
// onBackgroundMessage(messaging, (payload) => {
//   console.log('[firebase-messaging-sw.js] Received background message ', payload);

//   // Customize notification here
//   const notificationTitle = 'Строительство';
//   const notificationOptions = {
//     body: 'У вас новое уведомление.',
//     icon: '/images/launcher.png'
//   };

//   self.registration.showNotification(notificationTitle, notificationOptions);
// });
