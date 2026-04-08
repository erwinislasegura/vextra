<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h4 mb-0">Editar contacto</h1>
    <a href="<?= e(url('/app/contactos')) ?>" class="btn btn-outline-secondary btn-sm">Volver al listado</a>
</div>

<div class="card">
    <div class="card-header">Datos del contacto</div>
    <div class="card-body">
        <?php
        $accionFormulario = url('/app/contactos/editar/' . (int) $contacto['id']);
        $textoBoton = 'Guardar cambios';
        $mostrarCancelar = true;
        include __DIR__ . '/_formulario.php';
        ?>
    </div>
</div>
