<?php

namespace Controllers;

use Model\Cita;
use Model\CitasServicios;
use Model\Servicio;

class APIController {
    public static function index() {
        $servicios = Servicio::all();

        echo json_encode($servicios);
    }

    public static function guardar() {
        
        // Almacena la Cita y devuelve la ID
        $cita = new Cita($_POST);
        $resultado = $cita->guardar();
        
        $id = $resultado['id'];

        // Almacena los Servicios con el ID de la Cita 
        $idServicios = explode(',', $_POST['servicios']);
        foreach($idServicios as $idServicio) {
            $args = [
                'citaId' => $id,
                'servicioId' => $idServicio
            ];
            $citaServicio = new CitasServicios($args);
            $citaServicio->guardar();
        }
        
        echo json_encode(['resultado' => $resultado]);
    }

    public static function eliminar() {
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $id = $_POST['id'];

            if(filter_var($id, FILTER_VALIDATE_INT)){
                $cita = Cita::find($id);
            }
            
            if($cita){
                $cita->eliminar();
            }
            
            header('Location:' . $_SERVER['HTTP_REFERER']);
        }
    }
}