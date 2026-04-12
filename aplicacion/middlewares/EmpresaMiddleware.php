<?php

namespace Aplicacion\Middlewares;

use Aplicacion\Modelos\Empresa;

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
            $estadoCuenta = (string) ($empresa['estado'] ?? '');
            $estadosBloqueados = ['suspendida', 'vencida', 'cancelada'];
            if (in_array($estadoCuenta, $estadosBloqueados, true)) {
                $_SESSION['bloqueo_cuenta_estado'] = $estadoCuenta;
                if ($rutaActual !== '/app/panel') {
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
            '/app/seguimiento' => 'modulo_seguimiento',
            '/app/aprobaciones' => 'modulo_aprobaciones',
            '/app/documentos' => 'modulo_documentos',
            '/app/configuracion/envio-oc-html' => 'modulo_documentos',
            '/app/configuracion/correos-stock' => 'modulo_correos_stock',
            '/app/configuracion' => 'modulo_configuracion',
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

}
