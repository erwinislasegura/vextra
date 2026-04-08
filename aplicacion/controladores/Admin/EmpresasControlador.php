<?php

namespace Aplicacion\Controladores\Admin;

use Aplicacion\Modelos\Empresa;
use Aplicacion\Modelos\LogAdministracion;
use Aplicacion\Modelos\Plan;
use Aplicacion\Modelos\Suscripcion;
use Aplicacion\Modelos\Usuario;
use Aplicacion\Nucleo\BaseDatos;
use Aplicacion\Nucleo\Controlador;
use Throwable;

class EmpresasControlador extends Controlador
{
    public function index(): void
    {
        $empresaModelo = new Empresa();
        $filtros = [
            'busqueda' => trim($_GET['q'] ?? ''),
            'estado' => $_GET['estado'] ?? '',
            'plan_id' => $_GET['plan_id'] ?? '',
        ];

        $empresas = $empresaModelo->listar($filtros);
        $planes = (new Plan())->listar();
        $confirmacionEliminacion = null;
        $confirmarEliminar = (int) ($_GET['confirmar_eliminar'] ?? 0);
        if ($confirmarEliminar > 0) {
            $empresa = $empresaModelo->buscar($confirmarEliminar);
            if ($empresa && empty($empresa['fecha_eliminacion'])) {
                $confirmacionEliminacion = [
                    'empresa_id' => $confirmarEliminar,
                    'empresa_nombre' => (string) ($empresa['nombre_comercial'] ?? $empresa['razon_social'] ?? ('Empresa #' . $confirmarEliminar)),
                    'resumen' => $empresaModelo->obtenerResumenDatosAsociados($confirmarEliminar),
                ];
            }
        }

        $this->vista('admin/empresas/index', compact('empresas', 'planes', 'filtros', 'confirmacionEliminacion'), 'admin');
    }

    public function ver(int $id): void
    {
        $empresa = (new Empresa())->buscarDetalleAdmin($id);
        if (!$empresa) {
            flash('danger', 'Empresa no encontrada.');
            $this->redirigir('/admin/empresas');
        }

        $admins = (new Usuario())->listarAdministradoresEmpresa(['empresa_id' => $id]);
        $historial = BaseDatos::obtener()->prepare('SELECT hs.*, u.nombre AS admin_nombre FROM historial_suscripciones hs LEFT JOIN suscripciones s ON s.id = hs.suscripcion_id LEFT JOIN usuarios u ON u.id = s.empresa_id WHERE s.empresa_id = :empresa_id ORDER BY hs.id DESC LIMIT 20');
        $historial->execute(['empresa_id' => $id]);
        $historial = $historial->fetchAll();

        $planes = (new Plan())->listarActivos();
        $this->vista('admin/empresas/ver', compact('empresa', 'admins', 'historial', 'planes'), 'admin');
    }

    public function actualizarEstado(int $id): void
    {
        validar_csrf();
        $estado = $_POST['estado'] ?? 'activa';
        try {
            (new Empresa())->actualizarEstado($id, $estado);
            (new LogAdministracion())->registrar('empresas', 'cambiar_estado', 'Cambio de estado a ' . $estado, $id);
            flash('success', 'Estado de empresa actualizado.');
        } catch (\Throwable $e) {
            flash('danger', 'No se pudo actualizar el estado de la empresa.');
        }
        $this->redirigir('/admin/empresas/ver/' . $id);
    }

    public function cambiarPlan(int $id): void
    {
        validar_csrf();
        $planId = (int) ($_POST['plan_id'] ?? 0);
        $observaciones = trim($_POST['observaciones_internas'] ?? '');

        if ($planId <= 0 || !(new Plan())->buscar($planId)) {
            flash('danger', 'Selecciona un plan válido para aplicar el cambio.');
            $this->redirigir('/admin/empresas/ver/' . $id);
        }

        $empresaModelo = new Empresa();
        $suscripcionModelo = new Suscripcion();
        $suscripcionActual = $suscripcionModelo->obtenerUltimaPorEmpresa($id);

        try {
            if ($suscripcionActual) {
                $observacionesSuscripcion = trim((string) ($suscripcionActual['observaciones'] ?? ''));
                $notaCambioPlan = 'Cambio de plan desde admin: ID ' . $planId;
                if ($observaciones !== '') {
                    $notaCambioPlan .= ' | ' . $observaciones;
                }
                if ($observacionesSuscripcion !== '') {
                    $notaCambioPlan = $observacionesSuscripcion . ' | ' . $notaCambioPlan;
                }

                $suscripcionModelo->actualizar((int) $suscripcionActual['id'], [
                    'empresa_id' => $id,
                    'plan_id' => $planId,
                    'estado' => (string) ($suscripcionActual['estado'] ?? 'activa'),
                    'fecha_inicio' => $suscripcionActual['fecha_inicio'] ?? date('Y-m-d'),
                    'fecha_vencimiento' => $suscripcionActual['fecha_vencimiento'] ?? date('Y-m-d'),
                    'observaciones' => $notaCambioPlan,
                ]);
            }

            $empresaModelo->actualizarPlanYObservacion($id, $planId, $observaciones);
            (new LogAdministracion())->registrar('empresas', 'cambiar_plan', 'Plan asignado ID ' . $planId, $id);
            flash('success', 'Plan de empresa actualizado y aplicado a la suscripción activa.');
        } catch (Throwable $e) {
            flash('danger', 'No se pudo actualizar el plan de la empresa.');
        }
        $this->redirigir('/admin/empresas/ver/' . $id);
    }

    public function extenderSuscripcion(int $id): void
    {
        validar_csrf();
        $dias = max(1, (int) ($_POST['dias'] ?? 30));
        $suscripcionModelo = new Suscripcion();
        $actual = $suscripcionModelo->obtenerUltimaPorEmpresa($id);
        if (!$actual) {
            flash('danger', 'La empresa no tiene suscripción para extender.');
            $this->redirigir('/admin/empresas/ver/' . $id);
        }

        try {
            $hoy = date('Y-m-d');
            $base = ((string) ($actual['fecha_vencimiento'] ?? '') > $hoy) ? (string) $actual['fecha_vencimiento'] : $hoy;
            $nuevaFecha = date('Y-m-d', strtotime($base . ' +' . $dias . ' day'));

            $suscripcionModelo->actualizar((int) $actual['id'], [
                'empresa_id' => (int) ($actual['empresa_id'] ?? $id),
                'plan_id' => (int) $actual['plan_id'],
                'estado' => 'activa',
                'fecha_inicio' => $actual['fecha_inicio'],
                'fecha_vencimiento' => $nuevaFecha,
                'observaciones' => trim((string) (($actual['observaciones'] ?? '') . ' | Extensión admin: +' . $dias . ' días')),
            ]);
            (new LogAdministracion())->registrar('suscripciones', 'extender_vigencia', 'Extensión de ' . $dias . ' días', $id);
            flash('success', 'Vigencia extendida hasta ' . $nuevaFecha . '.');
        } catch (\Throwable $e) {
            flash('danger', 'No se pudo extender la vigencia de la suscripción.');
        }

        $this->redirigir('/admin/empresas/ver/' . $id);
    }

    public function eliminar(int $id): void
    {
        validar_csrf();
        $empresaModelo = new Empresa();
        $empresa = $empresaModelo->buscar($id);
        if (!$empresa || !empty($empresa['fecha_eliminacion'])) {
            flash('danger', 'Empresa no encontrada o ya eliminada.');
            $this->redirigir('/admin/empresas');
        }

        $forzar = (int) ($_POST['forzar'] ?? 0) === 1;
        $resumen = $empresaModelo->obtenerResumenDatosAsociados($id);
        if (!$forzar && !empty($resumen)) {
            flash('warning', 'La empresa tiene datos asociados. Revisa el detalle y confirma eliminación forzada.');
            $this->redirigir('/admin/empresas?confirmar_eliminar=' . $id);
        }

        try {
            $empresaModelo->eliminarConDatosAsociados($id);
            (new LogAdministracion())->registrar('empresas', 'eliminar', 'Eliminación completa de empresa y datos asociados', $id);
            flash('success', 'Empresa eliminada correctamente junto con sus datos asociados.');
        } catch (Throwable $e) {
            flash('danger', 'No se pudo eliminar la empresa.');
        }

        $this->redirigir('/admin/empresas');
    }
}
