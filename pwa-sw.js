function url(file) {
    return `/awpp1/${(file ? file : "")}`
}

function syncNotifications(reg) {}
function periodicSyncNotifications(reg) {}
function sendOneNotification(reg, title, body) {
    if (Notification.permission !== "granted") {
        console.log("info")
        console.info("Sin permisos para enviar notificaciones.")
        return false
    }

    reg.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: "GENERA_TU_APPLICATION_SERVER_KEY"
    })
    .then(function (pushSubscription) {
        console.log("info")
        console.info("Yey!", pushSubscription)
        var data = new FormData();
        data.append("sub", JSON.stringify(pushSubscription))
        data.append("title", title)
        data.append("body", body)

        fetch(url("web-push-push-server.php"), {
            method: "POST",
            body: data
        })
        .then(function (res) {
            res.text()
        })
        .then(function (txt) {
            console.log("log")
            console.log(txt)
        })
        .catch(function (err) {
            console.log("error")
            console.error("Boo!", err)
        })
    })
    .catch(function (err) {
        console.log("error")
        console.error("Boo!", err)
    })
}

var PRECACHENAME          = "practica1awp-precache-v1"
var DATACACHENAME         = "practica1awp-data-v1"
var SYNCEVENTNAME         = "practica1awp-sync-notifications"
var PERIODICSYNCEVENTNAME = "practica1awp-periodic-sync-notifications"
var OFFLINEURL            = url("offline.html")

self.addEventListener("install", function (event) {
    console.log("info")
    console.info("Instalando...")

    event.waitUntil(
        caches.open(PRECACHENAME).then(function (cache) {
            console.log("info")
            console.info("Instalación Completa")

            return cache.addAll([
                "https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css",
                "https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css.map",
                "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.4/font/bootstrap-icons.css",
                "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.4/font/fonts/bootstrap-icons.woff2?1fa40e8900654d2863d011707b9fb6f2",
                "https://code.jquery.com/jquery-3.7.1.min.js",
                "https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js",
                "https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js.map",

                url(),
                url("index.html"),
                url("inicio.html"),
                url("modulos/admin.html"),
                url("modulos/examenes.html"),
                url("modulos/crearExamen.html"),
                url("modulos/editarExamen.html"),
                //url("api/examenes.php"),
                url("modulos/notificaciones.html"),
                //url("api/notificaciones.php"),
                url("modulos/preguntas.html"),
                url("modulos/CrearPregunta.html"),
                //url("api/preguntas.php"), 
                url("modulos/respuestas.html"),
                url("api/respuestas.php"),
                url("api/examenes_respuestas.php"),
                url("modulos/llenados.html"),
                //url("api/llenados.php"),
                url("manifest.json"),
                url("?source=pwa"), 
                url("favicon.ico"),
                url("favicon.png"),
                url("favicon-512x512.png"),
                url("favicon-maskable.png"),
                url("find_user.png"),

                OFFLINEURL,
                url("pwa-constants.js"),
                url("pwa-installer.js"),
                url("pwa-sw.js"),
                url("pwa-network.js"),
                url("assets/css/main.css"),
                url("assets/js/jquery.min.js"),
                url("assets/js/browser.min.js"),
                url("assets/js/breakpoints.min.js"),
                url("assets/js/util.js"),
                url("assets/js/main.js"),
                "https://fonts.googleapis.com/css?family=Open+Sans:400,600,400italic,600italic|Roboto+Slab:400,700"
            ])
        })
    )
})

self.addEventListener("activate", function (event) {
    console.log("info")
    console.info("Activando Service Worker...")
    
    event.waitUntil(
        caches.keys().then(function(cacheNames) {
            return Promise.all(
                cacheNames.map(function(cacheName) {
                    if (cacheName !== PRECACHENAME && cacheName !== DATACACHENAME) {
                        console.log("info")
                        console.info("Eliminando caché antiguo:", cacheName)
                        return caches.delete(cacheName)
                    }
                })
            )
        })
    )
})

self.addEventListener("fetch", function (event) {
    const requestUrl = new URL(event.request.url);
    const isAPI = requestUrl.pathname.includes("/api/");
    const isExamenEdit = requestUrl.pathname.includes("examenes_update.php");
    const isExamenCreate = requestUrl.pathname.includes("examenes_create.php");
    const isExamenDelete = requestUrl.pathname.includes("examenes_delete.php");

    if (event.request.method === "POST" && (isExamenEdit || isExamenCreate || isExamenDelete)) {
        console.log("Peticion POST permitida sin interceptar:", requestUrl.pathname);
        return;
    }

    if (isAPI || requestUrl.pathname.endsWith(".php")) {
        event.respondWith(
            fetch(event.request)
                .then(response => {
                    if (response.status >= 200 && response.status < 300) {
                        const clone = response.clone();
                        caches.open(DATACACHENAME).then(cache => cache.put(event.request, clone));
                    }
                    return response;
                })
                .catch(() => {
                    console.warn("⚠️ Sin conexión, buscando en caché:", event.request.url);
                    return caches.match(event.request).then(cachedResponse => {
                        if (cachedResponse) return cachedResponse;
                        return new Response(JSON.stringify({ success: false, data: [] }), {
                            status: 503,
                            statusText: "Service Unavailable",
                            headers: { "Content-Type": "application/json" }
                        });
                    });
                })
        );
    }

    else {
        event.respondWith(
            caches.match(event.request)
                .then(cachedResponse => cachedResponse || fetch(event.request)
                    .then(response => {
                        if (response.status >= 200 && response.status < 300) {
                            const clone = response.clone();
                            caches.open(DATACACHENAME).then(cache => cache.put(event.request, clone));
                        }
                        return response;
                    })
                )
                .catch(() => {
                    if (event.request.mode === "navigate") {
                        console.warn("Sin conexión, mostrando página offline");
                        return caches.match(OFFLINEURL);
                    }
                    return new Response("Recurso no disponible", {
                        status: 503,
                        statusText: "Service Unavailable",
                        headers: { "Content-Type": "text/plain" }
                    });
                })
        );
    }
});



self.addEventListener("sync", function (event) {
    console.log("info")
    console.info("sync event", event)

    if (event.tag === SYNCEVENTNAME) {
        event.waitUntil(syncNotifications(registration))
    }
})

self.addEventListener("periodicsync", function (event) {
    console.log("info")
    console.info("periodic sync event", event)

    if (event.tag === PERIODICSYNCEVENTNAME) {
        event.waitUntil(periodicSyncNotifications(registration))
    }
})



const SYNC_DELETE_EXAMENES_TAG = "sync-examenes-delete";
const API_DELETE_EXAMENES_URL = "/awpp1/api/examenes_delete.php";

let eliminacionesPendientesExamenes = [];

self.addEventListener('message', event => {
	if (event.data.action === 'syncEliminarExamenes') {
		eliminacionesPendientesExamenes = event.data.pendientes || [];
	}
});

async function syncExamenesAEliminar() {
	if (eliminacionesPendientesExamenes.length === 0) {
		console.log("No hay examenes por eliminar");
		return;
	}

	for (const examen of eliminacionesPendientesExamenes) {
		try {
			const res = await fetch(API_DELETE_EXAMENES_URL, {
				method: "POST",
				headers: { "Content-Type": "application/json" },
				body: JSON.stringify({ idExamen: examen.idExamen })
			});
			const data = await res.json();
			if (data.success) {
				console.log("Examen eliminado en servidor:", examen.idExamen);
			} else {
				console.warn("Fallo al eliminar examen:", examen);
			}
		} catch(err) {
			console.error("Error al sincronizar eliminacion de examen:", err);
			return;
		}
	}

	eliminacionesPendientesExamenes = [];

	const allClients = await clients.matchAll({ includeUncontrolled: true });
	allClients.forEach(client => {
		client.postMessage({ action: 'limpiarEliminacionesPendientes' });
	});

	console.log("Eliminaciones de exámenes sincronizadas correctamente.");
}

self.addEventListener("sync", event => {
	if (event.tag === SYNC_DELETE_EXAMENES_TAG) {
		console.log("Background Sync ejecutando:", event.tag);
		event.waitUntil(syncExamenesAEliminar());
	}
});



const SYNC_EXAMENES_TAG = "sync-examenes-pendientes";
const API_CREATE_URL = "/awpp1/api/examenes_create.php";

async function syncExamenesPendientes() {
    const stored = await self.registration.storage.get("examenesPendientes") || [];
    if (stored.length === 0) return;

    for (const examen of stored) {
        try {
            const res = await fetch(API_CREATE_URL, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(examen)
            });
            const data = await res.json();
            if (data.success) {
                console.log("Examen sincronizado:", examen.tituloExamen);
            }
        } catch (err) {
            console.error("Error al sincronizar:", err);
            return;
        }
    }

    await self.registration.storage.delete("examenesPendientes");

    self.clients.matchAll().then(clients => {
        clients.forEach(client => {
            client.postMessage({ action: 'examenesSincronizados', examenes: stored });
        });
    });
}

self.addEventListener('sync', event => {
    if (event.tag === SYNC_EXAMENES_TAG) {
        event.waitUntil(syncExamenesPendientes());
    }
});

self.addEventListener('message', async event => {
    if (event.data.action === 'getExamenesPendientes') {
        const pendientes = await self.registration.storage.get("examenesPendientes") || [];
        event.ports[0].postMessage({ examenesPendientes: pendientes });
    }
});


const SYNC_DELETE_LLENADOS_TAG = "sync-llenados-delete";
const API_DELETE_LLENADOS_URL = "/awpp1/api/llenados_delete.php";

let eliminacionesPendientesLlenados = [];

self.addEventListener('message', event => {
    if(event.data.action === 'syncEliminarLlenados') {
        eliminacionesPendientesLlenados = event.data.pendientes || [];
    }
});

async function syncLlenadosAEliminar() {
    if (eliminacionesPendientesLlenados.length === 0) {
        console.log("No hay llenados por eliminar");
        return;
    }

    for (const llenado of eliminacionesPendientesLlenados) {
        try {
            const res = await fetch(API_DELETE_LLENADOS_URL, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ idLlenado: llenado.idLlenado })
            });
            const data = await res.json();
            if (data.success) {
                console.log("llenado eliminado en servidor:", llenado.idLlenado);
            } else {
                console.warn("fallo en eliminacion:", llenado);
            }
        } catch(err) {
            console.error("error al sincronizar eliminacion de llenado:", err);
            return;
        }
    }

    eliminacionesPendientesLlenados = [];

    const allClients = await clients.matchAll({ includeUncontrolled: true });
    allClients.forEach(client => {
        client.postMessage({ action: 'limpiarEliminacionesPendientesLlenados' });
    });

    console.log("Eliminaciones de llenados sincronizadas correctamente.");
}

self.addEventListener("sync", event => {
    if (event.tag === SYNC_DELETE_LLENADOS_TAG) {
        console.log("Background Sync ejecutando:", event.tag);
        event.waitUntil(syncLlenadosAEliminar());
    }
});


const SYNC_UPDATE_LLENADOS_TAG = "sync-llenados-update";
const API_UPDATE_LLENADOS_URL = "/awpp1/api/llenados_update.php";

async function syncLlenadosEditados() {
    console.log("Sincronizando llenados editados...");

    const data = await self.registration.storage?.get("llenadosEditados");
    const llenados = Array.isArray(data) ? data : [];

    if (llenados.length === 0) {
        console.log("No hay llenados editados pendientes");
        return;
    }

    for (const llenado of llenados) {
        try {
            const res = await fetch(API_UPDATE_LLENADOS_URL, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(llenado)
            });
            const json = await res.json();

            if (json.success) {
                console.log("Llenado editado en servidor:", llenado.idLlenado);
            } else {
                console.warn("Error en edicion:", json.error || json);
            }
        } catch (err) {
            console.error("Error al sincronizar llenado editado:", err);
            return;
        }
    }

    await self.registration.storage?.delete("llenadosEditados");
    console.log("🧹 Ediciones de llenados sincronizadas correctamente.");
}

self.addEventListener("sync", event => {
    if (event.tag === SYNC_UPDATE_LLENADOS_TAG) {
        console.log("📡 Sincronizando ediciones de llenados pendientes...");
        event.waitUntil(syncLlenadosEditados());
    }
});


const PENDING_PREGUNTAS_KEY = "preguntasPendientes";
const SYNC_PREGUNTAS_TAG = "sync-preguntas-pendientes";
const API_CREATE_URL_PREGUNTAS = "/awpp1/api/preguntas_create.php";


async function syncPreguntasPendientes() {
    const stored = await self.registration.storage.get(PENDING_PREGUNTAS_KEY) || []; 
    if (stored.length === 0) {
        console.log("No hay preguntas pendientes en SW storage.");
        return;
    }

    console.log(`Intentando sincronizar ${stored.length} preguntas pendientes desde SW...`);
    let sincronizados = [];
    let errores = [];

    for (const pregunta of stored) {
        try {
            const res = await fetch(API_CREATE_URL_PREGUNTAS, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(pregunta)
            });
            const data = await res.json();
            if (data.success) {
                console.log("✓ Pregunta sincronizada desde SW:", pregunta.pregunta);
                sincronizados.push(pregunta);
            } else {
                console.error("✗ Error al sincronizar pregunta desde SW:", data.error, pregunta);
                errores.push(pregunta);
            }
        } catch (err) {
            console.error("✗ Error de red al sincronizar pregunta desde SW:", err, pregunta);
            errores.push(pregunta);
        }
    }

    const pendientesRestantes = stored.filter(p => !sincronizados.some(s => JSON.stringify(s) === JSON.stringify(p)));

    if (pendientesRestantes.length === 0) {
        await self.registration.storage.delete(PENDING_PREGUNTAS_KEY);
        console.log("Todas las preguntas pendientes eliminadas de SW storage.");
    } else {
        await self.registration.storage.set(PENDING_PREGUNTAS_KEY, pendientesRestantes);
        console.log(`${pendientesRestantes.length} preguntas pendientes restantes en SW storage.`);
    }

    if (sincronizados.length > 0) {
        self.clients.matchAll().then(clients => {
            clients.forEach(client => {
                const idExamenSincronizado = sincronizados[0].idExamen; 
                client.postMessage({ action: 'preguntasSincronizadas', idExamenSincronizado: idExamenSincronizado });
            });
        });
    }
}


self.addEventListener('sync', event => {
    if (event.tag === SYNC_EXAMENES_TAG) {
        event.waitUntil(syncExamenesPendientes());
    }
    if (event.tag === SYNC_PREGUNTAS_TAG) {
        event.waitUntil(syncPreguntasPendientes());
    }
});

self.addEventListener('message', async event => {
    if (event.data.action === 'getPreguntasPendientes') {
        const pendientes = await self.registration.storage.get(PENDING_PREGUNTAS_KEY) || [];
        event.ports[0].postMessage({ preguntasPendientes: pendientes });
    }
});


self.addEventListener('message', async event => {
    if (!event.data) return;

    if (event.data.action === 'guardarPreguntaOffline') {
        const pregunta = event.data.pregunta;
        const key = event.data.key || PENDING_PREGUNTAS_KEY;

        try {
            const actuales = (await self.registration.storage.get(key)) || [];
            actuales.push(pregunta);
            await self.registration.storage.set(key, actuales);
            console.log(`💾 Pregunta guardada offline en SW (${key}):`, pregunta);
        } catch (err) {
            console.error("Error guardando pregunta offline en SW:", err);
        }
    }

    if (event.data.action === 'getPreguntasPendientes') {
        const pendientes = await self.registration.storage.get(PENDING_PREGUNTAS_KEY) || [];
        event.ports[0].postMessage({ preguntasPendientes: pendientes });
    }
});




const SYNC_RESPUESTAS_TAG = "sync-respuestas-pendientes";
const API_CREATE_RESP_URL = "/awpp1/api/respuestas_create.php";

async function syncRespuestasPendientes() {
    const stored = JSON.parse(localStorage.getItem("respuestasPendientes") || "[]");
    if (stored.length === 0) return;

    for (const r of stored) {
        try {
            const res = await fetch(API_CREATE_RESP_URL, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(r)
            });
            const data = await res.json();
            if (data.success) {
                console.log("Respuesta sincronizada:", r);
            }
        } catch(err) {
            console.error("Error al sincronizar respuesta:", err);
            return;
        }
    }

    localStorage.removeItem("respuestasPendientes");

    self.clients.matchAll().then(clients => {
        clients.forEach(client => {
            client.postMessage({ action: 'respuestasSincronizadas' });
        });
    });
}

self.addEventListener('sync', event => {
    if (event.tag === SYNC_RESPUESTAS_TAG) {
        event.waitUntil(syncRespuestasPendientes());
    }
});



const SYNC_DELETE_RESPUESTAS_TAG = "sync-respuestas-delete";
const API_DELETE_RESPUESTAS_URL = "/awpp1/api/respuestas_delete.php";

let eliminacionesPendientesRespuestas = [];

self.addEventListener('sync', event => {
    if(event.tag === SYNC_DELETE_RESPUESTAS_TAG) {
        event.waitUntil(syncRespuestasAEliminar());
    }
});

async function syncRespuestasAEliminar() {
    const stored = JSON.parse(await self.registration.storage.get(PENDING_DELETE_KEY) || "[]");
    if(stored.length === 0) return;

    for(const item of stored){
        try{
            const res = await fetch(API_DELETE_RESPUESTAS_URL, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ idRespuesta: item.idRespuesta })
            });
            const data = await res.json();
            if(data.success) {
                console.log("Respuesta eliminada en servidor:", item.idRespuesta);
            } else {
                console.warn("Fallo en eliminación:", item.idRespuesta);
            }
        } catch(err){
            console.error("Error al sincronizar eliminación de respuesta:", err);
            return;
        }
    }

    await self.registration.storage.delete(PENDING_DELETE_KEY);

    const clientsList = await clients.matchAll({ includeUncontrolled: true });
    clientsList.forEach(client => client.postMessage({ action: 'respuestasEliminadas', eliminadas: stored }));
}

self.addEventListener('message', async event => {
    if (event.data.action === 'guardarPreguntaOffline') {
        const pregunta = event.data.pregunta;
        let pendientes = await self.registration.storage.get(PENDING_PREGUNTAS_KEY) || [];
        pendientes.push(pregunta);
        await self.registration.storage.set(PENDING_PREGUNTAS_KEY, pendientes);
        console.log('Pregunta guardada en SW storage por mensaje:', pregunta);
    }
});




self.addEventListener("push", function (event) {
    console.log("info")
    console.info(event.data)

    var data = event.data.json()

    self.registration.showNotification(data.title, {
        body: data.body,
        icon: data.icon,
        image: data.image
    })
    
})