<?php

namespace Aplicacion\Nucleo;

use PDO;

abstract class Modelo
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = BaseDatos::obtener();
    }
}
