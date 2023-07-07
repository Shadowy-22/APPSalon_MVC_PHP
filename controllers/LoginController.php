<?php

namespace Controllers;

use MVC\Router;

use Classes\Email;
use Model\Usuario;

class LoginController {
    public static function login(Router $router) {

        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST'){

            $auth = new Usuario($_POST);

            $alertas = $auth->validarLogin();

            if(empty($alertas)) {
                // Comprobar que exista el usuario
                $usuario = Usuario::where('email', $auth->email);

                if($usuario) {
                    // Verificar que el usuario esté confirmado
                    if ( $usuario->verifyPasswordAndVerification($auth->password) ) {
                        // Autenticar el usuario
                        session_start();

                        $_SESSION['id'] = $usuario->id;
                        $_SESSION['nombre'] = $usuario->nombre . " " . $usuario->apellido;
                        $_SESSION['email'] = $usuario->email;
                        $_SESSION['login'] = true;

                        // Redireccionamiento
                        if($usuario->admin === '1'){
                            $_SESSION['admin'] = $usuario->admin ?? null;
                            header('Location: /admin');
                        } else {
                            header('Location: /cita');
                        };
                    }
                    
                } else {
                    Usuario::setAlerta('error', 'Usuario no registrado');
                }
            }
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/login', [
            'alertas' => $alertas
        ]); 
    }

    public static function logout() {
        session_start();

        $_SESSION = [];

        header('Location: /');
    }

    public static function olvide(Router $router) {

        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $auth = new Usuario($_POST);
            $alertas = $auth->validarEmail();

            if(empty($alertas)) {
                // Comprobar que existe el usuario
                $usuario = Usuario::where('email', $auth->email);

                if($usuario && $usuario->confirmado === '1'){

                    // Generar un Token Unico
                    $usuario->crearToken();
                    $usuario->guardar();

                    // Enviar el Email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->recuperarCuenta();

                    // Alerta Exito
                    Usuario::setAlerta('exito', 'Te hemos enviado un email para que recuperes tu cuenta');
                } else {
                    // Alerta Error
                    Usuario::setAlerta('error', 'El usuario no existe o no está confirmado');
                }
            }
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/olvide-password', [
            'alertas' => $alertas
        ]); 
    }
    public static function recuperar(Router $router) {

        $alertas = [];
        $error = false;
        $token = s($_GET['token']);

        // Buscar usuario por su token
        $usuario = Usuario::where('token', $token);


        if(empty($usuario)){
            // Si hay error, mandamos una alerta y no mostramos el codigo HTML 
            Usuario::setAlerta('error', 'Token No Válido');
            $error = true;
        } 

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            // Leer el nuevo password 
            $nuevoPassword = new Usuario($_POST);
            $alertas = $nuevoPassword->validarPassword();
            
            if(empty($alertas)) {
                // Actualizar el nuevo password y guardarlo
                $usuario->password = null;
                $usuario->password = $nuevoPassword->password;
                $usuario->hashPassword();
                $usuario->token = null;
                $resultado = $usuario->guardar();
                if($resultado){
                    // Se crea la alerta de exito
                    Usuario::setAlerta('exito', 'Su password ha sido actualizado');
                    
                    // Redirecciona al usuario luego de 3 segundos
                    header('Refresh: 3; url=/'); 
                } 
            }   
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/recuperar-password', [
            'alertas' => $alertas,
            'error' => $error
        ]); 
    }

    public static function crear(Router $router) {

        $usuario = new Usuario;

        // Alertas Vacias 
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarNuevaCuenta();
            
            // Revisar que alerta esté vacío
            if(empty($alertas)) {
                // Verificar que el usuario no esté registrado
                $resultado = $usuario->existeUsuario();

                if($resultado->num_rows) {
                    $alertas = Usuario::getAlertas();
                } else {
                    // Hashear el Password
                   $usuario->hashPassword();
                    
                   // Generar un Token Unico
                   $usuario->crearToken();

                   // Enviar el Email
                   $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                   $email->enviarConfirmacion();
                    
                   // Crear el Usuario
                   $resultado = $usuario->guardar();

                   if($resultado) {
                       header('Location: /mensaje');
                   }
                }
            }
        }

        $router->render('auth/crear-cuenta', [
            'usuario' => $usuario,
            'alertas' => $alertas
        ]); 
    }

    public static function mensaje(Router $router) {

        $router->render('auth/mensaje', [
        
        ]); 
    }
    public static function confirmar(Router $router){
        $alertas = [];

        $token = s($_GET['token']);

        $usuario = Usuario::where('token', $token);
        
        if(empty($usuario)){
            // Mostrar mensaje de error
            Usuario::setAlerta('error', 'Token No Válido');
        } else {
            // Modificar a usuario confirmado
            $usuario->confirmado = "1";
            $usuario->token = null;
            $usuario->guardar();
            Usuario::setAlerta('exito', 'Cuenta comprobada correctamente');
        }

        // Obtener alertas
        $alertas = Usuario::getAlertas();

        // Renderizar la vista
        $router->render('auth/confirmar-cuenta', [
            'alertas' => $alertas
        ]); 
    }
}