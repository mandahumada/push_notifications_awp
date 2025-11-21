import { initializeApp } from "https://www.gstatic.com/firebasejs/11.0.1/firebase-app.js";
import { getMessaging, getToken, onMessage } from "https://www.gstatic.com/firebasejs/11.0.1/firebase-messaging.js";

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

export async function solicitarPermisoYGenerarToken() {
  try {
    console.log("Solicitando permiso de notificaciones...");
    const token = await getToken(messaging, {
      vapidKey: "",
      serviceWorkerRegistration: await navigator.serviceWorker.register('/awpp1/firebase-messaging-sw.js', { 
        type: 'module',
        scope: '/awpp1/' 
      })
    });
    if (token) {
      console.log("Token generado:", token);
      return token;
    } else {
      console.warn("No se pudo generar el token");
      return null;
    }
  } catch (err) {
    console.error("Error al obtener token:", err);
    return null;
  }
}
onMessage(messaging, (payload) => {
  console.log("Notificacion recibida:", payload);
});

