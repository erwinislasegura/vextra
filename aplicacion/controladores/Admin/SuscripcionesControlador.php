<?php

namespace Aplicacion\Controladores\Admin;

use Aplicacion\Modelos\LogAdministracion;
use Aplicacion\Nucleo\Controlador;
use Aplicacion\Modelos\Suscripcion;
use Aplicacion\Modelos\Plan;
use Throwable;

class SuscripcionesControlador extends Controlador
{
    public function index(): void
    {
        $filtros = [
            'estado' => $_GET['estado'] ?? '',
            'plan_id' => $_GET['plan_id'] ?? '',
        ];
        $suscripciones = (new Suscripcion())->listar($filtros);
        $planes = (new Plan())->listar();
        $this->vista('admin/suscripciones/index', compact('suscripciones', 'filtros', 'planes'), 'admin');
    }

    public function actualizarEstado(int $id): void
    {
        validar_csrf();
        try {
            (new Suscripcion())->actualizarEstado($id, $_POST['estado'] ?? 'activa', trim($_POST['observaciones'] ?? ''));
            (new LogAdministracion())->registrar('suscripciones', 'actualizar_estado', 'Suscripción ' . $id . ' actualizada');
            flash('success', 'Estado de suscripción actualizado.');
        } catch (Throwable $e) {
            flash('danger', 'No se pudo actualizar el estado de la suscripción.');
        }
        $this->redirigir('/admin/suscripciones');
    }

    public function actualizar(int $id): void
    {
        validar_csrf();
        $suscripcion = (new Suscripcion())->buscar($id);
        if (!$suscripcion) {
            flash('danger', 'Suscripción no encontrada.');
            $this->redirigir('/admin/suscripciones');
        }

        try {
            (new Suscripcion())->actualizar($id, [
                'empresa_id' => (int) $suscripcion['empresa_id'],
                'plan_id' => (int) ($_POST['plan_id'] ?? $suscripcion['plan_id']),
                'estado' => $_POST['estado'] ?? $suscripcion['estado'],
                'fecha_inicio' => $_POST['fecha_inicio'] ?? $suscripcion['fecha_inicio'],
                'fecha_vencimiento' => $_POST['fecha_vencimiento'] ?? $suscripcion['fecha_vencimiento'],
                'observaciones' => trim($_POST['observaciones'] ?? $suscripcion['observaciones'] ?? ''),
            ]);
            (new LogAdministracion())->registrar('suscripciones', 'editar', 'Edición completa de suscripción ' . $id, (int) $suscripcion['empresa_id']);
            flash('success', 'Suscripción actualizada.');
        } catch (Throwable $e) {
            flash('danger', 'No se pudo editar la suscripción.');
        }
        $this->redirigir('/admin/suscripciones');
    }
}
