<?php

namespace Aplicacion\Controladores\Empresa;

use Aplicacion\Nucleo\Controlador;
use Aplicacion\Modelos\Cotizacion;
use Aplicacion\Modelos\Empresa;
use Aplicacion\Modelos\Cliente;
use Aplicacion\Modelos\GestionComercial;
use Aplicacion\Modelos\Inventario;

class DocumentosControlador extends Controlador
{
    public function index(): void
    {
        $empresaId = empresa_actual_id();
        $cotizaciones = (new Cotizacion())->listar($empresaId);
        $cotizacionId = (int) ($_REQUEST['cotizacion_id'] ?? 0);
        $plantillaHtml = trim((string) ($_POST['plantilla_html'] ?? ''));
        $asuntoCorreo = trim((string) ($_POST['asunto_correo'] ?? ''));
        $accion = trim((string) ($_POST['accion'] ?? 'preview'));
        $modeloComercial = new GestionComercial();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            validar_csrf();
        }

        $empresa = (new Empresa())->buscar($empresaId) ?: [];
        $cotizacion = null;
        $cliente = null;
        if ($cotizacionId > 0) {
            $cotizacion = (new Cotizacion())->obtenerPorId($empresaId, $cotizacionId);
            if ($cotizacion) {
                $cliente = (new Cliente())->obtenerPorId($empresaId, (int) ($cotizacion['cliente_id'] ?? 0));
            }
        }

        $variables = $this->armarVariablesPlantilla($empresa, $cotizacion, $cliente);
        $plantillaGuardada = $modeloComercial->obtenerPlantillaCorreoCotizacion($empresaId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($accion, ['guardar', 'restaurar'], true)) {
            if ($accion === 'restaurar') {
                $asuntoCorreo = 'Cotización {{numero_cotizacion}} - {{empresa_nombre}}';
                $plantillaHtml = $this->plantillaBaseCorreo();
            }

            if ($asuntoCorreo === '') {
                $asuntoCorreo = 'Cotización {{numero_cotizacion}} - {{empresa_nombre}}';
            }
            if ($plantillaHtml === '') {
                $plantillaHtml = $this->plantillaBaseCorreo();
            }
            $modeloComercial->guardarPlantillaCorreoCotizacion($empresaId, $asuntoCorreo, $plantillaHtml);
            $mensaje = $accion === 'restaurar'
                ? 'Plantilla original restaurada y guardada en base de datos.'
                : 'Plantilla de correo guardada. Se aplicará en los envíos desde cotizaciones.';
            flash('success', $mensaje);
            $plantillaGuardada = $modeloComercial->obtenerPlantillaCorreoCotizacion($empresaId);
        }

        if ($plantillaHtml === '') {
            $plantillaHtml = trim((string) ($plantillaGuardada['observaciones_defecto'] ?? ''));
        }
        if ($plantillaHtml === '') {
            $plantillaHtml = $this->plantillaBaseCorreo();
        }

        if ($asuntoCorreo === '') {
            $asuntoCorreo = trim((string) ($plantillaGuardada['terminos_defecto'] ?? ''));
        }
        if ($asuntoCorreo === '') {
            $asuntoCorreo = 'Cotización {{numero_cotizacion}} - {{empresa_nombre}}';
        }

        $vistaPrevia = $this->renderizarPlantilla($plantillaHtml, $variables);
        $asuntoPrevia = $this->renderizarPlantilla($asuntoCorreo, $variables);

        $this->vista('empresa/documentos/index', compact(
            'cotizaciones',
            'cotizacionId',
            'plantillaHtml',
            'asuntoCorreo',
            'asuntoPrevia',
            'vistaPrevia',
            'variables',
            'cotizacion',
            'cliente',
            'empresa'
        ), 'empresa');
    }

    public function ordenCompra(): void
    {
        $empresaId = (int) empresa_actual_id();
        $inventario = new Inventario();
        $ordenes = $inventario->listarOrdenesCompra($empresaId);
        $ordenId = (int) ($_REQUEST['orden_id'] ?? 0);
        $plantillaHtml = trim((string) ($_POST['plantilla_html'] ?? ''));
        $asuntoCorreo = trim((string) ($_POST['asunto_correo'] ?? ''));
        $accion = trim((string) ($_POST['accion'] ?? 'preview'));
        $modeloComercial = new GestionComercial();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            validar_csrf();
        }

        $empresa = (new Empresa())->buscar($empresaId) ?: [];
        $orden = $ordenId > 0 ? $inventario->obtenerOrdenCompra($empresaId, $ordenId) : null;

        $variables = $this->armarVariablesPlantillaOrdenCompra($empresa, $orden);
        $plantillaGuardada = $modeloComercial->obtenerPlantillaCorreoOrdenCompra($empresaId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($accion, ['guardar', 'restaurar'], true)) {
            if ($accion === 'restaurar') {
                $asuntoCorreo = 'Orden de compra {{numero_orden}} - {{empresa_nombre}}';
                $plantillaHtml = $this->plantillaBaseCorreoOrdenCompra();
            }

            if ($asuntoCorreo === '') {
                $asuntoCorreo = 'Orden de compra {{numero_orden}} - {{empresa_nombre}}';
            }
            if ($plantillaHtml === '') {
                $plantillaHtml = $this->plantillaBaseCorreoOrdenCompra();
            }
            $modeloComercial->guardarPlantillaCorreoOrdenCompra($empresaId, $asuntoCorreo, $plantillaHtml);
            flash('success', $accion === 'restaurar'
                ? 'Plantilla original de OC restaurada y guardada.'
                : 'Plantilla de correo OC guardada correctamente.');
            $plantillaGuardada = $modeloComercial->obtenerPlantillaCorreoOrdenCompra($empresaId);
        }

        if ($plantillaHtml === '') {
            $plantillaHtml = trim((string) ($plantillaGuardada['observaciones_defecto'] ?? ''));
        }
        if ($plantillaHtml === '') {
            $plantillaHtml = $this->plantillaBaseCorreoOrdenCompra();
        }

        if ($asuntoCorreo === '') {
            $asuntoCorreo = trim((string) ($plantillaGuardada['terminos_defecto'] ?? ''));
        }
        if ($asuntoCorreo === '') {
            $asuntoCorreo = 'Orden de compra {{numero_orden}} - {{empresa_nombre}}';
        }

        $vistaPrevia = $this->renderizarPlantilla($plantillaHtml, $variables);
        $asuntoPrevia = $this->renderizarPlantilla($asuntoCorreo, $variables);

        $this->vista('empresa/documentos/orden_compra', compact(
            'ordenes',
            'ordenId',
            'orden',
            'plantillaHtml',
            'asuntoCorreo',
            'asuntoPrevia',
            'vistaPrevia',
            'variables'
        ), 'empresa');
    }

    private function armarVariablesPlantilla(array $empresa, ?array $cotizacion, ?array $cliente): array
    {
        $empresaNombre = trim((string) ($empresa['nombre_comercial'] ?? $empresa['razon_social'] ?? 'Tu empresa'));
        $remitenteCorreo = trim((string) ($empresa['imap_remitente_correo'] ?? '')) !== ''
            ? trim((string) ($empresa['imap_remitente_correo'] ?? ''))
            : trim((string) ($empresa['correo'] ?? ''));
        $remitenteNombre = trim((string) ($empresa['imap_remitente_nombre'] ?? '')) !== ''
            ? trim((string) ($empresa['imap_remitente_nombre'] ?? ''))
            : $empresaNombre;

        $clienteNombre = trim((string) ($cliente['razon_social'] ?? ''));
        if ($clienteNombre === '') {
            $clienteNombre = trim((string) ($cliente['nombre_comercial'] ?? ''));
        }
        if ($clienteNombre === '') {
            $clienteNombre = trim((string) ($cliente['nombre'] ?? 'Cliente'));
        }

        $correoDestino = trim((string) ($cliente['correo'] ?? 'sin-correo@cliente.com'));
        if (!filter_var($correoDestino, FILTER_VALIDATE_EMAIL)) {
            $correoDestino = 'sin-correo@cliente.com';
        }

        $numero = (string) ($cotizacion['numero'] ?? 'COT-0000');
        $estadoCotizacion = (string) ($cotizacion['estado'] ?? 'borrador');
        $fechaEmision = (string) ($cotizacion['fecha_emision'] ?? date('Y-m-d'));
        $total = '$' . number_format((float) ($cotizacion['total'] ?? 0), 2, ',', '.');
        $subtotal = '$' . number_format((float) ($cotizacion['subtotal'] ?? 0), 2, ',', '.');
        $impuesto = '$' . number_format((float) ($cotizacion['impuesto'] ?? 0), 2, ',', '.');
        $descuento = '$' . number_format((float) ($cotizacion['descuento'] ?? 0), 2, ',', '.');
        $fechaVencimiento = (string) ($cotizacion['fecha_vencimiento'] ?? date('Y-m-d'));
        $urlPublica = url('/cotizacion/publica/' . (string) ($cotizacion['token_publico'] ?? '{token}'));
        $urlPdf = url('/app/cotizaciones/pdf/' . (int) ($cotizacion['id'] ?? 0));

        return [
            '{{empresa_nombre}}' => $empresaNombre,
            '{{cliente_nombre}}' => $clienteNombre,
            '{{correo_destino}}' => $correoDestino,
            '{{numero_cotizacion}}' => $numero,
            '{{estado_cotizacion}}' => $estadoCotizacion,
            '{{fecha_emision}}' => $fechaEmision,
            '{{total_cotizacion}}' => $total,
            '{{subtotal_cotizacion}}' => $subtotal,
            '{{impuesto_cotizacion}}' => $impuesto,
            '{{descuento_cotizacion}}' => $descuento,
            '{{fecha_vencimiento}}' => $fechaVencimiento,
            '{{url_publica}}' => $urlPublica,
            '{{url_pdf}}' => $urlPdf,
            '{{remitente_nombre}}' => $remitenteNombre,
            '{{remitente_correo}}' => $remitenteCorreo,
        ];
    }

    private function renderizarPlantilla(string $html, array $variables): string
    {
        $reemplazosSeguros = [];
        foreach ($variables as $clave => $valor) {
            $reemplazosSeguros[$clave] = htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
        }

        return strtr($html, $reemplazosSeguros);
    }

    private function armarVariablesPlantillaOrdenCompra(array $empresa, ?array $orden): array
    {
        $empresaNombre = trim((string) ($empresa['nombre_comercial'] ?? $empresa['razon_social'] ?? 'Tu empresa'));
        $remitenteCorreo = trim((string) ($empresa['imap_remitente_correo'] ?? '')) !== ''
            ? trim((string) ($empresa['imap_remitente_correo'] ?? ''))
            : trim((string) ($empresa['correo'] ?? ''));
        $remitenteNombre = trim((string) ($empresa['imap_remitente_nombre'] ?? '')) !== ''
            ? trim((string) ($empresa['imap_remitente_nombre'] ?? ''))
            : $empresaNombre;

        $proveedorNombre = trim((string) ($orden['proveedor_nombre'] ?? 'Proveedor'));
        $correoDestino = trim((string) ($orden['proveedor_correo'] ?? 'sin-correo@proveedor.com'));
        if (!filter_var($correoDestino, FILTER_VALIDATE_EMAIL)) {
            $correoDestino = 'sin-correo@proveedor.com';
        }

        $numero = (string) ($orden['numero'] ?? 'OC-0000');
        $fechaEmision = (string) ($orden['fecha_emision'] ?? date('Y-m-d'));
        $fechaEntrega = (string) ($orden['fecha_entrega_estimada'] ?? date('Y-m-d'));
        $estado = (string) ($orden['estado'] ?? 'borrador');
        $total = '$' . number_format((float) ($orden['total'] ?? 0), 2, ',', '.');
        $urlPdf = url('/app/inventario/ordenes-compra/pdf/' . (int) ($orden['id'] ?? 0));

        return [
            '{{empresa_nombre}}' => $empresaNombre,
            '{{proveedor_nombre}}' => $proveedorNombre,
            '{{correo_destino}}' => $correoDestino,
            '{{numero_orden}}' => $numero,
            '{{estado_orden}}' => $estado,
            '{{fecha_emision}}' => $fechaEmision,
            '{{fecha_entrega}}' => $fechaEntrega,
            '{{total_orden}}' => $total,
            '{{url_pdf}}' => $urlPdf,
            '{{remitente_nombre}}' => $remitenteNombre,
            '{{remitente_correo}}' => $remitenteCorreo,
        ];
    }

    private function plantillaBaseCorreo(): string
    {
        return <<<'HTML'
<div style="font-family:Arial,Helvetica,sans-serif;background:#f5f7fb;padding:24px;color:#111827;">
  <div style="max-width:680px;margin:0 auto;background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">
    <div style="background:#0f3d77;color:#ffffff;padding:20px 24px;">
      <h2 style="margin:0;font-size:20px;">{{empresa_nombre}}</h2>
      <p style="margin:6px 0 0;font-size:13px;opacity:.9;">Envío automático de cotización</p>
    </div>
    <div style="padding:24px;">
      <p style="margin:0 0 14px;">Hola <strong>{{cliente_nombre}}</strong>,</p>
      <p style="margin:0 0 14px;line-height:1.5;">Adjuntamos la cotización <strong>{{numero_cotizacion}}</strong> por un total de <strong>{{total_cotizacion}}</strong>, con vigencia hasta el <strong>{{fecha_vencimiento}}</strong>.</p>
      <div style="border:1px solid #e5e7eb;border-radius:10px;background:#f9fafb;padding:14px;margin:0 0 16px;">
        <div style="font-size:13px;margin-bottom:8px;color:#111827;"><strong>Detalle de la cotización</strong></div>
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
          <tr><td style="padding:4px 0;color:#6b7280;">Número</td><td style="padding:4px 0;text-align:right;"><strong>{{numero_cotizacion}}</strong></td></tr>
          <tr><td style="padding:4px 0;color:#6b7280;">Estado</td><td style="padding:4px 0;text-align:right;">{{estado_cotizacion}}</td></tr>
          <tr><td style="padding:4px 0;color:#6b7280;">Fecha emisión</td><td style="padding:4px 0;text-align:right;">{{fecha_emision}}</td></tr>
          <tr><td style="padding:4px 0;color:#6b7280;">Subtotal</td><td style="padding:4px 0;text-align:right;">{{subtotal_cotizacion}}</td></tr>
          <tr><td style="padding:4px 0;color:#6b7280;">Descuento</td><td style="padding:4px 0;text-align:right;">{{descuento_cotizacion}}</td></tr>
          <tr><td style="padding:4px 0;color:#6b7280;">Impuesto</td><td style="padding:4px 0;text-align:right;">{{impuesto_cotizacion}}</td></tr>
          <tr><td style="padding:6px 0;color:#111827;"><strong>Total</strong></td><td style="padding:6px 0;text-align:right;"><strong>{{total_cotizacion}}</strong></td></tr>
        </table>
      </div>
      <p style="margin:0 0 20px;line-height:1.5;">Puedes revisarla en línea y registrar tu decisión desde el siguiente botón:</p>
      <p style="margin:0 0 18px;">
        <a href="{{url_publica}}" style="display:inline-block;background:#0f3d77;color:#ffffff;text-decoration:none;padding:12px 18px;border-radius:8px;font-weight:600;">Ver, aceptar o rechazar cotización</a>
      </p>
      <p style="margin:0 0 8px;font-size:13px;color:#4b5563;">También puedes descargar el PDF directamente:</p>
      <p style="margin:0 0 20px;font-size:13px;"><a href="{{url_pdf}}" style="color:#0f3d77;">{{url_pdf}}</a></p>
      <p style="margin:0;font-size:12px;color:#6b7280;">Este correo fue enviado a {{correo_destino}} por {{remitente_nombre}} ({{remitente_correo}}).</p>
    </div>
  </div>
</div>
HTML;
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
      <p style="margin:0 0 8px;font-size:13px;color:#4b5563;">Descargar PDF de la orden:</p>
      <p style="margin:0 0 20px;font-size:13px;"><a href="{{url_pdf}}" style="color:#0f3d77;">{{url_pdf}}</a></p>
      <p style="margin:0;font-size:12px;color:#6b7280;">Este correo fue enviado a {{correo_destino}} por {{remitente_nombre}} ({{remitente_correo}}).</p>
    </div>
  </div>
</div>
HTML;
    }
}
