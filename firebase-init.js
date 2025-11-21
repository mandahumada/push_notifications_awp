import { initializeApp } from "https://www.gstatic.com/firebasejs/11.0.1/firebase-app.js";
import { getMessaging, getToken, onMessage } from "https://www.gstatic.com/firebasejs/11.0.1/firebase-messaging.js";

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

export async function solicitarPermisoYGenerarToken() {
  try {
    console.log("Solicitando permiso de notificaciones...");
    const token = await getToken(messaging, {
      vapidKey: "BMFp-c-UVRyKB6bJqetR3cCt6L9tF9aJOm1st-Bo-p_acn5t5L_B79ok95UUMQxrnSBg7nWdMMEFR9dX-sqwTC0",
      serviceWorkerRegistration: await navigator.serviceWorker.register('/awpp1/firebase-messaging-sw.js', { 
        type: 'module',
        scope: '/awpp1/' 
      })
    });
    if (token) {
      console.log("Token generado:", token);
      return token;
    } else {
      console.warn("⚠️ No se pudo generar el token");
      return null;
    }
  } catch (err) {
    console.error("❌ Error al obtener token:", err);
    return null;
  }
}
onMessage(messaging, (payload) => {
  console.log("📩 Notificación recibida:", payload);
});
