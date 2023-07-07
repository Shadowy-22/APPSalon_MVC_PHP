<h1 class="nombre-pagina">Panel de Administracion</h1>

<?php include_once __DIR__ . '/../templates/barra.php'  ?>

<h2>Buscar Citas</h2>
<div class="busqueda">
    <form class="formulario">
        <div class="campo">
            <label for="fecha">Fecha</label>
            <input 
                type="date"
                id="fecha"
                name="fecha"
                value="<?php echo $fecha; ?>"
            />
        </div>
    </form>
</div>

<?php
    if(count($citas) === 0) {
        echo "<h2>No hay Citas en esta fecha</h2>";
    }
?>

<div id="citas-admin">
    <ul class="citas">
        <?php
            $idCita = 0;
            foreach($citas as $key => $cita) {
                if($idCita !== $cita->id){
                    $total = 0;
                    $idCita = $cita->id;
        ?>
                    <li>
                        <p>ID: <span><?php echo $cita->id ?></p>
                        <p>Hora: <span><?php echo $cita->hora ?></p>
                        <p>Cliente: <span><?php echo $cita->cliente ?></p>
                        <p>Email: <span><?php echo $cita->email ?></p>
                        <p>Telefono: <span><?php echo $cita->telefono ?></p>
                        
                        <h3>Servicios</h3>
                <?php 
                } // Fin IF 
                    $total += $cita->precio;
                ?>
                
                        <p class="servicio"><?php echo $cita->servicio . " $" . $cita->precio;  ?></p>

                <?php
                    $actual = $cita->id;
                    $proximo = $citas[$key + 1]->id ?? 0;

                    if(esUltimo($actual, $proximo)){ ?>
                        <p class="total">Total: <span>$<?php echo $total ?></span></p>

                        <form action="/api/eliminar" method="POST">
                            <input type="hidden" name="id" value="<?php echo $cita->id ?>">
                            <input type="submit" value="Eliminar Cita" class="boton-eliminar">
                        </form>

                <?php } // Fin IF 
            } // Fin FOREACH ?>
    </ul>
</div>

<?php
    $script = "
        <script src='build/js/buscador.js'/></script>
    ";
?>