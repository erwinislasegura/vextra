<?php

namespace Aplicacion\Middlewares;

use Aplicacion\Modelos\Empresa;
use Aplicacion\Modelos\Suscripcion;

class EmpresaMiddleware
{
    public function manejar(): void
    {
        $usuario = usuario_actual();
        $esSuperAdmin = ($usuario['rol_codigo'] ?? '') === 'superadministrador';
        $contextoEmpresa = (int) ($_SESSION['admin_empresa_contexto_id'] ?? 0);
        $accesoComoContextoEmpresa = $esSuperAdmin && $contextoEmpresa > 0;

        if (!$accesoComoContextoEmpresa && !tiene_rol([
            'administrador_empresa',
            'vendedor',
            'administrativo',
            'contabilidad',
            'supervisor_comercial',
            'operaciones',
            'usuario_empresa',
        ])) {
            http_response_code(403);
            exit('Acceso restringido para usuarios de empresa.');
        }

        $rutaActual = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $base = base_path_url();
        if ($base !== '' && str_starts_with($rutaActual, $base . '/')) {
            $rutaActual = substr($rutaActual, strlen($base));
        }

        $empresaId = empresa_actual_id();
        if ($empresaId > 0) {
            $empresa = (new Empresa())->buscar($empresaId);
            $this->sincronizarVencimientoSuscripcion($empresaId, $empresa);
            $empresa = (new Empresa())->buscar($empresaId);
            $estadoCuenta = (string) ($empresa['estado'] ?? '');
            $estadosBloqueados = ['suspendida', 'vencida', 'cancelada'];
            if (in_array($estadoCuenta, $estadosBloqueados, true)) {
                $_SESSION['bloqueo_cuenta_estado'] = $estadoCuenta;
                $rutasPermitidas = ['/app/panel'];
                if ($estadoCuenta === 'vencida') {
                    $rutasPermitidas[] = '/app/panel/iniciar-pago-trial';
                    $rutasPermitidas[] = '/app/panel/iniciar-pago-cambio-plan';
                }
                if (!in_array($rutaActual, $rutasPermitidas, true)) {
                    $destino = rtrim(base_path_url(), '/') . '/app/panel';
                    header('Location: ' . $destino);
                    exit;
                }
            } else {
                unset($_SESSION['bloqueo_cuenta_estado']);
            }
        }

        $mapaModuloPorRuta = [
            '/app/clientes' => 'modulo_clientes',
            '/app/productos' => 'modulo_productos',
            '/app/cotizaciones' => 'modulo_cotizaciones',
            '/app/punto-venta' => 'modulo_pos',
            '/app/inventario/recepciones' => 'modulo_recepciones',
            '/app/inventario/ajustes' => 'modulo_ajustes',
            '/app/inventario/movimientos' => 'modulo_movimientos',
            '/app/inventario/proveedores' => 'modulo_inventario',
            '/app/inventario/ordenes-compra' => 'modulo_ordenes_compra',
            '/app/contactos' => 'modulo_contactos',
            '/app/vendedores' => 'modulo_vendedores',
            '/app/categorias' => 'modulo_categorias',
            '/app/listas-precios' => 'modulo_listas_precios',
            '/app/catalogo-en-linea' => 'modulo_catalogo_en_linea',
            '/app/compras-catalogo' => 'modulo_compras_catalogo',
            '/app/seguimiento' => 'modulo_seguimiento',
            '/app/aprobaciones' => 'modulo_aprobaciones',
            '/app/documentos' => 'modulo_documentos',
            '/app/configuracion/envio-oc-html' => 'modulo_documentos',
            '/app/configuracion/correos-stock' => 'modulo_correos_stock',
            '/app/configuracion' => 'modulo_configuracion',
            '/app/pagos/checkout-flow' => 'modulo_checkout_flow',
            '/app/usuarios' => 'modulo_usuarios',
            '/app/notificaciones' => 'modulo_notificaciones',
            '/app/historial' => 'modulo_historial',
        ];

        foreach ($mapaModuloPorRuta as $prefijo => $modulo) {
            if (($rutaActual === $prefijo || str_starts_with($rutaActual, $prefijo . '/'))
                && !plan_tiene_funcionalidad_empresa_actual($modulo)) {
                http_response_code(403);
                exit('Tu plan actual no incluye este módulo.');
            }
        }

        $mapaFuncionalidadPorRuta = [
            '/app/cotizaciones/imprimir' => 'cotizacion_pdf',
            '/app/cotizaciones/pdf' => 'cotizacion_pdf',
            '/app/cotizaciones/enviar' => 'cotizacion_correo',
            '/app/clientes/exportar/excel' => 'clientes_exportar_excel',
        ];

        foreach ($mapaFuncionalidadPorRuta as $prefijo => $funcionalidad) {
            if (($rutaActual === $prefijo || str_starts_with($rutaActual, $prefijo . '/'))
                && !plan_tiene_funcionalidad_empresa_actual($funcionalidad)) {
                http_response_code(403);
                exit('Tu plan actual no incluye esta funcionalidad.');
            }
        }
    }

    private function sincronizarVencimientoSuscripcion(int $empresaId, ?array $empresa): void
    {
        if ($empresaId <= 0 || !$empresa) {
            return;
        }

        $estadoEmpresa = (string) ($empresa['estado'] ?? '');
        if (in_array($estadoEmpresa, ['cancelada', 'suspendida'], true)) {
            return;
        }

        $suscripcion = (new Suscripcion())->obtenerUltimaPorEmpresa($empresaId);
        if (!$suscripcion) {
            return;
        }

        $fechaVencimiento = (string) ($suscripcion['fecha_vencimiento'] ?? '');
        if ($fechaVencimiento === '') {
            return;
        }

        $vencio = strtotime($fechaVencimiento) < strtotime(date('Y-m-d'));
        if (!$vencio) {
            return;
        }

        $estadoSuscripcion = (string) ($suscripcion['estado'] ?? '');
        if (in_array($estadoSuscripcion, ['vencida', 'cancelada', 'suspendida'], true) && $estadoEmpresa === 'vencida') {
            return;
        }

        (new Suscripcion())->actualizar((int) $suscripcion['id'], [
            'empresa_id' => $empresaId,
            'plan_id' => (int) ($suscripcion['plan_id'] ?? $empresa['plan_id'] ?? 0),
            'estado' => 'vencida',
            'fecha_inicio' => (string) ($suscripcion['fecha_inicio'] ?? date('Y-m-d')),
            'fecha_vencimiento' => $fechaVencimiento,
            'observaciones' => 'Suscripción vencida automáticamente por fin de período de prueba.',
        ]);
    }

}
