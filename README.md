# awp_pushnotifications

## Descripcion
aplicacion web para la gestion de examenes con evio de notificaciones push, implementa service worker para su funionamiento tanto offline como online, almacenamiento en localStorae y sincronziacion automatica.

## Configuracion y activacion de push
1- crear proyecto en firebase console, app web. El proyecto debera tener Firebase Cloud Messaging.
2- copiar configuracion del SDK para incorporarla en el proyecto.
3- Generar nueva clave privada en cuentas de servicio, guardar en carpeta bd

Solicitud de permiso y generar token, aquí se registra el service worker se genera el token de dispositivo y se incluye vapidKey, la cual se ubica en el proyecto firebase en la sección de Cloud Messaging.

Configuracion de notificaciones en primer y segundo plano. Cuando se carga la página, es ejecutada la función correspondiente a permisos y generación de token, onMessage captura notificaciones mientras que OnBackgroundMessage permite que sean recibidas.

Configuracion del Service Worker

## Vinculacion de push notificaction
Al permitir recibir notificaciones se genera un FCM Token único el cual es el enviado al backend junto con el usuario. Este token es utilizado para recibir notificaciones.

Elementos clave en la implementación
1- Permiso del usuario solicitado por el navegador.
2- Generación del token: Firebase genera un FCM Token único para el dispositivo.
3- Envío al servidor: fetch a actualizar/token.php
4- Almacenamiento en bd: actualización de tabla usuarios.
5- Uso de token: según las acciones realizadas se lee el token para el envío de notificaciones.
6- Recepción: el sw recibe la notificación y la muestra.
