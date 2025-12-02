🛒 Descripción del Proyecto – Mermelada Savora (Tienda Online en PHP + MySQL + Docker)

Mermelada Savora es una tienda en línea desarrollada en PHP nativo, utilizando MySQL como motor de base de datos y desplegada mediante Docker, lo que permite un entorno reproducible, portable y fácil de instalar.
El objetivo del proyecto es ofrecer una experiencia sencilla y funcional para que los clientes puedan explorar productos, agregarlos al carrito y realizar compras simuladas mediante un checkout básico.

🚀 Características principales
✔️ Frontend y estructura

Interfaz desarrollada en PHP + HTML + CSS + JavaScript.

Navegación clara y rápida entre productos, carrito, login y checkout.

Diseño adaptable y estructura de carpetas organizada:

/auth        → Login, registro, cierre de sesión
/cart        → Funciones del carrito
/checkout    → Proceso de compra
/products    → Listado y detalles de productos
/public      → Index principal (home)
/config      → Configuración global y base de datos

🗄️ Backend
✔️ Conexión a Base de Datos (PDO)

El sistema utiliza PDO con soporte para excepciones y consultas seguras.

✔️ Funcionalidades implementadas:

Manejo de sesiones para carrito y autenticación.

Consultas seguras a MySQL.

Gestión de productos y stock desde base de datos.

Sistema de autenticación básico:

Login

Registro

Logout

🐳 Infraestructura con Docker

La aplicación corre dentro de un entorno Docker que contiene dos servicios:

🔹 Servicio web (Apache + PHP 8.2)

Contenedor que ejecuta PHP con Apache.

Configuración personalizada para activar mod_rewrite.

Volumen montado para cargar los archivos del proyecto.

🔹 Servicio de base de datos (MySQL 8.0)

Base de datos aislada.

Datos persistentes mediante volúmenes Docker.

🧪 Funcionalidades listas para pruebas

Registrar usuarios

Iniciar sesión

Ver productos

Agregar al carrito

Ver carrito y modificar cantidades

Simular el proceso de compra

🎯 Propósito del proyecto

Este proyecto fue creado principalmente con los siguientes objetivos:

Aprender y practicar PHP nativo de forma estructurada.

Implementar una tienda real funcional paso a paso.

Comprender y dominar Docker para despliegues reproducibles.

Aprender a conectar servicios (PHP ↔ MySQL) usando contenedores.

Preparar una tienda lista para seguir ampliándose con nuevas funciones:

Panel administrativo

CRUD de productos

Pasarela de pago real (ej: PayPal, Stripe)

Sistema de roles y permisos
