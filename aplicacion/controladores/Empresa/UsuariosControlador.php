<?php

namespace Aplicacion\Controladores\Empresa;

use Aplicacion\Nucleo\Controlador;
use Aplicacion\Modelos\Usuario;
use Aplicacion\Servicios\ServicioPlan;

class UsuariosControlador extends Controlador
{
    public function index(): void
    {
        $modelo = new Usuario();
        $usuarios = $modelo->listarPorEmpresa(empresa_actual_id());
        $roles = $modelo->listarRolesEmpresa();
        $this->vista('empresa/usuarios/index', compact('usuarios', 'roles'), 'empresa');
    }

    public function guardar(): void
    {
        validar_csrf();
        $modelo = new Usuario();
        (new ServicioPlan())->validarLimite(empresa_actual_id(), 'maximo_usuarios', $modelo->contarPorEmpresa(empresa_actual_id()), 'Has alcanzado el máximo de usuarios permitido por tu plan.');
        $modelo->crear([
            'empresa_id' => empresa_actual_id(),
            'rol_id' => (int) ($_POST['rol_id'] ?? 0),
            'nombre' => trim($_POST['nombre'] ?? ''),
            'correo' => trim($_POST['correo'] ?? ''),
            'password' => password_hash($_POST['password'] ?? '123456', PASSWORD_BCRYPT),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'cargo' => trim($_POST['cargo'] ?? ''),
            'biografia' => trim($_POST['biografia'] ?? ''),
            'estado' => $_POST['estado'] ?? 'activo',
        ]);
        flash('success', 'Usuario creado correctamente.');
        $this->redirigir('/app/usuarios');
    }

    public function ver(int $id): void
    {
        $usuario = (new Usuario())->obtenerPorIdEmpresa(empresa_actual_id(), $id);
        if (!$usuario) {
            flash('danger', 'Usuario no encontrado.');
            $this->redirigir('/app/usuarios');
        }
        $this->vista('empresa/usuarios/ver', compact('usuario'), 'empresa');
    }

    public function editar(int $id): void
    {
        $modelo = new Usuario();
        $usuario = $modelo->obtenerPorIdEmpresa(empresa_actual_id(), $id);
        if (!$usuario) {
            flash('danger', 'Usuario no encontrado.');
            $this->redirigir('/app/usuarios');
        }
        $roles = $modelo->listarRolesEmpresa();
        $esUsuarioLogueado = (int) (usuario_actual()['id'] ?? 0) === (int) $usuario['id'];
        $this->vista('empresa/usuarios/editar', compact('usuario', 'roles', 'esUsuarioLogueado'), 'empresa');
    }

    public function actualizar(int $id): void
    {
        validar_csrf();
        $modelo = new Usuario();
        $usuario = $modelo->obtenerPorIdEmpresa(empresa_actual_id(), $id);

        if (!$usuario) {
            flash('danger', 'Usuario no encontrado.');
            $this->redirigir('/app/usuarios');
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $rolId = (int) ($_POST['rol_id'] ?? 0);
        $telefono = trim($_POST['telefono'] ?? '');
        $cargo = trim($_POST['cargo'] ?? '');
        $biografia = trim($_POST['biografia'] ?? '');
        $estado = $_POST['estado'] ?? 'activo';

        $datosActualizar = [
            'nombre' => $nombre,
            'correo' => $correo,
            'telefono' => $telefono,
            'cargo' => $cargo,
            'biografia' => $biografia,
            'rol_id' => $rolId,
            'estado' => $estado,
        ];

        $esUsuarioLogueado = (int) (usuario_actual()['id'] ?? 0) === (int) $usuario['id'];
        $nuevaPassword = trim($_POST['nueva_password'] ?? '');
        $confirmarPassword = trim($_POST['confirmar_password'] ?? '');

        if ($esUsuarioLogueado && $nuevaPassword !== '') {
            $passwordActual = $_POST['password_actual'] ?? '';

            if (!password_verify($passwordActual, $usuario['password'])) {
                flash('danger', 'La contraseña actual no es correcta.');
                $this->redirigir('/app/usuarios/editar/' . $id);
            }

            if (strlen($nuevaPassword) < 8) {
                flash('danger', 'La nueva contraseña debe tener al menos 8 caracteres.');
                $this->redirigir('/app/usuarios/editar/' . $id);
            }

            if ($nuevaPassword !== $confirmarPassword) {
                flash('danger', 'La confirmación de contraseña no coincide.');
                $this->redirigir('/app/usuarios/editar/' . $id);
            }

            $datosActualizar['password'] = password_hash($nuevaPassword, PASSWORD_BCRYPT);
        }

        $modelo->actualizarEmpresa(empresa_actual_id(), $id, $datosActualizar);

        if ($esUsuarioLogueado) {
            $_SESSION['usuario']['nombre'] = $nombre;
            $_SESSION['usuario']['correo'] = $correo;
        }

        flash('success', 'Usuario actualizado correctamente.');
        $this->redirigir('/app/usuarios');
    }
}
