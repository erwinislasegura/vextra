<?php

namespace Aplicacion\Modelos;

use Aplicacion\Nucleo\Modelo;

class Configuracion extends Modelo
{
    public function obtenerMapa(array $claves): array
    {
        if ($claves === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($claves), '?'));
        $stmt = $this->db->prepare("SELECT clave, valor FROM configuraciones WHERE clave IN ($placeholders)");
        $stmt->execute($claves);

        $mapa = [];
        foreach ($stmt->fetchAll() as $fila) {
            $mapa[(string) $fila['clave']] = (string) ($fila['valor'] ?? '');
        }

        return $mapa;
    }

    public function guardarMultiples(array $data): void
    {
        $stmt = $this->db->prepare('INSERT INTO configuraciones (clave, valor, fecha_actualizacion) VALUES (:clave,:valor,NOW()) ON DUPLICATE KEY UPDATE valor = VALUES(valor), fecha_actualizacion = NOW()');
        foreach ($data as $clave => $valor) {
            $stmt->execute([
                'clave' => (string) $clave,
                'valor' => (string) $valor,
            ]);
        }
    }
}
