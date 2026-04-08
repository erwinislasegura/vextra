<?php

namespace Aplicacion\Controladores\Empresa;

use Aplicacion\Nucleo\Controlador;
use Aplicacion\Modelos\Producto;
use Aplicacion\Modelos\GestionComercial;
use Aplicacion\Modelos\Inventario;
use Aplicacion\Servicios\ExcelExpoFormato;
use Aplicacion\Servicios\ServicioPlan;

class ProductosControlador extends Controlador
{
    public function index(): void
    {
        $buscar = trim($_GET['q'] ?? '');
        $productos = (new Producto())->listar(empresa_actual_id(), $buscar);
        $categorias = (new GestionComercial())->listarTablaEmpresa('categorias_productos', empresa_actual_id(), '', 200);
        $this->vista('empresa/productos/index', compact('productos', 'buscar', 'categorias'), 'empresa');
    }

    public function crear(): void
    {
        $categorias = (new GestionComercial())->listarTablaEmpresa('categorias_productos', empresa_actual_id(), '', 200);
        $this->vista('empresa/productos/formulario', ['producto' => null, 'categorias' => $categorias], 'empresa');
    }

    public function guardar(): void
    {
        validar_csrf();
        $empresaId = empresa_actual_id();
        $modelo = new Producto();
        (new ServicioPlan())->validarLimite($empresaId, 'maximo_productos', $modelo->contar($empresaId), 'Has alcanzado el máximo de productos permitido por tu plan.');

        $modelo->crear([
            'empresa_id' => $empresaId,
            'categoria_id' => (int) ($_POST['categoria_id'] ?? 0) ?: null,
            'tipo' => $_POST['tipo'] ?? 'producto',
            'codigo' => trim($_POST['codigo'] ?? ''),
            'nombre' => trim($_POST['nombre'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'sku' => trim($_POST['sku'] ?? ''),
            'codigo_barras' => trim($_POST['codigo_barras'] ?? ''),
            'unidad' => trim($_POST['unidad'] ?? 'unidad'),
            'precio' => (float) ($_POST['precio'] ?? 0),
            'costo' => (float) ($_POST['costo'] ?? 0),
            'impuesto' => (float) ($_POST['impuesto'] ?? 0),
            'descuento_maximo' => (float) ($_POST['descuento_maximo'] ?? 0),
            'stock_minimo' => (float) ($_POST['stock_minimo'] ?? 0),
            'stock_aviso' => (float) ($_POST['stock_critico'] ?? 0),
            'stock_actual' => (float) ($_POST['stock_actual'] ?? 0),
            'stock_critico' => (float) ($_POST['stock_critico'] ?? 0),
            'estado' => $_POST['estado'] ?? 'activo',
        ]);
        flash('success', 'Producto creado correctamente.');
        $this->redirigir($this->obtenerRutaRetorno('/app/productos'));
    }

    public function cargaMasiva(): void
    {
        $this->vista('empresa/productos/carga_masiva', [], 'empresa');
    }

    public function importarProductosMasivo(): void
    {
        validar_csrf();

        if (!isset($_FILES['archivo_productos']) || (int) ($_FILES['archivo_productos']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            flash('danger', 'Debes subir un archivo Excel válido para productos.');
            $this->redirigir('/app/productos/carga-masiva');
        }

        $filas = $this->leerArchivoMasivo((string) $_FILES['archivo_productos']['tmp_name'], (string) ($_FILES['archivo_productos']['name'] ?? ''));
        if ($filas === []) {
            flash('danger', 'El archivo no tiene datos para importar.');
            $this->redirigir('/app/productos/carga-masiva');
        }

        $empresaId = empresa_actual_id();
        $gestion = new GestionComercial();
        $modeloProducto = new Producto();

        $categorias = $gestion->listarTablaEmpresa('categorias_productos', $empresaId, '', 2000);
        $categoriasPorNombre = [];
        foreach ($categorias as $categoria) {
            $categoriasPorNombre[mb_strtolower(trim((string) ($categoria['nombre'] ?? '')))] = (int) $categoria['id'];
        }

        $creados = 0;
        $omitidos = 0;

        foreach ($filas as $fila) {
            $codigo = trim((string) ($fila['codigo'] ?? ''));
            $nombre = trim((string) ($fila['nombre'] ?? ''));

            if ($codigo === '' || $nombre === '') {
                $omitidos++;
                continue;
            }

            $categoriaId = null;
            $nombreCategoria = trim((string) ($fila['categoria'] ?? ''));
            if ($nombreCategoria !== '') {
                $llaveCategoria = mb_strtolower($nombreCategoria);
                if (!isset($categoriasPorNombre[$llaveCategoria])) {
                    $nuevaCategoriaId = $gestion->crear('categorias_productos', [
                        'empresa_id' => $empresaId,
                        'nombre' => $nombreCategoria,
                        'descripcion' => 'Creada por carga masiva de productos',
                        'estado' => 'activo',
                        'fecha_creacion' => date('Y-m-d H:i:s'),
                    ]);
                    $categoriasPorNombre[$llaveCategoria] = $nuevaCategoriaId;
                }
                $categoriaId = $categoriasPorNombre[$llaveCategoria];
            }

            $modeloProducto->crear([
                'empresa_id' => $empresaId,
                'categoria_id' => $categoriaId,
                'tipo' => $this->normalizarTipo((string) ($fila['tipo'] ?? 'producto')),
                'codigo' => $codigo,
                'sku' => trim((string) ($fila['sku'] ?? '')),
                'codigo_barras' => trim((string) ($fila['codigo_barras'] ?? '')),
                'nombre' => $nombre,
                'descripcion' => trim((string) ($fila['descripcion'] ?? '')),
                'unidad' => trim((string) ($fila['unidad'] ?? 'unidad')),
                'precio' => (float) ($fila['precio'] ?? 0),
                'costo' => (float) ($fila['costo'] ?? 0),
                'impuesto' => (float) ($fila['impuesto'] ?? 0),
                'descuento_maximo' => (float) ($fila['descuento_maximo'] ?? 0),
                'stock_minimo' => (float) ($fila['stock_minimo'] ?? 0),
                'stock_aviso' => (float) ($fila['stock_aviso'] ?? ($fila['stock_critico'] ?? 0)),
                'stock_actual' => (float) ($fila['stock_actual'] ?? 0),
                'stock_critico' => (float) ($fila['stock_critico'] ?? ($fila['stock_aviso'] ?? 0)),
                'estado' => $this->normalizarEstado((string) ($fila['estado'] ?? 'activo')),
            ]);
            $creados++;
        }

        flash('success', "Carga finalizada: {$creados} productos creados. Omitidos: {$omitidos}.");
        $this->redirigir('/app/productos/carga-masiva');
    }

    public function importarCategoriasMasivo(): void
    {
        validar_csrf();

        if (!isset($_FILES['archivo_categorias']) || (int) ($_FILES['archivo_categorias']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            flash('danger', 'Debes subir un archivo Excel válido para categorías.');
            $this->redirigir('/app/productos/carga-masiva');
        }

        $filas = $this->leerArchivoMasivo((string) $_FILES['archivo_categorias']['tmp_name'], (string) ($_FILES['archivo_categorias']['name'] ?? ''));
        if ($filas === []) {
            flash('danger', 'El archivo de categorías no tiene datos para importar.');
            $this->redirigir('/app/productos/carga-masiva');
        }

        $empresaId = empresa_actual_id();
        $gestion = new GestionComercial();
        $existentes = $gestion->listarTablaEmpresa('categorias_productos', $empresaId, '', 5000);
        $nombreExistente = [];
        foreach ($existentes as $categoria) {
            $nombreExistente[mb_strtolower(trim((string) ($categoria['nombre'] ?? '')))] = true;
        }

        $creadas = 0;
        $omitidas = 0;

        foreach ($filas as $fila) {
            $nombre = trim((string) ($fila['nombre'] ?? ''));
            if ($nombre === '') {
                $omitidas++;
                continue;
            }

            $llave = mb_strtolower($nombre);
            if (isset($nombreExistente[$llave])) {
                $omitidas++;
                continue;
            }

            $gestion->crear('categorias_productos', [
                'empresa_id' => $empresaId,
                'nombre' => $nombre,
                'descripcion' => trim((string) ($fila['descripcion'] ?? '')),
                'estado' => $this->normalizarEstado((string) ($fila['estado'] ?? 'activo')),
                'fecha_creacion' => date('Y-m-d H:i:s'),
            ]);
            $nombreExistente[$llave] = true;
            $creadas++;
        }

        flash('success', "Carga de categorías finalizada: {$creadas} creadas. Omitidas: {$omitidas}.");
        $this->redirigir('/app/productos/carga-masiva');
    }

    public function descargarPlantillaProductosExcel(): void
    {
        $nombreArchivo = 'plantilla_carga_masiva_productos.xls';
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "\xEF\xBB\xBF";
        echo '<html><head><meta charset="UTF-8"></head><body>';
        echo '<table border="1" cellspacing="0" cellpadding="4" style="' . ExcelExpoFormato::TABLA_ESTILO . '">';
        echo '<tr style="' . ExcelExpoFormato::ENCABEZADO_ESTILO . '"><th>tipo</th><th>categoria</th><th>codigo</th><th>sku</th><th>codigo_barras</th><th>nombre</th><th>descripcion</th><th>unidad</th><th>precio</th><th>costo</th><th>impuesto</th><th>descuento_maximo</th><th>stock_minimo</th><th>stock_critico</th><th>stock_actual</th><th>estado</th></tr>';
        echo '<tr><td>producto</td><td>Bebidas</td><td>P-001</td><td>SKU-001</td><td>7701234567890</td><td>Agua 600ml</td><td>Botella de agua sin gas</td><td>unidad</td><td>2.50</td><td>1.10</td><td>19</td><td>10</td><td>30</td><td>10</td><td>50</td><td>activo</td></tr>';
        echo '<tr><td>servicio</td><td>Soporte</td><td>S-001</td><td>SRV-001</td><td></td><td>Mantenimiento mensual</td><td>Servicio técnico preventivo</td><td>servicio</td><td>120.00</td><td>0</td><td>19</td><td>0</td><td>0</td><td>0</td><td>0</td><td>activo</td></tr>';
        echo '</table></body></html>';
        exit;
    }

    public function descargarPlantillaCategoriasExcel(): void
    {
        $nombreArchivo = 'plantilla_carga_masiva_categorias.xls';
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "\xEF\xBB\xBF";
        echo '<html><head><meta charset="UTF-8"></head><body>';
        echo '<table border="1" cellspacing="0" cellpadding="4" style="' . ExcelExpoFormato::TABLA_ESTILO . '">';
        echo '<tr style="' . ExcelExpoFormato::ENCABEZADO_ESTILO . '"><th>nombre</th><th>descripcion</th><th>estado</th></tr>';
        echo '<tr><td>Bebidas</td><td>Productos para consumo líquido</td><td>activo</td></tr>';
        echo '<tr><td>Insumos</td><td>Materiales de operación</td><td>activo</td></tr>';
        echo '</table></body></html>';
        exit;
    }

    public function exportarExcel(): void
    {
        $buscar = trim($_GET['q'] ?? '');
        $productos = (new Producto())->listar(empresa_actual_id(), $buscar);

        $nombreArchivo = 'productos_' . date('Ymd_His') . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "\xEF\xBB\xBF";
        echo '<html><head><meta charset="UTF-8"></head><body>';
        echo '<table border="1" cellspacing="0" cellpadding="4" style="' . ExcelExpoFormato::TABLA_ESTILO . '">';
        echo '<tr style="' . ExcelExpoFormato::ENCABEZADO_ESTILO . '">';
        echo '<th>N°</th>';
        echo '<th>ID</th>';
        echo '<th>Empresa ID</th>';
        echo '<th>Categoría ID</th>';
        echo '<th>Categoría</th>';
        echo '<th>Tipo</th>';
        echo '<th>Código</th>';
        echo '<th>SKU</th>';
        echo '<th>Código de barras</th>';
        echo '<th>Nombre</th>';
        echo '<th>Descripción</th>';
        echo '<th>Unidad</th>';
        echo '<th>Precio</th>';
        echo '<th>Costo</th>';
        echo '<th>Impuesto %</th>';
        echo '<th>Desc. máximo %</th>';
        echo '<th>Stock mínimo</th>';
        echo '<th>Stock crítico</th>';
        echo '<th>Stock actual</th>';
        echo '<th>Estado</th>';
        echo '<th>Fecha creación</th>';
        echo '<th>Fecha actualización</th>';
        echo '</tr>';

        $indice = 1;
        foreach ($productos as $producto) {
            echo '<tr>';
            echo '<td>' . $indice . '</td>';
            echo '<td>' . $this->escapeExcelHtml($producto['id'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($producto['empresa_id'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($producto['categoria_id'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($producto['categoria'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml(ucfirst((string) ($producto['tipo'] ?? 'producto'))) . '</td>';
            echo '<td style="' . ExcelExpoFormato::CELDA_TEXTO_EXCEL . '">' . $this->escapeExcelHtml($producto['codigo'] ?? '') . '</td>';
            echo '<td style="' . ExcelExpoFormato::CELDA_TEXTO_EXCEL . '">' . $this->escapeExcelHtml($producto['sku'] ?? '') . '</td>';
            echo '<td style="' . ExcelExpoFormato::CELDA_TEXTO_EXCEL . '">' . $this->escapeExcelHtml($producto['codigo_barras'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($producto['nombre'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($producto['descripcion'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($producto['unidad'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml(number_format((float) ($producto['precio'] ?? 0), 2)) . '</td>';
            echo '<td>' . $this->escapeExcelHtml(number_format((float) ($producto['costo'] ?? 0), 2)) . '</td>';
            echo '<td>' . $this->escapeExcelHtml(number_format((float) ($producto['impuesto'] ?? 0), 2)) . '</td>';
            echo '<td>' . $this->escapeExcelHtml(number_format((float) ($producto['descuento_maximo'] ?? 0), 2)) . '</td>';
            echo '<td>' . $this->escapeExcelHtml(number_format((float) ($producto['stock_minimo'] ?? 0), 2)) . '</td>';
            echo '<td>' . $this->escapeExcelHtml(number_format((float) ($producto['stock_critico'] ?? $producto['stock_aviso'] ?? 0), 2)) . '</td>';
            echo '<td>' . $this->escapeExcelHtml(number_format((float) ($producto['stock_actual'] ?? 0), 2)) . '</td>';
            echo '<td>' . $this->escapeExcelHtml(ucfirst((string) ($producto['estado'] ?? 'activo'))) . '</td>';
            echo '<td>' . $this->escapeExcelHtml($producto['fecha_creacion'] ?? '') . '</td>';
            echo '<td>' . $this->escapeExcelHtml($producto['fecha_actualizacion'] ?? '') . '</td>';
            echo '</tr>';
            $indice++;
        }

        echo '</table></body></html>';

        exit;
    }

    private function leerArchivoMasivo(string $rutaArchivo, string $nombreOriginal): array
    {
        $extension = mb_strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

        if ($extension === 'csv') {
            return $this->leerArchivoCsv($rutaArchivo);
        }

        $filas = $this->leerArchivoExcelHtml($rutaArchivo);
        if ($filas !== []) {
            return $filas;
        }

        return $this->leerArchivoCsv($rutaArchivo);
    }

    private function leerArchivoExcelHtml(string $rutaArchivo): array
    {
        if (!is_file($rutaArchivo)) {
            return [];
        }

        $contenido = (string) file_get_contents($rutaArchivo);
        if (trim($contenido) === '' || stripos($contenido, '<table') === false) {
            return [];
        }

        $internosPrevios = libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML($contenido);
        libxml_clear_errors();
        libxml_use_internal_errors($internosPrevios);

        $tabla = $dom->getElementsByTagName('table')->item(0);
        if (!$tabla) {
            return [];
        }

        $filas = [];
        $encabezados = [];
        foreach ($tabla->getElementsByTagName('tr') as $indiceFila => $filaHtml) {
            $celdas = [];
            foreach ($filaHtml->childNodes as $celda) {
                if (!in_array($celda->nodeName, ['th', 'td'], true)) {
                    continue;
                }
                $celdas[] = trim((string) $celda->textContent);
            }

            if ($celdas === []) {
                continue;
            }

            if ($indiceFila === 0) {
                $encabezados = array_map(fn($valor) => $this->normalizarEncabezado((string) $valor), $celdas);
                continue;
            }

            $registro = [];
            foreach ($encabezados as $indice => $encabezado) {
                if ($encabezado === '') {
                    continue;
                }
                $registro[$encabezado] = isset($celdas[$indice]) ? trim((string) $celdas[$indice]) : '';
            }

            if ($registro !== []) {
                $filas[] = $registro;
            }
        }

        return $filas;
    }

    private function leerArchivoCsv(string $rutaArchivo): array
    {
        if (!is_file($rutaArchivo)) {
            return [];
        }

        $filas = [];
        $handle = fopen($rutaArchivo, 'rb');
        if ($handle === false) {
            return [];
        }

        $encabezados = [];
        while (($fila = fgetcsv($handle, 0, ',')) !== false) {
            if ($encabezados === []) {
                $encabezados = array_map(fn($valor) => $this->normalizarEncabezado((string) $valor), $fila);
                continue;
            }

            if (count(array_filter($fila, fn($valor) => trim((string) $valor) !== '')) === 0) {
                continue;
            }

            $registro = [];
            foreach ($encabezados as $indice => $encabezado) {
                if ($encabezado === '') {
                    continue;
                }
                $registro[$encabezado] = isset($fila[$indice]) ? trim((string) $fila[$indice]) : '';
            }
            $filas[] = $registro;
        }

        fclose($handle);

        return $filas;
    }

    private function normalizarEncabezado(string $encabezado): string
    {
        $normalizado = mb_strtolower(trim($encabezado));
        $normalizado = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ', ' '], ['a', 'e', 'i', 'o', 'u', 'n', '_'], $normalizado);

        $mapa = [
            'codigo_de_barras' => 'codigo_barras',
            'cod_barras' => 'codigo_barras',
            'stock_min' => 'stock_minimo',
            'stock_alerta' => 'stock_aviso',
            'stock_critico' => 'stock_critico',
            'stock_actual' => 'stock_actual',
            'descuento_max' => 'descuento_maximo',
        ];

        return $mapa[$normalizado] ?? $normalizado;
    }

    private function normalizarTipo(string $tipo): string
    {
        return mb_strtolower(trim($tipo)) === 'servicio' ? 'servicio' : 'producto';
    }

    private function normalizarEstado(string $estado): string
    {
        return mb_strtolower(trim($estado)) === 'inactivo' ? 'inactivo' : 'activo';
    }

    private function escapeExcelHtml(mixed $valor): string
    {
        $texto = trim(str_replace(["\r\n", "\r", "\n", "\t"], ' ', (string) $valor));

        if ($texto !== '' && preg_match('/^[=+\-@]/', $texto) === 1) {
            $texto = "'" . $texto;
        }

        return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
    }

    private function obtenerRutaRetorno(string $rutaPredeterminada): string
    {
        $ruta = trim($_POST['redirect_to'] ?? '');
        if ($ruta !== '' && strpos($ruta, '/app/') === 0) {
            return $ruta;
        }

        return $rutaPredeterminada;
    }

    public function movimientos(int $id): void
    {
        $empresaId = (int) empresa_actual_id();
        $producto = (new Producto())->obtenerPorId($empresaId, $id);

        header('Content-Type: application/json; charset=UTF-8');

        if (!$producto) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'mensaje' => 'Producto no encontrado.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $movimientos = (new Inventario())->listarMovimientos($empresaId, $id);
        echo json_encode([
            'ok' => true,
            'producto' => [
                'id' => (int) $producto['id'],
                'nombre' => (string) ($producto['nombre'] ?? ''),
                'codigo' => (string) ($producto['codigo'] ?? ''),
            ],
            'movimientos' => $movimientos,
        ], JSON_UNESCAPED_UNICODE);
    }

    public function ver(int $id): void
    {
        $producto = (new Producto())->obtenerPorId(empresa_actual_id(), $id);
        if (!$producto) {
            flash('danger', 'Producto no encontrado.');
            $this->redirigir('/app/productos');
        }
        $this->vista('empresa/productos/ver', compact('producto'), 'empresa');
    }

    public function editar(int $id): void
    {
        $empresaId = empresa_actual_id();
        $producto = (new Producto())->obtenerPorId($empresaId, $id);
        if (!$producto) {
            flash('danger', 'Producto no encontrado.');
            $this->redirigir('/app/productos');
        }
        $categorias = (new GestionComercial())->listarTablaEmpresa('categorias_productos', $empresaId, '', 200);
        $this->vista('empresa/productos/editar', compact('producto', 'categorias'), 'empresa');
    }

    public function actualizar(int $id): void
    {
        validar_csrf();
        (new Producto())->actualizar(empresa_actual_id(), $id, [
            'categoria_id' => (int) ($_POST['categoria_id'] ?? 0) ?: null,
            'tipo' => $_POST['tipo'] ?? 'producto',
            'codigo' => trim($_POST['codigo'] ?? ''),
            'nombre' => trim($_POST['nombre'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'sku' => trim($_POST['sku'] ?? ''),
            'codigo_barras' => trim($_POST['codigo_barras'] ?? ''),
            'unidad' => trim($_POST['unidad'] ?? 'unidad'),
            'precio' => (float) ($_POST['precio'] ?? 0),
            'costo' => (float) ($_POST['costo'] ?? 0),
            'impuesto' => (float) ($_POST['impuesto'] ?? 0),
            'descuento_maximo' => (float) ($_POST['descuento_maximo'] ?? 0),
            'stock_minimo' => (float) ($_POST['stock_minimo'] ?? 0),
            'stock_aviso' => (float) ($_POST['stock_critico'] ?? 0),
            'stock_actual' => (float) ($_POST['stock_actual'] ?? 0),
            'stock_critico' => (float) ($_POST['stock_critico'] ?? 0),
            'estado' => $_POST['estado'] ?? 'activo',
        ]);
        flash('success', 'Producto actualizado correctamente.');
        $this->redirigir('/app/productos');
    }

    public function eliminar(int $id): void
    {
        validar_csrf();
        (new Producto())->eliminar(empresa_actual_id(), $id);
        flash('success', 'Producto eliminado correctamente.');
        $this->redirigir('/app/productos');
    }
}
