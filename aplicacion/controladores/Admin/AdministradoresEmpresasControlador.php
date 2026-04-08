<?php

namespace Aplicacion\Controladores\Admin;

use Aplicacion\Modelos\Empresa;
use Aplicacion\Modelos\LogAdministracion;
use Aplicacion\Modelos\Usuario;
use Aplicacion\Nucleo\Controlador;
use Throwable;

class AdministradoresEmpresasControlador extends Controlador
{
    public function index(): void
    {
        $filtros = [
            'busqueda' => trim($_GET['q'] ?? ''),
            'empresa_id' => $_GET['empresa_id'] ?? '',
        ];
        $administradores = (new Usuario())->listarAdministradoresEmpresa($filtros);
        $empresas = (new Empresa())->listar();
        $this->vista('admin/administradores/index', compact('administradores', 'filtros', 'empresas'), 'admin');
    }

    public function actualizar(int $id): void
    {
        validar_csrf();
        $correo = trim($_POST['correo'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        try {
            (new Usuario())->actualizarCredencialesAdmin($id, $correo, $nombre);
            (new LogAdministracion())->registrar('admin_empresas', 'actualizar_credenciales', 'Actualización de datos de acceso', null, $id);
            flash('success', 'Datos del administrador actualizados.');
        } catch (Throwable $e) {
            flash('danger', 'No se pudieron actualizar los datos del administrador.');
        }
        $this->redirigir('/admin/administradores-empresa');
    }

    public function cambiarEstado(int $id): void
    {
        validar_csrf();
        $estado = $_POST['estado'] ?? 'activo';
        try {
            (new Usuario())->actualizarEstado($id, $estado);
            (new LogAdministracion())->registrar('admin_empresas', 'cambiar_estado', 'Estado a ' . $estado, null, $id);
            flash('success', 'Estado actualizado.');
        } catch (Throwable $e) {
            flash('danger', 'No se pudo actualizar el estado del administrador.');
        }
        $this->redirigir('/admin/administradores-empresa');
    }

    public function resetearPassword(int $id): void
    {
        validar_csrf();
        $passwordManual = trim($_POST['password_nueva'] ?? '');
        $generarTemporal = isset($_POST['generar_temporal']);

        $passwordPlano = $passwordManual;
        if ($generarTemporal || $passwordPlano === '') {
            $passwordPlano = 'Tmp' . bin2hex(random_bytes(4)) . 'A!';
        }

        try {
            (new Usuario())->resetearPasswordAdminEmpresa($id, password_hash($passwordPlano, PASSWORD_DEFAULT));
            (new LogAdministracion())->registrar('admin_empresas', 'reset_password', 'Reseteo de contraseña administrativa', null, $id);
            flash('success', 'Contraseña actualizada correctamente. Clave temporal: ' . $passwordPlano);
        } catch (Throwable $e) {
            flash('danger', 'No se pudo resetear la contraseña del administrador.');
        }
        $this->redirigir('/admin/administradores-empresa');
    }

    public function accederPanelEmpresa(int $id): void
    {
        validar_csrf();
        $usuarioActual = usuario_actual();
        if (!$usuarioActual || ($usuarioActual['rol_codigo'] ?? '') !== 'superadministrador') {
            flash('danger', 'Solo un superadministrador puede acceder al panel de empresa desde esta sección.');
            $this->redirigir('/admin/administradores-empresa');
        }

        $administrador = (new Usuario())->obtenerAdministradorEmpresa($id);
        if (!$administrador) {
            flash('danger', 'No se encontró un administrador de empresa válido para el acceso solicitado.');
            $this->redirigir('/admin/administradores-empresa');
        }

        if (($administrador['estado'] ?? 'inactivo') !== 'activo') {
            flash('danger', 'El administrador de empresa está inactivo. Actívalo antes de acceder.');
            $this->redirigir('/admin/administradores-empresa');
        }

        $_SESSION['admin_empresa_contexto_id'] = (int) ($administrador['empresa_id'] ?? 0);
        $_SESSION['admin_empresa_contexto_nombre'] = (string) ($administrador['empresa_nombre'] ?? 'Empresa');

        (new LogAdministracion())->registrar(
            'admin_empresas',
            'acceso_panel_empresa',
            'Acceso de soporte al panel de empresa sin cambiar sesión de superadministrador.',
            (int) ($administrador['empresa_id'] ?? 0),
            (int) $administrador['id']
        );

        $this->redirigir('/app/panel');
    }


    public function volverPanelAdmin(): void
    {
        validar_csrf();
        $usuarioActual = usuario_actual();

        if (!$usuarioActual || ($usuarioActual['rol_codigo'] ?? '') !== 'superadministrador') {
            flash('danger', 'No tienes permisos para volver al panel de administrador.');
            $this->redirigir('/app/panel');
        }

        if (!isset($_SESSION['admin_empresa_contexto_id'])) {
            flash('info', 'Ya te encuentras en el panel de administrador.');
            $this->redirigir('/admin');
        }

        unset($_SESSION['admin_empresa_contexto_id'], $_SESSION['admin_empresa_contexto_nombre']);
        flash('success', 'Volviste al panel de administrador.');
        $this->redirigir('/admin');
    }

}
