<?php

namespace Aplicacion\Controladores\Empresa;

use Aplicacion\Nucleo\Controlador;
use Aplicacion\Modelos\Cliente;
use Aplicacion\Modelos\Producto;
use Aplicacion\Modelos\Cotizacion;
use Aplicacion\Modelos\Plan;
use Aplicacion\Modelos\GestionComercial;
use Aplicacion\Modelos\Inventario;
use Aplicacion\Modelos\PuntoVenta;

class PanelEmpresaControlador extends Controlador
{
    public function panel(): void
    {
        $empresaId = empresa_actual_id();
        $clienteModel = new Cliente();
        $productoModel = new Producto();
        $cotizacionModel = new Cotizacion();
        $gestionComercialModel = new GestionComercial();
        $inventarioModel = new Inventario();
        $puntoVentaModel = new PuntoVenta();
        $planEmpresa = (new Plan())->obtenerPlanActivoEmpresa($empresaId);
        $resumenComercial = $gestionComercialModel->estadisticasInicio($empresaId);
        $productosInventario = $inventarioModel->listarProductos($empresaId);
        $ordenesCompra = $inventarioModel->listarOrdenesCompra($empresaId);
        $ventasPos = $puntoVentaModel->listarVentas($empresaId);
        $seguimientos = $gestionComercialModel->listarSeguimientoCotizaciones($empresaId, '', '', 200);
        $aprobaciones = $gestionComercialModel->listarAprobacionesCotizaciones($empresaId, '', '', 200);
        $notificaciones = $gestionComercialModel->listarTablaEmpresa('notificaciones_empresa', $empresaId, '', 100);
        $historialActividad = $gestionComercialModel->listarTablaEmpresa('historial_actividad', $empresaId, '', 8);

        $stockBajo = 0;
        $stockCritico = 0;
        foreach ($productosInventario as $producto) {
            $stockActual = (float) ($producto['stock_actual'] ?? 0);
            $stockMinimo = (float) ($producto['stock_minimo'] ?? 0);
            $stockCriticoRef = (float) ($producto['stock_critico'] ?? 0);
            if ($stockMinimo > 0 && $stockActual <= $stockMinimo) {
                $stockBajo++;
            }
            if ($stockCriticoRef > 0 && $stockActual <= $stockCriticoRef) {
                $stockCritico++;
            }
        }

        $ordenesPendientes = 0;
        foreach ($ordenesCompra as $ordenCompra) {
            if (in_array((string) ($ordenCompra['estado'] ?? ''), ['borrador', 'emitida', 'parcial'], true)) {
                $ordenesPendientes++;
            }
        }

        $hoy = date('Y-m-d');
        $ventasHoy = 0;
        $montoVentasHoy = 0.0;
        foreach ($ventasPos as $ventaPos) {
            $fechaVenta = (string) ($ventaPos['fecha_venta'] ?? '');
            $esDeHoy = $fechaVenta !== '' && substr($fechaVenta, 0, 10) === $hoy;
            if ($esDeHoy && ($ventaPos['estado'] ?? 'pagada') === 'pagada') {
                $ventasHoy++;
                $montoVentasHoy += (float) ($ventaPos['total'] ?? 0);
            }
        }

        $seguimientosAbiertos = 0;
        foreach ($seguimientos as $seguimiento) {
            $estadoComercial = mb_strtolower((string) ($seguimiento['estado_comercial'] ?? ''));
            if (!in_array($estadoComercial, ['cerrado', 'cerrada', 'completado', 'completada'], true)) {
                $seguimientosAbiertos++;
            }
        }

        $aprobacionesPendientes = 0;
        foreach ($aprobaciones as $aprobacion) {
            if (($aprobacion['estado'] ?? '') === 'pendiente') {
                $aprobacionesPendientes++;
            }
        }

        $notificacionesPendientes = 0;
        foreach ($notificaciones as $notificacion) {
            if (($notificacion['estado'] ?? '') !== 'leida') {
                $notificacionesPendientes++;
            }
        }

        $resumen = array_merge($resumenComercial, [
            'total_clientes' => $clienteModel->contar($empresaId),
            'total_productos' => $productoModel->contar($empresaId),
            'total_cotizaciones' => count($cotizacionModel->listar($empresaId)),
            'plan_actual' => $planEmpresa['plan_id'] ?? null,
            'estado_suscripcion' => $planEmpresa['estado'] ?? null,
            'fecha_vencimiento' => $planEmpresa['fecha_vencimiento'] ?? null,
            'dias_restantes_plan' => isset($planEmpresa['fecha_vencimiento']) ? (int) floor((strtotime((string) $planEmpresa['fecha_vencimiento']) - strtotime(date('Y-m-d'))) / 86400) : null,
            'stock_bajo' => $stockBajo,
            'stock_critico' => $stockCritico,
            'ordenes_compra_pendientes' => $ordenesPendientes,
            'ventas_hoy' => $ventasHoy,
            'monto_ventas_hoy' => $montoVentasHoy,
            'seguimientos_abiertos' => $seguimientosAbiertos,
            'aprobaciones_pendientes' => $aprobacionesPendientes,
            'notificaciones_pendientes' => $notificacionesPendientes,
            'historial_reciente' => $historialActividad,
        ]);

        $cotizaciones = $cotizacionModel->listar($empresaId);
        $this->vista('empresa/panel', compact('resumen', 'cotizaciones'), 'empresa');
    }
}
