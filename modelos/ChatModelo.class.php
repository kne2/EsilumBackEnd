<?php 
    require '../EsilumBackEnd/utils/autoloader.php';

    class ChatModelo extends Modelo{
        public $id;
        public $nombreAsignatura;
        public $userId;
        public $fecha;
        public $resuelto;

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
            $sql = "INSERT INTO chat(userId,nombreAsignatura,fecha,resuelto) VALUES (?,?,?,?)";
            $this -> sentencia = $this -> conexion -> prepare($sql);
            $this -> sentencia -> bind_param("ssss",
                $this -> userId,
                $this -> nombreAsignatura,
                $this -> fecha,
                $this -> resuelto,
            );
        }

        private function prepararEliminar(){
            $sql = "DELETE FROM chat WHERE id = ?";
            $this -> sentencia = $this -> conexion -> prepare($sql);
            $this -> sentencia -> bind_param("i", $this -> id);
        }

        public function CheckearEstado($nombreAsignatura){
            $sql = "SELECT * FROM chat WHERE resuelto = 'false' AND nombreAsignatura = '{$nombreAsignatura}'";
            $res = $this -> conexion -> query($sql);
            if($res) return ($res->num_rows === 0) ? True : False;
            if(!$res) return True;
        }

        public function getChatConAsignatura($nombreAsignatura){
            if (!$this -> CheckearEstado($nombreAsignatura)){
                $sql = "SELECT chatId,userId,nombreAsignatura,fecha,resuelto FROM chat WHERE resuelto = 'false' AND nombreAsignatura = ?";
                $this -> sentencia = $this -> conexion -> prepare($sql);
                $this -> sentencia -> bind_param("s", $nombreAsignatura);
                $this -> sentencia -> execute();

                $resultado = $this -> sentencia -> get_result() -> fetch_assoc();

                $this -> id = $resultado['chatId'];
                $this -> userId = $resultado['userId'];
                $this -> nombreAsignatura = $resultado['nombreAsignatura'];
                $this -> fecha = $resultado['fecha'];
                $this -> resuelto = $resultado['resuelto'];
            }
        }

        public function CambiarAResuelto($id){
            $resuelto = "true";
            $sql = "UPDATE chat set resuelto = ? WHERE chatId = ?";
            $this -> sentencia = $this -> conexion -> prepare($sql);
            $this -> sentencia -> bind_param("si",
                $resuelto,
                $id
            );
            $this -> sentencia -> execute();
        }

        public function obtenerTodos(){
            $filas = $this -> crearArrayDeConsultas();
            if($this -> conexion -> error){
                throw new Exception("Error al obtener las personas: " . $this -> conexion -> error);
            }
            return $filas;
        }

        private function crearArrayDeConsultas(){
            $sql = "SELECT consultaId,consultaTitulo,consultaDescripcion,fecha,resuelto,alumnoId FROM consulta ORDER BY fecha DESC";
            $filas = array();
            foreach($this -> conexion -> query($sql) -> fetch_all(MYSQLI_ASSOC) as $fila){
                $c = new ConsultaModelo();
                $c -> id = $fila['consultaId'];
                $c -> titulo = $fila['consultaTitulo'];
                $c -> descripcion = $fila['consultaDescripcion'];
                $c -> fecha = $fila['fecha'];
                $c -> estado = $fila['resuelto'];
                $c -> alumnoId = $fila['alumnoId'];

                array_push($filas,$c);
            }
            return $filas;
        }

        public function obtenerUno(){
            $this -> prepararObtenerUno($this -> id);
            $resultado = $this -> sentencia -> execute() -> fetch_all();
            if($this -> sentencia -> error){
                throw new Exception("Error al obtener la personas: " . $this -> sentencia -> error);
            }
            $this -> asignarCamposDeConsulta($resultado);
        }

        public function getDatosConId(){
            $sql = "SELECT consultaId,consultaTitulo,consultaDescripcion,fecha,resuelto,alumnoId FROM consulta WHERE consultaId = ?";
            $this -> sentencia = $this -> conexion -> prepare($sql);
            $this -> sentencia -> bind_param("s", $this -> id);
            $this -> sentencia -> execute();

            $resultado = $this -> sentencia -> get_result() -> fetch_assoc();

            if($this -> sentencia -> error){
                throw new Exception("Error al obtener el usuario: " . $this -> sentencia -> error);
            }

            if($resultado){
                $this -> asignarCamposDeConsulta($resultado);
            }
        }

        private function prepararObtenerUno(){
            $sql = "SELECT consultaId,consultaTitulo,consultaDescripcion,fecha,resuelto,alumnoId FROM consulta WHERE consultaId = ?";
            $this -> sentencia = $this -> conexion -> prepare($sql);
            $this -> id = (int)$this -> id;
            $this -> sentencia -> bind_param("i", $this -> id);
        }

        private function asignarCamposDeConsulta($resultado){
            $this -> id = $resultado['consultaId'];
            $this -> titulo = $resultado['consultaTitulo'];
            $this -> descripcion = $resultado['consultaDescripcion'];
            $this -> fecha = $resultado['fecha'];
            $this -> estado = $resultado['resuelto'];
            $this -> alumnoId = $resultado['alumnoId'];
        }
    }