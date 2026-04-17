<?php

namespace Aplicacion\Controladores\Admin;

use Aplicacion\Nucleo\Controlador;
use Aplicacion\Modelos\Funcionalidad;
use Throwable;

class FuncionalidadesControlador extends Controlador
{
    public function index(): void
    {
        $funcionalidades = (new Funcionalidad())->listar();
        $funcionalidadesAgrupadas = $this->agruparFuncionalidades($funcionalidades);
        $dependencias = $this->mapaDependencias();
        $codigosNuevos = $this->codigosNuevosClientes();

        $this->vista(
            'admin/funcionalidades/index',
            compact('funcionalidades', 'funcionalidadesAgrupadas', 'dependencias', 'codigosNuevos'),
            'admin'
        );
    }

    public function crear(): void
    {
        $this->vista('admin/funcionalidades/formulario', ['funcionalidad' => null], 'admin');
    }

    public function guardar(): void
    {
        validar_csrf();
        try {
            (new Funcionalidad())->crear($this->datos());
            flash('success', 'Funcionalidad creada.');
        } catch (Throwable $e) {
            flash('danger', 'No se pudo crear la funcionalidad. Verifica código interno único.');
        }
        $this->redirigir('/admin/funcionalidades');
    }

    public function editar(int $id): void
    {
        $funcionalidad = (new Funcionalidad())->buscar($id);
        if (!$funcionalidad) {
            flash('danger', 'Funcionalidad no encontrada.');
            $this->redirigir('/admin/funcionalidades');
        }
        $this->vista('admin/funcionalidades/formulario', compact('funcionalidad'), 'admin');
    }

    public function actualizar(int $id): void
    {
        validar_csrf();
        try {
            (new Funcionalidad())->actualizar($id, $this->datos());
            flash('success', 'Funcionalidad actualizada.');
        } catch (Throwable $e) {
            flash('danger', 'No se pudo actualizar la funcionalidad. Verifica código interno único.');
        }
        $this->redirigir('/admin/funcionalidades');
    }

    private function datos(): array
    {
        return [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'codigo_interno' => trim($_POST['codigo_interno'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'tipo_valor' => $_POST['tipo_valor'] ?? 'booleano',
            'estado' => $_POST['estado'] ?? 'activo',
        ];
    }


    private function agruparFuncionalidades(array $funcionalidades): array
    {
        $grupos = [
            'limites' => [],
            'menu' => [],
            'adicionales' => [],
        ];

        foreach ($funcionalidades as $funcionalidad) {
            if (($funcionalidad['tipo_valor'] ?? 'booleano') !== 'booleano') {
                $grupos['limites'][] = $funcionalidad;
                continue;
            }

            if (str_starts_with((string) ($funcionalidad['codigo_interno'] ?? ''), 'modulo_')) {
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
            'catalogo_dominio_personalizado' => ['modulo_catalogo_en_linea', 'modulo_configuracion'],
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
