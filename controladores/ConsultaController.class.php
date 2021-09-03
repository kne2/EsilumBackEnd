<?php 

    require '../utils/autoloader.php';

    class ConsultaController{

        public static function NuevaConsulta($titulo,$descripcion){
            if(!isset($_SESSION['autenticado'])){
                header("Location: http://".$_SERVER['SERVER_NAME']);
                return;
            }
            if($titulo !== ""){
                try{
                    date_default_timezone_set('America/Montevideo');
                    $c = new ConsultaModelo();
                    $c -> titulo = $titulo;
                    $c -> descripcion = $descripcion; 
                    $c -> fecha = date("Y-m-d H:i:s");
                    $c -> estado = "false";
                    $c -> alumnoId = $_SESSION['usuarioId'];
                    $c -> guardar();
                    return generarHtml('realizarconsulta',['exito' => true]);
                }
                catch(Exception $e){
                    error_log($e -> getMessage());
                    return generarHtml('realizarconsulta',['exito' =>false]);
                }
            }
            return generarHtml('realizarconsulta',['exito' => false]);
        }

        public static function ObtenerConsultas(){
            
            if(!isset($_SESSION['autenticado'])){
                header("Location: http://".$_SERVER['SERVER_NAME']);
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

        public static function DevolverConsultaPorId($id){
            $c = new ConsultaModelo();
            $c -> id = $id;
            $c -> getDatosConId();
            return $c;
        }
        public static function MostrarConsulta($id){
            $r = new RespuestaModelo();
            $respuestas = array();
            foreach($r -> obtenerTodos($id) as $fila){
                $respuesta = array(
                    "id" => $fila -> id,
                    "contenido" => $fila -> contenido,
                    "fecha" => $fila -> fecha,
                    "userId" => $fila -> userId,
                    "consultaId" => $fila -> consultaId
                );
                array_push($respuestas,$respuesta);
            }
            generarHtml("consulta",['respuestas' => $respuestas]);
        }

        public static function EliminarConsulta($id){
            $c = new ConsultaModelo();
            $c -> obtenerUno($id);
            $c -> eliminar();
        }

    }
    
