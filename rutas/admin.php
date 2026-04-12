<?php

use Aplicacion\Middlewares\AutenticadoMiddleware;
use Aplicacion\Middlewares\SuperAdminMiddleware;
use Aplicacion\Controladores\Admin\PanelAdminControlador;
use Aplicacion\Controladores\Admin\EmpresasControlador;
use Aplicacion\Controladores\Admin\AdministradoresEmpresasControlador;
use Aplicacion\Controladores\Admin\PlanesControlador;
use Aplicacion\Controladores\Admin\FuncionalidadesControlador;
use Aplicacion\Controladores\Admin\PlanFuncionalidadesControlador;
use Aplicacion\Controladores\Admin\SuscripcionesControlador;
use Aplicacion\Controladores\Admin\PagosControlador;
use Aplicacion\Controladores\Admin\ReportesControlador;
use Aplicacion\Controladores\Admin\ConfiguracionGeneralControlador;
use Aplicacion\Controladores\Admin\HistorialAdminControlador;
use Aplicacion\Controladores\Admin\SoporteChatsAdminControlador;

use Aplicacion\Controladores\Admin\FlowAdminControlador;

$mw = [AutenticadoMiddleware::class, SuperAdminMiddleware::class];

$enrutador->agregar('GET', '/admin/panel', [PanelAdminControlador::class, 'panel'], $mw);
$enrutador->agregar('GET', '/admin/empresas', [EmpresasControlador::class, 'index'], $mw);
$enrutador->agregar('GET', '/admin/empresas/ver/{id}', [EmpresasControlador::class, 'ver'], $mw);
$enrutador->agregar('POST', '/admin/empresas/estado/{id}', [EmpresasControlador::class, 'actualizarEstado'], $mw);
$enrutador->agregar('POST', '/admin/empresas/plan/{id}', [EmpresasControlador::class, 'cambiarPlan'], $mw);
$enrutador->agregar('POST', '/admin/empresas/extender-vigencia/{id}', [EmpresasControlador::class, 'extenderSuscripcion'], $mw);
$enrutador->agregar('POST', '/admin/empresas/eliminar/{id}', [EmpresasControlador::class, 'eliminar'], $mw);

$enrutador->agregar('GET', '/admin/administradores-empresa', [AdministradoresEmpresasControlador::class, 'index'], $mw);
$enrutador->agregar('POST', '/admin/administradores-empresa/actualizar/{id}', [AdministradoresEmpresasControlador::class, 'actualizar'], $mw);
$enrutador->agregar('POST', '/admin/administradores-empresa/estado/{id}', [AdministradoresEmpresasControlador::class, 'cambiarEstado'], $mw);
$enrutador->agregar('POST', '/admin/administradores-empresa/reset-password/{id}', [AdministradoresEmpresasControlador::class, 'resetearPassword'], $mw);
$enrutador->agregar('POST', '/admin/administradores-empresa/acceder/{id}', [AdministradoresEmpresasControlador::class, 'accederPanelEmpresa'], $mw);

$enrutador->agregar('GET', '/admin/planes', [PlanesControlador::class, 'index'], $mw);
$enrutador->agregar('GET', '/admin/planes/crear', [PlanesControlador::class, 'crear'], $mw);
$enrutador->agregar('POST', '/admin/planes/crear', [PlanesControlador::class, 'guardar'], $mw);
$enrutador->agregar('GET', '/admin/planes/editar/{id}', [PlanesControlador::class, 'editar'], $mw);
$enrutador->agregar('POST', '/admin/planes/editar/{id}', [PlanesControlador::class, 'actualizar'], $mw);
$enrutador->agregar('POST', '/admin/planes/estado/{id}', [PlanesControlador::class, 'cambiarEstado'], $mw);
$enrutador->agregar('POST', '/admin/planes/eliminar/{id}', [PlanesControlador::class, 'eliminar'], $mw);

$enrutador->agregar('GET', '/admin/funcionalidades', [FuncionalidadesControlador::class, 'index'], $mw);
$enrutador->agregar('GET', '/admin/funcionalidades/crear', [FuncionalidadesControlador::class, 'crear'], $mw);
$enrutador->agregar('POST', '/admin/funcionalidades/crear', [FuncionalidadesControlador::class, 'guardar'], $mw);
$enrutador->agregar('GET', '/admin/funcionalidades/editar/{id}', [FuncionalidadesControlador::class, 'editar'], $mw);
$enrutador->agregar('POST', '/admin/funcionalidades/editar/{id}', [FuncionalidadesControlador::class, 'actualizar'], $mw);

$enrutador->agregar('GET', '/admin/plan-funcionalidades/{plan_id}', [PlanFuncionalidadesControlador::class, 'index'], $mw);
$enrutador->agregar('POST', '/admin/plan-funcionalidades/{plan_id}', [PlanFuncionalidadesControlador::class, 'guardar'], $mw);

$enrutador->agregar('GET', '/admin/suscripciones', [SuscripcionesControlador::class, 'index'], $mw);
$enrutador->agregar('POST', '/admin/suscripciones/ver/{id}', [SuscripcionesControlador::class, 'actualizarEstado'], $mw);
$enrutador->agregar('POST', '/admin/suscripciones/editar/{id}', [SuscripcionesControlador::class, 'actualizar'], $mw);

$enrutador->agregar('GET', '/admin/pagos', [PagosControlador::class, 'index'], $mw);
$enrutador->agregar('GET', '/admin/reportes', [ReportesControlador::class, 'index'], $mw);
$enrutador->agregar('GET', '/admin/configuracion', [ConfiguracionGeneralControlador::class, 'index'], $mw);
$enrutador->agregar('POST', '/admin/configuracion', [ConfiguracionGeneralControlador::class, 'guardar'], $mw);
$enrutador->agregar('GET', '/admin/historial', [HistorialAdminControlador::class, 'index'], $mw);

$enrutador->agregar('GET', '/admin/soporte-chats', [SoporteChatsAdminControlador::class, 'index'], $mw);
$enrutador->agregar('GET', '/admin/soporte-chats/ver/{id}', [SoporteChatsAdminControlador::class, 'ver'], $mw);
$enrutador->agregar('GET', '/admin/soporte-chats/mensajes/{id}', [SoporteChatsAdminControlador::class, 'mensajes'], $mw);
$enrutador->agregar('POST', '/admin/soporte-chats/responder/{id}', [SoporteChatsAdminControlador::class, 'responder'], $mw);
$enrutador->agregar('POST', '/admin/soporte-chats/cerrar/{id}', [SoporteChatsAdminControlador::class, 'cerrar'], $mw);
$enrutador->agregar('POST', '/admin/soporte-chats/eliminar/{id}', [SoporteChatsAdminControlador::class, 'eliminar'], $mw);


$enrutador->agregar('GET', '/admin/flow', [FlowAdminControlador::class, 'dashboard'], $mw);
$enrutador->agregar('GET', '/admin/flow/configuracion', [FlowAdminControlador::class, 'configuracion'], $mw);
$enrutador->agregar('POST', '/admin/flow/configuracion', [FlowAdminControlador::class, 'guardarConfiguracion'], $mw);
$enrutador->agregar('GET', '/admin/flow/planes', [FlowAdminControlador::class, 'planes'], $mw);
$enrutador->agregar('POST', '/admin/flow/planes/sincronizar/{planId}/{modalidad}', [FlowAdminControlador::class, 'crearPlanFlow'], $mw);
$enrutador->agregar('GET', '/admin/flow/clientes', [FlowAdminControlador::class, 'clientes'], $mw);
$enrutador->agregar('POST', '/admin/flow/clientes/crear', [FlowAdminControlador::class, 'crearCliente'], $mw);
$enrutador->agregar('POST', '/admin/flow/clientes/registro/{empresaId}', [FlowAdminControlador::class, 'iniciarRegistroTarjeta'], $mw);
$enrutador->agregar('GET', '/admin/flow/suscripciones', [FlowAdminControlador::class, 'suscripciones'], $mw);
$enrutador->agregar('POST', '/admin/flow/suscripciones/crear', [FlowAdminControlador::class, 'crearSuscripcion'], $mw);
$enrutador->agregar('POST', '/admin/flow/suscripciones/sincronizar/{flowSubscriptionId}', [FlowAdminControlador::class, 'sincronizarSuscripcion'], $mw);
$enrutador->agregar('POST', '/admin/flow/suscripciones/cancelar/{flowSubscriptionId}', [FlowAdminControlador::class, 'cancelarSuscripcion'], $mw);
$enrutador->agregar('GET', '/admin/flow/pagos', [FlowAdminControlador::class, 'pagos'], $mw);
$enrutador->agregar('POST', '/admin/flow/pagos/crear', [FlowAdminControlador::class, 'crearPago'], $mw);
$enrutador->agregar('POST', '/admin/flow/pagos/sincronizar/{token}', [FlowAdminControlador::class, 'sincronizarPago'], $mw);
$enrutador->agregar('GET', '/admin/flow/logs', [FlowAdminControlador::class, 'logs'], $mw);
