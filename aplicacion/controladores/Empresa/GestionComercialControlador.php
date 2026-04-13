<?php

namespace Aplicacion\Controladores\Empresa;

use Aplicacion\Nucleo\Controlador;
use Aplicacion\Modelos\GestionComercial;
use Aplicacion\Modelos\Cliente;
use Aplicacion\Modelos\Producto;
use Aplicacion\Modelos\Cotizacion;
use Aplicacion\Modelos\Plan;
use Aplicacion\Modelos\Suscripcion;
use Aplicacion\Modelos\Usuario;
use Aplicacion\Servicios\FlowApiService;
use Aplicacion\Servicios\ExcelExpoFormato;
use Aplicacion\Servicios\FlowPagosService;
use Aplicacion\Servicios\ServicioPreciosLista;

class GestionComercialControlador extends Controlador
{
    private GestionComercial $modelo;

    public function __construct()
    {
        $this->modelo = new GestionComercial();
    }

    public function inicio(): void
    {
        $empresaId = empresa_actual_id();
        $resumen = $this->modelo->estadisticasInicio($empresaId);
        $clienteModel = new Cliente();
        $productoModel = new Producto();
        $cotizacionModel = new Cotizacion();
        $planEmpresa = (new Plan())->obtenerPlanActivoEmpresa($empresaId);
        $planId = (int) ($planEmpresa['plan_id'] ?? 0);
        $planActual = $planId > 0 ? (new Plan())->buscar($planId) : null;
        $resumen = array_merge($resumen, [
            'total_clientes' => $clienteModel->contar($empresaId),
            'total_productos' => $productoModel->contar($empresaId),
            'total_cotizaciones' => $cotizacionModel->contar($empresaId),
            'plan_actual' => $planEmpresa['plan_id'] ?? null,
            'plan_actual_nombre' => $planActual['nombre'] ?? null,
            'estado_suscripcion' => $planEmpresa['estado'] ?? null,
            'fecha_vencimiento' => $planEmpresa['fecha_vencimiento'] ?? null,
            'dias_restantes_plan' => isset($planEmpresa['fecha_vencimiento']) ? (int) floor((strtotime((string) $planEmpresa['fecha_vencimiento']) - strtotime(date('Y-m-d'))) / 86400) : null,
        ]);
        $cotizaciones = $cotizacionModel->listar($empresaId);
        $this->vista('empresa/panel', compact('resumen', 'cotizaciones'), 'empresa');
    }

    public function iniciarPagoPlanTrial(): void
    {
        validar_csrf();

        $empresaId = empresa_actual_id();
        $suscripcion = (new Suscripcion())->obtenerUltimaPorEmpresa($empresaId);
        if (!$suscripcion) {
            flash('danger', 'No encontramos una suscripción activa para iniciar el pago del plan.');
            $this->redirigir('/app/panel');
        }

        $planId = (int) ($suscripcion['plan_id'] ?? 0);
        if ($planId <= 0) {
            flash('danger', 'No fue posible identificar el plan asociado a tu cuenta.');
            $this->redirigir('/app/panel');
        }

        $observaciones = mb_strtolower((string) ($suscripcion['observaciones'] ?? ''));
        $tipoCobro = str_contains($observaciones, '(anual)') ? 'anual' : 'mensual';

        try {
            $urlRetornoPago = FlowApiService::construirUrlPublica('/retorno/pago?origen=trial_pago');
            $urlWebhookPago = FlowApiService::construirUrlPublica('/flow/webhook/payment-confirmation');
            $respuestaPago = (new FlowPagosService())->crearPagoUnico(
                (int) $empresaId,
                $planId,
                $tipoCobro,
                'Activación del plan al finalizar periodo de prueba',
                $urlRetornoPago,
                $urlWebhookPago,
                (int) ($suscripcion['id'] ?? 0)
            );

            if (isset($respuestaPago['url'], $respuestaPago['token'])) {
                $_SESSION['flow_pago_trial_pendiente'] = [
                    'empresa_id' => (int) $empresaId,
                    'suscripcion_id' => (int) ($suscripcion['id'] ?? 0),
                    'flow_token' => (string) $respuestaPago['token'],
                    'plan_id' => $planId,
                    'tipo_cobro' => $tipoCobro,
                    'fecha' => date('c'),
                ];
                $this->redirigir($respuestaPago['url'] . '?token=' . $respuestaPago['token']);
            }

            throw new \RuntimeException('Flow no devolvió URL/token para iniciar el pago.');
        } catch (\Throwable $e) {
            flash('danger', 'No fue posible iniciar el pago ahora. Intenta nuevamente en unos minutos. Detalle: ' . $e->getMessage());
            $this->redirigir('/app/panel');
        }
    }

    public function iniciarPagoCambioPlan(): void
    {
        validar_csrf();

        $empresaId = empresa_actual_id();
        $planId = (int) ($_POST['plan_id'] ?? 0);
        $tipoCobro = (string) ($_POST['tipo_cobro'] ?? 'mensual');
        $tipoCobro = in_array($tipoCobro, ['mensual', 'anual'], true) ? $tipoCobro : 'mensual';

        $empresa = (new \Aplicacion\Modelos\Empresa())->buscar($empresaId);
        if (!$empresa || (string) ($empresa['estado'] ?? '') !== 'vencida') {
            flash('danger', 'Esta opción solo está disponible para cuentas vencidas.');
            $this->redirigir('/app/panel');
        }

        $plan = (new Plan())->buscar($planId);
        if (!$plan || (string) ($plan['estado'] ?? '') !== 'activo' || (int) ($plan['visible'] ?? 0) !== 1) {
            flash('danger', 'Selecciona un plan válido para continuar.');
            $this->redirigir('/app/panel');
        }

        $suscripcion = (new Suscripcion())->obtenerUltimaPorEmpresa($empresaId);
        if (!$suscripcion) {
            flash('danger', 'No encontramos una suscripción activa para iniciar el cambio de plan.');
            $this->redirigir('/app/panel');
        }

        try {
            $suscripcionActualizada = [
                'empresa_id' => (int) $empresaId,
                'plan_id' => (int) $plan['id'],
                'estado' => (string) ($suscripcion['estado'] ?? 'vencida'),
                'fecha_inicio' => (string) ($suscripcion['fecha_inicio'] ?? date('Y-m-d')),
                'fecha_vencimiento' => (string) ($suscripcion['fecha_vencimiento'] ?? date('Y-m-d')),
                'observaciones' => 'Cambio de plan solicitado desde cuenta vencida (' . $tipoCobro . ').',
            ];
            (new Suscripcion())->actualizar((int) ($suscripcion['id'] ?? 0), $suscripcionActualizada);

            $urlRetornoPago = FlowApiService::construirUrlPublica('/retorno/pago?origen=trial_pago');
            $urlWebhookPago = FlowApiService::construirUrlPublica('/flow/webhook/payment-confirmation');
            $respuestaPago = (new FlowPagosService())->crearPagoUnico(
                (int) $empresaId,
                (int) $plan['id'],
                $tipoCobro,
                'Cambio de plan y reactivación de cuenta vencida',
                $urlRetornoPago,
                $urlWebhookPago,
                (int) ($suscripcion['id'] ?? 0)
            );

            if (isset($respuestaPago['url'], $respuestaPago['token'])) {
                $_SESSION['flow_pago_trial_pendiente'] = [
                    'empresa_id' => (int) $empresaId,
                    'suscripcion_id' => (int) ($suscripcion['id'] ?? 0),
                    'flow_token' => (string) $respuestaPago['token'],
                    'plan_id' => (int) $plan['id'],
                    'tipo_cobro' => $tipoCobro,
                    'fecha' => date('c'),
                ];
                $this->redirigir($respuestaPago['url'] . '?token=' . $respuestaPago['token']);
            }

            throw new \RuntimeException('Flow no devolvió URL/token para iniciar el pago.');
        } catch (\Throwable $e) {
            flash('danger', 'No fue posible iniciar el pago del nuevo plan. Intenta nuevamente en unos minutos. Detalle: ' . $e->getMessage());
            $this->redirigir('/app/panel');
        }
    }

    public function contactos(): void
    {
        $empresaId = empresa_actual_id();
        $buscar = trim($_GET['q'] ?? '');
        $clientes = $this->obtenerClientesActivos($empresaId);
        $contactos = $this->modelo->listarContactosRegistrados($empresaId, $buscar, 100);
        $this->vista('empresa/contactos/index', compact('contactos', 'clientes', 'buscar'), 'empresa');
    }

    public function guardarContacto(): void
    {
        validar_csrf();
        $empresaId = empresa_actual_id();
        $clienteId = (int) ($_POST['cliente_id'] ?? 0);
        $cliente = (new Cliente())->obtenerPorId($empresaId, $clienteId);
        if (!$cliente) {
            flash('danger', 'Debes seleccionar un cliente registrado válido.');
            $this->redirigir('/app/contactos');
        }

        $nombre = trim($_POST['nombre'] ?? '');
        if ($nombre === '') {
            flash('danger', 'El nombre del contacto es obligatorio.');
            $this->redirigir('/app/contactos');
        }

        $this->modelo->crear('contactos_cliente', [
            'empresa_id' => $empresaId,
            'cliente_id' => $clienteId,
            'nombre' => $nombre,
            'cargo' => trim($_POST['cargo'] ?? ''),
            'correo' => trim($_POST['correo'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'celular' => trim($_POST['celular'] ?? ''),
            'es_principal' => isset($_POST['es_principal']) ? 1 : 0,
            'observaciones' => trim($_POST['observaciones'] ?? ''),
            'fecha_creacion' => date('Y-m-d H:i:s'),
        ]);
        flash('success', 'Contacto guardado correctamente.');
        $this->redirigir('/app/contactos');
    }

    public function editarContacto(int $id): void
    {
        $empresaId = empresa_actual_id();
        $contacto = $this->modelo->obtenerPorId('contactos_cliente', $empresaId, $id);
        if (!$contacto) {
            flash('danger', 'Contacto no encontrado.');
            $this->redirigir('/app/contactos');
        }

        $clientes = $this->obtenerClientesActivos($empresaId);
        if ($clientes === []) {
            flash('danger', 'No hay clientes activos registrados para editar este contacto.');
            $this->redirigir('/app/contactos');
        }

        $this->vista('empresa/contactos/editar', compact('contacto', 'clientes'), 'empresa');
    }

    public function actualizarContacto(int $id): void
    {
        validar_csrf();
        $empresaId = empresa_actual_id();
        $contacto = $this->modelo->obtenerPorId('contactos_cliente', $empresaId, $id);
        if (!$contacto) {
            flash('danger', 'Contacto no encontrado.');
            $this->redirigir('/app/contactos');
        }

        $clienteId = (int) ($_POST['cliente_id'] ?? 0);
        $cliente = (new Cliente())->obtenerPorId($empresaId, $clienteId);
        if (!$cliente || ($cliente['estado'] ?? 'activo') !== 'activo') {
            flash('danger', 'Debes seleccionar un cliente registrado activo.');
            $this->redirigir('/app/contactos/editar/' . $id);
        }

        $nombre = trim($_POST['nombre'] ?? '');
        if ($nombre === '') {
            flash('danger', 'El nombre del contacto es obligatorio.');
            $this->redirigir('/app/contactos/editar/' . $id);
        }

        $this->modelo->actualizarDinamico('contactos_cliente', $empresaId, $id, [
            'cliente_id' => $clienteId,
            'nombre' => $nombre,
            'cargo' => trim($_POST['cargo'] ?? ''),
            'correo' => trim($_POST['correo'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'celular' => trim($_POST['celular'] ?? ''),
            'es_principal' => isset($_POST['es_principal']) ? 1 : 0,
            'observaciones' => trim($_POST['observaciones'] ?? ''),
        ]);
        flash('success', 'Contacto actualizado correctamente.');
        $this->redirigir('/app/contactos');
    }

    public function exportarContactosExcel(): void
    {
        $empresaId = empresa_actual_id();
        $buscar = trim($_GET['q'] ?? '');
        $contactos = $this->modelo->listarContactosRegistrados($empresaId, $buscar, 5000);

        $nombreArchivo = 'contactos_' . date('Ymd_His') . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "\xEF\xBB\xBF";
        echo '<html><head><meta charset="UTF-8"></head><body>';
        echo '<table border="1" cellspacing="0" cellpadding="4" style="border-collapse:collapse;font-family:Calibri,Arial,sans-serif;font-size:11pt;">';
        echo '<tr style="background:#d9d9d9;font-weight:700;">';
        echo '<th>N°</th>';
        echo '<th>Cliente</th>';
        echo '<th>Nombre contacto</th>';
        echo '<th>Cargo</th>';
        echo '<th>Correo</th>';
        echo '<th>Teléfono</th>';
        echo '<th>Celular</th>';
        echo '<th>Principal</th>';
        echo '</tr>';

        $indice = 1;
        foreach ($contactos as $contacto) {
            echo '<tr>';
            echo '<td>' . $indice . '</td>';
            echo '<td>' . $this->escapeExcelHtml($contacto['cliente_nombre'] ?: ($contacto['cliente_razon_social'] ?? '')) . '</td>';
            echo '<td>' . $this->escapeExcelHtml($contacto['nombre'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($contacto['cargo'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($contacto['correo'] ?? '') . '</td>';
            echo '<td style="mso-number-format:\\@;">' . $this->escapeExcelHtml($contacto['telefono'] ?? '') . '</td>';
            echo '<td style="mso-number-format:\\@;">' . $this->escapeExcelHtml($contacto['celular'] ?? '') . '</td>';
            echo '<td>' . (!empty($contacto['es_principal']) ? 'Sí' : 'No') . '</td>';
            echo '</tr>';
            $indice++;
        }

        echo '</table></body></html>';

        exit;
    }


    public function exportarCategoriasExcel(): void
    {
        $empresaId = empresa_actual_id();
        $buscar = trim($_GET['q'] ?? '');
        $categorias = $this->modelo->listarTablaEmpresa('categorias_productos', $empresaId, $buscar, 5000);

        $nombreArchivo = 'categorias_' . date('Ymd_His') . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "ï»¿";
        echo '<html><head><meta charset="UTF-8"></head><body>';
        echo '<table border="1" cellspacing="0" cellpadding="4" style="' . ExcelExpoFormato::TABLA_ESTILO . '">';
        echo '<tr style="' . ExcelExpoFormato::ENCABEZADO_ESTILO . '">';
        echo '<th>N°</th>';
        echo '<th>Nombre</th>';
        echo '<th>Descripción</th>';
        echo '<th>Estado</th>';
        echo '</tr>';

        $indice = 1;
        foreach ($categorias as $categoria) {
            echo '<tr>';
            echo '<td>' . $indice . '</td>';
            echo '<td>' . $this->escapeExcelHtml($categoria['nombre'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($categoria['descripcion'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml(ucfirst((string) ($categoria['estado'] ?? 'activo'))) . '</td>';
            echo '</tr>';
            $indice++;
        }

        echo '</table></body></html>';

        exit;
    }

    public function exportarVendedoresExcel(): void
    {
        $empresaId = empresa_actual_id();
        $buscar = trim($_GET['q'] ?? '');
        $vendedores = $this->modelo->listarTablaEmpresa('vendedores', $empresaId, $buscar, 5000);

        $nombreArchivo = 'vendedores_' . date('Ymd_His') . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "\xEF\xBB\xBF";
        echo '<html><head><meta charset="UTF-8"></head><body>';
        echo '<table border="1" cellspacing="0" cellpadding="4" style="' . ExcelExpoFormato::TABLA_ESTILO . '">';
        echo '<tr style="' . ExcelExpoFormato::ENCABEZADO_ESTILO . '">';
        echo '<th>N°</th>';
        echo '<th>Nombre</th>';
        echo '<th>Correo</th>';
        echo '<th>Teléfono</th>';
        echo '<th>Comisión %</th>';
        echo '<th>Estado</th>';
        echo '</tr>';

        $indice = 1;
        foreach ($vendedores as $vendedor) {
            echo '<tr>';
            echo '<td>' . $indice . '</td>';
            echo '<td>' . $this->escapeExcelHtml($vendedor['nombre'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($vendedor['correo'] ?? '') . '</td>';
            echo '<td style="' . ExcelExpoFormato::CELDA_TEXTO_EXCEL . '">' . $this->escapeExcelHtml($vendedor['telefono'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml(number_format((float) ($vendedor['comision'] ?? 0), 2)) . '</td>';
            echo '<td>' . $this->escapeExcelHtml(ucfirst((string) ($vendedor['estado'] ?? 'activo'))) . '</td>';
            echo '</tr>';
            $indice++;
        }

        echo '</table></body></html>';

        exit;
    }

    public function exportarListasPreciosExcel(): void
    {
        $empresaId = empresa_actual_id();
        $buscar = trim($_GET['q'] ?? '');
        $listas = $this->modelo->listarTablaEmpresa('listas_precios', $empresaId, $buscar, 5000);

        $nombreArchivo = 'listas_precios_' . date('Ymd_His') . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "\xEF\xBB\xBF";
        echo '<html><head><meta charset="UTF-8"></head><body>';
        echo '<table border="1" cellspacing="0" cellpadding="4" style="' . ExcelExpoFormato::TABLA_ESTILO . '">';
        echo '<tr style="' . ExcelExpoFormato::ENCABEZADO_ESTILO . '">';
        echo '<th>N°</th>';
        echo '<th>Nombre</th>';
        echo '<th>Vigencia desde</th>';
        echo '<th>Vigencia hasta</th>';
        echo '<th>Tipo de lista</th>';
        echo '<th>Canal de venta</th>';
        echo '<th>Tipo de ajuste</th>';
        echo '<th>Ajuste %</th>';
        echo '<th>Estado</th>';
        echo '<th>Reglas base</th>';
        echo '</tr>';

        $indice = 1;
        foreach ($listas as $lista) {
            echo '<tr>';
            echo '<td>' . $indice . '</td>';
            echo '<td>' . $this->escapeExcelHtml($lista['nombre'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($lista['vigencia_desde'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($lista['vigencia_hasta'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($lista['tipo_lista'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($lista['canal_venta'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($lista['ajuste_tipo'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml(number_format((float) ($lista['ajuste_porcentaje'] ?? 0), 0)) . '</td>';
            echo '<td>' . $this->escapeExcelHtml(ucfirst((string) ($lista['estado'] ?? 'activo'))) . '</td>';
            echo '<td>' . $this->escapeExcelHtml($lista['reglas_base'] ?? '') . '</td>';
            echo '</tr>';
            $indice++;
        }

        echo '</table></body></html>';

        exit;
    }

    private function escapeExcelHtml(mixed $valor): string
    {
        $texto = trim(str_replace(["\r\n", "\r", "\n", "\t"], ' ', (string) $valor));

        if ($texto !== '' && preg_match('/^[=+\-@]/', $texto) === 1) {
            $texto = "'" . $texto;
        }

        return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
    }

    private function obtenerClientesActivos(int $empresaId): array
    {
        return array_values(array_filter((new Cliente())->listar($empresaId), static function (array $cliente): bool {
            return ($cliente['estado'] ?? 'activo') === 'activo';
        }));
    }

    public function moduloBase(string $modulo): void
    {
        $mapeo = [
            'vendedores' => ['tabla' => 'vendedores', 'vista' => 'empresa/modulos/vendedores', 'titulo' => 'Vendedores'],
            'categorias' => ['tabla' => 'categorias_productos', 'vista' => 'empresa/modulos/categorias', 'titulo' => 'Categorías'],
            'listas-precios' => ['tabla' => 'listas_precios', 'vista' => 'empresa/modulos/listas_precios', 'titulo' => 'Listas de precios'],
            'seguimiento' => ['tabla' => 'seguimientos_comerciales', 'vista' => 'empresa/modulos/seguimiento', 'titulo' => 'Seguimiento comercial'],
            'aprobaciones' => ['tabla' => 'aprobaciones_cotizacion', 'vista' => 'empresa/modulos/aprobaciones', 'titulo' => 'Aprobaciones'],
            'notificaciones' => ['tabla' => 'notificaciones_empresa', 'vista' => 'empresa/modulos/notificaciones', 'titulo' => 'Notificaciones'],
            'historial' => ['tabla' => 'historial_actividad', 'vista' => 'empresa/modulos/historial', 'titulo' => 'Historial de actividad'],
        ];

        if (!isset($mapeo[$modulo])) {
            http_response_code(404);
            echo 'Módulo no encontrado';
            return;
        }

        $buscar = trim($_GET['q'] ?? '');
        $empresaId = empresa_actual_id();
        $def = $mapeo[$modulo];
        $registros = $this->modelo->listarTablaEmpresa($def['tabla'], $empresaId, $buscar, 40);
        $clientes = (new Cliente())->listar($empresaId);
        $cotizaciones = (new Cotizacion())->listar($empresaId);
        $usuarios = (new Usuario())->listarPorEmpresa($empresaId);

        if ($modulo === 'vendedores') {
            $usuariosPorId = [];
            foreach ($usuarios as $usuario) {
                $usuariosPorId[(int) $usuario['id']] = $usuario['nombre'] ?? '';
            }

            $registros = array_map(static function (array $registro) use ($usuariosPorId): array {
                $usuarioId = (int) ($registro['usuario_id'] ?? 0);
                $registro['usuario_nombre'] = $usuarioId > 0 ? ($usuariosPorId[$usuarioId] ?? 'Usuario no encontrado') : 'Sin usuario';
                return $registro;
            }, $registros);
        }

        $titulo = $def['titulo'];
        $this->vista($def['vista'], compact('registros', 'buscar', 'clientes', 'cotizaciones', 'usuarios', 'titulo'), 'empresa');
    }

    public function guardarModuloBase(string $modulo): void
    {
        validar_csrf();
        $empresaId = empresa_actual_id();

        if ($modulo === 'vendedores') {
            $nombre = trim($_POST['nombre'] ?? '');
            $correo = trim($_POST['correo'] ?? '');
            $estado = $_POST['estado'] ?? 'activo';
            $comision = (float) ($_POST['comision'] ?? 0);
            $usuarioId = (int) ($_POST['usuario_id'] ?? 0);

            if ($nombre === '') {
                flash('danger', 'El nombre del vendedor es obligatorio.');
                $this->redirigir('/app/vendedores');
            }

            if ($correo !== '' && filter_var($correo, FILTER_VALIDATE_EMAIL) === false) {
                flash('danger', 'El correo del vendedor no es válido.');
                $this->redirigir('/app/vendedores');
            }

            if (!in_array($estado, ['activo', 'inactivo'], true)) {
                $estado = 'activo';
            }

            if ($comision < 0) {
                $comision = 0;
            }

            if ($comision > 100) {
                $comision = 100;
            }

            $usuarioAsignado = null;
            if ($usuarioId > 0) {
                $usuarioAsignado = (new Usuario())->obtenerPorIdEmpresa($empresaId, $usuarioId);
                if (!$usuarioAsignado) {
                    flash('danger', 'El usuario seleccionado no pertenece a tu empresa.');
                    $this->redirigir('/app/vendedores');
                }
            }

            $this->modelo->crear('vendedores', [
                'empresa_id' => $empresaId,
                'nombre' => $nombre,
                'correo' => $correo,
                'telefono' => trim($_POST['telefono'] ?? ''),
                'comision' => $comision,
                'estado' => $estado,
                'usuario_id' => $usuarioAsignado ? (int) $usuarioAsignado['id'] : null,
                'fecha_creacion' => date('Y-m-d H:i:s'),
            ]);
        }

        if ($modulo === 'categorias') {
            $this->modelo->crear('categorias_productos', [
                'empresa_id' => $empresaId,
                'nombre' => trim($_POST['nombre'] ?? ''),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'estado' => $_POST['estado'] ?? 'activo',
                'fecha_creacion' => date('Y-m-d H:i:s'),
            ]);
        }

        if ($modulo === 'listas-precios') {
            $nombre = trim($_POST['nombre'] ?? '');
            $vigenciaDesde = $_POST['vigencia_desde'] ?: null;
            $vigenciaHasta = $_POST['vigencia_hasta'] ?: null;
            $tipoLista = trim($_POST['tipo_lista'] ?? 'general');
            $canalVenta = trim($_POST['canal_venta'] ?? '');
            $ajusteTipo = ($_POST['ajuste_tipo'] ?? 'incremento') === 'descuento' ? 'descuento' : 'incremento';
            $ajustePorcentaje = max(0, (float) ($_POST['ajuste_porcentaje'] ?? 0));
            $estado = $_POST['estado'] ?? 'activo';

            if ($nombre === '') {
                flash('danger', 'El nombre de la lista de precios es obligatorio.');
                $this->redirigir('/app/listas-precios');
            }

            if (!in_array($estado, ['activo', 'inactivo'], true)) {
                $estado = 'activo';
            }

            if ($vigenciaDesde !== null && $vigenciaHasta !== null && $vigenciaHasta < $vigenciaDesde) {
                flash('danger', 'La vigencia hasta no puede ser anterior a la vigencia desde.');
                $this->redirigir('/app/listas-precios');
            }

            $this->modelo->crear('listas_precios', [
                'empresa_id' => $empresaId,
                'nombre' => $nombre,
                'vigencia_desde' => $vigenciaDesde,
                'vigencia_hasta' => $vigenciaHasta,
                'tipo_lista' => $tipoLista !== '' ? $tipoLista : 'general',
                'canal_venta' => $canalVenta !== '' ? $canalVenta : null,
                'ajuste_tipo' => $ajusteTipo,
                'ajuste_porcentaje' => $ajustePorcentaje,
                'estado' => $estado,
                'reglas_base' => trim($_POST['reglas_base'] ?? ''),
                'fecha_creacion' => date('Y-m-d H:i:s'),
            ]);
        }

        if ($modulo === 'seguimiento') {
            $this->modelo->crear('seguimientos_comerciales', [
                'empresa_id' => $empresaId,
                'cotizacion_id' => (int) ($_POST['cotizacion_id'] ?? 0) ?: null,
                'cliente_id' => (int) ($_POST['cliente_id'] ?? 0) ?: null,
                'responsable' => trim($_POST['responsable'] ?? ''),
                'proxima_accion' => trim($_POST['proxima_accion'] ?? ''),
                'fecha_seguimiento' => $_POST['fecha_seguimiento'] ?: null,
                'comentarios' => trim($_POST['comentarios'] ?? ''),
                'estado_comercial' => $_POST['estado_comercial'] ?? 'abierto',
                'probabilidad_cierre' => (int) ($_POST['probabilidad_cierre'] ?? 0),
                'fecha_creacion' => date('Y-m-d H:i:s'),
            ]);
        }

        if ($modulo === 'aprobaciones') {
            $this->modelo->crear('aprobaciones_cotizacion', [
                'empresa_id' => $empresaId,
                'cotizacion_id' => (int) ($_POST['cotizacion_id'] ?? 0) ?: null,
                'monto' => (float) ($_POST['monto'] ?? 0),
                'motivo' => trim($_POST['motivo'] ?? ''),
                'solicitante' => trim($_POST['solicitante'] ?? ''),
                'aprobador' => trim($_POST['aprobador'] ?? ''),
                'estado' => $_POST['estado'] ?? 'pendiente',
                'fecha_aprobacion' => $_POST['fecha_aprobacion'] ?: null,
                'observaciones' => trim($_POST['observaciones'] ?? ''),
                'fecha_creacion' => date('Y-m-d H:i:s'),
            ]);
        }
        if ($modulo === 'notificaciones') {
            $this->modelo->crear('notificaciones_empresa', [
                'empresa_id' => $empresaId,
                'tipo' => trim($_POST['tipo'] ?? 'sistema'),
                'titulo' => trim($_POST['titulo'] ?? ''),
                'mensaje' => trim($_POST['mensaje'] ?? ''),
                'estado' => trim($_POST['estado'] ?? 'pendiente'),
                'fecha_evento' => $_POST['fecha_evento'] ?: null,
                'fecha_creacion' => date('Y-m-d H:i:s'),
            ]);
        }

        if ($modulo === 'historial') {
            $this->modelo->crear('historial_actividad', [
                'empresa_id' => $empresaId,
                'usuario_nombre' => usuario_actual()['nombre'] ?? 'Sistema',
                'modulo' => trim($_POST['modulo'] ?? ''),
                'accion' => trim($_POST['accion'] ?? ''),
                'detalle' => trim($_POST['detalle'] ?? ''),
                'fecha_creacion' => date('Y-m-d H:i:s'),
            ]);
        }

        flash('success', 'Registro guardado correctamente.');

        $rutaRetorno = trim($_POST['redirect_to'] ?? '');
        if ($rutaRetorno !== '' && strpos($rutaRetorno, '/app/') === 0) {
            $this->redirigir($rutaRetorno);
        }

        $this->redirigir('/app/' . $modulo);
    }

    public function seguimiento(): void
    {
        $empresaId = empresa_actual_id();
        $buscar = trim($_GET['q'] ?? '');
        $estadoCotizacion = trim($_GET['estado_cotizacion'] ?? '');
        $estadosPermitidos = ['borrador', 'enviada', 'aprobada', 'rechazada', 'vencida', 'anulada'];
        if (!in_array($estadoCotizacion, $estadosPermitidos, true)) {
            $estadoCotizacion = '';
        }

        $registros = $this->modelo->listarSeguimientoCotizaciones($empresaId, $buscar, $estadoCotizacion, 80);
        $clientes = $this->obtenerClientesActivos($empresaId);
        $cotizaciones = (new Cotizacion())->listar($empresaId);
        $usuarios = (new Usuario())->listarPorEmpresa($empresaId);

        $this->vista('empresa/modulos/seguimiento', compact('registros', 'clientes', 'cotizaciones', 'usuarios', 'buscar', 'estadoCotizacion'), 'empresa');
    }


    public function exportarSeguimientoExcel(): void
    {
        $empresaId = empresa_actual_id();
        $buscar = trim($_GET['q'] ?? '');
        $estadoCotizacion = trim($_GET['estado_cotizacion'] ?? '');
        $estadosPermitidos = ['borrador', 'enviada', 'aprobada', 'rechazada', 'vencida', 'anulada'];
        if (!in_array($estadoCotizacion, $estadosPermitidos, true)) {
            $estadoCotizacion = '';
        }

        $registros = $this->modelo->listarSeguimientoCotizaciones($empresaId, $buscar, $estadoCotizacion, 5000);

        $nombreArchivo = 'seguimientos_' . date('Ymd_His') . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "ï»¿";
        echo '<html><head><meta charset="UTF-8"></head><body>';
        echo '<table border="1" cellspacing="0" cellpadding="4" style="' . ExcelExpoFormato::TABLA_ESTILO . '">';
        echo '<tr style="' . ExcelExpoFormato::ENCABEZADO_ESTILO . '">';
        echo '<th>N°</th>';
        echo '<th>Fecha seguimiento</th>';
        echo '<th>Cotización</th>';
        echo '<th>Cliente</th>';
        echo '<th>Estado cotización</th>';
        echo '<th>Estado comercial</th>';
        echo '<th>Probabilidad %</th>';
        echo '<th>Responsable</th>';
        echo '<th>Próxima acción</th>';
        echo '<th>Comentarios</th>';
        echo '</tr>';

        $indice = 1;
        foreach ($registros as $fila) {
            echo '<tr>';
            echo '<td>' . $indice . '</td>';
            echo '<td>' . $this->escapeExcelHtml($fila['fecha_seguimiento'] ?? '') . '</td>';
            echo '<td style="' . ExcelExpoFormato::CELDA_TEXTO_EXCEL . '">' . $this->escapeExcelHtml($fila['cotizacion_numero'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($fila['cliente_nombre'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($fila['cotizacion_estado'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($fila['estado_comercial'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml((string) ($fila['probabilidad_cierre'] ?? 0)) . '</td>';
            echo '<td>' . $this->escapeExcelHtml($fila['responsable'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($fila['proxima_accion'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($fila['comentarios'] ?? '') . '</td>';
            echo '</tr>';
            $indice++;
        }

        echo '</table></body></html>';
        exit;
    }

    public function guardarSeguimiento(): void
    {
        validar_csrf();
        $empresaId = empresa_actual_id();
        $cotizacionId = (int) ($_POST['cotizacion_id'] ?? 0);
        $clienteId = (int) ($_POST['cliente_id'] ?? 0);
        $responsable = trim($_POST['responsable'] ?? '');
        $estadosPermitidos = ['borrador', 'enviada', 'aprobada', 'rechazada', 'vencida', 'anulada'];

        if ($cotizacionId <= 0) {
            flash('danger', 'Debes seleccionar una cotización para registrar seguimiento.');
            $this->redirigir('/app/seguimiento');
        }

        $cotizacion = (new Cotizacion())->obtenerPorId($empresaId, $cotizacionId);
        if (!$cotizacion) {
            flash('danger', 'La cotización seleccionada no existe o no pertenece a tu empresa.');
            $this->redirigir('/app/seguimiento');
        }

        if ($clienteId <= 0) {
            $clienteId = (int) ($cotizacion['cliente_id'] ?? 0);
        }

        if ($clienteId <= 0) {
            flash('danger', 'No fue posible determinar el cliente asociado al seguimiento.');
            $this->redirigir('/app/seguimiento');
        }

        if ($responsable === '') {
            $responsable = (string) (usuario_actual()['nombre'] ?? 'Sin responsable');
        }

        $probabilidad = (int) ($_POST['probabilidad_cierre'] ?? 0);
        if ($probabilidad < 0) {
            $probabilidad = 0;
        }
        if ($probabilidad > 100) {
            $probabilidad = 100;
        }

        $this->modelo->crear('seguimientos_comerciales', [
            'empresa_id' => $empresaId,
            'cotizacion_id' => $cotizacionId,
            'cliente_id' => $clienteId,
            'responsable' => $responsable,
            'proxima_accion' => trim($_POST['proxima_accion'] ?? ''),
            'fecha_seguimiento' => $_POST['fecha_seguimiento'] ?: null,
            'comentarios' => trim($_POST['comentarios'] ?? ''),
            'estado_comercial' => trim($_POST['estado_comercial'] ?? 'abierto'),
            'probabilidad_cierre' => $probabilidad,
            'fecha_creacion' => date('Y-m-d H:i:s'),
        ]);

        $nuevoEstado = trim($_POST['nuevo_estado_cotizacion'] ?? '');
        if ($nuevoEstado !== '' && in_array($nuevoEstado, $estadosPermitidos, true) && $nuevoEstado !== (string) ($cotizacion['estado'] ?? '')) {
            (new Cotizacion())->actualizarEstadoConHistorial(
                $empresaId,
                $cotizacionId,
                $nuevoEstado,
                (int) (usuario_actual()['id'] ?? 0),
                trim($_POST['comentarios'] ?? '')
            );
        }

        flash('success', 'Seguimiento guardado correctamente.');
        $this->redirigir('/app/seguimiento');
    }

    public function aprobaciones(): void
    {
        $empresaId = empresa_actual_id();
        $buscar = trim($_GET['q'] ?? '');
        $estadoAprobacion = trim($_GET['estado_aprobacion'] ?? '');
        $estadosPermitidos = ['pendiente', 'aprobada', 'rechazada'];
        if (!in_array($estadoAprobacion, $estadosPermitidos, true)) {
            $estadoAprobacion = '';
        }

        $registros = $this->modelo->listarAprobacionesCotizaciones($empresaId, $buscar, $estadoAprobacion, 80);
        $cotizaciones = (new Cotizacion())->listar($empresaId);

        $this->vista('empresa/modulos/aprobaciones', compact('registros', 'cotizaciones', 'buscar', 'estadoAprobacion'), 'empresa');
    }


    public function exportarAprobacionesExcel(): void
    {
        $empresaId = empresa_actual_id();
        $buscar = trim($_GET['q'] ?? '');
        $estadoAprobacion = trim($_GET['estado_aprobacion'] ?? '');
        $estadosPermitidos = ['pendiente', 'aprobada', 'rechazada'];
        if (!in_array($estadoAprobacion, $estadosPermitidos, true)) {
            $estadoAprobacion = '';
        }

        $registros = $this->modelo->listarAprobacionesCotizaciones($empresaId, $buscar, $estadoAprobacion, 5000);

        $nombreArchivo = 'aprobaciones_' . date('Ymd_His') . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "ï»¿";
        echo '<html><head><meta charset="UTF-8"></head><body>';
        echo '<table border="1" cellspacing="0" cellpadding="4" style="' . ExcelExpoFormato::TABLA_ESTILO . '">';
        echo '<tr style="' . ExcelExpoFormato::ENCABEZADO_ESTILO . '">';
        echo '<th>N°</th>';
        echo '<th>Fecha aprobación</th>';
        echo '<th>Cotización</th>';
        echo '<th>Cliente</th>';
        echo '<th>Estado cotización</th>';
        echo '<th>Monto</th>';
        echo '<th>Estado aprobación</th>';
        echo '<th>Solicitante</th>';
        echo '<th>Aprobador</th>';
        echo '<th>Motivo</th>';
        echo '<th>Observaciones</th>';
        echo '</tr>';

        $indice = 1;
        foreach ($registros as $fila) {
            echo '<tr>';
            echo '<td>' . $indice . '</td>';
            echo '<td>' . $this->escapeExcelHtml($fila['fecha_aprobacion'] ?? '') . '</td>';
            echo '<td style="' . ExcelExpoFormato::CELDA_TEXTO_EXCEL . '">' . $this->escapeExcelHtml($fila['cotizacion_numero'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($fila['cliente_nombre'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($fila['cotizacion_estado'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml(number_format((float) ($fila['monto'] ?? 0), 2)) . '</td>';
            echo '<td>' . $this->escapeExcelHtml($fila['estado'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($fila['solicitante'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($fila['aprobador'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($fila['motivo'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($fila['observaciones'] ?? '') . '</td>';
            echo '</tr>';
            $indice++;
        }

        echo '</table></body></html>';
        exit;
    }

    public function guardarAprobacion(): void
    {
        validar_csrf();
        $empresaId = empresa_actual_id();
        $cotizacionId = (int) ($_POST['cotizacion_id'] ?? 0);
        $estadosPermitidos = ['pendiente', 'aprobada', 'rechazada'];
        $estado = trim($_POST['estado'] ?? 'pendiente');

        if ($cotizacionId <= 0) {
            flash('danger', 'Debes seleccionar una cotización para registrar la aprobación.');
            $this->redirigir('/app/aprobaciones');
        }

        $cotizacion = (new Cotizacion())->obtenerPorId($empresaId, $cotizacionId);
        if (!$cotizacion) {
            flash('danger', 'La cotización seleccionada no existe o no pertenece a tu empresa.');
            $this->redirigir('/app/aprobaciones');
        }

        if (!in_array($estado, $estadosPermitidos, true)) {
            $estado = 'pendiente';
        }

        $monto = (float) ($_POST['monto'] ?? 0);
        if ($monto <= 0) {
            $monto = (float) ($cotizacion['total'] ?? 0);
        }

        $this->modelo->crear('aprobaciones_cotizacion', [
            'empresa_id' => $empresaId,
            'cotizacion_id' => $cotizacionId,
            'monto' => $monto,
            'motivo' => trim($_POST['motivo'] ?? ''),
            'solicitante' => trim($_POST['solicitante'] ?? (usuario_actual()['nombre'] ?? '')),
            'aprobador' => trim($_POST['aprobador'] ?? ''),
            'estado' => $estado,
            'fecha_aprobacion' => $_POST['fecha_aprobacion'] ?: null,
            'observaciones' => trim($_POST['observaciones'] ?? ''),
            'fecha_creacion' => date('Y-m-d H:i:s'),
        ]);

        if ($estado === 'aprobada') {
            (new Cotizacion())->actualizarEstadoConHistorial(
                $empresaId,
                $cotizacionId,
                'aprobada',
                (int) (usuario_actual()['id'] ?? 0),
                'Aprobación registrada desde módulo de aprobaciones'
            );
        }

        if ($estado === 'rechazada') {
            (new Cotizacion())->actualizarEstadoConHistorial(
                $empresaId,
                $cotizacionId,
                'rechazada',
                (int) (usuario_actual()['id'] ?? 0),
                'Rechazo registrado desde módulo de aprobaciones'
            );
        }

        flash('success', 'Aprobación registrada correctamente.');
        $this->redirigir('/app/aprobaciones');
    }
    public function verRegistro(string $modulo, int $id): void
    {
        $mapeo = $this->mapeoModulos();
        if (!isset($mapeo[$modulo])) {
            http_response_code(404);
            echo 'Módulo no encontrado';
            return;
        }
        $registro = $this->modelo->obtenerPorId($mapeo[$modulo]['tabla'], empresa_actual_id(), $id);
        if (!$registro) {
            flash('danger', 'Registro no encontrado.');
            $this->redirigir('/app/' . $modulo);
        }
        $titulo = $mapeo[$modulo]['titulo'];
        $cotizacionAprobacion = null;
        $esVistaAprobaciones = $modulo === 'aprobaciones' || mb_strtolower((string) $titulo) === 'aprobaciones';
        if ($esVistaAprobaciones || isset($registro['cotizacion_id'])) {
            $cotizacionId = (int) ($registro['cotizacion_id'] ?? 0);
            if ($cotizacionId > 0) {
                $cotizacionAprobacion = (new Cotizacion())->obtenerFirmaCliente(empresa_actual_id(), $cotizacionId);
            }
        }
        $this->vista('empresa/modulos/ver', compact('registro', 'titulo', 'modulo', 'cotizacionAprobacion'), 'empresa');
    }

    public function editarRegistro(string $modulo, int $id): void
    {
        if ($modulo === 'vendedores') {
            $this->editarVendedor($id);
            return;
        }

        if ($modulo === 'listas-precios') {
            $this->editarListaPrecios($id);
            return;
        }

        $mapeo = $this->mapeoModulos();
        if (!isset($mapeo[$modulo])) {
            http_response_code(404);
            echo 'Módulo no encontrado';
            return;
        }
        $registro = $this->modelo->obtenerPorId($mapeo[$modulo]['tabla'], empresa_actual_id(), $id);
        if (!$registro) {
            flash('danger', 'Registro no encontrado.');
            $this->redirigir('/app/' . $modulo);
        }
        $titulo = $mapeo[$modulo]['titulo'];
        $this->vista('empresa/modulos/editar', compact('registro', 'titulo', 'modulo'), 'empresa');
    }

    public function actualizarRegistro(string $modulo, int $id): void
    {
        if ($modulo === 'vendedores') {
            $this->actualizarVendedor($id);
            return;
        }

        if ($modulo === 'listas-precios') {
            $this->actualizarListaPrecios($id);
            return;
        }

        validar_csrf();
        $mapeo = $this->mapeoModulos();
        if (!isset($mapeo[$modulo])) {
            $this->redirigir('/app/panel');
        }
        $data = $_POST;
        unset($data['_csrf']);
        $this->modelo->actualizarDinamico($mapeo[$modulo]['tabla'], empresa_actual_id(), $id, $data);
        flash('success', 'Registro actualizado correctamente.');
        $this->redirigir('/app/' . $modulo);
    }

    public function editarVendedor(int $id): void
    {
        $empresaId = empresa_actual_id();
        $vendedor = $this->modelo->obtenerPorId('vendedores', $empresaId, $id);
        if (!$vendedor) {
            flash('danger', 'Vendedor no encontrado.');
            $this->redirigir('/app/vendedores');
        }

        $usuarios = (new Usuario())->listarPorEmpresa($empresaId);
        $this->vista('empresa/modulos/vendedores_editar', compact('vendedor', 'usuarios'), 'empresa');
    }

    public function actualizarVendedor(int $id): void
    {
        validar_csrf();
        $empresaId = empresa_actual_id();
        $vendedor = $this->modelo->obtenerPorId('vendedores', $empresaId, $id);
        if (!$vendedor) {
            flash('danger', 'Vendedor no encontrado.');
            $this->redirigir('/app/vendedores');
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $estado = $_POST['estado'] ?? 'activo';
        $comision = (float) ($_POST['comision'] ?? 0);
        $usuarioId = (int) ($_POST['usuario_id'] ?? 0);

        if ($nombre === '') {
            flash('danger', 'El nombre del vendedor es obligatorio.');
            $this->redirigir('/app/vendedores/editar/' . $id);
        }

        if ($correo !== '' && filter_var($correo, FILTER_VALIDATE_EMAIL) === false) {
            flash('danger', 'El correo del vendedor no es válido.');
            $this->redirigir('/app/vendedores/editar/' . $id);
        }

        if (!in_array($estado, ['activo', 'inactivo'], true)) {
            $estado = 'activo';
        }

        if ($comision < 0) {
            $comision = 0;
        }

        if ($comision > 100) {
            $comision = 100;
        }

        $usuarioAsignado = null;
        if ($usuarioId > 0) {
            $usuarioAsignado = (new Usuario())->obtenerPorIdEmpresa($empresaId, $usuarioId);
            if (!$usuarioAsignado) {
                flash('danger', 'El usuario seleccionado no pertenece a tu empresa.');
                $this->redirigir('/app/vendedores/editar/' . $id);
            }
        }

        $this->modelo->actualizarDinamico('vendedores', $empresaId, $id, [
            'nombre' => $nombre,
            'correo' => $correo,
            'telefono' => trim($_POST['telefono'] ?? ''),
            'comision' => $comision,
            'estado' => $estado,
            'usuario_id' => $usuarioAsignado ? (int) $usuarioAsignado['id'] : null,
        ]);

        flash('success', 'Vendedor actualizado correctamente.');
        $this->redirigir('/app/vendedores');
    }

    public function eliminarRegistro(string $modulo, int $id): void
    {
        validar_csrf();
        $mapeo = $this->mapeoModulos();
        if (!isset($mapeo[$modulo])) {
            $this->redirigir('/app/panel');
        }

        try {
            $this->modelo->eliminar($mapeo[$modulo]['tabla'], empresa_actual_id(), $id);
            flash('success', 'Registro eliminado correctamente.');
        } catch (\PDOException $e) {
            $codigoSql = (string) ($e->errorInfo[0] ?? '');

            if ($codigoSql === '23000') {
                flash('danger', 'No se puede eliminar este registro porque está siendo utilizado por otros datos del sistema.');
            } else {
                flash('danger', 'No fue posible eliminar el registro en este momento.');
            }
        }

        $this->redirigir('/app/' . $modulo);
    }

    private function editarListaPrecios(int $id): void
    {
        $empresaId = empresa_actual_id();
        $registro = $this->modelo->obtenerPorId('listas_precios', $empresaId, $id);

        if (!$registro) {
            flash('danger', 'Lista de precios no encontrada.');
            $this->redirigir('/app/listas-precios');
        }

        $this->vista('empresa/modulos/listas_precios_editar', compact('registro'), 'empresa');
    }

    private function actualizarListaPrecios(int $id): void
    {
        validar_csrf();
        $empresaId = empresa_actual_id();
        $registro = $this->modelo->obtenerPorId('listas_precios', $empresaId, $id);

        if (!$registro) {
            flash('danger', 'Lista de precios no encontrada.');
            $this->redirigir('/app/listas-precios');
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $vigenciaDesde = $_POST['vigencia_desde'] ?: null;
        $vigenciaHasta = $_POST['vigencia_hasta'] ?: null;
        $tipoLista = trim($_POST['tipo_lista'] ?? 'general');
        $canalVenta = trim($_POST['canal_venta'] ?? '');
        $ajusteTipo = ($_POST['ajuste_tipo'] ?? 'incremento') === 'descuento' ? 'descuento' : 'incremento';
        $ajustePorcentaje = max(0, (float) ($_POST['ajuste_porcentaje'] ?? 0));
        $estado = $_POST['estado'] ?? 'activo';

        if ($nombre === '') {
            flash('danger', 'El nombre de la lista de precios es obligatorio.');
            $this->redirigir('/app/listas-precios/editar/' . $id);
        }

        if (!in_array($estado, ['activo', 'inactivo'], true)) {
            $estado = 'activo';
        }

        if ($vigenciaDesde !== null && $vigenciaHasta !== null && $vigenciaHasta < $vigenciaDesde) {
            flash('danger', 'La vigencia hasta no puede ser anterior a la vigencia desde.');
            $this->redirigir('/app/listas-precios/editar/' . $id);
        }

        $this->modelo->actualizarDinamico('listas_precios', $empresaId, $id, [
            'nombre' => $nombre,
            'vigencia_desde' => $vigenciaDesde,
            'vigencia_hasta' => $vigenciaHasta,
            'tipo_lista' => $tipoLista !== '' ? $tipoLista : 'general',
            'canal_venta' => $canalVenta !== '' ? $canalVenta : null,
            'ajuste_tipo' => $ajusteTipo,
            'ajuste_porcentaje' => $ajustePorcentaje,
            'estado' => $estado,
            'reglas_base' => trim($_POST['reglas_base'] ?? ''),
        ]);

        flash('success', 'Lista de precios actualizada correctamente.');
        $this->redirigir('/app/listas-precios');
    }

    private function mapeoModulos(): array
    {
        return [
            'contactos' => ['tabla' => 'contactos_cliente', 'titulo' => 'Contactos'],
            'vendedores' => ['tabla' => 'vendedores', 'titulo' => 'Vendedores'],
            'categorias' => ['tabla' => 'categorias_productos', 'titulo' => 'Categorías'],
            'listas-precios' => ['tabla' => 'listas_precios', 'titulo' => 'Listas de precios'],
            'seguimiento' => ['tabla' => 'seguimientos_comerciales', 'titulo' => 'Seguimiento comercial'],
            'aprobaciones' => ['tabla' => 'aprobaciones_cotizacion', 'titulo' => 'Aprobaciones'],
            'notificaciones' => ['tabla' => 'notificaciones_empresa', 'titulo' => 'Notificaciones'],
            'historial' => ['tabla' => 'historial_actividad', 'titulo' => 'Historial / actividad'],
        ];
    }

    public function precioProducto(): void
    {
        $empresaId = empresa_actual_id();
        $productoId = (int) ($_GET['producto_id'] ?? 0);
        $clienteId = (int) ($_GET['cliente_id'] ?? 0) ?: null;
        $listaPrecioId = (int) ($_GET['lista_precio_id'] ?? 0) ?: null;

        header('Content-Type: application/json; charset=UTF-8');

        if ($productoId <= 0) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'mensaje' => 'producto_id es obligatorio']);
            return;
        }

        $precio = (new ServicioPreciosLista())->calcularPrecioProducto(
            $empresaId,
            $productoId,
            $clienteId,
            null,
            date('Y-m-d'),
            $listaPrecioId
        );

        if (!$precio) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'mensaje' => 'Producto no encontrado']);
            return;
        }

        echo json_encode(['ok' => true, 'data' => $precio], JSON_UNESCAPED_UNICODE);
    }
}
