<?php 

    require '../utils/autoloader.php';

    class RespuestaController{

        public static function NuevaRespuesta($contenido,$consultaId){
            if(!isset($_SESSION['autenticado'])){
                header("Location: /login");
                return;
            }
            if($contenido !== "" && $consultaId !== ""){
                try{
                    date_default_timezone_set('America/Montevideo');
                    $r = new RespuestaModelo();
                    $r -> contenido = $contenido;
                    $r -> fecha = date("Y-m-d H:i:s");
                    $r -> userId = $_SESSION['usuarioId'];
                    $r -> consultaId = $consultaId;
                    $r -> guardar();
                    header("Location: /consulta".$consultaId);
                }
                catch(Exception $e){
                    error_log($e -> getMessage());
                    header("Location: /consulta".$consultaId);
                }
            }
            header("Location: /consulta".$consultaId);
        }

        public static function ObtenerRespuestas(){
            
            if(!isset($_SESSION['autenticado'])){
                header("Location: /login");
                return;
            }
            
            $r = new ConsultaModelo();
            $respuestas = array();
            foreach($r -> obtenerTodos() as $fila){
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
            return generarHtml('listar',['consultas' => $consultas]);
        }
        
        public static function DevolverRespuestasPorId($id){
            $r = new RespuestaModelo();
            $respuestas = $r -> obtenerTodos($id);
            return $respuestas;
        }

        public static function MostrarConsulta($id){
            generarHtml("consulta",["mostrarConsultaId" => $id]);
        }

        public static function EliminarConsulta($id){
            $c = new ConsultaModelo();
            $c -> obtenerUno($id);
            $c -> eliminar();
        }

    }
    
