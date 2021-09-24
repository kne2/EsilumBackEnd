<?php 

    require '../EsilumBackEnd/utils/autoloader.php';

    class MensajeController{

        public static function NuevoMensaje($chatId,$contenido){
            if(!isset($_SESSION['autenticado'])){
                header("Location: /login");
                return;
            }
            if($contenido !== "") {
                try{
                    date_default_timezone_set('America/Montevideo');
                    $m = new MensajeModelo();
                    $m -> chatId = $chatId;
                    $m -> userId = $_SESSION['usuarioId'];
                    $m -> fecha = date("Y-m-d H:i:s");
                    $m -> contenido = $contenido;
                    $m -> guardar();
                    header("Location: /");
                    return;
                }
                catch(Exception $e){
                    error_log($e -> getMessage());
                    header("Location: /");
                    return;
                }
            }
            header("Location: /");
            return;
        }

        public static function MensajeConAsignatura($nombreAsignatura, $mensaje){
            $d = new ChatModelo();
            $d -> getChatConAsignatura($nombreAsignatura);
            MensajeController::NuevoMensaje($d -> id, $mensaje);
        }

        public static function DevolverMensajesPorId($id){
            $m = new MensajeModelo();
            $mensajes = array();
            foreach($m -> obtenerTodos($id) as $fila){
                $mensaje = array(
                    "mensajeId" => $fila -> id,
                    "chatId" => $fila -> chatId,
                    "fecha" => $fila -> fecha,
                    "userId" => $fila -> userId,
                    "contenido" => $fila -> contenido
                );
                array_push($mensajes,$mensaje);
            }
            return $mensajes;
        }

        public static function EliminarMensaje($id){
            $m = new MensajeModelo();
            $m -> id = $id;
            $m -> eliminar();
        }
    }
    
