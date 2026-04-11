<?php

namespace Aplicacion\Controladores\Empresa;

use Aplicacion\Nucleo\Controlador;
use Aplicacion\Modelos\Empresa;
use Aplicacion\Modelos\GestionComercial;
use Aplicacion\Modelos\Inventario;
use Aplicacion\Servicios\ExcelExpoFormato;
use Aplicacion\Servicios\ServicioAlertaStock;
use Aplicacion\Servicios\ServicioCorreo;
use Throwable;

class InventarioControlador extends Controlador
{
    private const MOTIVOS_AJUSTE = [
        'correccion_inventario' => 'Corrección de inventario',
        'merma' => 'Merma',
        'perdida' => 'Pérdida',
        'danio' => 'Daño',
        'ajuste_manual' => 'Ajuste manual',
        'diferencia_conteo' => 'Diferencia en conteo',
        'devolucion' => 'Devolución',
        'regularizacion' => 'Regularización',
    ];

    private function validarPermiso(string $permiso): void
    {
        $usuario = usuario_actual();
        if (!$usuario) {
            http_response_code(403);
            exit('No autorizado');
        }

        if (($usuario['rol_codigo'] ?? '') === 'superadministrador') {
            return;
        }

        $roles = [
            'administrador_empresa' => ['inventario_ver_recepciones', 'inventario_crear_recepciones', 'inventario_ver_ajustes', 'inventario_crear_ajustes', 'inventario_ver_movimientos', 'inventario_configurar_alertas'],
            'usuario_empresa' => ['inventario_ver_recepciones', 'inventario_ver_ajustes', 'inventario_ver_movimientos'],
        ];

        if (!in_array($permiso, $roles[$usuario['rol_codigo'] ?? ''] ?? [], true)) {
            http_response_code(403);
            exit('No tienes permisos para esta sección de inventario.');
        }
    }

    public function recepciones(): void
    {
        $this->validarPermiso('inventario_ver_recepciones');
        $empresaId = (int) empresa_actual_id();
        $inventario = new Inventario();
        $ordenCompraId = (int) ($_GET['orden_compra_id'] ?? 0);
        $filtrosRecepciones = $this->obtenerFiltrosRecepciones();

        $recepciones = $inventario->listarRecepciones($empresaId, $filtrosRecepciones);
        $proveedores = $inventario->listarProveedores($empresaId);
        $productos = $inventario->listarProductos($empresaId);
        $ordenCompraSeleccionada = $ordenCompraId > 0 ? $inventario->obtenerOrdenCompra($empresaId, $ordenCompraId) : null;
        if ($ordenCompraSeleccionada && (string) ($ordenCompraSeleccionada['estado'] ?? '') !== 'aprobada') {
            $ordenCompraSeleccionada = null;
        }
        $ordenesCompraAprobadas = $inventario->listarOrdenesCompra($empresaId, ['estado' => 'aprobada']);
        $ordenesCompraAprobadas = array_map(static function (array $orden) use ($inventario, $empresaId): array {
            $ordenCompleta = $inventario->obtenerOrdenCompra($empresaId, (int) ($orden['id'] ?? 0)) ?? [];
            return [
                'id' => (int) ($orden['id'] ?? 0),
                'numero' => (string) ($orden['numero'] ?? ''),
                'proveedor_id' => (int) ($orden['proveedor_id'] ?? 0),
                'proveedor_nombre' => (string) ($orden['proveedor_nombre'] ?? ''),
                'fecha_emision' => (string) ($orden['fecha_emision'] ?? ''),
                'referencia' => (string) ($orden['referencia'] ?? ''),
                'observacion' => (string) ($orden['observacion'] ?? ''),
                'detalles' => $ordenCompleta['detalles'] ?? [],
            ];
        }, $ordenesCompraAprobadas);

        $this->vista('empresa/inventario/recepciones', compact('recepciones', 'proveedores', 'productos', 'ordenCompraSeleccionada', 'ordenesCompraAprobadas', 'filtrosRecepciones'), 'empresa');
    }

    public function exportarRecepcionesExcel(): void
    {
        $this->validarPermiso('inventario_ver_recepciones');
        $empresaId = (int) empresa_actual_id();
        $filtrosRecepciones = $this->obtenerFiltrosRecepciones();
        $recepciones = (new Inventario())->listarRecepciones($empresaId, $filtrosRecepciones);

        $nombreArchivo = 'recepciones_inventario_' . date('Ymd_His') . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "\xEF\xBB\xBF";
        echo '<html><head><meta charset="UTF-8"></head><body>';
        echo '<table border="1" cellspacing="0" cellpadding="4" style="' . ExcelExpoFormato::TABLA_ESTILO . '">';
        echo '<tr style="' . ExcelExpoFormato::ENCABEZADO_ESTILO . '">';
        echo '<th>Fecha</th>';
        echo '<th>Proveedor</th>';
        echo '<th>Tipo documento</th>';
        echo '<th>Número documento</th>';
        echo '<th>Referencia</th>';
        echo '<th>Usuario</th>';
        echo '<th>Observación</th>';
        echo '</tr>';

        foreach ($recepciones as $recepcion) {
            echo '<tr>';
            echo '<td>' . $this->escapeExcelHtml($recepcion['fecha_creacion'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($recepcion['proveedor_nombre'] ?? 'Sin proveedor') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($recepcion['tipo_documento'] ?? '') . '</td>';
            echo '<td style="' . ExcelExpoFormato::CELDA_TEXTO_EXCEL . '">' . $this->escapeExcelHtml($recepcion['numero_documento'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($recepcion['referencia_interna'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($recepcion['usuario_nombre'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($recepcion['observacion'] ?? '') . '</td>';
            echo '</tr>';
        }

        echo '</table></body></html>';
        exit;
    }

    private function obtenerFiltrosRecepciones(): array
    {
        $tipoDocumento = trim((string) ($_GET['tipo_documento'] ?? ''));
        if (!in_array($tipoDocumento, ['guia_despacho', 'factura'], true)) {
            $tipoDocumento = '';
        }

        return [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'tipo_documento' => $tipoDocumento,
        ];
    }

    public function guardarRecepcion(): void
    {
        $this->validarPermiso('inventario_crear_recepciones');
        validar_csrf();
        $empresaId = (int) empresa_actual_id();
        $usuario = usuario_actual();
        $inventario = new Inventario();

        $proveedorId = (int) ($_POST['proveedor_id'] ?? 0);
        $nuevoProveedor = trim((string) ($_POST['proveedor_nuevo'] ?? ''));
        if ($proveedorId <= 0 && $nuevoProveedor !== '') {
            $proveedorId = $inventario->crearProveedor($empresaId, [
                'nombre' => $nuevoProveedor,
                'identificador_fiscal' => trim((string) ($_POST['proveedor_identificador_fiscal'] ?? '')),
                'contacto' => trim((string) ($_POST['proveedor_contacto'] ?? '')),
                'correo' => trim((string) ($_POST['proveedor_correo'] ?? '')),
                'telefono' => trim((string) ($_POST['proveedor_telefono'] ?? '')),
                'direccion' => trim((string) ($_POST['proveedor_direccion'] ?? '')),
                'ciudad' => trim((string) ($_POST['proveedor_ciudad'] ?? '')),
                'observacion' => trim((string) ($_POST['proveedor_observacion'] ?? '')),
                'estado' => 'activo',
            ]);
        }

        $tiposPermitidos = ['guia_despacho', 'factura'];
        $tipoDocumento = in_array($_POST['tipo_documento'] ?? '', $tiposPermitidos, true) ? $_POST['tipo_documento'] : 'guia_despacho';
        $ordenCompraId = (int) ($_POST['orden_compra_id'] ?? 0);
        $ordenCompra = null;
        if ($ordenCompraId > 0) {
            $ordenCompra = $inventario->obtenerOrdenCompra($empresaId, $ordenCompraId);
            if (!$ordenCompra || (string) ($ordenCompra['estado'] ?? '') !== 'aprobada') {
                flash('danger', 'Solo puedes usar órdenes de compra aprobadas para cargar datos de recepción.');
                $this->redirigir('/app/inventario/recepciones');
            }
            if ($proveedorId <= 0) {
                $proveedorId = (int) ($ordenCompra['proveedor_id'] ?? 0);
            }
        }

        $productoIds = $_POST['producto_id'] ?? [];
        $cantidades = $_POST['cantidad'] ?? [];
        $costos = $_POST['costo_unitario'] ?? [];
        $detalles = [];
        foreach ((array) $productoIds as $idx => $productoId) {
            $pid = (int) $productoId;
            $cantidad = (float) ($cantidades[$idx] ?? 0);
            if ($pid <= 0 || $cantidad <= 0) {
                continue;
            }
            $costo = (float) ($costos[$idx] ?? 0);
            $detalles[] = [
                'producto_id' => $pid,
                'cantidad' => $cantidad,
                'costo_unitario' => $costo,
                'subtotal' => $costo > 0 ? ($cantidad * $costo) : 0,
            ];
        }

        if ($detalles === []) {
            if ($ordenCompra && !empty($ordenCompra['detalles'])) {
                foreach ((array) $ordenCompra['detalles'] as $detalleOrden) {
                    $pid = (int) ($detalleOrden['producto_id'] ?? 0);
                    $cantidad = (float) ($detalleOrden['cantidad'] ?? 0);
                    $costo = (float) ($detalleOrden['costo_unitario'] ?? 0);
                    if ($pid <= 0 || $cantidad <= 0) {
                        continue;
                    }
                    $detalles[] = [
                        'producto_id' => $pid,
                        'cantidad' => $cantidad,
                        'costo_unitario' => $costo,
                        'subtotal' => $costo > 0 ? ($cantidad * $costo) : 0,
                    ];
                }
            }
        }

        if ($detalles === []) {
            flash('danger', 'Agrega al menos un producto con cantidad válida para registrar la recepción.');
            $this->redirigir('/app/inventario/recepciones');
        }

        try {
            $recepcionId = $inventario->crearRecepcion([
                'empresa_id' => $empresaId,
                'proveedor_id' => $proveedorId > 0 ? $proveedorId : null,
                'orden_compra_id' => $ordenCompraId > 0 ? $ordenCompraId : null,
                'tipo_documento' => $tipoDocumento,
                'numero_documento' => trim((string) ($_POST['numero_documento'] ?? '')),
                'fecha_documento' => trim((string) ($_POST['fecha_documento'] ?? date('Y-m-d'))),
                'referencia_interna' => trim((string) ($_POST['referencia_interna'] ?? '')),
                'observacion' => trim((string) ($_POST['observacion'] ?? '')),
                'usuario_id' => (int) ($usuario['id'] ?? 0),
            ], $detalles);
            flash('success', 'Recepción de inventario guardada y stock actualizado correctamente.');
            if ($recepcionId > 0) {
                $this->redirigirSegunAccion((string) ($_POST['accion'] ?? 'guardar_salir'), '/app/inventario/recepciones/editar/' . $recepcionId, '/app/inventario/recepciones');
            }
        } catch (Throwable $e) {
            flash('danger', 'No fue posible guardar la recepción: ' . $e->getMessage());
        }

        $this->redirigir('/app/inventario/recepciones');
    }

    public function verRecepcion(int $id): void
    {
        $this->validarPermiso('inventario_ver_recepciones');
        $recepcion = (new Inventario())->obtenerRecepcion((int) empresa_actual_id(), $id);
        if (!$recepcion) {
            http_response_code(404);
            exit('Recepción no encontrada.');
        }

        $this->vista('empresa/inventario/recepcion_ver', compact('recepcion'), 'empresa');
    }

    public function editarRecepcion(int $id): void
    {
        $this->validarPermiso('inventario_ver_recepciones');
        $empresaId = (int) empresa_actual_id();
        $inventario = new Inventario();
        $recepcion = $inventario->obtenerRecepcion($empresaId, $id);
        if (!$recepcion) {
            flash('danger', 'Recepción no encontrada.');
            $this->redirigir('/app/inventario/recepciones');
        }

        $proveedores = $inventario->listarProveedores($empresaId);
        $this->vista('empresa/inventario/recepcion_editar', compact('recepcion', 'proveedores'), 'empresa');
    }

    public function actualizarRecepcion(int $id): void
    {
        $this->validarPermiso('inventario_crear_recepciones');
        validar_csrf();
        $empresaId = (int) empresa_actual_id();
        $inventario = new Inventario();
        $recepcion = $inventario->obtenerRecepcion($empresaId, $id);
        if (!$recepcion) {
            flash('danger', 'Recepción no encontrada.');
            $this->redirigir('/app/inventario/recepciones');
        }

        $tiposPermitidos = ['guia_despacho', 'factura'];
        $tipoDocumento = in_array($_POST['tipo_documento'] ?? '', $tiposPermitidos, true) ? $_POST['tipo_documento'] : 'guia_despacho';
        try {
            $inventario->actualizarRecepcionBasica($empresaId, $id, [
                'proveedor_id' => (int) ($_POST['proveedor_id'] ?? 0) ?: null,
                'tipo_documento' => $tipoDocumento,
                'numero_documento' => trim((string) ($_POST['numero_documento'] ?? '')),
                'fecha_documento' => trim((string) ($_POST['fecha_documento'] ?? date('Y-m-d'))),
                'referencia_interna' => trim((string) ($_POST['referencia_interna'] ?? '')),
                'observacion' => trim((string) ($_POST['observacion'] ?? '')),
            ]);

            flash('success', 'Recepción actualizada correctamente.');
            $this->redirigirSegunAccion((string) ($_POST['accion'] ?? 'guardar_salir'), '/app/inventario/recepciones/editar/' . $id, '/app/inventario/recepciones');
        } catch (Throwable $e) {
            flash('danger', 'No fue posible actualizar la recepción: ' . $e->getMessage());
            $this->redirigir('/app/inventario/recepciones/editar/' . $id);
        }
    }

    public function eliminarRecepcion(int $id): void
    {
        $this->validarPermiso('inventario_crear_recepciones');
        validar_csrf();
        $empresaId = (int) empresa_actual_id();
        $inventario = new Inventario();
        $recepcion = $inventario->obtenerRecepcion($empresaId, $id);
        if (!$recepcion) {
            flash('danger', 'Recepción no encontrada.');
            $this->redirigir('/app/inventario/recepciones');
        }

        try {
            $inventario->eliminarRecepcionCompleta($empresaId, $id);
            flash('success', 'Recepción eliminada correctamente junto a su detalle.');
        } catch (Throwable $e) {
            flash('danger', 'No fue posible eliminar la recepción: ' . $e->getMessage());
        }

        $this->redirigir('/app/inventario/recepciones');
    }

    public function imprimirRecepcion(int $id): void
    {
        $this->validarPermiso('inventario_ver_recepciones');
        $empresaId = (int) empresa_actual_id();
        $recepcion = (new Inventario())->obtenerRecepcion($empresaId, $id);
        if (!$recepcion) {
            flash('danger', 'Recepción no encontrada.');
            $this->redirigir('/app/inventario/recepciones');
        }
        $empresa = (new Empresa())->buscar($empresaId);
        $this->vista('empresa/inventario/recepcion_imprimir', compact('recepcion', 'empresa'), 'impresion');
    }

    public function descargarRecepcionPdf(int $id): void
    {
        $this->redirigir('/app/inventario/recepciones/imprimir/' . $id . '?modo=pdf');
    }

    public function enviarRecepcion(int $id): void
    {
        $this->validarPermiso('inventario_crear_recepciones');
        validar_csrf();
        $empresaId = (int) empresa_actual_id();
        $recepcion = (new Inventario())->obtenerRecepcion($empresaId, $id);
        if (!$recepcion) {
            flash('danger', 'Recepción no encontrada.');
            $this->redirigir('/app/inventario/recepciones');
        }

        $destinatario = filter_var((string) ($recepcion['proveedor_correo'] ?? ''), FILTER_VALIDATE_EMAIL);
        if (!$destinatario) {
            flash('danger', 'El proveedor de esta recepción no tiene correo válido.');
            $this->redirigir('/app/inventario/recepciones/editar/' . $id);
        }

        $empresa = (new Empresa())->buscar($empresaId) ?: [];
        $urlPdf = $this->construirUrlInterna('/app/inventario/recepciones/pdf/' . $id);
        (new ServicioCorreo())->enviar(
            $destinatario,
            'Recepción de inventario ' . ((string) ($recepcion['numero_documento'] ?? ('#' . $id))),
            'recepcion_inventario_proveedor',
            [
                'empresa' => (string) ($empresa['nombre_comercial'] ?? $empresa['razon_social'] ?? ''),
                'proveedor' => (string) ($recepcion['proveedor_nombre'] ?? ''),
                'numero_documento' => (string) ($recepcion['numero_documento'] ?? ''),
                'link_pdf' => $urlPdf,
            ]
        );

        flash('success', 'Recepción enviada por correo al proveedor.');
        $this->redirigir('/app/inventario/recepciones/editar/' . $id);
    }

    public function ajustes(): void
    {
        $this->validarPermiso('inventario_ver_ajustes');
        $empresaId = (int) empresa_actual_id();
        $inventario = new Inventario();
        $filtros = [
            'producto_id' => (int) ($_GET['producto_id'] ?? 0),
            'tipo_ajuste' => trim((string) ($_GET['tipo_ajuste'] ?? '')),
            'fecha_desde' => trim((string) ($_GET['fecha_desde'] ?? '')),
            'fecha_hasta' => trim((string) ($_GET['fecha_hasta'] ?? '')),
        ];

        $productos = $inventario->listarProductos($empresaId);
        $ajustes = $inventario->listarAjustes($empresaId, $filtros);
        $motivos = self::MOTIVOS_AJUSTE;

        $this->vista('empresa/inventario/ajustes', compact('ajustes', 'productos', 'motivos', 'filtros'), 'empresa');
    }

    public function exportarAjustesExcel(): void
    {
        $this->validarPermiso('inventario_ver_ajustes');
        $empresaId = (int) empresa_actual_id();
        $inventario = new Inventario();
        $filtros = [
            'producto_id' => (int) ($_GET['producto_id'] ?? 0),
            'tipo_ajuste' => trim((string) ($_GET['tipo_ajuste'] ?? '')),
            'fecha_desde' => trim((string) ($_GET['fecha_desde'] ?? '')),
            'fecha_hasta' => trim((string) ($_GET['fecha_hasta'] ?? '')),
        ];
        $ajustes = $inventario->listarAjustes($empresaId, $filtros);

        $nombreArchivo = 'ajustes_inventario_' . date('Ymd_His') . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "\xEF\xBB\xBF";
        echo '<html><head><meta charset="UTF-8"></head><body>';
        echo '<table border="1" cellspacing="0" cellpadding="4" style="' . ExcelExpoFormato::TABLA_ESTILO . '">';
        echo '<tr style="' . ExcelExpoFormato::ENCABEZADO_ESTILO . '">';
        echo '<th>Fecha</th>';
        echo '<th>Código</th>';
        echo '<th>Producto</th>';
        echo '<th>Tipo</th>';
        echo '<th>Cantidad</th>';
        echo '<th>Motivo</th>';
        echo '<th>Observación</th>';
        echo '<th>Usuario</th>';
        echo '</tr>';

        foreach ($ajustes as $ajuste) {
            $motivoCodigo = (string) ($ajuste['motivo'] ?? '');
            $motivo = self::MOTIVOS_AJUSTE[$motivoCodigo] ?? $motivoCodigo;

            echo '<tr>';
            echo '<td>' . $this->escapeExcelHtml($ajuste['fecha_creacion'] ?? '') . '</td>';
            echo '<td style="' . ExcelExpoFormato::CELDA_TEXTO_EXCEL . '">' . $this->escapeExcelHtml($ajuste['codigo'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($ajuste['producto_nombre'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($ajuste['tipo_ajuste'] ?? '') . '</td>';
            echo '<td>' . number_format((float) ($ajuste['cantidad'] ?? 0), 2, '.', '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($motivo) . '</td>';
            echo '<td>' . $this->escapeExcelHtml($ajuste['observacion'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($ajuste['usuario_nombre'] ?? '') . '</td>';
            echo '</tr>';
        }

        echo '</table></body></html>';
        exit;
    }

    public function guardarAjuste(): void
    {
        $this->validarPermiso('inventario_crear_ajustes');
        validar_csrf();
        $empresaId = (int) empresa_actual_id();
        $usuario = usuario_actual();
        $inventario = new Inventario();

        $productoId = (int) ($_POST['producto_id'] ?? 0);
        $tipoAjuste = ($_POST['tipo_ajuste'] ?? 'entrada') === 'salida' ? 'salida' : 'entrada';
        $cantidad = max(0, (float) ($_POST['cantidad'] ?? 0));

        if ($productoId <= 0 || $cantidad <= 0) {
            flash('danger', 'Producto y cantidad son obligatorios para crear un ajuste.');
            $this->redirigir('/app/inventario/ajustes');
        }

        $productoActual = null;
        foreach ($inventario->listarProductos($empresaId) as $p) {
            if ((int) $p['id'] === $productoId) {
                $productoActual = $p;
                break;
            }
        }
        $stockAnterior = (float) ($productoActual['stock_actual'] ?? 0);
        $stockNuevo = $tipoAjuste === 'entrada' ? $stockAnterior + $cantidad : $stockAnterior - $cantidad;

        try {
            $ajusteId = $inventario->crearAjuste([
                'empresa_id' => $empresaId,
                'producto_id' => $productoId,
                'tipo_ajuste' => $tipoAjuste,
                'cantidad' => $cantidad,
                'motivo' => trim((string) ($_POST['motivo'] ?? 'ajuste_manual')),
                'observacion' => trim((string) ($_POST['observacion'] ?? '')),
                'usuario_id' => (int) ($usuario['id'] ?? 0),
            ], $inventario->obtenerAjustePermitirNegativo($empresaId));

            (new ServicioAlertaStock())->evaluarYNotificar($empresaId, $productoId, $stockAnterior, max(0, $stockNuevo), (string) ($usuario['nombre'] ?? ''));
            flash('success', 'Ajuste registrado correctamente.');
            $this->redirigir('/app/inventario/ajustes/ver/' . $ajusteId);
        } catch (Throwable $e) {
            flash('danger', 'No fue posible registrar el ajuste: ' . $e->getMessage());
            $this->redirigir('/app/inventario/ajustes');
        }
    }

    public function verAjuste(int $id): void
    {
        $this->validarPermiso('inventario_ver_ajustes');
        $ajuste = (new Inventario())->obtenerAjuste((int) empresa_actual_id(), $id);
        if (!$ajuste) {
            http_response_code(404);
            exit('Ajuste no encontrado.');
        }
        $this->vista('empresa/inventario/ajuste_ver', compact('ajuste'), 'empresa');
    }


    public function proveedores(): void
    {
        $this->validarPermiso('inventario_ver_recepciones');
        $empresaId = (int) empresa_actual_id();
        $inventario = new Inventario();
        $filtros = [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'estado' => (string) ($_GET['estado'] ?? ''),
        ];
        $proveedores = $inventario->listarProveedores($empresaId, $filtros);

        $verId = (int) ($_GET['ver'] ?? 0);
        $editarId = (int) ($_GET['editar'] ?? 0);
        $proveedorVer = $verId > 0 ? $inventario->obtenerProveedor($empresaId, $verId) : null;
        $proveedorEdicion = $editarId > 0 ? $inventario->obtenerProveedor($empresaId, $editarId) : null;

        $this->vista('empresa/inventario/proveedores', compact('proveedores', 'proveedorVer', 'proveedorEdicion', 'filtros'), 'empresa');
    }

    public function guardarProveedor(): void
    {
        $this->validarPermiso('inventario_crear_recepciones');
        validar_csrf();
        $empresaId = (int) empresa_actual_id();
        $inventario = new Inventario();
        $nombre = trim((string) ($_POST['razon_social'] ?? ''));
        if ($nombre === '') {
            $nombre = trim((string) ($_POST['nombre_comercial'] ?? ''));
        }
        if ($nombre === '') {
            $nombre = trim((string) ($_POST['nombre'] ?? ''));
        }
        if ($nombre === '') {
            flash('danger', 'El nombre del proveedor es obligatorio.');
            $this->redirigir('/app/inventario/proveedores');
        }

        $data = [
            'nombre' => $nombre,
            'identificador_fiscal' => trim((string) ($_POST['identificador_fiscal'] ?? '')),
            'contacto' => trim((string) ($_POST['nombre_contacto'] ?? $_POST['contacto'] ?? '')),
            'correo' => trim((string) ($_POST['correo'] ?? '')),
            'telefono' => trim((string) ($_POST['telefono'] ?? '')),
            'direccion' => trim((string) ($_POST['direccion'] ?? '')),
            'ciudad' => trim((string) ($_POST['ciudad'] ?? '')),
            'observacion' => trim((string) ($_POST['observacion'] ?? '')),
            'estado' => ($_POST['estado'] ?? 'activo') === 'inactivo' ? 'inactivo' : 'activo',
        ];

        $proveedorId = (int) ($_POST['proveedor_id'] ?? 0);
        if ($proveedorId > 0) {
            $proveedor = $inventario->obtenerProveedor($empresaId, $proveedorId);
            if (!$proveedor) {
                flash('danger', 'El proveedor que intentas actualizar no existe.');
                $this->redirigir('/app/inventario/proveedores');
            }
            $inventario->actualizarProveedor($empresaId, $proveedorId, $data);
            flash('success', 'Proveedor actualizado correctamente.');
            $this->redirigir('/app/inventario/proveedores');
        }

        $inventario->crearProveedor($empresaId, $data);
        flash('success', 'Proveedor creado correctamente.');
        $this->redirigir((string) ($_POST['redirect_to'] ?? '/app/inventario/proveedores'));
    }

    public function eliminarProveedor(int $id): void
    {
        $this->validarPermiso('inventario_crear_recepciones');
        validar_csrf();
        $empresaId = (int) empresa_actual_id();
        $inventario = new Inventario();
        $proveedor = $inventario->obtenerProveedor($empresaId, $id);

        if (!$proveedor) {
            flash('danger', 'Proveedor no encontrado.');
            $this->redirigir('/app/inventario/proveedores');
        }

        try {
            $inventario->eliminarProveedor($empresaId, $id);
            flash('success', 'Proveedor eliminado correctamente.');
        } catch (Throwable $e) {
            flash('danger', 'No se pudo eliminar el proveedor porque tiene registros asociados.');
        }

        $this->redirigir('/app/inventario/proveedores');
    }

    public function exportarProveedoresExcel(): void
    {
        $this->validarPermiso('inventario_ver_recepciones');
        $empresaId = (int) empresa_actual_id();
        $filtros = [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'estado' => (string) ($_GET['estado'] ?? ''),
        ];
        $proveedores = (new Inventario())->listarProveedores($empresaId, $filtros);

        $nombreArchivo = 'proveedores_inventario_' . date('Ymd_His') . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "\xEF\xBB\xBF";
        echo '<html><head><meta charset="UTF-8"></head><body>';
        echo '<table border="1" cellspacing="0" cellpadding="4" style="' . ExcelExpoFormato::TABLA_ESTILO . '">';
        echo '<tr style="' . ExcelExpoFormato::ENCABEZADO_ESTILO . '">';
        echo '<th>Razón social</th>';
        echo '<th>Identificador fiscal</th>';
        echo '<th>Contacto</th>';
        echo '<th>Correo</th>';
        echo '<th>Teléfono</th>';
        echo '<th>Dirección</th>';
        echo '<th>Ciudad</th>';
        echo '<th>Estado</th>';
        echo '<th>Observación</th>';
        echo '</tr>';

        foreach ($proveedores as $proveedor) {
            echo '<tr>';
            echo '<td>' . $this->escapeExcelHtml($proveedor['nombre'] ?? '') . '</td>';
            echo '<td style="' . ExcelExpoFormato::CELDA_TEXTO_EXCEL . '">' . $this->escapeExcelHtml($proveedor['identificador_fiscal'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($proveedor['contacto'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($proveedor['correo'] ?? '') . '</td>';
            echo '<td style="' . ExcelExpoFormato::CELDA_TEXTO_EXCEL . '">' . $this->escapeExcelHtml($proveedor['telefono'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($proveedor['direccion'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($proveedor['ciudad'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($proveedor['estado'] ?? 'activo') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($proveedor['observacion'] ?? '') . '</td>';
            echo '</tr>';
        }

        echo '</table></body></html>';
        exit;
    }

    public function movimientos(): void
    {
        $this->validarPermiso('inventario_ver_movimientos');
        $empresaId = (int) empresa_actual_id();
        $productoId = (int) ($_GET['producto_id'] ?? 0) ?: null;
        $inventario = new Inventario();

        $movimientos = $inventario->listarMovimientos($empresaId, $productoId);
        $productos = $inventario->listarProductos($empresaId);

        $this->vista('empresa/inventario/movimientos', compact('movimientos', 'productos', 'productoId'), 'empresa');
    }

    public function exportarMovimientosExcel(): void
    {
        $this->validarPermiso('inventario_ver_movimientos');
        $empresaId = (int) empresa_actual_id();
        $productoId = (int) ($_GET['producto_id'] ?? 0) ?: null;
        $inventario = new Inventario();
        $movimientos = $inventario->listarMovimientos($empresaId, $productoId);

        $nombreArchivo = 'movimientos_inventario_' . date('Ymd_His') . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "\xEF\xBB\xBF";
        echo '<html><head><meta charset="UTF-8"></head><body>';
        echo '<table border="1" cellspacing="0" cellpadding="4" style="' . ExcelExpoFormato::TABLA_ESTILO . '">';
        echo '<tr style="' . ExcelExpoFormato::ENCABEZADO_ESTILO . '">';
        echo '<th>Fecha</th>';
        echo '<th>Código</th>';
        echo '<th>Producto</th>';
        echo '<th>Movimiento</th>';
        echo '<th>Origen</th>';
        echo '<th>Entrada</th>';
        echo '<th>Salida</th>';
        echo '<th>Saldo resultante</th>';
        echo '<th>Usuario</th>';
        echo '<th>Observación</th>';
        echo '</tr>';

        foreach ($movimientos as $movimiento) {
            echo '<tr>';
            echo '<td>' . $this->escapeExcelHtml($movimiento['fecha_creacion'] ?? '') . '</td>';
            echo '<td style="' . ExcelExpoFormato::CELDA_TEXTO_EXCEL . '">' . $this->escapeExcelHtml($movimiento['codigo'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($movimiento['producto_nombre'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($movimiento['tipo_movimiento'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($movimiento['documento_origen'] ?? '') . '</td>';
            echo '<td>' . number_format((float) ($movimiento['entrada'] ?? 0), 2, '.', '') . '</td>';
            echo '<td>' . number_format((float) ($movimiento['salida'] ?? 0), 2, '.', '') . '</td>';
            echo '<td>' . number_format((float) ($movimiento['saldo_resultante'] ?? 0), 2, '.', '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($movimiento['usuario_nombre'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($movimiento['observacion'] ?? '') . '</td>';
            echo '</tr>';
        }

        echo '</table></body></html>';
        exit;
    }

    public function ordenesCompra(): void
    {
        $this->validarPermiso('inventario_ver_recepciones');
        $empresaId = (int) empresa_actual_id();
        $inventario = new Inventario();
        $filtros = $this->obtenerFiltrosOrdenesCompra();
        $ordenes = $inventario->listarOrdenesCompra($empresaId, $filtros);
        $proveedores = $inventario->listarProveedores($empresaId);
        $productos = $inventario->listarProductos($empresaId);
        $siguienteNumero = $inventario->siguienteNumeroOrdenCompra($empresaId);

        $this->vista('empresa/inventario/ordenes_compra', compact('ordenes', 'proveedores', 'productos', 'siguienteNumero', 'filtros'), 'empresa');
    }

    public function exportarOrdenesCompraExcel(): void
    {
        $this->validarPermiso('inventario_ver_recepciones');
        $empresaId = (int) empresa_actual_id();
        $filtros = $this->obtenerFiltrosOrdenesCompra();
        $ordenes = (new Inventario())->listarOrdenesCompra($empresaId, $filtros);

        $nombreArchivo = 'ordenes_compra_' . date('Ymd_His') . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "\xEF\xBB\xBF";
        echo '<html><head><meta charset="UTF-8"></head><body>';
        echo '<table border="1" cellspacing="0" cellpadding="4" style="' . ExcelExpoFormato::TABLA_ESTILO . '">';
        echo '<tr style="' . ExcelExpoFormato::ENCABEZADO_ESTILO . '">';
        echo '<th>Número</th>';
        echo '<th>Proveedor</th>';
        echo '<th>Fecha emisión</th>';
        echo '<th>Fecha entrega estimada</th>';
        echo '<th>Estado</th>';
        echo '<th>N° recepción</th>';
        echo '<th>Usuario</th>';
        echo '<th>Referencia</th>';
        echo '<th>Observación</th>';
        echo '</tr>';

        foreach ($ordenes as $orden) {
            echo '<tr>';
            echo '<td style="' . ExcelExpoFormato::CELDA_TEXTO_EXCEL . '">' . $this->escapeExcelHtml($orden['numero'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($orden['proveedor_nombre'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($orden['fecha_emision'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($orden['fecha_entrega_estimada'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($orden['estado_mostrado'] ?? ($orden['estado'] ?? '')) . '</td>';
            echo '<td style="' . ExcelExpoFormato::CELDA_TEXTO_EXCEL . '">' . $this->escapeExcelHtml($orden['numero_recepcion'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($orden['usuario_nombre'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($orden['referencia'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($orden['observacion'] ?? '') . '</td>';
            echo '</tr>';
        }

        echo '</table></body></html>';
        exit;
    }

    private function obtenerFiltrosOrdenesCompra(): array
    {
        $estado = trim(mb_strtolower((string) ($_GET['estado'] ?? '')));
        $permitidos = ['emitida', 'parcial', 'recibida', 'aprobada', 'rechazada', 'anulada', 'recepcionada', 'cancelada'];
        if (!in_array($estado, $permitidos, true)) {
            $estado = '';
        }

        return [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'estado' => $estado,
        ];
    }

    public function cambiarEstadoOrdenCompra(int $id): void
    {
        $this->validarPermiso('inventario_crear_recepciones');
        validar_csrf();
        $empresaId = (int) empresa_actual_id();
        $estado = trim(mb_strtolower((string) ($_POST['estado'] ?? '')));
        if (!in_array($estado, ['aprobada', 'rechazada'], true)) {
            flash('danger', 'Estado de orden no permitido.');
            $this->redirigir('/app/inventario/ordenes-compra');
        }

        $inventario = new Inventario();
        $orden = $inventario->obtenerOrdenCompra($empresaId, $id);
        if (!$orden) {
            flash('danger', 'Orden de compra no encontrada.');
            $this->redirigir('/app/inventario/ordenes-compra');
        }

        try {
            $inventario->actualizarEstadoOrdenCompraManual($empresaId, $id, $estado);
            flash('success', 'Orden de compra ' . ($estado === 'aprobada' ? 'aprobada' : 'rechazada') . ' correctamente.');
        } catch (Throwable $e) {
            flash('danger', 'No fue posible actualizar el estado: ' . $e->getMessage());
        }

        $this->redirigir('/app/inventario/ordenes-compra');
    }

    public function guardarOrdenCompra(): void
    {
        $this->validarPermiso('inventario_crear_recepciones');
        validar_csrf();
        $empresaId = (int) empresa_actual_id();
        $inventario = new Inventario();
        $usuario = usuario_actual();

        $detalles = $this->extraerDetalleOrdenCompra();

        if ($detalles === []) {
            flash('danger', 'La orden de compra debe incluir al menos un producto con cantidad.');
            $this->redirigir('/app/inventario/ordenes-compra');
        }

        $proveedorId = (int) ($_POST['proveedor_id'] ?? 0);
        if ($proveedorId <= 0) {
            flash('danger', 'Debes seleccionar un proveedor para la orden de compra.');
            $this->redirigir('/app/inventario/ordenes-compra');
        }

        $numero = trim((string) ($_POST['numero'] ?? ''));
        if ($numero === '') {
            $numero = $inventario->siguienteNumeroOrdenCompra($empresaId);
        }

        try {
            $ordenId = $inventario->crearOrdenCompra([
                'empresa_id' => $empresaId,
                'proveedor_id' => $proveedorId,
                'numero' => $numero,
                'fecha_emision' => trim((string) ($_POST['fecha_emision'] ?? date('Y-m-d'))),
                'fecha_entrega_estimada' => trim((string) ($_POST['fecha_entrega_estimada'] ?? date('Y-m-d', strtotime('+7 days')))),
                'estado' => 'emitida',
                'referencia' => trim((string) ($_POST['referencia'] ?? '')),
                'observacion' => trim((string) ($_POST['observacion'] ?? '')),
                'usuario_id' => (int) ($usuario['id'] ?? 0),
                'token_publico' => bin2hex(random_bytes(32)),
            ], $detalles);

            flash('success', 'Orden de compra creada correctamente.');
            $this->redirigirSegunAccion((string) ($_POST['accion'] ?? 'guardar_salir'), '/app/inventario/ordenes-compra/editar/' . $ordenId, '/app/inventario/ordenes-compra');
        } catch (Throwable $e) {
            flash('danger', 'No fue posible crear la orden de compra: ' . $e->getMessage());
            $this->redirigir('/app/inventario/ordenes-compra');
        }
    }

    public function editarOrdenCompra(int $id): void
    {
        $this->validarPermiso('inventario_ver_recepciones');
        $empresaId = (int) empresa_actual_id();
        $inventario = new Inventario();
        $orden = $inventario->obtenerOrdenCompra($empresaId, $id);
        if (!$orden) {
            flash('danger', 'Orden de compra no encontrada.');
            $this->redirigir('/app/inventario/ordenes-compra');
        }
        if (in_array((string) ($orden['estado'] ?? ''), ['aprobada', 'recepcionada'], true)) {
            flash('danger', 'No puedes editar una orden en estado aprobada o recepcionada.');
            $this->redirigir('/app/inventario/ordenes-compra');
        }

        $proveedores = $inventario->listarProveedores($empresaId);
        $productos = $inventario->listarProductos($empresaId);
        $this->vista('empresa/inventario/orden_compra_editar', compact('orden', 'proveedores', 'productos'), 'empresa');
    }

    public function actualizarOrdenCompra(int $id): void
    {
        $this->validarPermiso('inventario_crear_recepciones');
        validar_csrf();
        $empresaId = (int) empresa_actual_id();
        $inventario = new Inventario();
        $orden = $inventario->obtenerOrdenCompra($empresaId, $id);
        if (!$orden) {
            flash('danger', 'Orden de compra no encontrada.');
            $this->redirigir('/app/inventario/ordenes-compra');
        }
        if (in_array((string) ($orden['estado'] ?? ''), ['aprobada', 'recepcionada'], true)) {
            flash('danger', 'No puedes editar una orden en estado aprobada o recepcionada.');
            $this->redirigir('/app/inventario/ordenes-compra');
        }

        $detalles = $this->extraerDetalleOrdenCompra();
        if ($detalles === []) {
            flash('danger', 'La orden de compra debe incluir al menos un producto con cantidad.');
            $this->redirigir('/app/inventario/ordenes-compra/editar/' . $id);
        }

        $proveedorId = (int) ($_POST['proveedor_id'] ?? 0);
        if ($proveedorId <= 0) {
            flash('danger', 'Debes seleccionar un proveedor para la orden de compra.');
            $this->redirigir('/app/inventario/ordenes-compra/editar/' . $id);
        }

        $numero = trim((string) ($_POST['numero'] ?? ''));
        if ($numero === '') {
            $numero = (string) ($orden['numero'] ?? ('OC-' . $id));
        }

        try {
            $inventario->actualizarOrdenCompra($empresaId, $id, [
                'proveedor_id' => $proveedorId,
                'numero' => $numero,
                'fecha_emision' => trim((string) ($_POST['fecha_emision'] ?? date('Y-m-d'))),
                'fecha_entrega_estimada' => trim((string) ($_POST['fecha_entrega_estimada'] ?? date('Y-m-d', strtotime('+7 days')))),
                'referencia' => trim((string) ($_POST['referencia'] ?? '')),
                'observacion' => trim((string) ($_POST['observacion'] ?? '')),
                'usuario_id' => (int) ((usuario_actual()['id'] ?? 0)),
            ], $detalles);

            flash('success', 'Orden de compra actualizada correctamente.');
            $this->redirigirSegunAccion((string) ($_POST['accion'] ?? 'guardar_salir'), '/app/inventario/ordenes-compra/editar/' . $id, '/app/inventario/ordenes-compra');
        } catch (Throwable $e) {
            flash('danger', 'No fue posible actualizar la orden de compra: ' . $e->getMessage());
            $this->redirigir('/app/inventario/ordenes-compra/editar/' . $id);
        }
    }

    public function verOrdenCompra(int $id): void
    {
        $this->validarPermiso('inventario_ver_recepciones');
        $empresaId = (int) empresa_actual_id();
        $orden = (new Inventario())->obtenerOrdenCompra($empresaId, $id);
        if (!$orden) {
            http_response_code(404);
            exit('Orden de compra no encontrada.');
        }

        $this->vista('empresa/inventario/orden_compra_ver', compact('orden'), 'empresa');
    }

    public function imprimirOrdenCompra(int $id): void
    {
        $this->validarPermiso('inventario_ver_recepciones');
        $empresaId = (int) empresa_actual_id();
        $orden = (new Inventario())->obtenerOrdenCompra($empresaId, $id);
        if (!$orden) {
            flash('danger', 'Orden de compra no encontrada.');
            $this->redirigir('/app/inventario/ordenes-compra');
        }

        $empresa = (new Empresa())->buscar($empresaId);
        $this->vista('empresa/inventario/orden_compra_imprimir', compact('orden', 'empresa'), 'impresion');
    }

    public function descargarOrdenCompraPdf(int $id): void
    {
        $this->redirigir('/app/inventario/ordenes-compra/imprimir/' . $id . '?modo=pdf');
    }

    public function enviarOrdenCompra(int $id): void
    {
        $this->validarPermiso('inventario_crear_recepciones');
        validar_csrf();
        $empresaId = (int) empresa_actual_id();
        $orden = (new Inventario())->obtenerOrdenCompra($empresaId, $id);
        if (!$orden) {
            flash('danger', 'Orden de compra no encontrada.');
            $this->redirigir('/app/inventario/ordenes-compra');
        }

        $destinatario = filter_var((string) ($orden['proveedor_correo'] ?? ''), FILTER_VALIDATE_EMAIL);
        if (!$destinatario) {
            flash('danger', 'El proveedor no tiene un correo válido para enviar la orden.');
            $this->redirigir('/app/inventario/ordenes-compra/editar/' . $id);
        }

        $empresa = (new Empresa())->buscar($empresaId) ?: [];
        $tokenPublico = (string) ($orden['token_publico'] ?? '');
        if ($tokenPublico === '') {
            $tokenPublico = bin2hex(random_bytes(32));
            (new Inventario())->actualizarTokenPublicoOrdenCompra($empresaId, $id, $tokenPublico);
            $orden['token_publico'] = $tokenPublico;
        }
        $urlPublica = $this->construirUrlPublicaOrdenCompra($tokenPublico);
        $urlPdf = $this->construirUrlInterna('/app/inventario/ordenes-compra/pdf/' . $id);
        $variablesPlantilla = [
            '{{empresa_nombre}}' => (string) ($empresa['nombre_comercial'] ?? $empresa['razon_social'] ?? 'Tu empresa'),
            '{{proveedor_nombre}}' => (string) ($orden['proveedor_nombre'] ?? 'Proveedor'),
            '{{correo_destino}}' => $destinatario,
            '{{numero_orden}}' => (string) ($orden['numero'] ?? ('#' . $id)),
            '{{estado_orden}}' => (string) ($orden['estado'] ?? ''),
            '{{fecha_emision}}' => (string) ($orden['fecha_emision'] ?? date('Y-m-d')),
            '{{fecha_entrega}}' => (string) ($orden['fecha_entrega_estimada'] ?? ''),
            '{{total_orden}}' => '$' . number_format((float) ($orden['total'] ?? 0), 2, ',', '.'),
            '{{detalle_orden}}' => $this->construirDetalleOrdenCorreo($orden),
            '{{url_publica}}' => $urlPublica,
            '{{url_pdf}}' => $urlPdf,
            '{{remitente_nombre}}' => (string) ($empresa['imap_remitente_nombre'] ?? $empresa['nombre_comercial'] ?? ''),
            '{{remitente_correo}}' => (string) ($empresa['imap_remitente_correo'] ?? $empresa['correo'] ?? ''),
        ];
        $plantillaCorreo = (new GestionComercial())->obtenerPlantillaCorreoOrdenCompra($empresaId);
        $asuntoPlantilla = trim((string) ($plantillaCorreo['terminos_defecto'] ?? ''));
        $htmlPlantilla = trim((string) ($plantillaCorreo['observaciones_defecto'] ?? ''));
        $asunto = $asuntoPlantilla !== ''
            ? $this->renderizarPlantillaCorreo($asuntoPlantilla, $variablesPlantilla)
            : ('Orden de compra ' . ((string) ($orden['numero'] ?? ('#' . $id))));
        if ($htmlPlantilla === '') {
            $htmlPlantilla = $this->plantillaBaseCorreoOrdenCompra();
        }
        if (mb_strpos($htmlPlantilla, '{{detalle_orden}}') === false) {
            $htmlPlantilla .= '<div style="margin-top:18px;"><h3 style="margin:0 0 10px;font-size:15px;color:#111827;">Detalle de productos</h3>{{detalle_orden}}</div>';
        }
        $mensajeHtml = $htmlPlantilla !== ''
            ? $this->renderizarPlantillaCorreo($htmlPlantilla, $variablesPlantilla)
            : '';

        (new ServicioCorreo())->enviar(
            $destinatario,
            $asunto,
            'orden_compra_proveedor',
            [
                'empresa' => (string) ($empresa['nombre_comercial'] ?? $empresa['razon_social'] ?? ''),
                'proveedor' => (string) ($orden['proveedor_nombre'] ?? ''),
                'numero' => (string) ($orden['numero'] ?? ''),
                'html' => $mensajeHtml,
                'mensaje_html' => $mensajeHtml,
                'link_publico' => $urlPublica,
                'link_pdf' => $urlPdf,
            ]
        );

        flash('success', 'Orden de compra enviada al correo del proveedor.');
        $this->redirigir('/app/inventario/ordenes-compra/editar/' . $id);
    }

    private function extraerDetalleOrdenCompra(): array
    {
        $productoIds = $_POST['producto_id'] ?? [];
        $cantidades = $_POST['cantidad'] ?? [];
        $costos = $_POST['costo_unitario'] ?? [];
        $detalles = [];
        foreach ((array) $productoIds as $idx => $productoId) {
            $pid = (int) $productoId;
            $cantidad = (float) ($cantidades[$idx] ?? 0);
            if ($pid <= 0 || $cantidad <= 0) {
                continue;
            }
            $costo = (float) ($costos[$idx] ?? 0);
            $detalles[] = [
                'producto_id' => $pid,
                'cantidad' => $cantidad,
                'costo_unitario' => $costo,
                'subtotal' => $cantidad * $costo,
            ];
        }

        return $detalles;
    }

    private function redirigirSegunAccion(string $accion, string $rutaMantener, string $rutaSalir): void
    {
        if ($accion === 'guardar') {
            $this->redirigir($rutaMantener);
        }
        $this->redirigir($rutaSalir);
    }

    private function construirUrlInterna(string $ruta): string
    {
        $config = require __DIR__ . '/../../../configuracion/aplicacion.php';
        $base = rtrim((string) ($config['url'] ?? ''), '/');
        if ($base !== '' && preg_match('/localhost|127\\.0\\.0\\.1/i', $base)) {
            $base = 'https://vextra.cl';
        }
        if ($base === '') {
            $esHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
            $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost:8000');
            if ($host === '' || preg_match('/localhost|127\\.0\\.0\\.1/i', $host)) {
                $host = 'vextra.cl';
                $esHttps = true;
            }
            $base = ($esHttps ? 'https://' : 'http://') . $host;
        }

        return $base . url($ruta);
    }

    private function construirUrlPublicaOrdenCompra(string $tokenPublico): string
    {
        return $this->construirUrlInterna('/orden-compra/publica/' . $tokenPublico);
    }

    private function renderizarPlantillaCorreo(string $contenido, array $variables): string
    {
        $reemplazos = [];
        foreach ($variables as $clave => $valor) {
            if ($clave === '{{detalle_orden}}') {
                $reemplazos[$clave] = (string) $valor;
                continue;
            }
            $reemplazos[$clave] = htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
        }
        return strtr($contenido, $reemplazos);
    }

    private function plantillaBaseCorreoOrdenCompra(): string
    {
        return <<<'HTML'
<div style="font-family:Arial,Helvetica,sans-serif;background:#f5f7fb;padding:24px;color:#111827;">
  <div style="max-width:680px;margin:0 auto;background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">
    <div style="background:#0f3d77;color:#ffffff;padding:20px 24px;">
      <h2 style="margin:0;font-size:20px;">{{empresa_nombre}}</h2>
      <p style="margin:6px 0 0;font-size:13px;opacity:.9;">Envío automático de orden de compra</p>
    </div>
    <div style="padding:24px;">
      <p style="margin:0 0 14px;">Hola <strong>{{proveedor_nombre}}</strong>,</p>
      <p style="margin:0 0 14px;line-height:1.5;">Adjuntamos la orden de compra <strong>{{numero_orden}}</strong> con fecha de emisión <strong>{{fecha_emision}}</strong> y entrega estimada <strong>{{fecha_entrega}}</strong>.</p>
      <div style="border:1px solid #e5e7eb;border-radius:10px;background:#f9fafb;padding:14px;margin:0 0 16px;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
          <tr><td style="padding:4px 0;color:#6b7280;">Orden</td><td style="padding:4px 0;text-align:right;"><strong>{{numero_orden}}</strong></td></tr>
          <tr><td style="padding:4px 0;color:#6b7280;">Estado</td><td style="padding:4px 0;text-align:right;">{{estado_orden}}</td></tr>
          <tr><td style="padding:4px 0;color:#6b7280;">Total</td><td style="padding:4px 0;text-align:right;">{{total_orden}}</td></tr>
        </table>
      </div>
      <div style="margin:0 0 16px;">
        <div style="font-size:13px;margin-bottom:8px;color:#111827;"><strong>Detalle de productos</strong></div>
        {{detalle_orden}}
      </div>
      <p style="margin:0 0 20px;line-height:1.5;">Puedes revisar la orden en línea desde el siguiente botón:</p>
      <p style="margin:0 0 18px;">
        <a href="{{url_publica}}" style="display:inline-block;background:#0f3d77;color:#ffffff;text-decoration:none;padding:12px 18px;border-radius:8px;font-weight:600;">Ver orden de compra</a>
      </p>
      <p style="margin:0 0 8px;font-size:13px;color:#4b5563;">Descargar PDF de la orden:</p>
      <p style="margin:0 0 20px;font-size:13px;"><a href="{{url_pdf}}" style="color:#0f3d77;">{{url_pdf}}</a></p>
      <p style="margin:0;font-size:12px;color:#6b7280;">Este correo fue enviado a {{correo_destino}} por {{remitente_nombre}} ({{remitente_correo}}).</p>
    </div>
  </div>
</div>
HTML;
    }

    private function construirDetalleOrdenCorreo(array $orden): string
    {
        $filas = '';
        foreach (($orden['detalles'] ?? []) as $item) {
            $filas .= '<tr>'
                . '<td style="padding:8px;border:1px solid #e5e7eb;">' . htmlspecialchars((string) ($item['codigo'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>'
                . '<td style="padding:8px;border:1px solid #e5e7eb;">' . htmlspecialchars((string) ($item['nombre'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>'
                . '<td style="padding:8px;border:1px solid #e5e7eb;text-align:right;">' . number_format((float) ($item['cantidad'] ?? 0), 2, ',', '.') . '</td>'
                . '<td style="padding:8px;border:1px solid #e5e7eb;text-align:right;">$' . number_format((float) ($item['costo_unitario'] ?? 0), 2, ',', '.') . '</td>'
                . '<td style="padding:8px;border:1px solid #e5e7eb;text-align:right;">$' . number_format((float) ($item['subtotal'] ?? 0), 2, ',', '.') . '</td>'
                . '</tr>';
        }

        if ($filas === '') {
            $filas = '<tr><td colspan="5" style="padding:10px;border:1px solid #e5e7eb;text-align:center;color:#6b7280;">Sin detalle de productos.</td></tr>';
        }

        return '<table style="width:100%;border-collapse:collapse;font-size:12px;background:#ffffff;">'
            . '<thead><tr>'
            . '<th style="padding:8px;border:1px solid #e5e7eb;background:#f9fafb;text-align:left;">Código</th>'
            . '<th style="padding:8px;border:1px solid #e5e7eb;background:#f9fafb;text-align:left;">Descripción</th>'
            . '<th style="padding:8px;border:1px solid #e5e7eb;background:#f9fafb;text-align:right;">Cant.</th>'
            . '<th style="padding:8px;border:1px solid #e5e7eb;background:#f9fafb;text-align:right;">Costo</th>'
            . '<th style="padding:8px;border:1px solid #e5e7eb;background:#f9fafb;text-align:right;">Subtotal</th>'
            . '</tr></thead><tbody>' . $filas . '</tbody></table>';
    }

    private function escapeExcelHtml(mixed $valor): string
    {
        $texto = trim(str_replace(["\r\n", "\r", "\n", "\t"], ' ', (string) $valor));

        if ($texto !== '' && preg_match('/^[=+\-@]/', $texto) === 1) {
            $texto = "'" . $texto;
        }

        return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
    }
}
