<?php

namespace Aplicacion\Controladores\Admin;

use Aplicacion\Nucleo\Controlador;
use Aplicacion\Modelos\Plan;
use Aplicacion\Modelos\Funcionalidad;
use Aplicacion\Modelos\PlanFuncionalidad;

class PlanFuncionalidadesControlador extends Controlador
{
    public function index(int $planId): void
    {
        $plan = (new Plan())->buscar($planId);
        if (!$plan) {
            flash('danger', 'El plan solicitado no existe.');
            $this->redirigir('/admin/planes');
        }

        $funcionalidades = (new Funcionalidad())->listar();
        $asignadas = (new PlanFuncionalidad())->listarPorPlan($planId);
        $mapa = [];
        foreach ($asignadas as $fila) {
            $mapa[$fila['funcionalidad_id']] = $fila;
        }

        $funcionalidadesAgrupadas = $this->agruparFuncionalidades($funcionalidades);
        $dependencias = $this->mapaDependencias();
        $codigosNuevos = $this->codigosNuevosClientes();

        $this->vista(
            'admin/plan_funcionalidades/index',
            compact('plan', 'mapa', 'funcionalidadesAgrupadas', 'dependencias', 'codigosNuevos'),
            'admin'
        );
    }

    public function guardar(int $planId): void
    {
        validar_csrf();

        $funcionalidades = (new Funcionalidad())->listar();
        $dependencias = $this->mapaDependencias();

        $idPorCodigo = [];
        $codigoPorId = [];
        foreach ($funcionalidades as $funcionalidad) {
            $id = (int) $funcionalidad['id'];
            $codigo = (string) $funcionalidad['codigo_interno'];
            $idPorCodigo[$codigo] = $id;
            $codigoPorId[$id] = $codigo;
        }

        $seleccionadas = [];
        foreach ($_POST['funcionalidades'] ?? [] as $funcionalidadId => $valor) {
            if (isset($valor['activo'])) {
                $seleccionadas[(int) $funcionalidadId] = true;
            }
        }

        $forzadasPorDependencia = [];
        $cambios = true;
        while ($cambios) {
            $cambios = false;

            foreach (array_keys($seleccionadas) as $funcionalidadId) {
                $codigo = $codigoPorId[$funcionalidadId] ?? null;
                if ($codigo === null || !isset($dependencias[$codigo])) {
                    continue;
                }

                foreach ($dependencias[$codigo] as $codigoDependencia) {
                    if (!isset($idPorCodigo[$codigoDependencia])) {
                        continue;
                    }

                    $idDependencia = $idPorCodigo[$codigoDependencia];
                    if (!isset($seleccionadas[$idDependencia])) {
                        $seleccionadas[$idDependencia] = true;
                        $forzadasPorDependencia[$codigoDependencia] = true;
                        $cambios = true;
                    }
                }
            }
        }

        $modelo = new PlanFuncionalidad();
        $limiteUsuarios = null;
        foreach ($funcionalidades as $funcionalidad) {
            $funcionalidadId = (int) $funcionalidad['id'];
            $valor = $_POST['funcionalidades'][$funcionalidadId] ?? [];
            $activo = isset($seleccionadas[$funcionalidadId]) ? 1 : 0;

            if (($funcionalidad['codigo_interno'] ?? '') === 'maximo_usuarios') {
                $limiteUsuarios = [
                    'activo' => $activo,
                    'valor_numerico' => (int) ($valor['valor_numerico'] ?? 0),
                    'es_ilimitado' => isset($valor['es_ilimitado']) ? 1 : 0,
                ];
            }

            $modelo->guardarAsignacion($planId, $funcionalidadId, [
                'activo' => $activo,
                'valor_numerico' => (int) ($valor['valor_numerico'] ?? 0),
                'es_ilimitado' => isset($valor['es_ilimitado']) ? 1 : 0,
            ]);
        }

        if ($limiteUsuarios !== null) {
            $maximoUsuarios = $limiteUsuarios['activo'] ? max(0, (int) $limiteUsuarios['valor_numerico']) : 0;
            $usuariosIlimitados = $limiteUsuarios['activo'] ? (int) $limiteUsuarios['es_ilimitado'] : 0;
            (new Plan())->actualizarLimiteUsuarios($planId, $maximoUsuarios, $usuariosIlimitados);
        }

        if ($forzadasPorDependencia !== []) {
            flash(
                'warning',
                'Se activaron dependencias automáticamente: ' . implode(', ', array_keys($forzadasPorDependencia)) . '.'
            );
        } else {
            flash('success', 'Asignaciones guardadas.');
        }

        $this->redirigir('/admin/plan-funcionalidades/' . $planId);
    }

    private function agruparFuncionalidades(array $funcionalidades): array
    {
        $grupos = [
            'limites' => [],
            'menu' => [],
            'adicionales' => [],
        ];

        foreach ($funcionalidades as $funcionalidad) {
            if ($funcionalidad['tipo_valor'] !== 'booleano') {
                $grupos['limites'][] = $funcionalidad;
                continue;
            }

            if (str_starts_with((string) $funcionalidad['codigo_interno'], 'modulo_')) {
                $grupos['menu'][] = $funcionalidad;
                continue;
            }

            $grupos['adicionales'][] = $funcionalidad;
        }

        return $grupos;
    }

    private function mapaDependencias(): array
    {
        return [
            'modulo_cotizaciones' => ['modulo_clientes', 'modulo_productos', 'modulo_categorias'],
            'modulo_pos' => ['modulo_clientes', 'modulo_productos'],
            'modulo_inventario' => ['modulo_productos', 'modulo_categorias'],
            'modulo_recepciones' => ['modulo_inventario', 'modulo_productos'],
            'modulo_ajustes' => ['modulo_inventario', 'modulo_productos'],
            'modulo_movimientos' => ['modulo_inventario', 'modulo_productos'],
            'modulo_ordenes_compra' => ['modulo_inventario', 'modulo_productos'],
            'modulo_listas_precios' => ['modulo_productos', 'modulo_categorias'],
            'modulo_seguimiento' => ['modulo_clientes', 'modulo_cotizaciones'],
            'modulo_aprobaciones' => ['modulo_cotizaciones'],
            'clientes_exportar_excel' => ['modulo_clientes'],
            'clientes_gestion_listas_precios' => ['modulo_clientes', 'modulo_listas_precios'],
            'clientes_asignar_vendedor' => ['modulo_clientes', 'modulo_vendedores'],
        ];
    }

    private function codigosNuevosClientes(): array
    {
        return [
            'clientes_exportar_excel',
            'clientes_gestion_listas_precios',
            'clientes_asignar_vendedor',
        ];
    }

}
