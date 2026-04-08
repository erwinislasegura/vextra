<?php

namespace Aplicacion\Controladores\Empresa;

use Aplicacion\Nucleo\Controlador;
use Aplicacion\Modelos\PuntoVenta;
use Aplicacion\Modelos\GestionComercial;
use Aplicacion\Servicios\ExcelExpoFormato;
use Aplicacion\Servicios\ServicioAlertaStock;

class PuntoVentaControlador extends Controlador
{
    private function rutaRetornoPosPorDefecto(): string
    {
        return '/app/punto-venta';
    }

    private function resolverRetornoPos(): string
    {
        $retorno = trim((string) ($_POST['retorno'] ?? $_SERVER['HTTP_REFERER'] ?? ''));
        if ($retorno === '') {
            return $this->rutaRetornoPosPorDefecto();
        }

        if (!str_starts_with($retorno, '/')) {
            $ruta = (string) parse_url($retorno, PHP_URL_PATH);
            $query = (string) parse_url($retorno, PHP_URL_QUERY);
            $retorno = $ruta . ($query !== '' ? '?' . $query : '');
        }

        if (!str_starts_with($retorno, '/app/punto-venta')) {
            return $this->rutaRetornoPosPorDefecto();
        }

        return $retorno;
    }

    private function validarPermiso(string $permiso): void
    {
        $usuario = usuario_actual();
        if (!$usuario) {
            http_response_code(403);
            exit('No autorizado');
        }

        $roles = [
            'administrador_empresa' => ['ver_pos', 'abrir_caja_pos', 'cerrar_caja_pos', 'registrar_venta_pos', 'aplicar_descuento_pos', 'editar_precio_pos', 'ver_historial_pos', 'administrar_cajas_pos', 'configurar_pos'],
            'usuario_empresa' => ['ver_pos', 'abrir_caja_pos', 'registrar_venta_pos', 'ver_historial_pos'],
        ];

        if (($usuario['rol_codigo'] ?? '') === 'superadministrador') {
            return;
        }

        $permitidos = $roles[$usuario['rol_codigo'] ?? ''] ?? [];
        if (!in_array($permiso, $permitidos, true)) {
            http_response_code(403);
            exit('No tienes permisos para esta acción del POS.');
        }
    }

    public function index(): void
    {
        $this->validarPermiso('ver_pos');
        $empresaId = (int) empresa_actual_id();
        $usuario = usuario_actual();
        $pos = new PuntoVenta();

        $apertura = $pos->obtenerAperturaActiva($empresaId, (int) $usuario['id']);

        $buscar = trim($_GET['q'] ?? '');
        $categoriaId = (int) ($_GET['categoria_id'] ?? 0) ?: null;

        $productos = $pos->listarProductosPos($empresaId, $buscar, $categoriaId);
        $clientes = $pos->listarClientesPos($empresaId);
        $categorias = (new GestionComercial())->listarTablaEmpresa('categorias_productos', $empresaId, '', 300);
        $configuracion = $pos->obtenerConfiguracion($empresaId);
        $cajas = $pos->listarCajas($empresaId);
        $resumenCierre = $apertura ? $pos->resumenCierre($empresaId, (int) $apertura['id']) : null;

        $this->vista('empresa/pos/index', compact('apertura', 'productos', 'clientes', 'categorias', 'buscar', 'categoriaId', 'configuracion', 'cajas', 'resumenCierre'), 'empresa');
    }

    public function aperturaCaja(): void
    {
        $this->validarPermiso('abrir_caja_pos');
        $empresaId = (int) empresa_actual_id();
        $pos = new PuntoVenta();
        $cajas = $pos->listarCajas($empresaId);

        $apertura = $pos->obtenerAperturaActiva($empresaId, (int) (usuario_actual()['id'] ?? 0));
        $this->vista('empresa/pos/apertura_caja', compact('cajas', 'apertura'), 'empresa');
    }

    public function guardarAperturaCaja(): void
    {
        $this->validarPermiso('abrir_caja_pos');
        validar_csrf();
        $empresaId = (int) empresa_actual_id();
        $usuarioId = (int) (usuario_actual()['id'] ?? 0);
        $pos = new PuntoVenta();

        if ($pos->obtenerAperturaActiva($empresaId, $usuarioId)) {
            flash('warning', 'Ya tienes una caja abierta para operar.');
            $this->redirigir('/app/punto-venta');
        }

        $cajaId = (int) ($_POST['caja_id'] ?? 0);
        if ($cajaId <= 0) {
            flash('danger', 'Selecciona una caja válida.');
            $this->redirigir('/app/punto-venta/apertura-caja');
        }

        $pos->abrirCaja([
            'empresa_id' => $empresaId,
            'caja_id' => $cajaId,
            'usuario_id' => $usuarioId,
            'monto_inicial' => (float) ($_POST['monto_inicial'] ?? 0),
            'observacion' => trim($_POST['observacion'] ?? ''),
        ]);

        flash('success', 'Caja abierta correctamente.');
        $this->redirigir('/app/punto-venta');
    }

    public function guardarVenta(): void
    {
        $this->validarPermiso('registrar_venta_pos');
        validar_csrf();
        $empresaId = (int) empresa_actual_id();
        $usuarioId = (int) (usuario_actual()['id'] ?? 0);
        $pos = new PuntoVenta();

        $apertura = $pos->obtenerAperturaActiva($empresaId, $usuarioId);
        if (!$apertura) {
            flash('danger', 'No hay caja abierta para registrar ventas.');
            $this->redirigir('/app/punto-venta/apertura-caja');
        }

        $items = json_decode((string) ($_POST['items_json'] ?? '[]'), true);
        $pagos = json_decode((string) ($_POST['pagos_json'] ?? '[]'), true);

        if (!is_array($items) || $items === []) {
            flash('danger', 'La venta debe tener al menos un producto.');
            $this->redirigir('/app/punto-venta');
        }

        if (!is_array($pagos) || $pagos === []) {
            flash('danger', 'Registra al menos un pago para finalizar la venta.');
            $this->redirigir('/app/punto-venta');
        }

        $tipoVenta = $_POST['tipo_venta'] ?? 'rapida';
        $clienteId = (int) ($_POST['cliente_id'] ?? 0);
        if ($tipoVenta === 'rapida' || $clienteId <= 0) {
            $clienteId = $pos->asegurarClienteRapido($empresaId);
        }

        $configuracion = $pos->obtenerConfiguracion($empresaId);

        try {
            $ventaId = $pos->registrarVenta([
                'empresa_id' => $empresaId,
                'caja_id' => (int) $apertura['caja_id'],
                'apertura_caja_id' => (int) $apertura['id'],
                'cliente_id' => $clienteId,
                'usuario_id' => $usuarioId,
                'tipo_venta' => $tipoVenta,
                'subtotal' => (float) ($_POST['subtotal'] ?? 0),
                'descuento' => (float) ($_POST['descuento'] ?? 0),
                'impuesto' => (float) ($_POST['impuesto'] ?? 0),
                'total' => (float) ($_POST['total'] ?? 0),
                'numero_venta' => $pos->siguienteNumeroVenta($empresaId),
                'observaciones' => trim($_POST['observaciones'] ?? ''),
                'monto_recibido' => (float) ($_POST['monto_recibido'] ?? 0),
                'vuelto' => (float) ($_POST['vuelto'] ?? 0),
            ], $items, $pagos, (bool) ($configuracion['permitir_venta_sin_stock'] ?? false));

            $alertas = new ServicioAlertaStock();
            foreach ($pos->obtenerTransicionesStock() as $transicion) {
                $alertas->evaluarYNotificar($empresaId, (int) $transicion['producto_id'], (float) $transicion['stock_anterior'], (float) $transicion['stock_actual'], (string) (usuario_actual()['nombre'] ?? ''));
            }

            flash('success', 'Venta registrada correctamente.');
            $this->redirigir('/app/punto-venta/ventas/imprimir/' . $ventaId . '?retorno_pos=1');
        } catch (\Throwable $e) {
            flash('danger', 'No fue posible registrar la venta: ' . $e->getMessage());
            $this->redirigir('/app/punto-venta');
        }
    }

    public function ventas(): void
    {
        $this->validarPermiso('ver_historial_pos');
        $pos = new PuntoVenta();
        $empresaId = (int) empresa_actual_id();
        $ventas = $pos->listarVentas($empresaId);
        $configuracion = $pos->obtenerConfiguracion($empresaId);
        $this->vista('empresa/pos/ventas', compact('ventas', 'configuracion'), 'empresa');
    }

    public function verVenta(int $id): void
    {
        $this->validarPermiso('ver_historial_pos');
        $pos = new PuntoVenta();
        $empresaId = (int) empresa_actual_id();
        $venta = $pos->obtenerVenta($empresaId, $id);
        if (!$venta) {
            http_response_code(404);
            exit('Venta no encontrada.');
        }
        $configuracion = $pos->obtenerConfiguracion($empresaId);
        $this->vista('empresa/pos/ver_venta', compact('venta', 'configuracion'), 'empresa');
    }

    public function imprimirVenta(int $id): void
    {
        $this->validarPermiso('ver_historial_pos');
        $pos = new PuntoVenta();
        $empresaId = (int) empresa_actual_id();
        $venta = $pos->obtenerVenta($empresaId, $id);
        if (!$venta) {
            http_response_code(404);
            exit('Venta no encontrada.');
        }
        $configuracion = $pos->obtenerConfiguracion($empresaId);
        $this->vista('empresa/pos/imprimir_venta', compact('venta', 'configuracion'), 'impresion');
    }

    public function cierreCaja(): void
    {
        $this->validarPermiso('cerrar_caja_pos');
        $empresaId = (int) empresa_actual_id();
        $usuarioId = (int) (usuario_actual()['id'] ?? 0);
        $pos = new PuntoVenta();
        $apertura = $pos->obtenerAperturaActiva($empresaId, $usuarioId);
        $resumen = null;
        if ($apertura) {
            $resumen = $pos->resumenCierre($empresaId, (int) $apertura['id']);
        }
        $historialCierres = $pos->listarHistorialCierres($empresaId);
        $configuracion = $pos->obtenerConfiguracion($empresaId);

        $this->vista('empresa/pos/cierre_caja', compact('apertura', 'resumen', 'historialCierres', 'configuracion'), 'empresa');
    }

    public function guardarCierreCaja(): void
    {
        $this->validarPermiso('cerrar_caja_pos');
        validar_csrf();
        $empresaId = (int) empresa_actual_id();
        $usuarioId = (int) (usuario_actual()['id'] ?? 0);
        $pos = new PuntoVenta();
        $apertura = $pos->obtenerAperturaActiva($empresaId, $usuarioId);

        if (!$apertura) {
            flash('warning', 'No hay caja abierta para cerrar.');
            $this->redirigir('/app/punto-venta/cierre-caja');
        }

        $resumen = $pos->resumenCierre($empresaId, (int) $apertura['id']);
        $montoEsperado = (float) $apertura['monto_inicial']
            + (float) ($resumen['total_ventas'] ?? 0)
            + (float) ($resumen['ingresos_manuales'] ?? 0)
            - (float) ($resumen['egresos_manuales'] ?? 0);
        $montoContado = (float) ($_POST['monto_contado'] ?? 0);
        $diferencia = $montoContado - $montoEsperado;

        $pos->cerrarCaja([
            'empresa_id' => $empresaId,
            'apertura_caja_id' => (int) $apertura['id'],
            'usuario_id' => $usuarioId,
            'monto_esperado' => $montoEsperado,
            'monto_contado' => $montoContado,
            'diferencia' => $diferencia,
            'observacion' => trim($_POST['observacion'] ?? ''),
            'monto_efectivo' => (float) ($resumen['efectivo'] ?? 0),
            'monto_transferencia' => (float) ($resumen['transferencia'] ?? 0),
            'monto_tarjeta' => (float) ($resumen['tarjeta'] ?? 0),
            'monto_inicial' => (float) $apertura['monto_inicial'],
        ]);

        flash('success', 'Caja cerrada correctamente. Diferencia: ' . number_format($diferencia, 2));
        $this->redirigir('/app/punto-venta/cierre-caja');
    }

    public function cajas(): void
    {
        $this->validarPermiso('administrar_cajas_pos');
        $cajas = (new PuntoVenta())->listarCajas((int) empresa_actual_id());
        $this->vista('empresa/pos/cajas', compact('cajas'), 'empresa');
    }

    public function guardarCaja(): void
    {
        $this->validarPermiso('administrar_cajas_pos');
        validar_csrf();
        (new PuntoVenta())->crearCaja([
            'empresa_id' => (int) empresa_actual_id(),
            'nombre' => trim($_POST['nombre'] ?? ''),
            'codigo' => trim($_POST['codigo'] ?? ''),
            'estado' => $_POST['estado'] ?? 'activa',
        ]);
        flash('success', 'Caja creada correctamente.');
        $this->redirigir('/app/punto-venta/cajas');
    }

    public function verCaja(int $id): void
    {
        $this->validarPermiso('administrar_cajas_pos');
        $pos = new PuntoVenta();
        $empresaId = (int) empresa_actual_id();
        $caja = $pos->obtenerCaja($empresaId, $id);
        if (!$caja) {
            http_response_code(404);
            exit('Caja no encontrada.');
        }
        $this->vista('empresa/pos/caja_ver', compact('caja'), 'empresa');
    }

    public function editarCaja(int $id): void
    {
        $this->validarPermiso('administrar_cajas_pos');
        $pos = new PuntoVenta();
        $empresaId = (int) empresa_actual_id();
        $caja = $pos->obtenerCaja($empresaId, $id);
        if (!$caja) {
            http_response_code(404);
            exit('Caja no encontrada.');
        }
        $this->vista('empresa/pos/caja_editar', compact('caja'), 'empresa');
    }

    public function actualizarCaja(int $id): void
    {
        $this->validarPermiso('administrar_cajas_pos');
        validar_csrf();
        $pos = new PuntoVenta();
        $empresaId = (int) empresa_actual_id();
        $caja = $pos->obtenerCaja($empresaId, $id);
        if (!$caja) {
            flash('danger', 'Caja no encontrada.');
            $this->redirigir('/app/punto-venta/cajas');
        }

        $ok = $pos->actualizarCaja([
            'empresa_id' => $empresaId,
            'id' => $id,
            'nombre' => trim((string) ($_POST['nombre'] ?? '')),
            'codigo' => trim((string) ($_POST['codigo'] ?? '')),
            'estado' => (string) ($_POST['estado'] ?? 'activa'),
        ]);

        flash($ok ? 'success' : 'danger', $ok ? 'Caja actualizada correctamente.' : 'No fue posible actualizar la caja.');
        $this->redirigir('/app/punto-venta/cajas');
    }

    public function eliminarCaja(int $id): void
    {
        $this->validarPermiso('administrar_cajas_pos');
        validar_csrf();
        $ok = (new PuntoVenta())->inactivarCaja((int) empresa_actual_id(), $id);
        flash($ok ? 'success' : 'danger', $ok ? 'Caja inactivada correctamente.' : 'No fue posible inactivar la caja.');
        $this->redirigir('/app/punto-venta/cajas');
    }

    public function exportarCajasExcel(): void
    {
        $this->validarPermiso('administrar_cajas_pos');
        $cajas = (new PuntoVenta())->listarCajas((int) empresa_actual_id());
        $nombreArchivo = 'cajas_pos_' . date('Ymd_His') . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "\xEF\xBB\xBF";
        echo '<html><head><meta charset="UTF-8"></head><body>';
        echo '<table border="1" cellspacing="0" cellpadding="4" style="' . ExcelExpoFormato::TABLA_ESTILO . '">';
        echo '<tr style="' . ExcelExpoFormato::ENCABEZADO_ESTILO . '"><th>ID</th><th>Nombre</th><th>Código</th><th>Estado</th><th>Fecha creación</th></tr>';
        foreach ($cajas as $caja) {
            echo '<tr>';
            echo '<td>' . (int) ($caja['id'] ?? 0) . '</td>';
            echo '<td>' . $this->escapeExcelHtml($caja['nombre'] ?? '') . '</td>';
            echo '<td style="' . ExcelExpoFormato::CELDA_TEXTO_EXCEL . '">' . $this->escapeExcelHtml($caja['codigo'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($caja['estado'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($caja['fecha_creacion'] ?? '') . '</td>';
            echo '</tr>';
        }
        echo '</table></body></html>';
        exit;
    }

    public function movimientosCaja(): void
    {
        $this->validarPermiso('ver_historial_pos');
        $pos = new PuntoVenta();
        $empresaId = (int) empresa_actual_id();
        $usuarioId = (int) (usuario_actual()['id'] ?? 0);
        $apertura = $pos->obtenerAperturaActiva($empresaId, $usuarioId);
        $movimientos = $pos->listarMovimientosCaja($empresaId);
        $configuracion = $pos->obtenerConfiguracion($empresaId);
        $this->vista('empresa/pos/movimientos', compact('movimientos', 'configuracion', 'apertura'), 'empresa');
    }

    public function exportarMovimientosCajaExcel(): void
    {
        $this->validarPermiso('ver_historial_pos');
        $pos = new PuntoVenta();
        $empresaId = (int) empresa_actual_id();
        $movimientos = $pos->listarMovimientosCaja($empresaId);

        $nombreArchivo = 'movimientos_caja_pos_' . date('Ymd_His') . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "\xEF\xBB\xBF";
        echo '<html><head><meta charset="UTF-8"></head><body>';
        echo '<table border="1" cellspacing="0" cellpadding="4" style="' . ExcelExpoFormato::TABLA_ESTILO . '">';
        echo '<tr style="' . ExcelExpoFormato::ENCABEZADO_ESTILO . '">';
        echo '<th>Fecha</th>';
        echo '<th>Caja</th>';
        echo '<th>Nombre</th>';
        echo '<th>Tipo</th>';
        echo '<th>Concepto</th>';
        echo '<th>Monto</th>';
        echo '<th>Usuario</th>';
        echo '</tr>';

        foreach ($movimientos as $movimiento) {
            $tipoNombre = match ((string) ($movimiento['tipo_movimiento'] ?? '')) {
                'ingreso_manual' => 'Ingreso manual',
                'egreso_manual' => 'Egreso manual',
                'ingreso_venta' => 'Ingreso por venta',
                default => ucwords(str_replace('_', ' ', (string) ($movimiento['tipo_movimiento'] ?? ''))),
            };

            echo '<tr>';
            echo '<td>' . $this->escapeExcelHtml($movimiento['fecha_movimiento'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($movimiento['caja_nombre'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($movimiento['concepto'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($tipoNombre) . '</td>';
            echo '<td>' . $this->escapeExcelHtml($movimiento['concepto'] ?? '') . '</td>';
            echo '<td>' . number_format((float) ($movimiento['monto'] ?? 0), 2, '.', '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($movimiento['usuario_nombre'] ?? '') . '</td>';
            echo '</tr>';
        }

        echo '</table></body></html>';
        exit;
    }

    public function guardarMovimientoCaja(): void
    {
        $this->validarPermiso('registrar_venta_pos');
        validar_csrf();
        $retorno = $this->resolverRetornoPos();

        $pos = new PuntoVenta();
        $empresaId = (int) empresa_actual_id();
        $usuarioId = (int) (usuario_actual()['id'] ?? 0);
        $apertura = $pos->obtenerAperturaActiva($empresaId, $usuarioId);
        if (!$apertura) {
            flash('warning', 'Debes tener una caja abierta para registrar ingresos o egresos.');
            $this->redirigir($retorno);
        }

        $tipo = (string) ($_POST['tipo_movimiento'] ?? '');
        $tipoMovimiento = match ($tipo) {
            'ingreso' => 'ingreso_manual',
            'egreso' => 'egreso_manual',
            default => '',
        };
        if ($tipoMovimiento === '') {
            flash('danger', 'Selecciona un tipo de movimiento válido.');
            $this->redirigir($retorno);
        }

        $monto = (float) ($_POST['monto'] ?? 0);
        if ($monto <= 0) {
            flash('danger', 'El monto debe ser mayor a cero.');
            $this->redirigir($retorno);
        }

        $comentario = trim((string) ($_POST['comentario'] ?? ''));
        if ($comentario === '') {
            flash('danger', 'Ingresa un comentario para registrar el movimiento.');
            $this->redirigir($retorno);
        }

        $pos->registrarMovimientoCaja([
            'empresa_id' => $empresaId,
            'caja_id' => (int) $apertura['caja_id'],
            'apertura_caja_id' => (int) $apertura['id'],
            'tipo_movimiento' => $tipoMovimiento,
            'concepto' => $comentario,
            'monto' => $monto,
            'usuario_id' => $usuarioId,
        ]);

        flash('success', 'Movimiento de caja registrado correctamente.');
        $this->redirigir($retorno);
    }

    public function configuracion(): void
    {
        $this->validarPermiso('configurar_pos');
        $configuracion = (new PuntoVenta())->obtenerConfiguracion((int) empresa_actual_id());
        $this->vista('empresa/pos/configuracion', compact('configuracion'), 'empresa');
    }

    public function guardarConfiguracion(): void
    {
        $this->validarPermiso('configurar_pos');
        validar_csrf();
        (new PuntoVenta())->guardarConfiguracion((int) empresa_actual_id(), [
            'permitir_venta_sin_stock' => isset($_POST['permitir_venta_sin_stock']) ? 1 : 0,
            'impuesto_por_defecto' => (float) ($_POST['impuesto_por_defecto'] ?? 0),
            'usar_decimales' => isset($_POST['usar_decimales']) ? 1 : 0,
            'cantidad_decimales' => max(0, min(6, (int) ($_POST['cantidad_decimales'] ?? 2))),
            'moneda' => in_array((string) ($_POST['moneda'] ?? 'CLP'), ['CLP', 'USD', 'EU'], true) ? (string) $_POST['moneda'] : 'CLP',
        ]);

        flash('success', 'Configuración POS actualizada.');
        $this->redirigir('/app/punto-venta/configuracion');
    }

    private function escapeExcelHtml(mixed $valor): string
    {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}
