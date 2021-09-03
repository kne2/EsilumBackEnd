<?php 
    require '../EsilumBackEnd/utils/autoloader.php';

    class RespuestaModelo extends Modelo{
        public $id;
        public $contenido;
        public $fecha;
        public $userId;
        public $consultaId;

        public function guardar(){
            error_log("Guardar");
            $this -> prepararInsert();
            $this -> sentencia -> execute();

            if($this -> sentencia -> error){
                throw new Exception("Hubo un problema al cargar la persona: " . $this -> sentencia -> error);
            }
        }

        private function prepararInsert(){
            $sql = "INSERT INTO respuesta(respuestaContenido,fecha,userId,consultaId) VALUES (?,?,?,?)";
            $this -> sentencia = $this -> conexion -> prepare($sql);
            $this -> sentencia -> bind_param("ssss",
                $this -> contenido,
                $this -> fecha,
                $this -> userId,
                $this -> consultaId
            );
        }
        public function eliminar(){
            $this -> prepararEliminar();
            $this -> sentencia -> execute();

            if($this -> sentencia -> error){
                throw new Exception("Hubo un problema al eliminar la persona: " . $this -> conexion -> error);
            }
        }

        private function prepararEliminar(){
            $sql = "DELETE FROM respuesta WHERE id = ?";
            $this -> sentencia = $this -> conexion -> prepare($sql);
            $this -> sentencia -> bind_param("i", $this -> id);
        }



        public function obtenerTodos($id){
            $filas = $this -> crearArrayDeRespuestas($id);
            if($this -> conexion -> error){
                throw new Exception("Error al obtener las personas: " . $this -> conexion -> error);
            }
            return $filas;
        }

        private function crearArrayDeRespuestas($id){
            $sql = "SELECT respuestaId,respuestaContenido,fecha,userId,consultaId FROM respuesta WHERE consultaId= ". $id ." ORDER BY fecha";
            #$this -> sentencia = $this -> conexion -> prepare($sql);
            #$id = (int)$id;
            #$this -> sentencia -> bind_param("i", $this -> id);
            $filas = array();
            foreach($this -> conexion -> query($sql) -> fetch_all(MYSQLI_ASSOC) as $fila){
                $r = new RespuestaModelo();
                $r -> id = $fila['respuestaId'];
                $r -> contenido = $fila['respuestaContenido'];
                $r -> fecha = $fila['fecha'];
                $r -> userId = $fila['userId'];
                $r -> consultaId = $fila['consultaId'];

                array_push($filas,$r);
            }
            return $filas;

        }

        private function asignarCamposDeRespuesta($resultado){
                $this -> id = $resultado['respuestaId'];
                $this -> contenido = $resultado['respuestaContenido'];
                $this -> fecha = $resultado['fecha'];
                $this -> userId = $resultado['userId'];
                $this -> consultaId = $resultado['consultaId'];
        }
    }