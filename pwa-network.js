function reload(redir) {
    window.location = url((redir ? redir : ""))
}
async function unregWorker(redir, /** fun */ ) {
    navigator.serviceWorker.ready
    .then(function (reg) {
        reg.unregister()
        if ("periodicSync" in reg) {
            reg.periodicSync.unregister(PERIODICSYNCEVENTNAME)
        }

        caches.delete(PRECACHENAME)
        .then(function () {
            if (typeof fun == "function") {
                // No soportado en celular el cacheado sin recargar.
                // regAll(fun)
                // return
            }

            reload(redir)
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
function reinstall(redir) {
    setTabApp(defaultTabApp())

    if (redir) {
        setNextTabApp(defaultTabApp())
    }

    unregWorker(redir, /** function () {
        // cacheado sin recargar.
        frmProducto.reset()
        cargarProductos("json")
        cargarProductos("html.tr")
    } */ )
}
function validateOnline() {
    var elements = document.querySelectorAll(".online-required")
    elements.forEach(function (el, index) {
        el.setAttribute("disabled", "true")
        el.classList.add("disabled")

        if (navigator.onLine) {
            el.removeAttribute("disabled")
            el.classList.remove("disabled")
        }
    })
}

window.addEventListener("online", function (event) {
    console.log("Se reestablecio la conexion")
    validateOnline()
})
window.addEventListener("offline", function (event) {
    console.log("Se perdio la conexion")
    validateOnline()
})
