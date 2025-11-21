import { initializeApp } from "https://www.gstatic.com/firebasejs/11.0.1/firebase-app.js";
import { getMessaging, onBackgroundMessage } from "https://www.gstatic.com/firebasejs/11.0.1/firebase-messaging-sw.js";

const firebaseConfig = {
  apiKey: "",
  authDomain: "",
  projectId: "",
  storageBucket: "",
  messagingSenderId: "",
  appId: "",
  measurementId: ""
};

const app = initializeApp(firebaseConfig);
const messaging = getMessaging(app);

onBackgroundMessage(messaging, (payload) => {
  console.log("Payload completo:", JSON.stringify(payload, null, 2));
  console.log("payload.notification:", payload.notification);
  console.log("payload.data:", payload.data);
  
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
