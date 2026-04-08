<?php

return [
    'superadministrador' => ['*'],
    'administrador_empresa' => [
        'panel_empresa', 'clientes', 'productos', 'cotizaciones', 'usuarios_empresa', 'configuracion_empresa',
        'pos_ver', 'pos_abrir_caja', 'pos_cerrar_caja', 'pos_registrar_venta', 'pos_ver_historial', 'pos_admin_cajas', 'pos_configuracion',
        'inventario_ver_recepciones', 'inventario_crear_recepciones', 'inventario_ver_ajustes', 'inventario_crear_ajustes', 'inventario_ver_movimientos', 'inventario_configurar_alertas'
    ],
    'usuario_empresa' => [
        'panel_empresa', 'clientes', 'productos', 'cotizaciones',
        'pos_ver', 'pos_abrir_caja', 'pos_registrar_venta', 'pos_ver_historial',
        'inventario_ver_recepciones', 'inventario_ver_ajustes', 'inventario_ver_movimientos'
    ],
];
