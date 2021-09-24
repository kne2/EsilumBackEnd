<?php 

    require '../EsilumBackEnd/utils/autoloader.php';

    class ChatController{

        public static function NuevoChat($nombreAsignatura,$mensaje){
            if(!isset($_SESSION['autenticado'])){
                header("Location: /login");
                return;
            }
            if($nombreAsignatura !== ""){
                if(self::CheckearEstado($nombreAsignatura)){
                    try{
                        date_default_timezone_set('America/Montevideo');
                        $c = new ChatModelo();
                        $c -> userId = $_SESSION['usuarioId'];
                        $c -> nombreAsignatura = $nombreAsignatura;
                        $c -> fecha = date("Y-m-d H:i:s");
                        $c -> resuelto = "false";
                        $c -> guardar();
                        $d = new ChatModelo();
                        $d -> getChatConAsignatura($nombreAsignatura);
                        MensajeController::NuevoMensaje($d -> id, $mensaje);
                        header("Location: /chat".$nombreAsignatura);
                        return;
                    }
                    catch(Exception $e){
                        error_log($e -> getMessage());
                        header("Location: /chat".$nombreAsignatura);
                        return;
                    }
                }
            }
            header("Location: /chat".$nombreAsignatura);
            return;
        }

        public static function ObtenerConsultas(){
            
            if(!isset($_SESSION['autenticado'])){
                header("Location: /login");
                return;
            }
            
            $c = new ConsultaModelo();
            $consultas = array();
            foreach($c -> obtenerTodos() as $fila){
                $consulta = array(
                    "id" => $fila -> id,
                    "titulo" => $fila -> titulo,
                    "descripcion" => $fila -> descripcion,
                    "fecha" => $fila -> fecha,
                    "resuelto" => $fila -> estado,
                    "alumnoId" => $fila -> alumnoId,
                    "docenteId" => $fila -> docenteId
                );
                array_push($consultas,$consulta);
            }
            return generarHtml('verconsultas',['consultas' => $consultas]);
        }

        public static function EliminarChat($id){
            $c = new ChatModelo();
            $c -> id = $id;
            $c -> eliminar();
        }

        public static function ResolverChat($nombreAsignatura){
            $c = new ChatModelo();
            $c -> getChatConAsignatura($nombreAsignatura);
            $c -> CambiarAResuelto($c -> id);
            header("Location: /chat".$nombreAsignatura);
            return;
        }
        
        public static function CheckearEstado($nombreAsignatura){
            $c = new ChatModelo();
            return $c -> CheckearEstado($nombreAsignatura);
        }

        public static function DevolverIdPorAsignatura($nombreAsignatura){
            $c = new ChatModelo();
            $c -> getChatConAsignatura($nombreAsignatura);
            return $c -> id;
        }

        public static function DevolverChatPorAsignatura($nombreAsignatura){
            $c = new ChatModelo();
            $c -> getChatConAsignatura($nombreAsignatura);
            return $c;
        }

        public static function CargarChat($nombreAsignatura){
            $idchat = self::DevolverIdPorAsignatura($nombreAsignatura);
            foreach(MensajeController::DevolverMensajesPorId($idchat) as $fila){
                echo'
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-muted text-left">'. UserController::DevolverUserConId($fila['userId']) -> nombre . " " . UserController::DevolverUserConId($fila['userId']) -> apellido ." - ".$fila['fecha'].'</h6>
                        <p class="card-text float-left"> '. $fila['contenido'] . '</p>
                    </div>
                </div>
                ';
            }
        }
    }
    
