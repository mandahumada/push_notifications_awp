function regAll(fun) {
    regWorker(fun)
    regSyncNotifications()
    regPeriodicSyncNotifications()
}
async function regWorker(fun) {
    navigator.serviceWorker
        .register("../pwa-sw.js", { scope: "../" })
        .then(function (reg) {
            if (typeof fun === "function") {
                fun();
            }

            if (!("sync" in reg)) {
                syncNotifications(reg);
                console.error("Error registering background sync");
            }

            console.info("✅ Service Worker registrado correctamente con scope:", reg.scope);
        })
        .catch(function (err) {
            console.error("❌ Error al registrar el Service Worker:", err);
        });
}

async function regSyncNotifications() {
    navigator.serviceWorker.ready.then(function (reg) {
        if ("sync" in reg) {
            reg.sync.register(SYNCEVENTNAME)
            .then(function () {
                console.log("info")
                console.info("Registered background sync")
            })
            .catch(function (err) {
                syncNotifications(reg)
                console.log("error")
                console.error("Error registering background sync", err)
            })
        }
    })
}
async function regPeriodicSyncNotifications() {
    navigator.serviceWorker.ready.then(function (reg) {
        if ("periodicSync" in reg) {
            navigator.permissions.query({
                name: "periodic-background-sync"
            }).then(function (status) {
                console.log("info")

                if (status.state === "granted") {
                    console.info("Periodic background sync can be used.")

                    reg.periodicSync.register(PERIODICSYNCEVENTNAME, {
                        minInterval: 24 * 60 * 60 * 1000
                    })
                    .then(function () {
                        console.log("info")
                        console.info("Registered periodic background sync")
                    })
                    .catch(function (err) {
                        console.log("error")
                        console.error("Periodic background sync cannot be used.", err)
                    })
                }
                else {
                    console.info("Periodic background sync cannot be used.")
                }
            })
        }
    })
}
function toggleButtonsSuscripcion(permission) {
    btnSuscribir.style.display   = "block"
    btnDesuscribir.style.display = "none"

    if (permission === "granted") {
        btnSuscribir.style.display   = "none"
        btnDesuscribir.style.display = "block"
    }
}

window.addEventListener("load", function (event) {
    if (!("serviceWorker" in navigator)
     || !("PushManager"   in window)) {
        console.log("info")
        console.info("Sin soporte.")
        return
    }

    regAll()

    var btnSuscribir = document.getElementById("btnSuscribir")
    if (btnSuscribir) {
        btnSuscribir.addEventListener("click", function (event) {
            if (Notification.permission === "default") {
                Notification.requestPermission().then(function (perm) {
                    if (Notification.permission === "granted") {
                        regWorker(function () {
                            toggleButtonsSuscripcion("granted")
                        }).catch(function (err) {
                            console.log("error")
                            console.error(err)
                        })
                    }
                })
            }
            else if (Notification.permission === "granted") {
                regWorker().catch(function (err) {
                    console.log("error")
                    console.error(err)
                })
            }
        })
    }

    var btnDesuscribir = document.getElementById("btnDesuscribir")
    if (btnDesuscribir) {
        btnDesuscribir.style.display = "none"
    }

    if (btnSuscribir && btnDesuscribir) {
        toggleButtonsSuscripcion(Notification.permission)
    }

    validateOnline()
})
