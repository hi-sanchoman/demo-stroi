import { initializeApp } from 'firebase/app'
import { getMessaging, getToken } from 'firebase/messaging'

const firebaseConfig = {
  apiKey: "AIzaSyB6DH-DmDVQOZHHEQH1aQ5A6SjQcKJvXqA",
  authDomain: "oks-oasis.firebaseapp.com",
  projectId: "oks-oasis",
  storageBucket: "oks-oasis.appspot.com",
  messagingSenderId: "223102235039",
  appId: "1:223102235039:web:bfa087202a00421bfe8daa",
  measurementId: "G-MZR602M5D2"
};

const app = initializeApp(firebaseConfig)
const messaging = getMessaging(app)

export default messaging