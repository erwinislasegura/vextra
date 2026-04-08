<?php

namespace Aplicacion\Controladores\Admin;

use Aplicacion\Modelos\LogAdministracion;
use Aplicacion\Nucleo\Controlador;
use Aplicacion\Modelos\Plan;
use Aplicacion\Modelos\Funcionalidad;
use Aplicacion\Modelos\PlanFuncionalidad;
use Throwable;

class PlanesControlador extends Controlador
{
    public function index(): void
    {
        $planes = (new Plan())->listar();
        $this->vista('admin/planes/index', compact('planes'), 'admin');
    }

    public function crear(): void
    {
        $this->vista('admin/planes/formulario', ['plan' => null], 'admin');
    }

    public function guardar(): void
    {
        validar_csrf();
        $data = $this->obtenerDatosFormulario();
        $planId = (new Plan())->crear($data);
        $this->sincronizarLimiteUsuariosPlan($planId, $data);
        (new LogAdministracion())->registrar('planes', 'crear', 'Creación de plan ID ' . $planId);
        flash('success', 'Plan creado correctamente.');
        $this->redirigir('/admin/planes');
    }

    public function editar(int $id): void
    {
        $plan = (new Plan())->buscar($id);
        if (!$plan) {
            flash('danger', 'Plan no encontrado.');
            $this->redirigir('/admin/planes');
        }
        $this->vista('admin/planes/formulario', compact('plan'), 'admin');
    }

    public function actualizar(int $id): void
    {
        validar_csrf();
        try {
            $data = $this->obtenerDatosFormulario();
            (new Plan())->actualizar($id, $data);
            $this->sincronizarLimiteUsuariosPlan($id, $data);
            (new LogAdministracion())->registrar('planes', 'editar', 'Actualización de plan ID ' . $id);
            flash('success', 'Plan actualizado.');
        } catch (Throwable $e) {
            flash('danger', 'No se pudo actualizar el plan. Verifica slug único y campos obligatorios.');
        }
        $this->redirigir('/admin/planes');
    }

    public function cambiarEstado(int $id): void
    {
        validar_csrf();
        $plan = (new Plan())->buscar($id);
        if (!$plan) {
            flash('danger', 'Plan no encontrado.');
            $this->redirigir('/admin/planes');
        }

        $nuevoEstado = $plan['estado'] === 'activo' ? 'inactivo' : 'activo';
        $data = $this->normalizarDatosPlan($plan);
        $data['estado'] = $nuevoEstado;
        try {
            (new Plan())->actualizar($id, $data);
            (new LogAdministracion())->registrar('planes', 'cambiar_estado', "Plan ID {$id} pasó a {$nuevoEstado}");
            flash('success', 'Estado del plan actualizado.');
        } catch (Throwable $e) {
            flash('danger', 'No se pudo actualizar el estado del plan.');
        }

        $this->redirigir('/admin/planes');
    }

    public function eliminar(int $id): void
    {
        validar_csrf();
        $plan = (new Plan())->buscar($id);
        if (!$plan) {
            flash('danger', 'Plan no encontrado.');
            $this->redirigir('/admin/planes');
        }

        try {
            (new Plan())->eliminar($id);
            (new LogAdministracion())->registrar('planes', 'eliminar', "Eliminación lógica de plan ID {$id}");
            flash('success', 'Plan eliminado correctamente.');
        } catch (Throwable $e) {
            flash('danger', 'No se pudo eliminar el plan.');
        }

        $this->redirigir('/admin/planes');
    }

    private function obtenerDatosFormulario(): array
    {
        $precioMensual = (float) ($_POST['precio_mensual'] ?? 0);
        $descuentoAnual = (float) ($_POST['descuento_anual_pct'] ?? 0);
        $precioAnual = (float) ($_POST['precio_anual'] ?? 0);
        if ($precioAnual <= 0 && $precioMensual > 0) {
            $precioAnual = ($precioMensual * 12) * ((100 - $descuentoAnual) / 100);
        }

        return [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),
            'descripcion_comercial' => trim($_POST['descripcion_comercial'] ?? ''),
            'precio_mensual' => $precioMensual,
            'descuento_anual_pct' => $descuentoAnual,
            'precio_anual' => $precioAnual,
            'duracion_dias' => (int) ($_POST['duracion_dias'] ?? 30),
            'visible' => isset($_POST['visible']) ? 1 : 0,
            'destacado' => isset($_POST['destacado']) ? 1 : 0,
            'recomendado' => isset($_POST['recomendado']) ? 1 : 0,
            'orden_visualizacion' => (int) ($_POST['orden_visualizacion'] ?? 0),
            'insignia' => trim($_POST['insignia'] ?? ''),
            'resumen_comercial' => trim($_POST['resumen_comercial'] ?? ''),
            'color_visual' => trim($_POST['color_visual'] ?? '#4632a8'),
            'maximo_usuarios' => (int) ($_POST['maximo_usuarios'] ?? 0),
            'usuarios_ilimitados' => isset($_POST['usuarios_ilimitados']) ? 1 : 0,
            'flow_dias_prueba' => max(0, (int) ($_POST['flow_dias_prueba'] ?? 0)),
            'flow_dias_cobro' => max(1, (int) ($_POST['flow_dias_cobro'] ?? 3)),
            'observaciones_internas' => trim($_POST['observaciones_internas'] ?? ''),
            'estado' => $_POST['estado'] ?? 'activo',
        ];
    }

    private function normalizarDatosPlan(array $plan): array
    {
        return [
            'nombre' => trim((string) ($plan['nombre'] ?? '')),
            'slug' => trim((string) ($plan['slug'] ?? '')),
            'descripcion_comercial' => trim((string) ($plan['descripcion_comercial'] ?? '')),
            'precio_mensual' => (float) ($plan['precio_mensual'] ?? 0),
            'descuento_anual_pct' => (float) ($plan['descuento_anual_pct'] ?? 0),
            'precio_anual' => (float) ($plan['precio_anual'] ?? 0),
            'duracion_dias' => (int) ($plan['duracion_dias'] ?? 30),
            'visible' => (int) ($plan['visible'] ?? 0),
            'destacado' => (int) ($plan['destacado'] ?? 0),
            'recomendado' => (int) ($plan['recomendado'] ?? 0),
            'orden_visualizacion' => (int) ($plan['orden_visualizacion'] ?? 0),
            'insignia' => trim((string) ($plan['insignia'] ?? '')),
            'resumen_comercial' => trim((string) ($plan['resumen_comercial'] ?? '')),
            'color_visual' => trim((string) ($plan['color_visual'] ?? '#4632a8')),
            'maximo_usuarios' => (int) ($plan['maximo_usuarios'] ?? 0),
            'usuarios_ilimitados' => (int) ($plan['usuarios_ilimitados'] ?? 0),
            'flow_dias_prueba' => (int) ($plan['flow_dias_prueba'] ?? 0),
            'flow_dias_cobro' => max(1, (int) ($plan['flow_dias_cobro'] ?? 3)),
            'observaciones_internas' => trim((string) ($plan['observaciones_internas'] ?? '')),
            'estado' => (string) ($plan['estado'] ?? 'activo'),
        ];
    }


    private function sincronizarLimiteUsuariosPlan(int $planId, array $data): void
    {
        $funcionalidad = (new Funcionalidad())->buscarPorCodigo('maximo_usuarios');
        if (!$funcionalidad) {
            return;
        }

        (new PlanFuncionalidad())->guardarAsignacion($planId, (int) $funcionalidad['id'], [
            'activo' => 1,
            'valor_numerico' => max(0, (int) ($data['maximo_usuarios'] ?? 0)),
            'es_ilimitado' => (int) ($data['usuarios_ilimitados'] ?? 0) === 1 ? 1 : 0,
        ]);
    }

}
