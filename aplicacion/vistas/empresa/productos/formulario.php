<h1 class="h4 mb-3">Crear producto/servicio</h1>
<div class="card">
  <div class="card-header">Nuevo ítem comercial</div>
  <div class="card-body">
    <?php
    $accion = url('/app/productos/crear');
    $textoBoton = 'Guardar ítem';
    $mostrarCancelar = true;
    $rutaCancelar = url('/app/productos');
    $mostrarModalCategoria = true;
    $modalId = 'modalNuevaCategoriaProductoCrear';
    $redirigirA = '/app/productos/crear';
    require __DIR__ . '/_formulario.php';
    ?>
  </div>
</div>
