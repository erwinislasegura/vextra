<?php

namespace Aplicacion\Controladores\Admin;

use Aplicacion\Nucleo\BaseDatos;
use Aplicacion\Nucleo\Controlador;

class PanelAdminControlador extends Controlador
{
    public function panel(): void
    {
        $db = BaseDatos::obtener();

        $resumen = [
            'empresas_total' => (int) $db->query('SELECT COUNT(*) total FROM empresas WHERE fecha_eliminacion IS NULL')->fetch()['total'],
            'empresas_activas' => (int) $db->query("SELECT COUNT(*) total FROM empresas WHERE estado = 'activa' AND fecha_eliminacion IS NULL")->fetch()['total'],
            'empresas_suspendidas' => (int) $db->query("SELECT COUNT(*) total FROM empresas WHERE estado = 'suspendida' AND fecha_eliminacion IS NULL")->fetch()['total'],
            'empresas_vencidas' => (int) $db->query("SELECT COUNT(*) total FROM empresas WHERE estado = 'vencida' AND fecha_eliminacion IS NULL")->fetch()['total'],
            'empresas_por_vencer' => (int) $db->query("SELECT COUNT(*) total FROM suscripciones WHERE estado IN ('activa','por_vencer') AND fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 10 DAY) AND fecha_eliminacion IS NULL")->fetch()['total'],
            'planes_activos' => (int) $db->query("SELECT COUNT(*) total FROM planes WHERE estado = 'activo' AND fecha_eliminacion IS NULL")->fetch()['total'],
            'total_usuarios_empresas' => (int) $db->query('SELECT COUNT(*) total FROM usuarios u INNER JOIN roles r ON r.id = u.rol_id WHERE r.codigo != "superadministrador" AND u.fecha_eliminacion IS NULL')->fetch()['total'],
            'ingresos_mensuales_estimados' => (float) $db->query("SELECT COALESCE(SUM(p.precio_mensual),0) total FROM suscripciones s INNER JOIN planes p ON p.id = s.plan_id WHERE s.estado IN ('activa','por_vencer') AND s.fecha_eliminacion IS NULL")->fetch()['total'],
            'ingresos_anuales_estimados' => (float) $db->query("SELECT COALESCE(SUM(CASE WHEN p.precio_anual > 0 THEN p.precio_anual ELSE (p.precio_mensual * 12) END),0) total FROM suscripciones s INNER JOIN planes p ON p.id = s.plan_id WHERE s.estado IN ('activa','por_vencer') AND s.fecha_eliminacion IS NULL")->fetch()['total'],
            'nuevas_empresas_7_dias' => (int) $db->query("SELECT COUNT(*) total FROM empresas WHERE fecha_eliminacion IS NULL AND fecha_creacion >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch()['total'],
            'renovaciones_hoy' => (int) $db->query("SELECT COUNT(*) total FROM suscripciones WHERE fecha_eliminacion IS NULL AND fecha_vencimiento = CURDATE()")->fetch()['total'],
            'mrr_en_riesgo' => (float) $db->query("SELECT COALESCE(SUM(p.precio_mensual),0) total
                FROM suscripciones s
                INNER JOIN planes p ON p.id = s.plan_id
                WHERE s.fecha_eliminacion IS NULL
                  AND s.estado IN ('vencida','suspendida')")->fetch()['total'],
            'flow_pagos_hoy' => 0,
            'flow_suscripciones_activas' => 0,
        ];

        try {
            $resumen['flow_pagos_hoy'] = (int) $db->query('SELECT COUNT(*) total FROM flow_pagos WHERE DATE(fecha_creacion)=CURDATE()')->fetch()['total'];
            $resumen['flow_suscripciones_activas'] = (int) $db->query("SELECT COUNT(*) total FROM flow_suscripciones WHERE estado_local='activa'")->fetch()['total'];
        } catch (\Throwable $e) {
            // Tablas Flow no aplicadas aún.
        }

        $empresasPorPlan = $db->query('SELECT p.nombre, COUNT(e.id) total FROM planes p LEFT JOIN empresas e ON e.plan_id = p.id AND e.fecha_eliminacion IS NULL GROUP BY p.id, p.nombre ORDER BY total DESC')->fetchAll();
        $ultimasEmpresas = $db->query('SELECT id, nombre_comercial, correo, estado, fecha_creacion FROM empresas WHERE fecha_eliminacion IS NULL ORDER BY id DESC LIMIT 8')->fetchAll();
        $ultimasSuscripciones = $db->query('SELECT s.id, e.nombre_comercial AS empresa, p.nombre AS plan, s.estado, s.fecha_inicio, s.fecha_vencimiento, COALESCE(s.fecha_actualizacion, s.fecha_creacion) AS fecha_movimiento FROM suscripciones s INNER JOIN empresas e ON e.id = s.empresa_id INNER JOIN planes p ON p.id = s.plan_id WHERE s.fecha_eliminacion IS NULL ORDER BY COALESCE(s.fecha_actualizacion, s.fecha_creacion) DESC LIMIT 8')->fetchAll();
        $proximosVencimientos = $db->query("SELECT s.id, e.nombre_comercial AS empresa, p.nombre AS plan, s.fecha_vencimiento, DATEDIFF(s.fecha_vencimiento, CURDATE()) AS dias_restantes FROM suscripciones s INNER JOIN empresas e ON e.id = s.empresa_id INNER JOIN planes p ON p.id = s.plan_id WHERE s.estado IN ('activa','por_vencer') AND s.fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 15 DAY) ORDER BY s.fecha_vencimiento ASC LIMIT 10")->fetchAll();

        $alertas = [];
        if ($resumen['empresas_vencidas'] > 0) {
            $alertas[] = "Hay {$resumen['empresas_vencidas']} empresas vencidas que requieren gestión comercial.";
        }
        if ($resumen['empresas_por_vencer'] > 0) {
            $alertas[] = "{$resumen['empresas_por_vencer']} suscripciones vencerán dentro de los próximos 10 días.";
        }
        if ($resumen['mrr_en_riesgo'] > 0) {
            $alertas[] = 'Hay $' . number_format($resumen['mrr_en_riesgo'], 0, ',', '.') . ' de MRR en riesgo por cuentas vencidas/suspendidas.';
        }

        $this->vista('admin/panel', compact('resumen', 'empresasPorPlan', 'ultimasEmpresas', 'ultimasSuscripciones', 'proximosVencimientos', 'alertas'), 'admin');
    }
}
