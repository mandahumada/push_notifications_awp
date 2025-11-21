import { initializeApp } from "https://www.gstatic.com/firebasejs/11.0.1/firebase-app.js";
import { getMessaging, onBackgroundMessage } from "https://www.gstatic.com/firebasejs/11.0.1/firebase-messaging-sw.js";

const firebaseConfig = {
  apiKey: "AIzaSyCqXuXwPhZyh3Mas12hw0323vWLkGqLxhY",
  authDomain: "actividadseis-314d1.firebaseapp.com",
  projectId: "actividadseis-314d1",
  storageBucket: "actividadseis-314d1.firebasestorage.app",
  messagingSenderId: "764109432872",
  appId: "1:764109432872:web:90f19424377e72356f2d1d",
  measurementId: "G-675Z2LMLVT"
};

const app = initializeApp(firebaseConfig);
const messaging = getMessaging(app);

onBackgroundMessage(messaging, (payload) => {
  console.log("📩 Payload completo:", JSON.stringify(payload, null, 2));
  console.log("📩 payload.notification:", payload.notification);
  console.log("📩 payload.data:", payload.data);
  
  let title = 'Nueva notificación';
  let body = 'Tienes una actualización';
  
  if (payload.notification) {
    title = payload.notification.title || title;
    body = payload.notification.body || body;
  } else if (payload.data) {
    title = payload.data.title || title;
    body = payload.data.body || body;
  }
  
  console.log("Mostrando - Título:", title, "| Body:", body);
  
  self.registration.showNotification(title, {
    body: body,
    icon: "/awpp1/favicon.png",
    badge: "/awpp1/favicon.png",
    vibrate: [200, 100, 200],
    tag: 'notificacion-examen'
  });
});