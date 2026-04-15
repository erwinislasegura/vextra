<?php

namespace Aplicacion\Controladores\Publico;

use Aplicacion\Nucleo\Controlador;
use Aplicacion\Modelos\Plan;
use Aplicacion\Modelos\Cotizacion;
use Aplicacion\Modelos\Empresa;
use Aplicacion\Modelos\Producto;
use Aplicacion\Modelos\ProductoImagen;
use Aplicacion\Modelos\GestionComercial;
use Aplicacion\Modelos\Inventario;
use Aplicacion\Modelos\PlanFuncionalidad;
use Aplicacion\Modelos\CatalogoCompra;
use Aplicacion\Servicios\ServicioCorreo;
use Aplicacion\Servicios\FlowApiService;

class PublicoControlador extends Controlador
{
    private const LANDING_SITEMAP = [
        ['path' => '/', 'changefreq' => 'weekly', 'priority' => '1.0', 'view' => 'inicio.php'],
        ['path' => '/caracteristicas', 'changefreq' => 'weekly', 'priority' => '0.9', 'view' => 'caracteristicas.php'],
        ['path' => '/planes', 'changefreq' => 'weekly', 'priority' => '0.9', 'view' => 'planes.php'],
        ['path' => '/contacto', 'changefreq' => 'monthly', 'priority' => '0.8', 'view' => 'contacto.php'],
        ['path' => '/preguntas-frecuentes', 'changefreq' => 'monthly', 'priority' => '0.7', 'view' => 'preguntas_frecuentes.php'],
    ];

    public function inicio(): void
    {
        $planes = (new Plan())->listar(true);
        $planes = $this->agregarFuncionalidadesPlanes($planes);
        $this->vistaPublica('publico/inicio', ['planes' => $planes], 'inicio');
    }

    public function caracteristicas(): void
    {
        $this->vistaPublica('publico/caracteristicas', [], 'caracteristicas');
    }

    public function planes(): void
    {
        $planes = (new Plan())->listar(true);
        $planes = $this->agregarFuncionalidadesPlanes($planes);
        $this->vistaPublica('publico/planes', ['planes' => $planes], 'planes');
    }

    public function contacto(): void
    {
        $this->vistaPublica('publico/contacto', [], 'contacto');
    }

    public function preguntasFrecuentes(): void
    {
        $this->vistaPublica('publico/preguntas_frecuentes', [], 'faq');
    }

    public function imagenOptimizada(string $clave): void
    {
        $imagenes = [
            'dashboard_inicio' => 'Dashboard - Inicio.png',
            'punto_venta' => 'Punto de venta.png',
            'movimientos_inventario' => 'Movimientos de inventario.png',
            'clientes' => 'Clientes.png',
            'cotizaciones_1' => 'Cotizaciones 1.png',
            'cotizaciones_2' => 'Cotizaciones 2.png',
            'cotizaciones_3' => 'Cotizaciones 3.png',
            'cotizaciones_4' => 'Cotizaciones 4.png',
            'cotizaciones_5' => 'Cotizaciones 5.png',
        ];

        if (!isset($imagenes[$clave])) {
            http_response_code(404);
            exit('Imagen no disponible');
        }

        $anchoMax = isset($_GET['w']) ? max(200, min(1600, (int) $_GET['w'])) : 1200;
        $altoMax = isset($_GET['h']) ? max(200, min(1200, (int) $_GET['h'])) : 800;
        $calidad = isset($_GET['q']) ? max(55, min(85, (int) $_GET['q'])) : 76;

        $rutaOrigen = __DIR__ . '/../../../img/Captura Sistema/' . $imagenes[$clave];
        if (!is_file($rutaOrigen)) {
            http_response_code(404);
            exit('Imagen no encontrada');
        }

        $cacheDir = __DIR__ . '/../../../public/assets/cache/landing';
        if (!is_dir($cacheDir) && !mkdir($cacheDir, 0775, true) && !is_dir($cacheDir)) {
            http_response_code(500);
            exit('No se pudo crear caché');
        }

        $nombreCache = sprintf('%s_%dx%d_q%d.jpg', $clave, $anchoMax, $altoMax, $calidad);
        $rutaCache = $cacheDir . '/' . $nombreCache;

        if (!is_file($rutaCache) || filemtime($rutaCache) < filemtime($rutaOrigen)) {
            $origen = @imagecreatefrompng($rutaOrigen);
            if (!$origen) {
                http_response_code(500);
                exit('No se pudo procesar imagen');
            }

            $ancho = imagesx($origen);
            $alto = imagesy($origen);
            $ratio = min($anchoMax / max(1, $ancho), $altoMax / max(1, $alto), 1);
            $nuevoAncho = max(1, (int) round($ancho * $ratio));
            $nuevoAlto = max(1, (int) round($alto * $ratio));

            $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
            imagefill($destino, 0, 0, imagecolorallocate($destino, 255, 255, 255));
            imagecopyresampled($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

            imagejpeg($destino, $rutaCache, $calidad);
            imagedestroy($destino);
            imagedestroy($origen);
        }

        $ultimaMod = gmdate('D, d M Y H:i:s', filemtime($rutaCache)) . ' GMT';
        $etag = '"' . md5($rutaCache . '|' . filemtime($rutaCache) . '|' . filesize($rutaCache)) . '"';

        header('Content-Type: image/jpeg');
        header('Cache-Control: public, max-age=2592000, immutable');
        header('Last-Modified: ' . $ultimaMod);
        header('ETag: ' . $etag);

        if ((isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim((string) $_SERVER['HTTP_IF_NONE_MATCH']) === $etag)
            || (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && trim((string) $_SERVER['HTTP_IF_MODIFIED_SINCE']) === $ultimaMod)) {
            http_response_code(304);
            exit;
        }

        readfile($rutaCache);
        exit;
    }

    public function sitemapXml(): void
    {
        header('Content-Type: application/xml; charset=UTF-8');

        $baseUrl = $this->obtenerUrlBaseSitio();
        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('urlset');
        $xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach (self::LANDING_SITEMAP as $pagina) {
            $xml->startElement('url');
            $xml->writeElement('loc', $baseUrl . url($pagina['path']));
            $xml->writeElement('lastmod', $this->resolverUltimaActualizacionPagina($pagina['view']));
            $xml->writeElement('changefreq', $pagina['changefreq']);
            $xml->writeElement('priority', $pagina['priority']);
            $xml->endElement();
        }

        $xml->endElement();
        $xml->endDocument();

        echo $xml->outputMemory();
    }

    public function robotsTxt(): void
    {
        header('Content-Type: text/plain; charset=UTF-8');

        $baseUrl = $this->obtenerUrlBaseSitio();
        echo "User-agent: *\n";
        echo "Allow: /\n";
        echo "Sitemap: " . $baseUrl . url('/sitemap.xml') . "\n";
    }

    public function enviarContacto(): void
    {
        validar_csrf();

        if (!validar_recaptcha_post('contacto_landing')) {
            flash('danger', 'No pudimos validar reCAPTCHA. Intenta nuevamente.');
            $this->redirigir('/contacto');
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $correo = filter_var($_POST['correo'] ?? '', FILTER_VALIDATE_EMAIL);
        $telefono = trim((string) ($_POST['telefono'] ?? ''));
        $empresa = trim((string) ($_POST['empresa'] ?? ''));
        $tipoContacto = (string) ($_POST['tipo_contacto'] ?? 'prospecto');
        if (!in_array($tipoContacto, ['prospecto', 'cliente_actual'], true)) {
            $tipoContacto = 'prospecto';
        }
        $motivoConsulta = trim((string) ($_POST['motivo_consulta'] ?? ''));
        $mensaje = trim($_POST['mensaje'] ?? '');

        if ($nombre === '' || !$correo || $mensaje === '' || $motivoConsulta === '') {
            flash('danger', 'Completa todos los campos del formulario de contacto.');
            $this->redirigir('/contacto');
        }

        $html = '<h2>Nuevo lead desde landing</h2>'
            . '<p><strong>Nombre:</strong> ' . htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') . '</p>'
            . '<p><strong>Correo:</strong> ' . htmlspecialchars((string) $correo, ENT_QUOTES, 'UTF-8') . '</p>'
            . '<p><strong>Teléfono:</strong> ' . htmlspecialchars($telefono !== '' ? $telefono : 'No informado', ENT_QUOTES, 'UTF-8') . '</p>'
            . '<p><strong>Empresa:</strong> ' . htmlspecialchars($empresa !== '' ? $empresa : 'No informada', ENT_QUOTES, 'UTF-8') . '</p>'
            . '<p><strong>Tipo de contacto:</strong> ' . htmlspecialchars($tipoContacto === 'cliente_actual' ? 'Cliente actual' : 'Posible cliente', ENT_QUOTES, 'UTF-8') . '</p>'
            . '<p><strong>Motivo de consulta:</strong> ' . htmlspecialchars($motivoConsulta, ENT_QUOTES, 'UTF-8') . '</p>'
            . '<p><strong>Mensaje:</strong><br>' . nl2br(htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8')) . '</p>';

        (new ServicioCorreo())->enviar(
            'contacto@vextra.cl',
            'Nuevo lead desde landing',
            'landing_contacto',
            [
                'nombre' => $nombre,
                'correo' => $correo,
                'telefono' => $telefono,
                'empresa' => $empresa,
                'tipo_contacto' => $tipoContacto,
                'motivo_consulta' => $motivoConsulta,
                'mensaje' => $mensaje,
                'html' => $html,
            ]
        );

        flash('success', 'Gracias por escribirnos. Te contactaremos pronto.');
        $this->redirigir('/contacto');
    }

    public function contratar(string $planSlug): void
    {
        $plan = (new Plan())->buscarPublicoPorSlug($planSlug);
        if (!$plan) {
            http_response_code(404);
            require __DIR__ . '/../../vistas/errores/404.php';
            return;
        }
        $this->vistaPublica('publico/contratar', ['plan' => $plan], 'contratar');
    }

    private function agregarFuncionalidadesPlanes(array $planes): array
    {
        $planFuncionalidadModelo = new PlanFuncionalidad();
        foreach ($planes as &$plan) {
            $plan['funcionalidades'] = $planFuncionalidadModelo->listarActivasPorPlan((int) $plan['id']);
        }
        unset($plan);

        return $planes;
    }

    public function verCotizacionPublica(string $token): void
    {
        $cotizacion = (new Cotizacion())->obtenerPorTokenPublico($token);
        if (!$cotizacion) {
            http_response_code(404);
            require __DIR__ . '/../../vistas/errores/404.php';
            return;
        }

        $this->vistaPublica('publico/cotizacion_publica', compact('cotizacion', 'token'), 'cotizacion_publica');
    }

    public function imprimirCotizacionPublica(string $token): void
    {
        $cotizacion = (new Cotizacion())->obtenerPorTokenPublico($token);
        if (!$cotizacion) {
            http_response_code(404);
            require __DIR__ . '/../../vistas/errores/404.php';
            return;
        }

        $empresa = (new Empresa())->buscar((int) ($cotizacion['empresa_id'] ?? 0));
        $listaAplicada = [];
        $esVistaPublica = true;
        $this->vista('empresa/cotizaciones/imprimir', compact('cotizacion', 'empresa', 'listaAplicada', 'esVistaPublica', 'token'), 'impresion');
    }

    public function registrarDecisionCotizacion(string $token): void
    {
        $cotizacion = (new Cotizacion())->obtenerPorTokenPublico($token);
        if (!$cotizacion) {
            http_response_code(404);
            require __DIR__ . '/../../vistas/errores/404.php';
            return;
        }

        $decision = $_POST['decision'] ?? '';
        if (!in_array($decision, ['aprobada', 'rechazada'], true)) {
            flash('danger', 'Decisión no válida.');
            $this->redirigir('/cotizacion/publica/' . $token);
        }

        $nombreFirmante = trim((string) ($_POST['nombre_firmante_cliente'] ?? ''));
        $firmaCliente = trim((string) ($_POST['firma_cliente'] ?? ''));

        if ($decision === 'aprobada') {
            if ($nombreFirmante === '' || $firmaCliente === '') {
                flash('danger', 'Para aprobar debes ingresar el nombre y la firma del cliente.');
                $this->redirigir('/cotizacion/publica/' . $token);
            }

            if (strpos($firmaCliente, 'data:image/png;base64,') !== 0) {
                flash('danger', 'La firma enviada no tiene un formato válido.');
                $this->redirigir('/cotizacion/publica/' . $token);
            }
        } else {
            $nombreFirmante = null;
            $firmaCliente = null;
        }

        (new Cotizacion())->actualizarDecisionPublica((int) $cotizacion['empresa_id'], (int) $cotizacion['id'], [
            'estado' => $decision,
            'observaciones' => (string) ($cotizacion['observaciones'] ?? ''),
            'terminos_condiciones' => (string) ($cotizacion['terminos_condiciones'] ?? ''),
            'fecha_vencimiento' => (string) ($cotizacion['fecha_vencimiento'] ?? date('Y-m-d')),
            'firma_cliente' => $firmaCliente,
            'nombre_firmante_cliente' => $nombreFirmante,
            'fecha_aprobacion_cliente' => $decision === 'aprobada' ? date('Y-m-d H:i:s') : null,
        ]);

        $gestion = new GestionComercial();
        $empresaId = (int) ($cotizacion['empresa_id'] ?? 0);
        $cotizacionId = (int) ($cotizacion['id'] ?? 0);
        if (!$gestion->existeAprobacionRegistrada($empresaId, $cotizacionId, $decision)) {
            $gestion->crear('aprobaciones_cotizacion', [
                'empresa_id' => $empresaId,
                'cotizacion_id' => $cotizacionId,
                'monto' => (float) ($cotizacion['total'] ?? 0),
                'motivo' => $decision === 'aprobada' ? 'Aprobación desde enlace público' : 'Rechazo desde enlace público',
                'solicitante' => $decision === 'aprobada' ? $nombreFirmante : ((string) ($cotizacion['cliente'] ?? 'Cliente')),
                'aprobador' => 'Cliente (portal público)',
                'estado' => $decision,
                'fecha_aprobacion' => date('Y-m-d'),
                'observaciones' => $decision === 'aprobada'
                    ? 'Cliente aprobó desde enlace público con firma digital.'
                    : 'Cliente rechazó desde enlace público.',
                'fecha_creacion' => date('Y-m-d H:i:s'),
            ]);
        }

        flash('success', $decision === 'aprobada'
            ? 'Has aceptado la cotización correctamente y registrado tu firma.'
            : 'Has rechazado la cotización correctamente.');
        $this->redirigir('/cotizacion/publica/' . $token);
    }

    public function verOrdenCompraPublica(string $token): void
    {
        $orden = (new Inventario())->obtenerOrdenCompraPorTokenPublico($token);
        if (!$orden) {
            http_response_code(404);
            require __DIR__ . '/../../vistas/errores/404.php';
            return;
        }

        $this->vistaPublica('publico/orden_compra_publica', compact('orden', 'token'), 'orden_compra_publica');
    }

    public function imprimirOrdenCompraPublica(string $token): void
    {
        $orden = (new Inventario())->obtenerOrdenCompraPorTokenPublico($token);
        if (!$orden) {
            http_response_code(404);
            require __DIR__ . '/../../vistas/errores/404.php';
            return;
        }

        $empresa = (new Empresa())->buscar((int) ($orden['empresa_id'] ?? 0));
        $esVistaPublica = true;
        $this->vista('empresa/inventario/orden_compra_imprimir', compact('orden', 'empresa', 'esVistaPublica', 'token'), 'impresion');
    }

    public function catalogoEnLinea(int $empresaId): void
    {
        $contexto = $this->obtenerContextoCatalogo($empresaId);
        if ($contexto === null) {
            http_response_code(404);
            require __DIR__ . '/../../vistas/errores/404.php';
            return;
        }
        $empresa = $contexto['empresa'];
        $logoCatalogo = $contexto['logoCatalogo'];
        $sliderCatalogo = $contexto['sliderCatalogo'];
        $catalogoTopbar = $contexto['catalogoTopbar'];

        $buscar = trim((string) ($_GET['q'] ?? ''));
        $categoriaId = (int) ($_GET['categoria'] ?? 0);
        $productos = (new Producto())->listarParaCatalogoPublico($empresaId, $buscar, $categoriaId > 0 ? $categoriaId : null);
        $categorias = (new GestionComercial())->listarTablaEmpresa('categorias_productos', $empresaId, '', 300);

        $ocultarNavbarPublico = true;
        $this->vistaPublica('publico/catalogo', compact('empresa', 'productos', 'categorias', 'buscar', 'categoriaId', 'logoCatalogo', 'sliderCatalogo', 'catalogoTopbar', 'ocultarNavbarPublico'), 'catalogo_publico');
    }

    public function catalogoNosotros(int $empresaId): void
    {
        $contexto = $this->obtenerContextoCatalogo($empresaId);
        if ($contexto === null) {
            http_response_code(404);
            require __DIR__ . '/../../vistas/errores/404.php';
            return;
        }

        $empresa = $contexto['empresa'];
        $logoCatalogo = $contexto['logoCatalogo'];
        $sliderCatalogo = $contexto['sliderCatalogo'];
        $catalogoTopbar = $contexto['catalogoTopbar'];
        $ocultarNavbarPublico = true;

        $this->vistaPublica('publico/catalogo_nosotros', compact('empresa', 'logoCatalogo', 'sliderCatalogo', 'catalogoTopbar', 'ocultarNavbarPublico'), 'catalogo_publico');
    }

    public function catalogoContacto(int $empresaId): void
    {
        $contexto = $this->obtenerContextoCatalogo($empresaId);
        if ($contexto === null) {
            http_response_code(404);
            require __DIR__ . '/../../vistas/errores/404.php';
            return;
        }

        $empresa = $contexto['empresa'];
        $logoCatalogo = $contexto['logoCatalogo'];
        $sliderCatalogo = $contexto['sliderCatalogo'];
        $catalogoTopbar = $contexto['catalogoTopbar'];
        $ocultarNavbarPublico = true;

        $this->vistaPublica('publico/catalogo_contacto', compact('empresa', 'logoCatalogo', 'sliderCatalogo', 'catalogoTopbar', 'ocultarNavbarPublico'), 'catalogo_publico');
    }

    public function enviarContactoCatalogo(int $empresaId): void
    {
        validar_csrf();

        $contexto = $this->obtenerContextoCatalogo($empresaId);
        if ($contexto === null) {
            http_response_code(404);
            require __DIR__ . '/../../vistas/errores/404.php';
            return;
        }

        $empresa = $contexto['empresa'];
        $catalogoTopbar = $contexto['catalogoTopbar'];
        $camposActivos = $this->normalizarCamposFormularioCatalogo((string) ($catalogoTopbar['contacto_form_campos'] ?? ''));

        $datos = [];
        foreach ($camposActivos as $campo) {
            $datos[$campo] = trim((string) ($_POST[$campo] ?? ''));
        }

        $errores = [];
        if (in_array('nombre', $camposActivos, true) && ($datos['nombre'] ?? '') === '') {
            $errores[] = 'Nombre';
        }
        if (in_array('email', $camposActivos, true)) {
            $correo = filter_var((string) ($datos['email'] ?? ''), FILTER_VALIDATE_EMAIL);
            if (!$correo) {
                $errores[] = 'Email';
            } else {
                $datos['email'] = (string) $correo;
            }
        }
        if (in_array('mensaje', $camposActivos, true) && ($datos['mensaje'] ?? '') === '') {
            $errores[] = 'Mensaje';
        }

        if ($errores !== []) {
            flash('danger', 'Completa correctamente: ' . implode(', ', $errores) . '.');
            $this->redirigir('/catalogo/' . $empresaId . '/contacto');
        }

        $correoDestino = trim((string) ($catalogoTopbar['contacto_form_correo_destino'] ?? ''));
        if ($correoDestino === '' || filter_var($correoDestino, FILTER_VALIDATE_EMAIL) === false) {
            $correoDestino = trim((string) ($empresa['correo'] ?? 'contacto@vextra.cl'));
        }
        if (filter_var($correoDestino, FILTER_VALIDATE_EMAIL) === false) {
            $correoDestino = 'contacto@vextra.cl';
        }

        $etiquetas = [
            'nombre' => 'Nombre',
            'telefono' => 'Teléfono',
            'email' => 'Email',
            'asunto' => 'Asunto',
            'mensaje' => 'Mensaje',
            'empresa' => 'Empresa',
            'whatsapp' => 'WhatsApp',
            'ciudad' => 'Ciudad',
            'direccion' => 'Dirección',
            'cargo' => 'Cargo',
        ];

        $lineasHtml = '<h2>Nuevo mensaje desde contacto de catálogo</h2>';
        foreach ($camposActivos as $campo) {
            $valor = trim((string) ($datos[$campo] ?? ''));
            if ($valor === '') {
                continue;
            }
            $etiqueta = $etiquetas[$campo] ?? ucfirst($campo);
            $lineasHtml .= '<p><strong>' . htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8') . ':</strong> ' . nl2br(htmlspecialchars($valor, ENT_QUOTES, 'UTF-8')) . '</p>';
        }
        $lineasHtml .= '<p><strong>Empresa catálogo:</strong> ' . htmlspecialchars((string) ($empresa['nombre_comercial'] ?? 'Empresa'), ENT_QUOTES, 'UTF-8') . '</p>';

        (new ServicioCorreo())->enviar(
            $correoDestino,
            'Nuevo mensaje desde contacto del catálogo',
            'landing_contacto',
            [
                'nombre' => (string) ($datos['nombre'] ?? 'Visitante'),
                'correo' => (string) ($datos['email'] ?? ''),
                'telefono' => (string) ($datos['telefono'] ?? ''),
                'empresa' => (string) ($datos['empresa'] ?? ''),
                'mensaje' => (string) ($datos['mensaje'] ?? ''),
                'html' => $lineasHtml,
            ]
        );

        flash('success', 'Gracias por tu mensaje. Te responderemos pronto.');
        $this->redirigir('/catalogo/' . $empresaId . '/contacto');
    }

    public function logoCatalogoEmpresa(int $empresaId): void
    {
        $empresa = (new Empresa())->buscar($empresaId);
        if (!$empresa) {
            $this->emitirArchivoCatalogo('', '/img/logo/icono.png');
        }
        $logo = trim((string) ($empresa['logo'] ?? ''));
        $this->emitirArchivoCatalogo($logo, '/img/logo/icono.png');
    }

    public function sliderCatalogoImagen(int $empresaId, string $tipo): void
    {
        $empresa = (new Empresa())->buscar($empresaId);
        if (!$empresa) {
            $this->emitirArchivoCatalogo('', '/img/placeholder-producto.svg');
        }
        $campo = $tipo === 'secundaria' ? 'slider_imagen_secundaria' : 'slider_imagen';
        $ruta = trim((string) ($empresa[$campo] ?? ''));
        if ($ruta === '' || !$this->rutaCatalogoExiste($ruta)) {
            $ruta = $this->inferirRutaSliderPorEmpresa($empresaId, $tipo);
        }
        if ($tipo === 'secundaria' && ($ruta === '' || !$this->rutaCatalogoExiste($ruta))) {
            $ruta = trim((string) ($empresa['slider_imagen'] ?? ''));
            if ($ruta === '' || !$this->rutaCatalogoExiste($ruta)) {
                $ruta = $this->inferirRutaSliderPorEmpresa($empresaId, 'principal');
            }
        }
        $this->emitirArchivoCatalogo($ruta, '/img/placeholder-producto.svg');
    }

    public function imagenCatalogoNosotros(int $empresaId): void
    {
        $empresa = (new Empresa())->buscar($empresaId);
        if (!$empresa) {
            $this->emitirArchivoCatalogo('', '/img/placeholder-producto.svg');
        }
        $ruta = trim((string) ($empresa['catalogo_nosotros_imagen'] ?? ''));
        if ($ruta === '' || !$this->rutaCatalogoExiste($ruta)) {
            $ruta = trim((string) ($empresa['slider_imagen'] ?? ''));
        }
        $this->emitirArchivoCatalogo($ruta, '/img/placeholder-producto.svg');
    }

    public function imagenCatalogoNosotrosBanner(int $empresaId): void
    {
        $empresa = (new Empresa())->buscar($empresaId);
        if (!$empresa) {
            $this->emitirArchivoCatalogo('', '/img/placeholder-producto.svg');
        }
        $ruta = trim((string) ($empresa['catalogo_nosotros_banner_imagen'] ?? ''));
        if ($ruta === '' || !$this->rutaCatalogoExiste($ruta)) {
            $ruta = trim((string) ($empresa['slider_imagen'] ?? ''));
        }
        $this->emitirArchivoCatalogo($ruta, '/img/placeholder-producto.svg');
    }

    public function imagenCatalogoProducto(int $empresaId, int $productoId): void
    {
        $empresa = (new Empresa())->buscar($empresaId);
        if (!$empresa) {
            $this->emitirArchivoCatalogo('', '/img/placeholder-producto.svg');
        }
        $imagenes = (new ProductoImagen())->listarPorProducto($empresaId, $productoId);
        $ruta = '';
        if ($imagenes !== []) {
            $ruta = (string) ($imagenes[0]['ruta'] ?? '');
        }
        if ($ruta === '' || !$this->rutaCatalogoExiste($ruta)) {
            $producto = (new Producto())->obtenerPorId($empresaId, $productoId);
            $ruta = (string) ($producto['imagen_catalogo_url'] ?? '');
        }
        if ($ruta === '' || !$this->rutaCatalogoExiste($ruta)) {
            $ruta = $this->inferirRutaProductoPorEmpresa($empresaId, $productoId);
        }
        $this->emitirArchivoCatalogo($ruta, '/img/placeholder-producto.svg');
    }


    public function prepararCheckoutCatalogo(int $empresaId): void
    {
        validar_csrf();

        $empresa = (new Empresa())->buscar($empresaId);
        if (!$empresa) {
            http_response_code(404);
            require __DIR__ . '/../../vistas/errores/404.php';
            return;
        }

        $carrito = json_decode((string) ($_POST['carrito_json'] ?? '[]'), true);
        if (!is_array($carrito) || $carrito === []) {
            flash('danger', 'Tu carrito está vacío. Agrega productos para continuar.');
            $this->redirigir('/catalogo/' . $empresaId);
        }

        $_SESSION['catalogo_checkout_preparado_' . $empresaId] = [
            'carrito' => $carrito,
            'fecha' => date('c'),
        ];

        $this->redirigir('/catalogo/' . $empresaId . '/checkout');
    }

    public function formularioCheckoutCatalogo(int $empresaId): void
    {
        $contexto = $this->obtenerContextoCatalogo($empresaId);
        if (!$contexto) {
            http_response_code(404);
            require __DIR__ . '/../../vistas/errores/404.php';
            return;
        }

        $empresa = $contexto['empresa'];
        $checkout = $_SESSION['catalogo_checkout_preparado_' . $empresaId] ?? null;
        if (!is_array($checkout) || !is_array($checkout['carrito'] ?? null) || $checkout['carrito'] === []) {
            flash('danger', 'No encontramos un carrito preparado para checkout.');
            $this->redirigir('/catalogo/' . $empresaId);
        }

        $itemsCatalogo = [];
        foreach ((new Producto())->listarParaCatalogoPublico($empresaId) as $producto) {
            $itemsCatalogo[(int) $producto['id']] = $producto;
        }

        $total = 0.0;
        $resumen = [];
        foreach ((array) $checkout['carrito'] as $fila) {
            $productoId = (int) ($fila['producto_id'] ?? 0);
            $cantidad = max(1, (int) ($fila['cantidad'] ?? 1));
            if (!isset($itemsCatalogo[$productoId])) {
                continue;
            }
            $producto = $itemsCatalogo[$productoId];
            $precio = (float) ($producto['precio'] ?? 0);
            $subtotal = $precio * $cantidad;
            $total += $subtotal;
            $resumen[] = ['id' => $productoId, 'nombre' => (string) $producto['nombre'], 'cantidad' => $cantidad, 'precio' => $precio, 'subtotal' => $subtotal];
        }

        if ($resumen === [] || $total <= 0) {
            flash('danger', 'No fue posible reconstruir tu carrito para el checkout.');
            $this->redirigir('/catalogo/' . $empresaId);
        }

        $ocultarNavbarPublico = true;
        $metodosEnvio = [
            'starken' => 'Starken',
            'chile_express' => 'Chile Express',
        ];
        $this->vistaPublica('publico/catalogo_checkout_formulario', compact('empresa', 'resumen', 'total', 'metodosEnvio', 'ocultarNavbarPublico'), 'catalogo_publico');
    }

    public function checkoutCatalogo(int $empresaId): void
    {
        validar_csrf();

        $empresa = (new Empresa())->buscar($empresaId);
        if (!$empresa) {
            http_response_code(404);
            require __DIR__ . '/../../vistas/errores/404.php';
            return;
        }

        $carrito = json_decode((string) ($_POST['carrito_json'] ?? '[]'), true);
        if (!is_array($carrito) || $carrito === []) {
            flash('danger', 'Tu carrito está vacío. Agrega productos para continuar.');
            $this->redirigir('/catalogo/' . $empresaId . '/checkout');
        }

        $correo = mb_strtolower(trim((string) ($_POST['correo'] ?? '')));
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $telefono = trim((string) ($_POST['telefono'] ?? ''));
        $documento = trim((string) ($_POST['documento'] ?? ''));
        $empresaComprador = trim((string) ($_POST['empresa'] ?? ''));
        $direccion = trim((string) ($_POST['direccion'] ?? ''));
        $envioMetodo = (string) ($_POST['envio_metodo'] ?? 'starken');
        $referencia = trim((string) ($_POST['referencia'] ?? ''));
        $comuna = trim((string) ($_POST['comuna'] ?? ''));
        $ciudad = trim((string) ($_POST['ciudad'] ?? ''));
        $region = trim((string) ($_POST['region'] ?? ''));
        $acepta = isset($_POST['acepta_terminos']) && (string) $_POST['acepta_terminos'] === '1';
        if (!in_array($envioMetodo, ['starken', 'chile_express'], true)) {
            $envioMetodo = 'starken';
        }

        $telefonoNormalizado = preg_replace('/\s+/', '', $telefono) ?? '';
        $telefonoValido = preg_match('/^\+?[0-9]{8,15}$/', $telefonoNormalizado) === 1;

        if (
            $nombre === ''
            || $direccion === ''
            || $comuna === ''
            || $ciudad === ''
            || $region === ''
            || !filter_var($correo, FILTER_VALIDATE_EMAIL)
            || !$telefonoValido
            || !$acepta
        ) {
            flash('danger', 'Completa los datos personales y de envío (nombre, correo, teléfono, dirección, comuna, ciudad, región y aceptación de términos).');
            $this->redirigir('/catalogo/' . $empresaId . '/checkout');
        }

        $itemsCatalogo = [];
        foreach ((new Producto())->listarParaCatalogoPublico($empresaId) as $producto) {
            $itemsCatalogo[(int) $producto['id']] = $producto;
        }

        $total = 0;
        $resumen = [];
        foreach ($carrito as $fila) {
            $productoId = (int) ($fila['producto_id'] ?? 0);
            $cantidad = max(1, (int) ($fila['cantidad'] ?? 1));
            if (!isset($itemsCatalogo[$productoId])) {
                continue;
            }
            $producto = $itemsCatalogo[$productoId];
            $precio = (float) ($producto['precio'] ?? 0);
            $subtotal = $precio * $cantidad;
            $total += $subtotal;
            $resumen[] = ['id' => $productoId, 'nombre' => (string) $producto['nombre'], 'cantidad' => $cantidad, 'precio' => $precio, 'subtotal' => $subtotal];
        }

        if ($total <= 0 || $resumen === []) {
            flash('danger', 'No fue posible validar los productos del carrito.');
            $this->redirigir('/catalogo/' . $empresaId . '/checkout');
        }

        $commerceOrder = 'CAT-' . $empresaId . '-' . strtoupper(substr(sha1((string) microtime(true)), 0, 10));
        $urlRetorno = FlowApiService::construirUrlPublica('/catalogo/' . $empresaId . '/checkout/exito');
        $urlConfirmacion = FlowApiService::construirUrlPublica('/flow/webhook/payment-confirmation');

        try {
            $respuesta = (new FlowApiService())->postParaEmpresa($empresaId, 'payment/create', [
                'commerceOrder' => $commerceOrder,
                'subject' => 'Compra catálogo ' . (string) ($empresa['nombre_comercial'] ?? 'Vextra'),
                'currency' => 'CLP',
                'amount' => (int) round($total),
                'email' => $correo,
                'urlConfirmation' => $urlConfirmacion,
                'urlReturn' => $urlRetorno,
            ]);
        } catch (\Throwable $e) {
            flash('danger', 'No fue posible iniciar el pago en Flow: ' . $e->getMessage());
            $this->redirigir('/catalogo/' . $empresaId . '/checkout');
        }

        if (!isset($respuesta['url'], $respuesta['token'])) {
            flash('danger', 'Flow no devolvió URL de pago.');
            $this->redirigir('/catalogo/' . $empresaId . '/checkout');
        }


        $compraId = (new CatalogoCompra())->crear([
            'empresa_id' => $empresaId,
            'flow_token' => (string) $respuesta['token'],
            'commerce_order' => $commerceOrder,
            'estado_pago' => 'pendiente',
            'estado_envio' => 'pendiente',
            'comprador_nombre' => $nombre,
            'comprador_correo' => $correo,
            'comprador_telefono' => $telefonoNormalizado !== '' ? $telefonoNormalizado : $telefono,
            'comprador_documento' => $documento !== '' ? $documento : null,
            'comprador_empresa' => $empresaComprador !== '' ? $empresaComprador : null,
            'envio_metodo' => $envioMetodo,
            'envio_direccion' => $direccion,
            'envio_referencia' => $referencia !== '' ? $referencia : null,
            'envio_comuna' => $comuna,
            'envio_ciudad' => $ciudad,
            'envio_region' => $region,
            'total' => $total,
            'moneda' => 'CLP',
            'payload_flow' => json_encode($respuesta, JSON_UNESCAPED_UNICODE),
        ]);
        (new CatalogoCompra())->guardarItems($compraId, $resumen);

        $_SESSION['catalogo_checkout_' . $respuesta['token']] = [
            'empresa_id' => $empresaId,
            'comprador' => [
                'nombre' => $nombre,
                'correo' => $correo,
                'telefono' => $telefono,
                'documento' => $documento,
                'empresa' => $empresaComprador,
                'envio_metodo' => $envioMetodo,
                'direccion' => $direccion,
                'referencia' => $referencia,
                'comuna' => $comuna,
                'ciudad' => $ciudad,
                'region' => $region,
            ],
            'total' => $total,
            'items' => $resumen,
            'fecha' => date('c'),
        ];

        $this->redirigir($respuesta['url'] . '?token=' . $respuesta['token']);
    }

    public function exitoCheckoutCatalogo(int $empresaId): void
    {
        $empresa = (new Empresa())->buscar($empresaId);
        if (!$empresa) {
            http_response_code(404);
            require __DIR__ . '/../../vistas/errores/404.php';
            return;
        }

        $token = trim((string) ($_GET['token'] ?? ''));
        $estado = 'pendiente';
        if ($token !== '') {
            try {
                $status = (new FlowApiService())->getParaEmpresa($empresaId, 'payment/getStatus', ['token' => $token]);
                $estado = match ((int) ($status['status'] ?? 0)) {
                    2 => 'aprobado',
                    3 => 'rechazado',
                    4 => 'anulado',
                    default => 'pendiente',
                };
            } catch (\Throwable $e) {
                $estado = 'pendiente';
            }
        }

        $orden = $_SESSION['catalogo_checkout_' . $token] ?? null;
        if (!is_array($orden) && $token !== '') {
            $compra = (new CatalogoCompra())->buscarPorToken($token);
            if (is_array($compra)) {
                $orden = [
                    'comprador' => [
                        'nombre' => (string) ($compra['comprador_nombre'] ?? ''),
                        'correo' => (string) ($compra['comprador_correo'] ?? ''),
                        'telefono' => (string) ($compra['comprador_telefono'] ?? ''),
                        'envio_metodo' => (string) ($compra['envio_metodo'] ?? 'starken'),
                        'direccion' => (string) ($compra['envio_direccion'] ?? ''),
                        'comuna' => (string) ($compra['envio_comuna'] ?? ''),
                        'ciudad' => (string) ($compra['envio_ciudad'] ?? ''),
                        'region' => (string) ($compra['envio_region'] ?? ''),
                    ],
                    'total' => (float) ($compra['total'] ?? 0),
                    'items' => (new CatalogoCompra())->listarItems((int) ($compra['id'] ?? 0)),
                ];
            }
        }
        $ocultarNavbarPublico = true;
        $this->vistaPublica('publico/catalogo_checkout_exito', compact('empresa', 'estado', 'orden', 'token', 'ocultarNavbarPublico'), 'catalogo_publico');
    }

    private function obtenerContextoCatalogo(int $empresaId): ?array
    {
        $empresa = (new Empresa())->buscar($empresaId);
        if (!$empresa || (string) ($empresa['estado'] ?? '') === 'cancelada') {
            return null;
        }

        $logoCatalogo = url('/catalogo/' . $empresaId . '/logo?v=' . rawurlencode((string) ($empresa['fecha_actualizacion'] ?? time())));
        $sliderCatalogo = [
            'imagen' => url('/catalogo/' . $empresaId . '/slider/principal?v=' . rawurlencode((string) ($empresa['fecha_actualizacion'] ?? time()))),
            'imagen_secundaria' => url('/catalogo/' . $empresaId . '/slider/secundaria?v=' . rawurlencode((string) ($empresa['fecha_actualizacion'] ?? time()))),
            'titulo' => trim((string) ($empresa['slider_titulo'] ?? '')),
            'bajada' => trim((string) ($empresa['slider_bajada'] ?? '')),
            'boton_texto' => trim((string) ($empresa['slider_boton_texto'] ?? '')),
            'boton_url' => trim((string) ($empresa['slider_boton_url'] ?? '')),
        ];
        $catalogoTopbar = [
            'texto' => trim((string) ($empresa['catalogo_topbar_texto'] ?? '')),
            'color_primario' => trim((string) ($empresa['catalogo_color_primario'] ?? '')),
            'color_acento' => trim((string) ($empresa['catalogo_color_acento'] ?? '')),
            'columnas_productos' => (int) ($empresa['catalogo_columnas_productos'] ?? 3),
            'nosotros_titulo' => trim((string) ($empresa['catalogo_nosotros_titulo'] ?? '')),
            'nosotros_descripcion' => trim((string) ($empresa['catalogo_nosotros_descripcion'] ?? '')),
            'nosotros_bloque_titulo' => trim((string) ($empresa['catalogo_nosotros_bloque_titulo'] ?? '')),
            'nosotros_bloque_texto' => trim((string) ($empresa['catalogo_nosotros_bloque_texto'] ?? '')),
            'contacto_titulo' => trim((string) ($empresa['catalogo_contacto_titulo'] ?? '')),
            'contacto_descripcion' => trim((string) ($empresa['catalogo_contacto_descripcion'] ?? '')),
            'contacto_horario' => trim((string) ($empresa['catalogo_contacto_horario'] ?? '')),
            'contacto_whatsapp' => trim((string) ($empresa['catalogo_contacto_whatsapp'] ?? '')),
            'contacto_form_titulo' => trim((string) ($empresa['catalogo_contacto_form_titulo'] ?? '')),
            'contacto_form_subtitulo' => trim((string) ($empresa['catalogo_contacto_form_subtitulo'] ?? '')),
            'contacto_form_bajada' => trim((string) ($empresa['catalogo_contacto_form_bajada'] ?? '')),
            'contacto_form_correo_destino' => trim((string) ($empresa['catalogo_contacto_form_correo_destino'] ?? '')),
            'contacto_form_campos' => trim((string) ($empresa['catalogo_contacto_form_campos'] ?? '')),
            'contacto_form_texto_boton' => trim((string) ($empresa['catalogo_contacto_form_texto_boton'] ?? '')),
            'contacto_mapa_url' => trim((string) ($empresa['catalogo_contacto_mapa_url'] ?? '')),
            'contacto_mapa_activo' => (string) ($empresa['catalogo_contacto_mapa_activo'] ?? '1'),
            'sociales' => [
                'facebook' => trim((string) ($empresa['catalogo_social_facebook'] ?? '')),
                'instagram' => trim((string) ($empresa['catalogo_social_instagram'] ?? '')),
                'tiktok' => trim((string) ($empresa['catalogo_social_tiktok'] ?? '')),
                'linkedin' => trim((string) ($empresa['catalogo_social_linkedin'] ?? '')),
                'youtube' => trim((string) ($empresa['catalogo_social_youtube'] ?? '')),
            ],
            'nosotros_imagen' => url('/catalogo/' . $empresaId . '/nosotros/imagen?v=' . rawurlencode((string) ($empresa['fecha_actualizacion'] ?? time()))),
            'nosotros_banner_imagen' => url('/catalogo/' . $empresaId . '/nosotros/banner?v=' . rawurlencode((string) ($empresa['fecha_actualizacion'] ?? time()))),
        ];

        return [
            'empresa' => $empresa,
            'logoCatalogo' => $logoCatalogo,
            'sliderCatalogo' => $sliderCatalogo,
            'catalogoTopbar' => $catalogoTopbar,
        ];
    }

    private function normalizarCamposFormularioCatalogo(string $jsonCampos): array
    {
        $permitidos = ['nombre', 'telefono', 'email', 'asunto', 'mensaje', 'empresa', 'whatsapp', 'ciudad', 'direccion', 'cargo'];
        $campos = json_decode($jsonCampos, true);
        if (!is_array($campos)) {
            $campos = ['nombre', 'telefono', 'email', 'asunto', 'mensaje'];
        }
        $campos = array_values(array_unique(array_filter(array_map(static fn($campo): string => trim((string) $campo), $campos))));
        $campos = array_values(array_filter($campos, static fn($campo): bool => in_array($campo, $permitidos, true)));
        if (!in_array('nombre', $campos, true)) {
            array_unshift($campos, 'nombre');
        }
        if (!in_array('email', $campos, true)) {
            $campos[] = 'email';
        }
        if (!in_array('mensaje', $campos, true)) {
            $campos[] = 'mensaje';
        }

        return $campos;
    }

    public function imagenProducto(int $id): void
    {
        $imagenModel = new ProductoImagen();
        $imagen = $imagenModel->obtenerPorId($id);
        if (!$imagen) {
            // Compatibilidad: permite usar /media/producto/{productoId} además de {imagenId}.
            $imagen = $imagenModel->obtenerPrincipalPorProductoId($id);
        }
        if (!$imagen) {
            http_response_code(404);
            exit('Imagen no encontrada');
        }

        $rutaRel = (string) ($imagen['ruta'] ?? '');
        if ($rutaRel === '') {
            http_response_code(404);
            exit('Imagen no disponible');
        }
        $rutaRel = '/' . ltrim(str_replace('\\', '/', $rutaRel), '/');
        if (str_starts_with($rutaRel, '/public/uploads/')) {
            $rutaRel = '/uploads/' . ltrim(substr($rutaRel, 16), '/');
        } elseif (str_starts_with($rutaRel, '/aplicacion/public/uploads/')) {
            $rutaRel = '/uploads/' . ltrim(substr($rutaRel, 26), '/');
        }

        $raiz = dirname(__DIR__, 4);
        $candidatas = [
            $raiz . '/public' . $rutaRel,
            $raiz . $rutaRel,
            $raiz . '/aplicacion/public' . $rutaRel,
        ];
        $rutaAbs = null;
        foreach ($candidatas as $candidata) {
            if (is_file($candidata)) {
                $rutaAbs = $candidata;
                break;
            }
        }

        if ($rutaAbs === null) {
            http_response_code(404);
            exit('Archivo no encontrado');
        }

        $mime = (string) (mime_content_type($rutaAbs) ?: 'application/octet-stream');
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . (string) filesize($rutaAbs));
        header('Cache-Control: public, max-age=86400');
        readfile($rutaAbs);
        exit;
    }

    public function mediaArchivo(): void
    {
        $ruta = trim((string) ($_GET['ruta'] ?? ''));
        if ($ruta === '') {
            http_response_code(404);
            exit('Archivo no encontrado');
        }

        $normalizada = str_replace('\\', '/', $ruta);
        $normalizada = preg_replace('#^https?://[^/]+#i', '', $normalizada) ?? $normalizada;
        $normalizada = preg_replace('#^/?public/#i', '/', $normalizada) ?? $normalizada;
        if (!str_starts_with($normalizada, '/')) {
            $normalizada = '/' . ltrim($normalizada, '/');
        }

        if (
            !str_starts_with($normalizada, '/uploads/')
            && !str_starts_with($normalizada, '/img/')
        ) {
            http_response_code(403);
            exit('Ruta no permitida');
        }

        $raiz = dirname(__DIR__, 4);
        $candidatas = [
            $raiz . '/public' . $normalizada,
            $raiz . $normalizada,
            $raiz . '/aplicacion/public' . $normalizada,
        ];

        $rutaAbs = null;
        foreach ($candidatas as $candidata) {
            if (is_file($candidata)) {
                $rutaAbs = $candidata;
                break;
            }
        }

        if ($rutaAbs === null) {
            http_response_code(404);
            exit('Archivo no encontrado');
        }

        $mime = (string) (mime_content_type($rutaAbs) ?: 'application/octet-stream');
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . (string) filesize($rutaAbs));
        header('Cache-Control: public, max-age=86400');
        readfile($rutaAbs);
        exit;
    }

    private function resolverRutaPublicaArchivo(string $ruta): ?string
    {
        $ruta = trim($ruta);
        if ($ruta === '') {
            return null;
        }
        if (preg_match('/^https?:\/\//i', $ruta) === 1) {
            return $ruta;
        }
        if (str_starts_with($ruta, '/media/archivo')) {
            return url($ruta);
        }

        $normalizada = str_replace('\\', '/', $ruta);
        $normalizada = preg_replace('#^https?://[^/]+#i', '', $normalizada) ?? $normalizada;
        if (!str_starts_with($normalizada, '/')) {
            $normalizada = '/' . ltrim($normalizada, '/');
        }
        if (str_starts_with($normalizada, '/public/uploads/')) {
            $normalizada = '/uploads/' . ltrim(substr($normalizada, 16), '/');
        } elseif (str_starts_with($normalizada, '/aplicacion/public/uploads/')) {
            $normalizada = '/uploads/' . ltrim(substr($normalizada, 26), '/');
        }
        $normalizada = '/' . ltrim($normalizada, '/');

        if (!str_starts_with($normalizada, '/uploads/') && !str_starts_with($normalizada, '/img/')) {
            return null;
        }

        return url('/media/archivo?ruta=' . rawurlencode($normalizada));
    }

    private function emitirArchivoCatalogo(string $ruta, string $fallbackRel): void
    {
        $ruta = trim($ruta);
        if ($ruta !== '' && preg_match('/^https?:\/\//i', $ruta) === 1) {
            header('Location: ' . $ruta, true, 302);
            return;
        }
        if ($ruta !== '' && str_starts_with($ruta, '/media/archivo')) {
            header('Location: ' . url($ruta), true, 302);
            return;
        }

        $normalizada = str_replace('\\', '/', $ruta);
        if ($normalizada !== '' && !str_starts_with($normalizada, '/')) {
            $normalizada = '/' . ltrim($normalizada, '/');
        }
        if (str_starts_with($normalizada, '/public/uploads/')) {
            $normalizada = '/uploads/' . ltrim(substr($normalizada, 16), '/');
        } elseif (str_starts_with($normalizada, '/aplicacion/public/uploads/')) {
            $normalizada = '/uploads/' . ltrim(substr($normalizada, 26), '/');
        }

        $raiz = dirname(__DIR__, 4);
        $candidatas = [];
        if ($normalizada !== '') {
            $candidatas[] = $raiz . $normalizada;
            $candidatas[] = $raiz . '/public' . $normalizada;
            $candidatas[] = $raiz . '/aplicacion/public' . $normalizada;
        }
        $candidatas[] = $raiz . $fallbackRel;
        $candidatas[] = $raiz . '/public' . $fallbackRel;
        $candidatas[] = $raiz . '/aplicacion/public' . $fallbackRel;

        $rutaAbs = null;
        foreach ($candidatas as $candidata) {
            if (is_file($candidata)) {
                $rutaAbs = $candidata;
                break;
            }
        }
        if ($rutaAbs === null) {
            http_response_code(404);
            exit('Archivo no encontrado');
        }

        $mime = (string) (mime_content_type($rutaAbs) ?: 'application/octet-stream');
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . (string) filesize($rutaAbs));
        header('Cache-Control: public, max-age=300');
        readfile($rutaAbs);
        exit;
    }

    private function rutaCatalogoExiste(string $ruta): bool
    {
        $normalizada = str_replace('\\', '/', trim($ruta));
        if ($normalizada === '' || preg_match('/^https?:\/\//i', $normalizada) === 1) {
            return false;
        }
        if (!str_starts_with($normalizada, '/')) {
            $normalizada = '/' . ltrim($normalizada, '/');
        }
        if (str_starts_with($normalizada, '/public/uploads/')) {
            $normalizada = '/uploads/' . ltrim(substr($normalizada, 16), '/');
        } elseif (str_starts_with($normalizada, '/aplicacion/public/uploads/')) {
            $normalizada = '/uploads/' . ltrim(substr($normalizada, 26), '/');
        }
        $raiz = dirname(__DIR__, 4);
        $candidatas = [
            $raiz . $normalizada,
            $raiz . '/public' . $normalizada,
            $raiz . '/aplicacion/public' . $normalizada,
        ];
        foreach ($candidatas as $candidata) {
            if (is_file($candidata)) {
                return true;
            }
        }

        return false;
    }

    private function inferirRutaSliderPorEmpresa(int $empresaId, string $tipo): string
    {
        $raiz = dirname(__DIR__, 4);
        $directorios = [
            $raiz . '/public/uploads/catalogo_slider/' . $empresaId,
            $raiz . '/aplicacion/public/uploads/catalogo_slider/' . $empresaId,
        ];
        $archivos = [];
        foreach ($directorios as $dir) {
            if (!is_dir($dir)) {
                continue;
            }
            foreach ((array) glob($dir . '/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE) as $archivo) {
                if (is_file($archivo)) {
                    $archivos[] = $archivo;
                }
            }
        }
        if ($archivos === []) {
            return '';
        }
        usort($archivos, static fn (string $a, string $b): int => filemtime($b) <=> filemtime($a));
        $seleccionado = $archivos[0];
        if ($tipo === 'secundaria' && isset($archivos[1])) {
            $seleccionado = $archivos[1];
        }

        $normalizado = str_replace('\\', '/', $seleccionado);
        if (str_contains($normalizado, '/public/uploads/')) {
            $partes = explode('/public/uploads/', $normalizado, 2);
            return '/uploads/' . ($partes[1] ?? '');
        }
        if (str_contains($normalizado, '/aplicacion/public/uploads/')) {
            $partes = explode('/aplicacion/public/uploads/', $normalizado, 2);
            return '/uploads/' . ($partes[1] ?? '');
        }

        return '';
    }

    private function inferirRutaProductoPorEmpresa(int $empresaId, int $productoId): string
    {
        $raiz = dirname(__DIR__, 4);
        $directorios = [
            $raiz . '/public/uploads/productos_catalogo/' . $empresaId,
            $raiz . '/aplicacion/public/uploads/productos_catalogo/' . $empresaId,
        ];
        $patrones = [
            'prod_' . $productoId . '_*.jpg',
            'prod_' . $productoId . '_*.jpeg',
            'prod_' . $productoId . '_*.png',
            'prod_' . $productoId . '_*.webp',
            'prod_' . $productoId . '_*.JPG',
            'prod_' . $productoId . '_*.JPEG',
            'prod_' . $productoId . '_*.PNG',
            'prod_' . $productoId . '_*.WEBP',
        ];
        $archivos = [];
        foreach ($directorios as $dir) {
            if (!is_dir($dir)) {
                continue;
            }
            foreach ($patrones as $patron) {
                foreach ((array) glob($dir . '/' . $patron) as $archivo) {
                    if (is_file($archivo)) {
                        $archivos[] = $archivo;
                    }
                }
            }
        }
        if ($archivos === []) {
            return '';
        }
        usort($archivos, static fn (string $a, string $b): int => filemtime($b) <=> filemtime($a));
        $normalizado = str_replace('\\', '/', $archivos[0]);
        if (str_contains($normalizado, '/public/uploads/')) {
            $partes = explode('/public/uploads/', $normalizado, 2);
            return '/uploads/' . ($partes[1] ?? '');
        }
        if (str_contains($normalizado, '/aplicacion/public/uploads/')) {
            $partes = explode('/aplicacion/public/uploads/', $normalizado, 2);
            return '/uploads/' . ($partes[1] ?? '');
        }

        return '';
    }

    private function vistaPublica(string $vista, array $data, string $pagina): void
    {
        $this->vista($vista, array_merge($this->obtenerSeoPorPagina($pagina), $data), 'publico');
    }

    private function resolverUltimaActualizacionPagina(string $archivoVista): string
    {
        $rutaVista = __DIR__ . '/../../vistas/publico/' . $archivoVista;
        $timestamp = file_exists($rutaVista) ? (int) filemtime($rutaVista) : time();

        return gmdate('Y-m-d', $timestamp > 0 ? $timestamp : time());
    }

    private function obtenerUrlBaseSitio(): string
    {
        $esHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $esHttps = $esHttps || ((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
        $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');

        return ($esHttps ? 'https' : 'http') . '://' . $host;
    }

    private function obtenerSeoPorPagina(string $pagina): array
    {
        $seo = [
            'inicio' => [
                'meta_title' => 'Sistema de cotizaciones, punto de venta e inventario en Chile | Vextra',
                'meta_description' => 'Vextra es un software de cotización online para empresas en Chile: sistema de cotizaciones, sistema punto de venta y sistema de inventario para vender más con control real.',
                'meta_keywords' => 'sistema de cotizaciones, software de cotización online, sistema punto de venta, sistema de inventario, software para empresas chile, sistema de ventas con inventario',
            ],
            'caracteristicas' => [
                'meta_title' => 'Características del sistema de cotizaciones | Vextra',
                'meta_description' => 'Descubre las funcionalidades de Vextra: cotizaciones, clientes, productos, seguimiento comercial, POS e inventario.',
                'meta_keywords' => 'características vextra, funcionalidades software cotizaciones, gestión comercial',
            ],
            'planes' => [
                'meta_title' => 'Planes y precios del sistema | Vextra',
                'meta_description' => 'Compara planes y precios de Vextra, con modalidad mensual o anual, para implementar tu sistema de cotizaciones empresarial.',
                'meta_keywords' => 'planes vextra, precios software cotizaciones, plan mensual, plan anual',
            ],
            'contacto' => [
                'meta_title' => 'Contacto comercial | Vextra',
                'meta_description' => 'Contacta al equipo de Vextra para resolver dudas de implementación, planes, soporte y necesidades de tu operación comercial.',
                'meta_keywords' => 'contacto vextra, asesoría comercial, soporte clientes',
            ],
            'faq' => [
                'meta_title' => 'Preguntas frecuentes (FAQ) | Vextra',
                'meta_description' => 'Resuelve dudas frecuentes sobre el software de cotizaciones Vextra: funcionamiento, planes, implementación y uso diario.',
                'meta_keywords' => 'faq vextra, preguntas frecuentes software cotizaciones',
            ],
            'contratar' => [
                'meta_title' => 'Contratar plan | Vextra',
                'meta_description' => 'Inicia la contratación de tu plan Vextra y comienza a gestionar cotizaciones y ventas con una plataforma profesional.',
                'meta_keywords' => 'contratar vextra, activar plan, software cotizaciones',
            ],
            'cotizacion_publica' => [
                'meta_title' => 'Cotización en línea | Vextra',
                'meta_description' => 'Revisa el detalle de tu cotización en línea, incluyendo productos, condiciones comerciales y estado de aprobación.',
                'meta_keywords' => 'cotización online, seguimiento cotización, aprobación cliente',
            ],
            'orden_compra_publica' => [
                'meta_title' => 'Orden de compra en línea | Vextra',
                'meta_description' => 'Revisa el detalle de una orden de compra en línea y descarga su versión PDF.',
                'meta_keywords' => 'orden de compra online, proveedor, documento compra',
            ],
            'catalogo_publico' => [
                'meta_title' => 'Catálogo en línea | Vextra',
                'meta_description' => 'Explora productos y servicios, usa filtros por categoría y compra con checkout Flow.',
                'meta_keywords' => 'catalogo en linea, tienda b2b, checkout flow, carrito de compra',
            ],
        ];

        return $seo[$pagina] ?? [
            'meta_title' => 'Vextra | Sistema de cotizaciones para empresas',
            'meta_description' => 'Vextra es un sistema de cotizaciones para empresas que ayuda a vender más con procesos comerciales ordenados.',
            'meta_keywords' => 'sistema de cotizaciones, software de cotizaciones, cotizaciones para empresas',
        ];
    }
}
