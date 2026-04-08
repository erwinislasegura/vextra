<h1 class="h4 mb-3">Editar producto/servicio</h1>
<div class="card">
  <div class="card-header">Editar ítem comercial</div>
  <div class="card-body">
    <?php
    $accion = url('/app/productos/editar/' . $producto['id']);
    $textoBoton = 'Guardar cambios';
    $mostrarCancelar = true;
    $rutaCancelar = url('/app/productos');
    $mostrarModalCategoria = true;
    $modalId = 'modalNuevaCategoriaProductoEditar';
    $redirigirA = '/app/productos/editar/' . $producto['id'];
    require __DIR__ . '/_formulario.php';
    ?>
  </div>
</div>
