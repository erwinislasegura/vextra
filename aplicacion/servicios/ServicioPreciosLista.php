<?php

namespace Aplicacion\Servicios;

use Aplicacion\Nucleo\BaseDatos;
use PDO;

class ServicioPreciosLista
{
    private PDO $db;

    public function __construct()
    {
        $this->db = BaseDatos::obtener();
    }

    public function calcularPrecioProducto(int $empresaId, int $productoId, ?int $clienteId = null, ?string $canal = null, ?string $fecha = null, ?int $listaPrecioId = null): ?array
    {
        $producto = $this->obtenerProducto($empresaId, $productoId);
        if (!$producto) {
            return null;
        }

        $lista = $this->resolverListaPrecio($empresaId, $clienteId, $canal, $fecha, $listaPrecioId);
        $regla = $lista ? $this->resolverRegla($empresaId, (int) $lista['id'], (int) $producto['id'], (int) ($producto['categoria_id'] ?? 0)) : null;
        if ($regla === null && $lista) {
            $regla = $this->resolverReglaDesdeCamposLista($lista);
        }
        if ($regla === null && $lista) {
            $regla = $this->resolverReglaDesdeTexto((string) ($lista['reglas_base'] ?? ''));
        }

        $precioBase = (float) ($producto['precio'] ?? 0);
        $porcentaje = (float) ($regla['porcentaje'] ?? 0);
        $tipoAjuste = $regla['tipo_ajuste'] ?? 'incremento';
        $factor = $tipoAjuste === 'descuento' ? -1 : 1;

        $precioFinal = $precioBase * (1 + (($porcentaje * $factor) / 100));
        $precioFinal = max(0, round($precioFinal, 2));

        return [
            'producto_id' => (int) $producto['id'],
            'producto' => $producto['nombre'] ?? '',
            'precio_base' => round($precioBase, 2),
            'precio_final' => $precioFinal,
            'ajuste_porcentaje' => $porcentaje,
            'ajuste_tipo' => $tipoAjuste,
            'lista_precio_id' => $lista ? (int) $lista['id'] : null,
            'lista_precio_nombre' => $lista['nombre'] ?? null,
            'canal' => $canal ?: null,
            'regla_aplicada' => $regla ? [
                'id' => (int) $regla['id'],
                'ambito' => $regla['ambito'],
                'prioridad' => (int) $regla['prioridad'],
            ] : null,
        ];
    }

    public function resolverListaPrecio(int $empresaId, ?int $clienteId = null, ?string $canal = null, ?string $fecha = null, ?int $listaPrecioId = null): ?array
    {
        if (!$this->tablaExiste('listas_precios')) {
            return null;
        }

        $fechaRef = $fecha ?: date('Y-m-d');

        if ($listaPrecioId !== null && $listaPrecioId > 0) {
            $sql = 'SELECT * FROM listas_precios WHERE empresa_id = :empresa_id AND id = :id';
            $params = [
                'empresa_id' => $empresaId,
                'id' => $listaPrecioId,
            ];

            if ($this->columnaExiste('listas_precios', 'estado')) {
                $sql .= ' AND estado = "activo"';
            }
            if ($this->columnaExiste('listas_precios', 'vigencia_desde')) {
                $sql .= ' AND (vigencia_desde IS NULL OR vigencia_desde <= :fecha_ref)';
                $params['fecha_ref'] = $fechaRef;
            }
            if ($this->columnaExiste('listas_precios', 'vigencia_hasta')) {
                $sql .= ' AND (vigencia_hasta IS NULL OR vigencia_hasta >= :fecha_ref)';
                $params['fecha_ref'] = $fechaRef;
            }
            $sql .= ' LIMIT 1';

            $stmtLista = $this->db->prepare($sql);
            $stmtLista->execute($params);
            $listaManual = $stmtLista->fetch();
            if ($listaManual) {
                return $listaManual;
            }

            return null;
        }

        return null;
    }

    private function resolverRegla(int $empresaId, int $listaId, int $productoId, int $categoriaId): ?array
    {
        if (!$this->tablaExiste('listas_precios_reglas')) {
            return null;
        }

        $sql = 'SELECT * FROM listas_precios_reglas
            WHERE empresa_id = :empresa_id
              AND lista_precio_id = :lista_precio_id
              AND estado = "activo"
              AND (
                ambito = "global"
                OR (ambito = "producto" AND producto_id = :producto_id)
                OR (ambito = "categoria" AND categoria_id = :categoria_id)
              )
            ORDER BY
              CASE
                WHEN ambito = "producto" THEN 1
                WHEN ambito = "categoria" THEN 2
                ELSE 3
              END,
              prioridad ASC,
              id DESC
            LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'empresa_id' => $empresaId,
            'lista_precio_id' => $listaId,
            'producto_id' => $productoId,
            'categoria_id' => $categoriaId,
        ]);
        return $stmt->fetch() ?: null;
    }

    private function obtenerProducto(int $empresaId, int $productoId): ?array
    {
        $stmt = $this->db->prepare('SELECT id, categoria_id, nombre, precio
            FROM productos
            WHERE empresa_id = :empresa_id AND id = :id AND fecha_eliminacion IS NULL
            LIMIT 1');
        $stmt->execute(['empresa_id' => $empresaId, 'id' => $productoId]);
        return $stmt->fetch() ?: null;
    }

    private function resolverReglaDesdeTexto(string $reglasBase): ?array
    {
        if ($reglasBase === '') {
            return null;
        }

        if (preg_match('/([+-]?\d+(?:[\.,]\d+)?)\s*%/', $reglasBase, $matches) !== 1) {
            return null;
        }

        $valor = (float) str_replace(',', '.', $matches[1]);
        $tipo = $valor < 0 ? 'descuento' : 'incremento';

        return [
            'id' => 0,
            'ambito' => 'global',
            'prioridad' => 999,
            'porcentaje' => abs($valor),
            'tipo_ajuste' => $tipo,
        ];
    }

    private function resolverReglaDesdeCamposLista(array $lista): ?array
    {
        $porcentaje = (float) ($lista['ajuste_porcentaje'] ?? 0);
        if ($porcentaje <= 0) {
            return null;
        }

        $tipo = ($lista['ajuste_tipo'] ?? 'incremento') === 'descuento' ? 'descuento' : 'incremento';

        return [
            'id' => 0,
            'ambito' => 'global',
            'prioridad' => 500,
            'porcentaje' => $porcentaje,
            'tipo_ajuste' => $tipo,
        ];
    }

    private function tablaExiste(string $tabla): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :tabla LIMIT 1');
        $stmt->execute(['tabla' => $tabla]);
        return (bool) $stmt->fetchColumn();
    }

    private function columnaExiste(string $tabla, string $columna): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :tabla AND COLUMN_NAME = :columna LIMIT 1');
        $stmt->execute(['tabla' => $tabla, 'columna' => $columna]);
        return (bool) $stmt->fetchColumn();
    }

}
