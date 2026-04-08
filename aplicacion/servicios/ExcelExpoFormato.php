<?php

namespace Aplicacion\Servicios;

final class ExcelExpoFormato
{
    public const BOTON_TEXTO = 'Exportar Excel';
    public const BOTON_CLASES = 'btn btn-success btn-sm fw-semibold';
    public const BOTON_ESTILO = 'background:#217346;border-color:#217346;color:#ffffff;font-family:Calibri,Arial,sans-serif;font-size:12px;';

    public const TABLA_ESTILO = 'border-collapse:collapse;font-family:Calibri,Arial,sans-serif;font-size:11pt;';
    public const ENCABEZADO_ESTILO = 'background:#d9d9d9;font-weight:700;';
    public const CELDA_TEXTO_EXCEL = 'mso-number-format:\\@;';

    private function __construct()
    {
    }
}
