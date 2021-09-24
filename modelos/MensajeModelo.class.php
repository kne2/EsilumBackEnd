<?php 
    require '../EsilumBackEnd/utils/autoloader.php';

    class MensajeModelo extends Modelo{
        public $id;
        public $chatId;
        public $userId;
        public $fecha;
        public $contenido;

        public function guardar(){
            $this -> prepararInsert();
            $this -> sentencia -> execute();

            if($this -> sentencia -> error){
                throw new Exception("Hubo un problema al cargar la persona: " . $this -> sentencia -> error);
            }
        }

        public function eliminar(){
            $this -> prepararEliminar();
            $this -> sentencia -> execute();

            if($this -> sentencia -> error){
                throw new Exception("Hubo un problema al eliminar la persona: " . $this -> conexion -> error);
            }
        }

        private function prepararInsert(){
            error_log("prepararInsert");
            $sql = "INSERT INTO mensaje(chatId,userId,fecha,contenido) VALUES (?,?,?,?)";
            $this -> sentencia = $this -> conexion -> prepare($sql);
            $this -> sentencia -> bind_param("isss",
                $this -> chatId,
                $this -> userId,
                $this -> fecha,
                $this -> contenido
            );
        }

        private function prepararEliminar(){
            $sql = "DELETE FROM mensaje WHERE id = ?";
            $this -> sentencia = $this -> conexion -> prepare($sql);
            $this -> sentencia -> bind_param("i", $this -> id);
        }

        public function obtenerTodos($id){
            $filas = $this -> crearArrayDeMensajes($id);
            if($this -> conexion -> error){
                throw new Exception("Error al obtener las personas: " . $this -> conexion -> error);
            }
            return $filas;
        }

        private function crearArrayDeMensajes($id){
            $sql = "SELECT mensajeId,chatId,userId,fecha,contenido FROM mensaje WHERE chatId= ". $id ." ORDER BY fecha ASC";
            #$this -> sentencia = $this -> conexion -> prepare($sql);
            #$id = (int)$id;
            #$this -> sentencia -> bind_param("i", $this -> id);
            $filas = array();
            foreach($this -> conexion -> query($sql) -> fetch_all(MYSQLI_ASSOC) as $fila){
                $m = new RespuestaModelo();
                $m -> id = $fila['mensajeId'];
                $m -> chatId = $fila['chatId'];
                $m -> fecha = $fila['fecha'];
                $m -> userId = $fila['userId'];
                $m -> contenido = $fila['contenido'];

                array_push($filas,$m);
            }
            return $filas;

        }
    }