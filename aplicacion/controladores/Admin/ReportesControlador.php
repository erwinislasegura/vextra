<?php

namespace Aplicacion\Controladores\Admin;

use Aplicacion\Nucleo\Controlador;
use Aplicacion\Nucleo\BaseDatos;

class ReportesControlador extends Controlador
{
    public function index(): void
    {
        $db = BaseDatos::obtener();
        $reportes = [
            'empresas_por_plan' => $db->query('SELECT p.nombre, COUNT(e.id) total FROM planes p LEFT JOIN empresas e ON e.plan_id = p.id AND e.fecha_eliminacion IS NULL GROUP BY p.id, p.nombre ORDER BY total DESC')->fetchAll(),
            'suscripciones_activas' => (int) $db->query("SELECT COUNT(*) total FROM suscripciones WHERE estado='activa' AND fecha_eliminacion IS NULL")->fetch()['total'],
            'cuentas_por_vencer' => (int) $db->query("SELECT COUNT(*) total FROM suscripciones WHERE estado IN ('activa','por_vencer') AND fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 10 DAY) AND fecha_eliminacion IS NULL")->fetch()['total'],
            'cuentas_vencidas' => (int) $db->query("SELECT COUNT(*) total FROM suscripciones WHERE fecha_vencimiento < CURDATE() AND fecha_eliminacion IS NULL")->fetch()['total'],
            'empresas_suspendidas' => (int) $db->query("SELECT COUNT(*) total FROM empresas WHERE estado = 'suspendida' AND fecha_eliminacion IS NULL")->fetch()['total'],
            'ingresos_mensuales_estimados' => (float) $db->query("SELECT COALESCE(SUM(p.precio_mensual),0) total FROM suscripciones s INNER JOIN planes p ON p.id = s.plan_id WHERE s.estado IN ('activa','por_vencer') AND s.fecha_eliminacion IS NULL")->fetch()['total'],
            'ingresos_anuales_estimados' => (float) $db->query("SELECT COALESCE(SUM(CASE WHEN p.precio_anual > 0 THEN p.precio_anual ELSE p.precio_mensual * 12 END),0) total FROM suscripciones s INNER JOIN planes p ON p.id = s.plan_id WHERE s.estado IN ('activa','por_vencer') AND s.fecha_eliminacion IS NULL")->fetch()['total'],
            'planes_mas_contratados' => $db->query('SELECT p.nombre, COUNT(s.id) total FROM suscripciones s INNER JOIN planes p ON p.id = s.plan_id WHERE s.fecha_eliminacion IS NULL GROUP BY p.id, p.nombre ORDER BY total DESC LIMIT 5')->fetchAll(),
            'renovaciones_proximas' => $db->query("SELECT e.nombre_comercial AS empresa, p.nombre AS plan, s.fecha_vencimiento, DATEDIFF(s.fecha_vencimiento, CURDATE()) AS dias_restantes FROM suscripciones s INNER JOIN empresas e ON e.id = s.empresa_id INNER JOIN planes p ON p.id = s.plan_id WHERE s.estado IN ('activa','por_vencer') AND s.fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 15 DAY) ORDER BY s.fecha_vencimiento ASC LIMIT 12")->fetchAll(),
            'crecimiento_registros' => $db->query('SELECT DATE_FORMAT(fecha_creacion, "%Y-%m") periodo, COUNT(*) total FROM empresas WHERE fecha_eliminacion IS NULL GROUP BY DATE_FORMAT(fecha_creacion, "%Y-%m") ORDER BY periodo DESC LIMIT 6')->fetchAll(),
        ];
        $this->vista('admin/reportes/index', compact('reportes'), 'admin');
    }
}
