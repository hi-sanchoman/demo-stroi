// import { initializeApp } from 'firebase/app'
// import { getMessaging } from 'firebase/messaging'
// import { onBackgroundMessage } from "firebase/messaging/sw";


// const firebaseApp = initializeApp({
//   apiKey: "AIzaSyB6DH-DmDVQOZHHEQH1aQ5A6SjQcKJvXqA",
//   authDomain: "oks-oasis.firebaseapp.com",
//   projectId: "oks-oasis",
//   storageBucket: "oks-oasis.appspot.com",
//   messagingSenderId: "223102235039",
//   appId: "1:223102235039:web:bfa087202a00421bfe8daa",
//   measurementId: "G-MZR602M5D2"
// });

// const messaging = getMessaging(firebaseApp);

// // on background
// onBackgroundMessage(messaging, (payload) => {
//   console.log('[firebase-messaging-sw.js] Received background message ', payload);

//   // Customize notification here
//   const notificationTitle = 'OKS OASIS';
//   const notificationOptions = {
//     body: 'У вас новое уведомление.',
//     icon: '/images/launcher.png'
//   };

//   self.registration.showNotification(notificationTitle, notificationOptions);
// });
