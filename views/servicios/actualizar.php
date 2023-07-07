<?php
    include_once __DIR__ . '/../templates/boton-volver.php';
?>

<h1 class="nombre-pagina">Actualizar</h1>
<p class="descripcion-pagina">Modifica los valores del formulario</p>

<form method="POST">
    <?php
        include_once __DIR__ . '/../templates/alertas.php';
        include_once __DIR__ . '/formulario.php';
    ?>
    <input type="submit" value="Actualizar Servicio" class="boton">
</form>