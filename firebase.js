import { initializeApp } from "https://www.gstatic.com/firebasejs/13.1.0/firebase-app.js";
import { getAuth, signInWithPopup, GoogleAuthProvider } from "https://www.gstatic.com/firebasejs/13.1.0/firebase-auth.js";

const firebaseConfig = {
  apiKey: "AIzaSyCKAv02Qh0eFAaNwNq2-xAiyvZEL27W0kU",
  authDomain: "lartproject.firebaseapp.com",
  projectId: "lartproject",
  storageBucket: "lartproject.firebasestorage.app",
  messagingSenderId: "1013130253616",
  appId: "1:1013130253616:web:3794438c9e54868d3824f5"
};

const app = initializeApp(firebaseConfig);
const auth = getAuth(app);
const googleProvider = new GoogleAuthProvider();
googleProvider.setCustomParameters({ prompt: "select_account" });

export async function signInWithGoogle() {
  const result = await signInWithPopup(auth, googleProvider);
  return result.user.getIdToken();
}