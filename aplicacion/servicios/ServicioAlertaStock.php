<?php

namespace Aplicacion\Servicios;

use Aplicacion\Modelos\Empresa;
use Aplicacion\Nucleo\BaseDatos;

class ServicioAlertaStock
{
    private const CLAVES = [
        'activar_alerta_stock_bajo',
        'activar_alerta_stock_critico',
        'destinatarios_alerta_stock',
        'asunto_stock_bajo',
        'asunto_stock_critico',
        'plantilla_html_stock_bajo',
        'plantilla_html_stock_critico',
    ];

    public function evaluarYNotificar(int $empresaId, int $productoId, float $stockAnterior, float $stockActual, string $usuario = ''): void
    {
        $db = BaseDatos::obtener();
        $stmt = $db->prepare('SELECT id,codigo,nombre,COALESCE(stock_minimo,0) AS stock_minimo,COALESCE(stock_critico,0) AS stock_critico,COALESCE(stock_actual,0) AS stock_actual,ultimo_aviso_stock_bajo,ultimo_aviso_stock_critico FROM productos WHERE id=:id AND empresa_id=:empresa_id AND fecha_eliminacion IS NULL LIMIT 1');
        $stmt->execute(['id' => $productoId, 'empresa_id' => $empresaId]);
        $producto = $stmt->fetch();
        if (!$producto) {
            return;
        }

        $nivelAnterior = $this->resolverNivel($stockAnterior, (float) $producto['stock_minimo'], (float) $producto['stock_critico']);
        $nivelActual = $this->resolverNivel($stockActual, (float) $producto['stock_minimo'], (float) $producto['stock_critico']);

        if ($nivelAnterior === $nivelActual) {
            return;
        }

        if ($nivelActual === 'normal') {
            $db->prepare('UPDATE productos SET ultimo_aviso_stock_bajo = NULL, ultimo_aviso_stock_critico = NULL, fecha_actualizacion = NOW() WHERE id=:id AND empresa_id=:empresa_id')
                ->execute(['id' => $productoId, 'empresa_id' => $empresaId]);
            return;
        }

        $config = $this->obtenerConfiguracion($empresaId);
        $destinatarios = $this->normalizarDestinatarios((string) $config['destinatarios_alerta_stock']);
        if ($destinatarios === []) {
            return;
        }

        $empresa = (new Empresa())->buscar($empresaId) ?: [];
        $variables = $this->armarVariables($empresa, $producto, $stockActual, $usuario);
        $correo = new ServicioCorreo();

        if ($nivelActual === 'critico' && $config['activar_alerta_stock_critico'] === '1') {
            $asunto = $this->render((string) $config['asunto_stock_critico'], $variables);
            $html = $this->render((string) $config['plantilla_html_stock_critico'], $variables);
            foreach ($destinatarios as $destino) {
                $correo->enviarConEmpresa($empresaId, $destino, $asunto, 'alerta_stock_critico', ['html' => $html, 'variables' => $variables]);
            }
            $db->prepare('UPDATE productos SET ultimo_aviso_stock_critico = NOW(), fecha_actualizacion = NOW() WHERE id=:id AND empresa_id=:empresa_id')
                ->execute(['id' => $productoId, 'empresa_id' => $empresaId]);
            return;
        }

        if ($nivelActual === 'bajo' && $config['activar_alerta_stock_bajo'] === '1' && $nivelAnterior === 'normal') {
            $asunto = $this->render((string) $config['asunto_stock_bajo'], $variables);
            $html = $this->render((string) $config['plantilla_html_stock_bajo'], $variables);
            foreach ($destinatarios as $destino) {
                $correo->enviarConEmpresa($empresaId, $destino, $asunto, 'alerta_stock_bajo', ['html' => $html, 'variables' => $variables]);
            }
            $db->prepare('UPDATE productos SET ultimo_aviso_stock_bajo = NOW(), fecha_actualizacion = NOW() WHERE id=:id AND empresa_id=:empresa_id')
                ->execute(['id' => $productoId, 'empresa_id' => $empresaId]);
        }
    }

    public function obtenerConfiguracion(int $empresaId): array
    {
        $db = BaseDatos::obtener();
        $in = implode(',', array_fill(0, count(self::CLAVES), '?'));
        $stmt = $db->prepare("SELECT clave, valor FROM configuraciones_empresa WHERE empresa_id = ? AND clave IN ($in)");
        $stmt->execute(array_merge([$empresaId], self::CLAVES));
        $rows = $stmt->fetchAll();
        $cfg = $this->valoresPorDefecto();
        foreach ($rows as $row) {
            $cfg[$row['clave']] = (string) ($row['valor'] ?? '');
        }
        return $cfg;
    }

    public function guardarConfiguracion(int $empresaId, array $data): void
    {
        $db = BaseDatos::obtener();
        $stmt = $db->prepare('INSERT INTO configuraciones_empresa (empresa_id, clave, valor, fecha_actualizacion) VALUES (:empresa_id,:clave,:valor,NOW()) ON DUPLICATE KEY UPDATE valor = VALUES(valor), fecha_actualizacion = NOW()');
        foreach (self::CLAVES as $clave) {
            $stmt->execute([
                'empresa_id' => $empresaId,
                'clave' => $clave,
                'valor' => (string) ($data[$clave] ?? ''),
            ]);
        }
    }

    public function vistaPrevia(string $html, array $variables): string
    {
        $seguras = [];
        foreach ($variables as $k => $v) {
            $seguras[$k] = htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        }
        return strtr($html, $seguras);
    }

    public function variablesAyuda(): array
    {
        return ['{empresa}','{producto}','{codigo}','{stock_actual}','{stock_minimo}','{stock_critico}','{fecha}','{usuario}'];
    }

    public function valoresPorDefecto(): array
    {
        return [
            'activar_alerta_stock_bajo' => '1',
            'activar_alerta_stock_critico' => '1',
            'destinatarios_alerta_stock' => '',
            'asunto_stock_bajo' => 'Stock bajo: {producto} ({codigo})',
            'asunto_stock_critico' => 'Stock crítico: {producto} ({codigo})',
            'plantilla_html_stock_bajo' => '<div style="font-family:Arial,sans-serif"><h2 style="color:#b45309;">Alerta de stock bajo</h2><p>Empresa: <strong>{empresa}</strong></p><p>Producto: <strong>{producto}</strong> ({codigo})</p><p>Stock actual: <strong>{stock_actual}</strong></p><p>Mínimo: {stock_minimo} · Crítico: {stock_critico}</p><p>Fecha: {fecha}</p><p>Usuario: {usuario}</p></div>',
            'plantilla_html_stock_critico' => '<div style="font-family:Arial,sans-serif"><h2 style="color:#b91c1c;">Alerta de stock crítico</h2><p>Empresa: <strong>{empresa}</strong></p><p>Producto: <strong>{producto}</strong> ({codigo})</p><p>Stock actual: <strong>{stock_actual}</strong></p><p>Mínimo: {stock_minimo} · Crítico: {stock_critico}</p><p>Fecha: {fecha}</p><p>Usuario: {usuario}</p></div>',
        ];
    }

    private function armarVariables(array $empresa, array $producto, float $stockActual, string $usuario): array
    {
        return [
            '{empresa}' => (string) ($empresa['nombre_comercial'] ?? $empresa['razon_social'] ?? 'Empresa'),
            '{producto}' => (string) ($producto['nombre'] ?? ''),
            '{codigo}' => (string) ($producto['codigo'] ?? ''),
            '{stock_actual}' => number_format($stockActual, 2, '.', ''),
            '{stock_minimo}' => number_format((float) ($producto['stock_minimo'] ?? 0), 2, '.', ''),
            '{stock_critico}' => number_format((float) ($producto['stock_critico'] ?? 0), 2, '.', ''),
            '{fecha}' => date('Y-m-d H:i:s'),
            '{usuario}' => $usuario !== '' ? $usuario : 'Sistema',
        ];
    }

    private function resolverNivel(float $stock, float $minimo, float $critico): string
    {
        if ($stock <= $critico) {
            return 'critico';
        }
        if ($stock <= $minimo) {
            return 'bajo';
        }
        return 'normal';
    }

    private function render(string $texto, array $vars): string
    {
        return strtr($texto, $vars);
    }

    private function normalizarDestinatarios(string $raw): array
    {
        $partes = preg_split('/[;,\s]+/', $raw) ?: [];
        $validos = [];
        foreach ($partes as $parte) {
            $correo = trim($parte);
            if ($correo !== '' && filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $validos[$correo] = true;
            }
        }
        return array_keys($validos);
    }
}
