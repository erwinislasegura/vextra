<?php

namespace Aplicacion\Modelos;

use Aplicacion\Nucleo\Modelo;

class Producto extends Modelo
{
    private array $cacheColumnas = [];

    public function listar(int $empresaId, string $buscar = ''): array
    {
        $sql = 'SELECT p.*, c.nombre AS categoria FROM productos p LEFT JOIN categorias_productos c ON c.id = p.categoria_id WHERE p.empresa_id=:empresa_id AND p.fecha_eliminacion IS NULL';
        $params = ['empresa_id' => $empresaId];
        if ($buscar !== '') {
            $sql .= ' AND (p.nombre LIKE :buscar OR p.codigo LIKE :buscar OR p.sku LIKE :buscar OR p.codigo_barras LIKE :buscar)';
            $params['buscar'] = "%{$buscar}%";
        }
        $sql .= ' ORDER BY p.id DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function contar(int $empresaId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) AS total FROM productos WHERE empresa_id = :empresa_id AND fecha_eliminacion IS NULL');
        $stmt->execute(['empresa_id' => $empresaId]);
        return (int) $stmt->fetch()['total'];
    }

    public function crear(array $data): int
    {
        $columnas = ['empresa_id','categoria_id','tipo','codigo','sku','codigo_barras','nombre','descripcion','unidad','precio','costo','impuesto','descuento_maximo','stock_minimo','stock_aviso','estado','fecha_creacion'];
        $valores = [':empresa_id',':categoria_id',':tipo',':codigo',':sku',':codigo_barras',':nombre',':descripcion',':unidad',':precio',':costo',':impuesto',':descuento_maximo',':stock_minimo',':stock_aviso',':estado','NOW()'];

        if ($this->tieneColumna('productos', 'stock_actual')) {
            $columnas[] = 'stock_actual';
            $valores[] = ':stock_actual';
        }
        if ($this->tieneColumna('productos', 'stock_critico')) {
            $columnas[] = 'stock_critico';
            $valores[] = ':stock_critico';
        }

        $sql = 'INSERT INTO productos (' . implode(',', $columnas) . ') VALUES (' . implode(',', $valores) . ')';
        $this->db->prepare($sql)->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function obtenerPorId(int $empresaId, int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM productos WHERE empresa_id = :empresa_id AND id = :id AND fecha_eliminacion IS NULL LIMIT 1');
        $stmt->execute(['empresa_id' => $empresaId, 'id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function actualizar(int $empresaId, int $id, array $data): void
    {
        $sets = [
            'categoria_id=:categoria_id',
            'tipo=:tipo',
            'codigo=:codigo',
            'sku=:sku',
            'codigo_barras=:codigo_barras',
            'nombre=:nombre',
            'descripcion=:descripcion',
            'unidad=:unidad',
            'precio=:precio',
            'costo=:costo',
            'impuesto=:impuesto',
            'descuento_maximo=:descuento_maximo',
            'stock_minimo=:stock_minimo',
            'stock_aviso=:stock_aviso',
            'estado=:estado',
            'fecha_actualizacion=NOW()',
        ];

        if ($this->tieneColumna('productos', 'stock_critico')) {
            $sets[] = 'stock_critico=:stock_critico';
        }
        if ($this->tieneColumna('productos', 'stock_actual')) {
            $sets[] = 'stock_actual=:stock_actual';
        }

        $sql = 'UPDATE productos SET ' . implode(', ', $sets) . ' WHERE empresa_id=:empresa_id AND id=:id AND fecha_eliminacion IS NULL';
        $data['empresa_id'] = $empresaId;
        $data['id'] = $id;
        $this->db->prepare($sql)->execute($data);
    }

    public function eliminar(int $empresaId, int $id): void
    {
        $stmt = $this->db->prepare('UPDATE productos SET fecha_eliminacion = NOW() WHERE empresa_id = :empresa_id AND id = :id AND fecha_eliminacion IS NULL');
        $stmt->execute(['empresa_id' => $empresaId, 'id' => $id]);
    }

    private function tieneColumna(string $tabla, string $columna): bool
    {
        $llave = $tabla . '.' . $columna;
        if (array_key_exists($llave, $this->cacheColumnas)) {
            return $this->cacheColumnas[$llave];
        }

        $stmt = $this->db->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :tabla AND COLUMN_NAME = :columna');
        $stmt->execute(['tabla' => $tabla, 'columna' => $columna]);
        $this->cacheColumnas[$llave] = ((int) $stmt->fetchColumn()) > 0;
        return $this->cacheColumnas[$llave];
    }
}
