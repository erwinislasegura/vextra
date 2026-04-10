<?php

namespace Aplicacion\Controladores\Empresa;

use Aplicacion\Nucleo\Controlador;
use Aplicacion\Modelos\Cotizacion;
use Aplicacion\Modelos\Cliente;
use Aplicacion\Modelos\Producto;
use Aplicacion\Modelos\Inventario;
use Aplicacion\Modelos\Empresa;
use Aplicacion\Modelos\GestionComercial;
use Aplicacion\Servicios\ExcelExpoFormato;
use Aplicacion\Servicios\ServicioPlan;
use Aplicacion\Servicios\ServicioPreciosLista;
use Aplicacion\Servicios\ServicioCorreo;

class CotizacionesControlador extends Controlador
{
    public function index(): void
    {
        $this->requerirFuncionalidadPlan('modulo_cotizaciones');
        $empresaId = empresa_actual_id();
        $buscar = trim($_GET['q'] ?? '');
        $estado = trim($_GET['estado'] ?? '');
        $clienteId = (int) ($_GET['cliente_id'] ?? 0) ?: null;
        $fechaDesde = trim($_GET['fecha_desde'] ?? '');
        $fechaHasta = trim($_GET['fecha_hasta'] ?? '');

        $cotizaciones = (new Cotizacion())->listar($empresaId);
        $cotizaciones = $this->filtrarCotizaciones($cotizaciones, $buscar, $estado, $clienteId, $fechaDesde, $fechaHasta);
        $clientes = (new Cliente())->listar($empresaId);

        $this->vista('empresa/cotizaciones/index', compact('cotizaciones', 'buscar', 'estado', 'clienteId', 'fechaDesde', 'fechaHasta', 'clientes'), 'empresa');
    }

    public function exportarExcel(): void
    {
        $this->requerirFuncionalidadPlan('modulo_cotizaciones');
        $empresaId = empresa_actual_id();
        $buscar = trim($_GET['q'] ?? '');
        $estado = trim($_GET['estado'] ?? '');
        $clienteId = (int) ($_GET['cliente_id'] ?? 0) ?: null;
        $fechaDesde = trim($_GET['fecha_desde'] ?? '');
        $fechaHasta = trim($_GET['fecha_hasta'] ?? '');

        $cotizaciones = (new Cotizacion())->listar($empresaId);
        $cotizaciones = $this->filtrarCotizaciones($cotizaciones, $buscar, $estado, $clienteId, $fechaDesde, $fechaHasta);

        $nombreArchivo = 'cotizaciones_' . date('Ymd_His') . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "\xEF\xBB\xBF";
        echo '<html><head><meta charset="UTF-8"></head><body>';
        echo '<table border="1" cellspacing="0" cellpadding="4" style="' . ExcelExpoFormato::TABLA_ESTILO . '">';
        echo '<tr style="' . ExcelExpoFormato::ENCABEZADO_ESTILO . '">';
        echo '<th>Número</th><th>Cliente</th><th>Emisión</th><th>Vencimiento</th><th>Vendedor</th><th>Subtotal</th><th>Impuesto</th><th>Total</th><th>Estado</th>';
        echo '</tr>';

        foreach ($cotizaciones as $c) {
            echo '<tr>';
            echo '<td style="' . ExcelExpoFormato::CELDA_TEXTO_EXCEL . '">' . $this->escapeExcelHtml($c['numero'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($c['cliente'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($c['fecha_emision'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($c['fecha_vencimiento'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($c['vendedor'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml(number_format((float) ($c['subtotal'] ?? 0), 2)) . '</td>';
            echo '<td>' . $this->escapeExcelHtml(number_format((float) ($c['impuesto'] ?? 0), 2)) . '</td>';
            echo '<td>' . $this->escapeExcelHtml(number_format((float) ($c['total'] ?? 0), 2)) . '</td>';
            echo '<td>' . $this->escapeExcelHtml((string) ($c['estado'] ?? '')) . '</td>';
            echo '</tr>';
        }
        echo '</table></body></html>';
        exit;
    }

    public function crear(): void
    {
        $this->requerirFuncionalidadPlan('modulo_cotizaciones');
        $empresaId = empresa_actual_id();
        $clientes = (new Cliente())->listar($empresaId);
        $productos = (new Producto())->listar($empresaId);
        $gestion = new GestionComercial();
        $listasPrecios = $gestion->listarListasPreciosActivas($empresaId);
        $listasPreciosPorCliente = [];
        foreach ($clientes as $cliente) {
            $listasPreciosPorCliente[(int) $cliente['id']] = $gestion->obtenerListasPrecioCliente($empresaId, (int) $cliente['id']);
        }
        $siguienteNumero = (new Cotizacion())->siguienteNumero($empresaId);
        $tokenPrevisualizacion = bin2hex(random_bytes(32));
        $this->vista('empresa/cotizaciones/formulario', compact('clientes', 'productos', 'siguienteNumero', 'listasPrecios', 'listasPreciosPorCliente', 'tokenPrevisualizacion'), 'empresa');
    }

    public function guardar(): void
    {
        validar_csrf();
        $this->requerirFuncionalidadPlan('modulo_cotizaciones');
        $empresaId = empresa_actual_id();
        $usuario = usuario_actual();
        $modelo = new Cotizacion();

        (new ServicioPlan())->validarLimite($empresaId, 'maximo_cotizaciones_mes', $modelo->contarMes($empresaId), 'Llegaste al límite mensual de cotizaciones de tu plan.');

        $productoIds = $_POST['producto_id'] ?? [];
        $descripciones = $_POST['descripcion_item'] ?? [];
        $cantidades = $_POST['cantidad'] ?? [];
        $precios = $_POST['precio_unitario'] ?? [];
        $impuestos = $_POST['impuesto_item'] ?? [];
        $descuentoTiposLinea = $_POST['descuento_tipo_item'] ?? [];
        $descuentoValoresLinea = $_POST['descuento_item'] ?? [];
        $clienteIdSeleccionado = (int) ($_POST['cliente_id'] ?? 0) ?: null;
        $listaPrecioId = (int) ($_POST['lista_precio_id'] ?? 0) ?: null;
        $servicioPrecios = new ServicioPreciosLista();

        $items = [];
        $subtotal = 0.0;
        $impuesto = 0.0;
        $conteoLineas = max(
            count((array) $productoIds),
            count((array) $descripciones),
            count((array) $cantidades),
            count((array) $precios)
        );

        for ($i = 0; $i < $conteoLineas; $i++) {
            $productoId = (int) ($productoIds[$i] ?? 0) ?: null;
            $descripcion = trim((string) ($descripciones[$i] ?? ''));
            $cantidad = (float) ($cantidades[$i] ?? 0);
            $precio = (float) ($precios[$i] ?? 0);

            $impuestoPorcentaje = max(0, (float) ($impuestos[$i] ?? 0));
            $descuentoTipo = ($descuentoTiposLinea[$i] ?? 'valor') === 'porcentaje' ? 'porcentaje' : 'valor';
            $descuentoValor = max(0, (float) ($descuentoValoresLinea[$i] ?? 0));
            [$precio, $descuentoTipo, $descuentoValor] = $this->aplicarPrecioListaLinea(
                $servicioPrecios,
                $empresaId,
                $productoId,
                $clienteIdSeleccionado,
                $listaPrecioId,
                $precio,
                $descuentoTipo,
                $descuentoValor
            );

            if ($cantidad <= 0 || $precio < 0) {
                continue;
            }

            $baseLinea = $cantidad * $precio;
            $descuentoMonto = $descuentoTipo === 'porcentaje'
                ? $baseLinea * (min($descuentoValor, 100) / 100)
                : min($descuentoValor, $baseLinea);

            $subtotalLinea = max(0, $baseLinea - $descuentoMonto);
            $impuestoLinea = $subtotalLinea * ($impuestoPorcentaje / 100);
            $totalLinea = $subtotalLinea + $impuestoLinea;

            $subtotal += $subtotalLinea;
            $impuesto += $impuestoLinea;
            $items[] = [
                'producto_id' => $productoId,
                'descripcion' => $descripcion !== '' ? $descripcion : 'Ítem ' . ($i + 1),
                'cantidad' => $cantidad,
                'precio_unitario' => $precio,
                'descuento_tipo' => $descuentoTipo,
                'descuento_valor' => $descuentoValor,
                'descuento_monto' => $descuentoMonto,
                'porcentaje_impuesto' => $impuestoPorcentaje,
                'subtotal' => $subtotalLinea,
                'total' => $totalLinea,
            ];
        }

        if ($items === []) {
            flash('danger', 'Debes agregar al menos un servicio o producto con cantidad válida.');
            $this->redirigir('/app/cotizaciones/crear');
        }

        $descuentoTipo = ($_POST['descuento_tipo_total'] ?? 'valor') === 'porcentaje' ? 'porcentaje' : 'valor';
        $descuentoValor = max(0, (float) ($_POST['descuento_total'] ?? 0));
        $descuento = $descuentoTipo === 'porcentaje'
            ? ($subtotal + $impuesto) * (min($descuentoValor, 100) / 100)
            : min($descuentoValor, $subtotal + $impuesto);
        $total = max(0, ($subtotal + $impuesto) - $descuento);

        $numero = $modelo->siguienteNumero($empresaId);
        $consecutivo = (int) preg_replace('/^.*-/', '', $numero);

        $tokenPublico = trim((string) ($_POST['token_publico'] ?? ''));
        if (!preg_match('/^[a-f0-9]{64}$/', $tokenPublico)) {
            $tokenPublico = bin2hex(random_bytes(32));
        }

        $cotizacionId = $modelo->crearConItems([
            'empresa_id' => $empresaId,
            'cliente_id' => (int) $_POST['cliente_id'],
            'usuario_id' => (int) $usuario['id'],
            'numero' => $numero,
            'consecutivo' => $consecutivo,
            'estado' => $_POST['estado'] ?? 'borrador',
            'subtotal' => $subtotal,
            'descuento_tipo' => $descuentoTipo,
            'descuento_valor' => $descuentoValor,
            'descuento' => $descuento,
            'impuesto' => $impuesto,
            'total' => $total,
            'observaciones' => trim($_POST['observaciones'] ?? ''),
            'terminos_condiciones' => trim($_POST['terminos_condiciones'] ?? ''),
            'lista_precio_id' => $listaPrecioId,
            'token_publico' => $tokenPublico,
            'fecha_emision' => $_POST['fecha_emision'] ?? date('Y-m-d'),
            'fecha_vencimiento' => $_POST['fecha_vencimiento'] ?? date('Y-m-d', strtotime('+15 days')),
        ], $items);

        flash('success', 'Cotización creada y numerada correctamente.');
        $this->redirigirSegunAccion($_POST['accion'] ?? 'guardar_salir', '/app/cotizaciones/editar/' . $cotizacionId, '/app/cotizaciones');
    }

    public function ver(int $id): void
    {
        $this->requerirFuncionalidadPlan('modulo_cotizaciones');
        $cotizacion = (new Cotizacion())->obtenerPorId(empresa_actual_id(), $id);
        if (!$cotizacion) {
            flash('danger', 'Cotización no encontrada.');
            $this->redirigir('/app/cotizaciones');
        }
        $this->vista('empresa/cotizaciones/ver', compact('cotizacion'), 'empresa');
    }

    public function movimientosProducto(int $productoId): void
    {
        $this->requerirFuncionalidadPlan('modulo_cotizaciones');
        header('Content-Type: application/json; charset=UTF-8');
        $empresaId = empresa_actual_id();
        if ($productoId <= 0) {
            echo json_encode(['ok' => false, 'mensaje' => 'Producto inválido']);
            return;
        }

        $producto = (new Producto())->obtenerPorId($empresaId, $productoId);
        if (!$producto) {
            echo json_encode(['ok' => false, 'mensaje' => 'Producto no encontrado']);
            return;
        }

        $movimientos = (new Inventario())->listarMovimientos($empresaId, $productoId);
        $movimientos = array_slice($movimientos, 0, 25);

        echo json_encode([
            'ok' => true,
            'data' => [
                'producto' => [
                    'id' => (int) $producto['id'],
                    'nombre' => (string) ($producto['nombre'] ?? ''),
                    'stock_actual' => (float) ($producto['stock_actual'] ?? 0),
                ],
                'movimientos' => $movimientos,
            ],
        ], JSON_UNESCAPED_UNICODE);
    }

    public function imprimir(int $id): void
    {
        $this->requerirFuncionalidadPlan('modulo_cotizaciones');
        $this->requerirFuncionalidadPlan('cotizacion_pdf');
        $empresaId = empresa_actual_id();
        $cotizacion = (new Cotizacion())->obtenerPorId($empresaId, $id);
        if (!$cotizacion) {
            flash('danger', 'Cotización no encontrada.');
            $this->redirigir('/app/cotizaciones');
        }

        $empresa = (new Empresa())->buscar($empresaId);
        $listaPrecioId = (int) ($_GET['lista_precio_id'] ?? 0) ?: null;
        $servicioPrecios = new ServicioPreciosLista();
        $listaAplicada = $servicioPrecios->resolverListaPrecio(
            $empresaId,
            (int) ($cotizacion['cliente_id'] ?? 0) ?: null,
            null,
            date('Y-m-d'),
            $listaPrecioId ?: ((int) ($cotizacion['lista_precio_id'] ?? 0) ?: null)
        );
        $listasPrecios = (new GestionComercial())->listarListasPreciosActivas($empresaId);
        $this->vista('empresa/cotizaciones/imprimir', compact('cotizacion', 'empresa', 'listaAplicada', 'listasPrecios'), 'impresion');
    }

    public function descargarPdf(int $id): void
    {
        $this->requerirFuncionalidadPlan('modulo_cotizaciones');
        $this->requerirFuncionalidadPlan('cotizacion_pdf');
        $listaPrecioId = (int) ($_GET['lista_precio_id'] ?? 0);
        $query = $listaPrecioId > 0
            ? '?lista_precio_id=' . $listaPrecioId . '&modo=pdf'
            : '?modo=pdf';

        $this->redirigir('/app/cotizaciones/imprimir/' . $id . $query);
    }

    public function editar(int $id): void
    {
        $this->requerirFuncionalidadPlan('modulo_cotizaciones');
        $empresaId = empresa_actual_id();
        $cotizacion = (new Cotizacion())->obtenerPorId($empresaId, $id);
        if (!$cotizacion) {
            flash('danger', 'Cotización no encontrada.');
            $this->redirigir('/app/cotizaciones');
        }

        $tokenPublico = trim((string) ($cotizacion['token_publico'] ?? ''));
        if ($tokenPublico === '' || !preg_match('/^[a-f0-9]{64}$/', $tokenPublico)) {
            $tokenPublico = bin2hex(random_bytes(32));
            (new Cotizacion())->actualizarTokenPublico($empresaId, $id, $tokenPublico);
            $cotizacion['token_publico'] = $tokenPublico;
        }

        $linkAprobacionCliente = url('/cotizacion/publica/' . $tokenPublico);

        $clientes = (new Cliente())->listar($empresaId);
        $productos = (new Producto())->listar($empresaId);
        $gestion = new GestionComercial();
        $listasPrecios = $gestion->listarListasPreciosActivas($empresaId);
        $listasPreciosPorCliente = [];
        foreach ($clientes as $cliente) {
            $listasPreciosPorCliente[(int) $cliente['id']] = $gestion->obtenerListasPrecioCliente($empresaId, (int) $cliente['id']);
        }
        $listaPrecioSeleccionada = (new ServicioPreciosLista())->resolverListaPrecio(
            $empresaId,
            (int) $cotizacion['cliente_id'],
            null,
            date('Y-m-d'),
            (int) ($cotizacion['lista_precio_id'] ?? 0) ?: null
        );
        $this->vista('empresa/cotizaciones/editar', compact('cotizacion', 'clientes', 'productos', 'listasPrecios', 'listaPrecioSeleccionada', 'listasPreciosPorCliente', 'linkAprobacionCliente'), 'empresa');
    }

    public function enviar(int $id): void
    {
        validar_csrf();
        $this->requerirFuncionalidadPlan('modulo_cotizaciones');
        $this->requerirFuncionalidadPlan('cotizacion_correo');
        $this->requerirFuncionalidadPlan('cotizacion_pdf');
        $empresaId = empresa_actual_id();
        $modelo = new Cotizacion();
        $cotizacion = $modelo->obtenerPorId($empresaId, $id);
        if (!$cotizacion) {
            flash('danger', 'Cotización no encontrada.');
            $this->redirigir('/app/cotizaciones');
        }

        $clienteActual = (new Cliente())->obtenerPorId($empresaId, (int) ($cotizacion['cliente_id'] ?? 0));
        if (!$clienteActual) {
            flash('danger', 'No se encontró el cliente asociado en la base de datos.');
            $this->redirigir('/app/cotizaciones/editar/' . $id);
        }

        $destinatario = filter_var((string) ($clienteActual['correo'] ?? ''), FILTER_VALIDATE_EMAIL);
        if (!$destinatario) {
            flash('danger', 'El cliente no tiene correo válido para enviar la cotización.');
            $this->redirigir('/app/cotizaciones/editar/' . $id);
        }

        $tokenPublico = (string) ($cotizacion['token_publico'] ?? '');
        if ($tokenPublico === '') {
            $tokenPublico = bin2hex(random_bytes(32));
            $modelo->actualizarTokenPublico($empresaId, $id, $tokenPublico);
            $cotizacion['token_publico'] = $tokenPublico;
        }

        $empresa = (new Empresa())->buscar($empresaId);
        $remitenteCorreo = trim((string) ($empresa['imap_remitente_correo'] ?? '')) !== ''
            ? trim((string) ($empresa['imap_remitente_correo'] ?? ''))
            : trim((string) ($empresa['correo'] ?? ''));
        $remitenteNombre = trim((string) ($empresa['imap_remitente_nombre'] ?? '')) !== ''
            ? trim((string) ($empresa['imap_remitente_nombre'] ?? ''))
            : trim((string) ($empresa['nombre_comercial'] ?? $empresa['razon_social'] ?? 'Vextra'));
        $urlPublica = $this->construirUrlPublica($tokenPublico);
        $urlPdf = $this->construirUrlInterna('/app/cotizaciones/pdf/' . $id);
        $pdfContenido = $this->generarPdfCotizacion($cotizacion, $empresa ?: []);

        $clienteNombrePlantilla = trim((string) ($clienteActual['razon_social'] ?? ''));
        if ($clienteNombrePlantilla === '') {
            $clienteNombrePlantilla = trim((string) ($clienteActual['nombre_comercial'] ?? ''));
        }
        if ($clienteNombrePlantilla === '') {
            $clienteNombrePlantilla = trim((string) ($clienteActual['nombre'] ?? ($cotizacion['cliente'] ?? '')));
        }

        $variablesPlantilla = [
            '{{empresa_nombre}}' => (string) ($empresa['nombre_comercial'] ?? $empresa['razon_social'] ?? 'Tu empresa'),
            '{{cliente_nombre}}' => $clienteNombrePlantilla,
            '{{correo_destino}}' => $destinatario,
            '{{numero_cotizacion}}' => (string) ($cotizacion['numero'] ?? ('#' . $id)),
            '{{estado_cotizacion}}' => (string) ($cotizacion['estado'] ?? 'borrador'),
            '{{fecha_emision}}' => (string) ($cotizacion['fecha_emision'] ?? date('Y-m-d')),
            '{{total_cotizacion}}' => '$' . number_format((float) ($cotizacion['total'] ?? 0), 2, ',', '.'),
            '{{subtotal_cotizacion}}' => '$' . number_format((float) ($cotizacion['subtotal'] ?? 0), 2, ',', '.'),
            '{{impuesto_cotizacion}}' => '$' . number_format((float) ($cotizacion['impuesto'] ?? 0), 2, ',', '.'),
            '{{descuento_cotizacion}}' => '$' . number_format((float) ($cotizacion['descuento'] ?? 0), 2, ',', '.'),
            '{{fecha_vencimiento}}' => (string) ($cotizacion['fecha_vencimiento'] ?? ''),
            '{{url_publica}}' => $urlPublica,
            '{{url_pdf}}' => $urlPdf,
            '{{remitente_nombre}}' => $remitenteNombre,
            '{{remitente_correo}}' => $remitenteCorreo,
        ];

        $plantillaCorreo = (new GestionComercial())->obtenerPlantillaCorreoCotizacion($empresaId);
        $asuntoPlantilla = trim((string) ($plantillaCorreo['terminos_defecto'] ?? ''));
        $htmlPlantilla = trim((string) ($plantillaCorreo['observaciones_defecto'] ?? ''));

        $asuntoCorreo = $asuntoPlantilla !== ''
            ? $this->renderizarPlantillaCorreo($asuntoPlantilla, $variablesPlantilla)
            : ('Cotización ' . ($cotizacion['numero'] ?? ('#' . $id)) . ' - ' . ($empresa['nombre_comercial'] ?? 'Vextra'));

        $mensajeHtml = $htmlPlantilla !== ''
            ? $this->renderizarPlantillaCorreo($htmlPlantilla, $variablesPlantilla)
            : $this->construirPlantillaCorreoCotizacion($cotizacion, $empresa ?: [], $urlPublica, $urlPdf, $clienteNombrePlantilla);

        (new ServicioCorreo())->enviar(
            $destinatario,
            $asuntoCorreo,
            'cotizacion_cliente_profesional',
            [
                'empresa' => $empresa['nombre_comercial'] ?? '',
                'cliente' => $clienteNombrePlantilla,
                'cliente_id' => (int) ($clienteActual['id'] ?? 0),
                'numero' => $cotizacion['numero'] ?? '',
                'fecha_vencimiento' => $cotizacion['fecha_vencimiento'] ?? '',
                'total' => number_format((float) ($cotizacion['total'] ?? 0), 2, ',', '.'),
                'remitente_correo' => $remitenteCorreo,
                'remitente_nombre' => $remitenteNombre,
                'mensaje_html' => $mensajeHtml,
                'adjuntos' => [[
                    'nombre' => 'Cotizacion-' . ($cotizacion['numero'] ?? $id) . '.pdf',
                    'mime' => 'application/pdf',
                    'contenido_base64' => base64_encode($pdfContenido),
                ]],
                'link_publico' => $urlPublica,
                'link_pdf' => $urlPdf,
            ]
        );

        (new Cotizacion())->actualizarBasico($empresaId, $id, [
            'estado' => 'enviada',
            'observaciones' => (string) ($cotizacion['observaciones'] ?? ''),
            'terminos_condiciones' => (string) ($cotizacion['terminos_condiciones'] ?? ''),
            'fecha_vencimiento' => (string) ($cotizacion['fecha_vencimiento'] ?? date('Y-m-d')),
        ]);

        flash('success', 'Cotización enviada automáticamente al cliente con PDF adjunto y enlace público.');
        $this->redirigir('/app/cotizaciones/editar/' . $id);
    }

    public function actualizar(int $id): void
    {
        validar_csrf();
        $this->requerirFuncionalidadPlan('modulo_cotizaciones');

        $productoIds = $_POST['producto_id'] ?? [];
        $descripciones = $_POST['descripcion_item'] ?? [];
        $cantidades = $_POST['cantidad'] ?? [];
        $precios = $_POST['precio_unitario'] ?? [];
        $impuestos = $_POST['impuesto_item'] ?? [];
        $descuentoTiposLinea = $_POST['descuento_tipo_item'] ?? [];
        $descuentoValoresLinea = $_POST['descuento_item'] ?? [];
        $clienteIdSeleccionado = (int) ($_POST['cliente_id'] ?? 0) ?: null;
        $listaPrecioId = (int) ($_POST['lista_precio_id'] ?? 0) ?: null;
        $servicioPrecios = new ServicioPreciosLista();

        $items = [];
        $subtotal = 0.0;
        $impuesto = 0.0;
        $conteoLineas = max(
            count((array) $productoIds),
            count((array) $descripciones),
            count((array) $cantidades),
            count((array) $precios)
        );

        for ($i = 0; $i < $conteoLineas; $i++) {
            $productoId = (int) ($productoIds[$i] ?? 0) ?: null;
            $descripcion = trim((string) ($descripciones[$i] ?? ''));
            $cantidad = (float) ($cantidades[$i] ?? 0);
            $precio = (float) ($precios[$i] ?? 0);

            $impuestoPorcentaje = max(0, (float) ($impuestos[$i] ?? 0));
            $descuentoTipo = ($descuentoTiposLinea[$i] ?? 'valor') === 'porcentaje' ? 'porcentaje' : 'valor';
            $descuentoValor = max(0, (float) ($descuentoValoresLinea[$i] ?? 0));
            [$precio, $descuentoTipo, $descuentoValor] = $this->aplicarPrecioListaLinea(
                $servicioPrecios,
                empresa_actual_id(),
                $productoId,
                $clienteIdSeleccionado,
                $listaPrecioId,
                $precio,
                $descuentoTipo,
                $descuentoValor
            );

            if ($cantidad <= 0 || $precio < 0) {
                continue;
            }

            $baseLinea = $cantidad * $precio;
            $descuentoMonto = $descuentoTipo === 'porcentaje'
                ? $baseLinea * (min($descuentoValor, 100) / 100)
                : min($descuentoValor, $baseLinea);
            $subtotalLinea = max(0, $baseLinea - $descuentoMonto);
            $impuestoLinea = $subtotalLinea * ($impuestoPorcentaje / 100);
            $totalLinea = $subtotalLinea + $impuestoLinea;

            $subtotal += $subtotalLinea;
            $impuesto += $impuestoLinea;
            $items[] = [
                'producto_id' => $productoId,
                'descripcion' => $descripcion !== '' ? $descripcion : 'Ítem ' . ($i + 1),
                'cantidad' => $cantidad,
                'precio_unitario' => $precio,
                'descuento_tipo' => $descuentoTipo,
                'descuento_valor' => $descuentoValor,
                'descuento_monto' => $descuentoMonto,
                'porcentaje_impuesto' => $impuestoPorcentaje,
                'subtotal' => $subtotalLinea,
                'total' => $totalLinea,
            ];
        }

        if ($items === []) {
            flash('danger', 'Debes mantener al menos un servicio o producto con cantidad válida.');
            $this->redirigir('/app/cotizaciones/editar/' . $id);
        }

        $descuentoTipo = ($_POST['descuento_tipo_total'] ?? 'valor') === 'porcentaje' ? 'porcentaje' : 'valor';
        $descuentoValor = max(0, (float) ($_POST['descuento_total'] ?? 0));
        $descuento = $descuentoTipo === 'porcentaje'
            ? ($subtotal + $impuesto) * (min($descuentoValor, 100) / 100)
            : min($descuentoValor, $subtotal + $impuesto);
        $total = max(0, ($subtotal + $impuesto) - $descuento);

        (new Cotizacion())->actualizarConItems(empresa_actual_id(), $id, [
            'cliente_id' => (int) $_POST['cliente_id'],
            'estado' => $_POST['estado'] ?? 'borrador',
            'subtotal' => $subtotal,
            'descuento_tipo' => $descuentoTipo,
            'descuento_valor' => $descuentoValor,
            'descuento' => $descuento,
            'impuesto' => $impuesto,
            'total' => $total,
            'observaciones' => trim($_POST['observaciones'] ?? ''),
            'terminos_condiciones' => trim($_POST['terminos_condiciones'] ?? ''),
            'lista_precio_id' => $listaPrecioId,
            'fecha_emision' => $_POST['fecha_emision'] ?? date('Y-m-d'),
            'fecha_vencimiento' => $_POST['fecha_vencimiento'] ?? date('Y-m-d'),
        ], $items);
        flash('success', 'Cotización actualizada correctamente.');
        $this->redirigirSegunAccion($_POST['accion'] ?? 'guardar_salir', '/app/cotizaciones/editar/' . $id, '/app/cotizaciones');
    }


    private function requerirFuncionalidadPlan(string $codigo): void
    {
        if (plan_tiene_funcionalidad_empresa_actual($codigo)) {
            return;
        }

        flash('danger', 'Tu plan no incluye esta funcionalidad: ' . $codigo . '.');
        $this->redirigir('/app/cotizaciones');
    }

    private function aplicarPrecioListaLinea(
        ServicioPreciosLista $servicioPrecios,
        int $empresaId,
        ?int $productoId,
        ?int $clienteId,
        ?int $listaPrecioId,
        float $precio,
        string $descuentoTipo,
        float $descuentoValor
    ): array {
        if ($productoId === null) {
            return [$precio, $descuentoTipo, $descuentoValor];
        }

        $precioCalculado = $servicioPrecios->calcularPrecioProducto($empresaId, $productoId, $clienteId, null, date('Y-m-d'), $listaPrecioId);
        if (!$precioCalculado) {
            return [$precio, $descuentoTipo, $descuentoValor];
        }

        $usaDescuentoLista = ($precioCalculado['ajuste_tipo'] ?? '') === 'descuento' && (float) ($precioCalculado['ajuste_porcentaje'] ?? 0) > 0;
        if ($usaDescuentoLista) {
            $precio = (float) ($precioCalculado['precio_base'] ?? 0);
            $descuentoTipo = 'porcentaje';
            $descuentoValor = (float) $precioCalculado['ajuste_porcentaje'];
        } else {
            $precio = (float) ($precioCalculado['precio_final'] ?? 0);
            $descuentoTipo = 'valor';
            $descuentoValor = 0;
        }

        return [$precio, $descuentoTipo, $descuentoValor];
    }

    private function redirigirSegunAccion(string $accion, string $rutaMantener, string $rutaSalir): void
    {
        if ($accion === 'guardar') {
            $this->redirigir($rutaMantener);
        }
        $this->redirigir($rutaSalir);
    }

    private function filtrarCotizaciones(array $cotizaciones, string $buscar, string $estado, ?int $clienteId, string $fechaDesde, string $fechaHasta): array
    {
        return array_values(array_filter($cotizaciones, static function (array $cotizacion) use ($buscar, $estado, $clienteId, $fechaDesde, $fechaHasta): bool {
            if ($buscar !== '') {
                $texto = strtolower($buscar);
                $matchBuscar = str_contains(strtolower((string) ($cotizacion['numero'] ?? '')), $texto)
                    || str_contains(strtolower((string) ($cotizacion['cliente'] ?? '')), $texto)
                    || str_contains(strtolower((string) ($cotizacion['vendedor'] ?? '')), $texto);
                if (!$matchBuscar) {
                    return false;
                }
            }

            if ($estado !== '' && strtolower((string) ($cotizacion['estado'] ?? '')) !== strtolower($estado)) {
                return false;
            }

            if ($clienteId !== null && (int) ($cotizacion['cliente_id'] ?? 0) !== $clienteId) {
                return false;
            }

            $fechaEmision = (string) ($cotizacion['fecha_emision'] ?? '');
            if ($fechaDesde !== '' && $fechaEmision !== '' && $fechaEmision < $fechaDesde) {
                return false;
            }
            if ($fechaHasta !== '' && $fechaEmision !== '' && $fechaEmision > $fechaHasta) {
                return false;
            }

            return true;
        }));
    }

    private function escapeExcelHtml(mixed $valor): string
    {
        $texto = trim(str_replace(["\r\n", "\r", "\n", "\t"], ' ', (string) $valor));
        if ($texto !== '' && preg_match('/^[=+\-@]/', $texto) === 1) {
            $texto = "'" . $texto;
        }
        return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
    }

    private function renderizarPlantillaCorreo(string $template, array $variables): string
    {
        $reemplazos = [];
        foreach ($variables as $clave => $valor) {
            $reemplazos[$clave] = htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
        }
        return strtr($template, $reemplazos);
    }

    private function construirPlantillaCorreoCotizacion(array $cotizacion, array $empresa, string $urlPublica, string $urlPdf, string $clienteNombre = ''): string
    {
        $empresaNombre = htmlspecialchars((string) ($empresa['nombre_comercial'] ?? $empresa['razon_social'] ?? 'Tu empresa'), ENT_QUOTES, 'UTF-8');
        $cliente = htmlspecialchars($clienteNombre !== '' ? $clienteNombre : (string) ($cotizacion['cliente'] ?? 'cliente'), ENT_QUOTES, 'UTF-8');
        $numero = htmlspecialchars((string) ($cotizacion['numero'] ?? ''), ENT_QUOTES, 'UTF-8');
        $fechaVencimiento = htmlspecialchars((string) ($cotizacion['fecha_vencimiento'] ?? ''), ENT_QUOTES, 'UTF-8');
        $total = number_format((float) ($cotizacion['total'] ?? 0), 2, ',', '.');
        $urlPublicaEsc = htmlspecialchars($urlPublica, ENT_QUOTES, 'UTF-8');
        $urlPdfEsc = htmlspecialchars($urlPdf, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<div style="font-family:Arial,Helvetica,sans-serif;background:#f5f7fb;padding:24px;color:#111827;">
  <div style="max-width:680px;margin:0 auto;background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">
    <div style="background:#0f3d77;color:#ffffff;padding:20px 24px;">
      <h2 style="margin:0;font-size:20px;">{$empresaNombre}</h2>
      <p style="margin:6px 0 0;font-size:13px;opacity:.9;">Envío automático de cotización</p>
    </div>
    <div style="padding:24px;">
      <p style="margin:0 0 14px;">Hola <strong>{$cliente}</strong>,</p>
      <p style="margin:0 0 14px;line-height:1.5;">Adjuntamos la cotización <strong>{$numero}</strong> por un total de <strong>\${$total}</strong>, con vigencia hasta el <strong>{$fechaVencimiento}</strong>.</p>
      <p style="margin:0 0 20px;line-height:1.5;">Puedes revisarla en línea y registrar tu decisión desde el siguiente botón:</p>
      <p style="margin:0 0 18px;">
        <a href="{$urlPublicaEsc}" style="display:inline-block;background:#0f3d77;color:#ffffff;text-decoration:none;padding:12px 18px;border-radius:8px;font-weight:600;">Ver, aceptar o rechazar cotización</a>
      </p>
      <p style="margin:0 0 8px;font-size:13px;color:#4b5563;">También puedes descargar el PDF directamente:</p>
      <p style="margin:0 0 20px;font-size:13px;"><a href="{$urlPdfEsc}" style="color:#0f3d77;">{$urlPdfEsc}</a></p>
      <p style="margin:0;font-size:12px;color:#6b7280;">Este correo fue generado automáticamente por el sistema de cotizaciones.</p>
    </div>
  </div>
</div>
HTML;
    }

    private function construirUrlPublica(string $tokenPublico): string
    {
        return $this->construirUrlDominio('/cotizacion/publica/' . $tokenPublico);
    }

    private function construirUrlInterna(string $ruta): string
    {
        $config = require __DIR__ . '/../../../configuracion/aplicacion.php';
        $base = rtrim((string) ($config['url'] ?? ''), '/');
        if ($base === '') {
            $esHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
            $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost:8000');
            $base = ($esHttps ? 'https://' : 'http://') . $host;
        }
        return $base . url($ruta);
    }

    private function construirUrlDominio(string $ruta): string
    {
        $config = require __DIR__ . '/../../../configuracion/aplicacion.php';
        $base = rtrim((string) ($config['url'] ?? ''), '/');
        if ($base === '' || preg_match('/localhost|127\\.0\\.0\\.1/i', $base)) {
            $base = 'https://vextra.cl';
        }
        $base = preg_replace('#/(public|app)$#i', '', $base) ?? $base;
        return rtrim($base, '/') . '/' . ltrim($ruta, '/');
    }

    private function generarPdfCotizacion(array $cotizacion, array $empresa): string
    {
        $clienteNombre = trim((string) (($cotizacion['cliente_razon_social'] ?? '') !== '' ? $cotizacion['cliente_razon_social'] : ($cotizacion['cliente'] ?? '')));
        $items = $cotizacion['items'] ?? [];
        $descuentoMontoTotal = (float) ($cotizacion['descuento'] ?? 0);
        $descuentoTexto = (($cotizacion['descuento_tipo'] ?? 'valor') === 'porcentaje')
            ? number_format((float) ($cotizacion['descuento_valor'] ?? 0), 2) . '% ($' . number_format($descuentoMontoTotal, 0, ',', '.') . ')'
            : '$' . number_format($descuentoMontoTotal, 0, ',', '.');
        $descuentoListaMonto = 0.0;
        foreach ($items as $itemDescuento) {
            $descuentoListaMonto += (float) ($itemDescuento['descuento_monto'] ?? 0);
        }
        $listaNombre = trim((string) ($cotizacion['lista_precio_nombre'] ?? ''));
        if ($listaNombre === '' && (int) ($cotizacion['lista_precio_id'] ?? 0) > 0) {
            $lista = (new ServicioPreciosLista())->resolverListaPrecio(
                (int) ($cotizacion['empresa_id'] ?? empresa_actual_id()),
                (int) ($cotizacion['cliente_id'] ?? 0) ?: null,
                null,
                (string) ($cotizacion['fecha_emision'] ?? date('Y-m-d')),
                (int) $cotizacion['lista_precio_id']
            );
            $listaNombre = trim((string) ($lista['nombre'] ?? ''));
        }
        $neto = max(0, (float) ($cotizacion['subtotal'] ?? 0) - $descuentoMontoTotal);

        $c = [];
        $c[] = '0.95 0.96 0.98 rg 0 0 612 792 re f';
        $c[] = '1 1 1 rg 26 26 560 740 re f';
        $c[] = '0.12 0.31 0.47 RG 2 w 26 695 m 586 695 l S';

        $c[] = 'BT /F1 20 Tf 0.12 0.31 0.47 rg 40 742 Td (' . $this->pdfEsc($empresa['nombre_comercial'] ?? $empresa['razon_social'] ?? 'Comercial') . ') Tj ET';
        $c[] = 'BT /F1 11 Tf 0.12 0.31 0.47 rg 430 748 Td (COTIZACION) Tj ET';
        $c[] = 'BT /F1 9 Tf 0 0 0 rg 430 734 Td (N\\260: ' . $this->pdfEsc((string) ($cotizacion['numero'] ?? '')) . ') Tj ET';
        $c[] = 'BT /F1 9 Tf 0 0 0 rg 430 720 Td (Fecha: ' . $this->pdfEsc((string) ($cotizacion['fecha_emision'] ?? '')) . ') Tj ET';
        $c[] = 'BT /F1 9 Tf 0 0 0 rg 430 706 Td (Validez: ' . $this->pdfEsc((string) ($cotizacion['fecha_vencimiento'] ?? '')) . ') Tj ET';

        $c[] = 'BT /F1 9 Tf 0 0 0 rg 40 724 Td (RUT: ' . $this->pdfEsc((string) ($empresa['identificador_fiscal'] ?? '')) . ') Tj ET';
        $c[] = 'BT /F1 9 Tf 0 0 0 rg 40 710 Td (' . $this->pdfEsc(trim((string) (($empresa['direccion'] ?? '') . ', ' . ($empresa['ciudad'] ?? '')))) . ') Tj ET';
        $c[] = 'BT /F1 9 Tf 0 0 0 rg 40 696 Td (Telefono: ' . $this->pdfEsc((string) ($empresa['telefono'] ?? '')) . ') Tj ET';
        $c[] = 'BT /F1 9 Tf 0 0 0 rg 40 682 Td (Correo: ' . $this->pdfEsc((string) ($empresa['correo'] ?? '')) . ') Tj ET';

        $c[] = 'BT /F1 10 Tf 0.12 0.31 0.47 rg 40 668 Td (Datos del cliente) Tj ET';
        $c[] = 'BT /F1 9 Tf 0 0 0 rg 40 652 Td (Cliente: ' . $this->pdfEsc($clienteNombre) . ') Tj ET';
        $c[] = 'BT /F1 9 Tf 0 0 0 rg 300 652 Td (RUT: ' . $this->pdfEsc((string) ($cotizacion['cliente_identificador_fiscal'] ?? '')) . ') Tj ET';
        $c[] = 'BT /F1 9 Tf 0 0 0 rg 40 638 Td (Contacto: ' . $this->pdfEsc((string) ($cotizacion['cliente'] ?? '')) . ') Tj ET';
        $c[] = 'BT /F1 9 Tf 0 0 0 rg 300 638 Td (Correo: ' . $this->pdfEsc((string) ($cotizacion['cliente_correo'] ?? '')) . ') Tj ET';
        $c[] = 'BT /F1 9 Tf 0 0 0 rg 40 624 Td (Telefono: ' . $this->pdfEsc((string) ($cotizacion['cliente_telefono'] ?? '')) . ') Tj ET';
        $c[] = 'BT /F1 9 Tf 0 0 0 rg 300 624 Td (Direccion: ' . $this->pdfEsc(trim((string) (($cotizacion['cliente_direccion'] ?? '') . ', ' . ($cotizacion['cliente_ciudad'] ?? '')))) . ') Tj ET';
        if ($listaNombre !== '' && $descuentoListaMonto > 0) {
            $c[] = 'BT /F1 9 Tf 0.18 0.55 0.35 rg 40 608 Td (Descuento por lista aplicado: ' . $this->pdfEsc($listaNombre) . ' - $' . $this->pdfEsc(number_format($descuentoListaMonto, 0, ',', '.')) . ') Tj ET';
        }

        $c[] = '0.12 0.31 0.47 rg 40 594 532 18 re f';
        $headers = [['Codigo', 44], ['Descripcion', 100], ['Cant.', 345], ['Unidad', 390], ['P. Unitario', 450], ['Total', 520]];
        foreach ($headers as [$txt, $x]) {
            $c[] = 'BT /F1 8 Tf 1 1 1 rg ' . $x . ' 600 Td (' . $this->pdfEsc($txt) . ') Tj ET';
        }

        $y = 578;
        foreach ($items as $item) {
            if ($y < 430) {
                break;
            }
            $c[] = '0.86 0.89 0.92 RG 0.5 w 40 ' . ($y - 2) . ' 532 20 re S';
            $c[] = 'BT /F1 8 Tf 0 0 0 rg 44 ' . ($y + 6) . ' Td (' . $this->pdfEsc((string) ($item['codigo'] ?? ('ITM-' . (string) ($item['id'] ?? '')))) . ') Tj ET';
            $nombreProducto = trim((string) ($item['producto_nombre'] ?? ''));
            $detalleItem = trim((string) ($item['descripcion'] ?? ''));
            if ($nombreProducto !== '' && $detalleItem !== '') {
                $detallePdf = $nombreProducto . ' - ' . $detalleItem;
            } elseif ($nombreProducto !== '') {
                $detallePdf = $nombreProducto;
            } else {
                $detallePdf = $detalleItem;
            }
            $c[] = 'BT /F1 8 Tf 0 0 0 rg 100 ' . ($y + 6) . ' Td (' . $this->pdfEsc($detallePdf) . ') Tj ET';
            $c[] = 'BT /F1 8 Tf 0 0 0 rg 348 ' . ($y + 6) . ' Td (' . $this->pdfEsc(number_format((float) ($item['cantidad'] ?? 0), 2)) . ') Tj ET';
            $c[] = 'BT /F1 8 Tf 0 0 0 rg 392 ' . ($y + 6) . ' Td (' . $this->pdfEsc((string) ($item['unidad'] ?? 'Unidad')) . ') Tj ET';
            $c[] = 'BT /F1 8 Tf 0 0 0 rg 452 ' . ($y + 6) . ' Td ($' . $this->pdfEsc(number_format((float) ($item['precio_unitario'] ?? 0), 0, ',', '.')) . ') Tj ET';
            $c[] = 'BT /F1 8 Tf 0 0 0 rg 522 ' . ($y + 6) . ' Td ($' . $this->pdfEsc(number_format((float) ($item['total'] ?? 0), 0, ',', '.')) . ') Tj ET';
            $y -= 20;
        }

        $totY = 360;
        $rows = [
            ['Subtotal', '$' . number_format((float) ($cotizacion['subtotal'] ?? 0), 0, ',', '.')],
            ['Descuento', '- ' . $descuentoTexto],
            ['Neto', '$' . number_format($neto, 0, ',', '.')],
            ['IVA (19%)', '$' . number_format((float) ($cotizacion['impuesto'] ?? 0), 0, ',', '.')],
        ];
        if ($listaNombre !== '' && $descuentoListaMonto > 0) {
            array_splice($rows, 2, 0, [['Desc. por lista', '- $' . number_format($descuentoListaMonto, 0, ',', '.')]]);
        }
        foreach ($rows as $i => [$label, $value]) {
            $yy = $totY - ($i * 20);
            $c[] = '0.86 0.89 0.92 RG 0.5 w 330 ' . $yy . ' 242 20 re S';
            $c[] = 'BT /F1 8 Tf 0 0 0 rg 338 ' . ($yy + 7) . ' Td (' . $this->pdfEsc($label) . ') Tj ET';
            $c[] = 'BT /F1 8 Tf 0 0 0 rg 500 ' . ($yy + 7) . ' Td (' . $this->pdfEsc($value) . ') Tj ET';
        }
        $c[] = '0.12 0.31 0.47 rg 330 280 242 22 re f';
        $c[] = 'BT /F1 9 Tf 1 1 1 rg 338 288 Td (Total) Tj ET';
        $c[] = 'BT /F1 9 Tf 1 1 1 rg 500 288 Td ($' . $this->pdfEsc(number_format((float) ($cotizacion['total'] ?? 0), 0, ',', '.')) . ') Tj ET';

        $c[] = 'BT /F1 10 Tf 0.12 0.31 0.47 rg 40 258 Td (Observaciones) Tj ET';
        $c[] = '0.97 0.98 0.99 rg 40 210 532 40 re f';
        $c[] = '0.12 0.31 0.47 RG 2 w 40 210 m 40 250 l S';
        $c[] = 'BT /F1 8 Tf 0 0 0 rg 50 236 Td (' . $this->pdfEsc((string) ($cotizacion['observaciones'] ?? '')) . ') Tj ET';

        $c[] = 'BT /F1 10 Tf 0.12 0.31 0.47 rg 40 190 Td (Terminos y condiciones) Tj ET';
        $ty = 176;
        foreach (preg_split('/\\r\\n|\\r|\\n/', trim((string) ($cotizacion['terminos_condiciones'] ?? ''))) as $term) {
            if (trim($term) === '' || $ty < 110) {
                continue;
            }
            $c[] = 'BT /F1 8 Tf 0 0 0 rg 46 ' . $ty . ' Td (- ' . $this->pdfEsc(trim($term)) . ') Tj ET';
            $ty -= 12;
        }

        $c[] = '0.3 0.35 0.4 RG 1 w 70 78 m 260 78 l S';
        $c[] = '0.3 0.35 0.4 RG 1 w 350 78 m 540 78 l S';
        $c[] = 'BT /F1 9 Tf 0 0 0 rg 130 66 Td (' . $this->pdfEsc((string) ($cotizacion['vendedor'] ?? '')) . ') Tj ET';
        $c[] = 'BT /F1 8 Tf 0 0 0 rg 130 54 Td (Ejecutivo Comercial) Tj ET';
        $c[] = 'BT /F1 9 Tf 0 0 0 rg 420 66 Td (' . $this->pdfEsc((string) ($cotizacion['cliente'] ?? '')) . ') Tj ET';
        $c[] = 'BT /F1 8 Tf 0 0 0 rg 410 54 Td (Aceptacion cliente) Tj ET';
        $c[] = 'BT /F1 7 Tf 0.4 0.45 0.5 rg 210 36 Td (Documento generado automaticamente por el sistema de cotizaciones.) Tj ET';

        return $this->crearPdfTexto($c);
    }

    private function crearPdfTexto(array $comandos): string
    {
        $contenido = implode("\n", $comandos);

        $objetos = [];
        $objetos[] = "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj";
        $objetos[] = "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj";
        $objetos[] = "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >> endobj";
        $objetos[] = "4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj";
        $objetos[] = "5 0 obj << /Length " . strlen($contenido) . " >> stream\n" . $contenido . "\nendstream endobj";

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objetos as $obj) {
            $offsets[] = strlen($pdf);
            $pdf .= $obj . "\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objetos) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($objetos); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= "trailer << /Size " . (count($objetos) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n" . $xref . "\n%%EOF";

        return $pdf;
    }

    private function pdfEsc(string $texto): string
    {
        $t = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $texto) ?: $texto;
        $t = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $t);
        return preg_replace('/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]/', '', $t) ?? '';
    }

    public function eliminar(int $id): void
    {
        validar_csrf();
        (new Cotizacion())->eliminar(empresa_actual_id(), $id);
        flash('success', 'Cotización eliminada correctamente.');
        $this->redirigir('/app/cotizaciones');
    }
}
