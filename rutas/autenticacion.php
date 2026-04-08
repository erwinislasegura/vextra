<?php

use Aplicacion\Controladores\Autenticacion\AutenticacionControlador;

$enrutador->agregar('GET', '/iniciar-sesion', [AutenticacionControlador::class, 'mostrarLogin']);
$enrutador->agregar('POST', '/iniciar-sesion', [AutenticacionControlador::class, 'iniciarSesion']);
$enrutador->agregar('POST', '/cerrar-sesion', [AutenticacionControlador::class, 'cerrarSesion']);
$enrutador->agregar('GET', '/registro', [AutenticacionControlador::class, 'mostrarRegistro']);
$enrutador->agregar('POST', '/registro', [AutenticacionControlador::class, 'registrarEmpresa']);
$enrutador->agregar('GET', '/recuperar-contrasena', [AutenticacionControlador::class, 'recuperarContrasena']);
$enrutador->agregar('GET', '/restablecer-contrasena', [AutenticacionControlador::class, 'restablecerContrasena']);
