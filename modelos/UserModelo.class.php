<?php 
    require '../EsilumBackEnd/utils/autoloader.php';

    class UserModelo extends Modelo{
        public $id;
        public $nombre;
        public $apellido;
        public $password;
        public $email;
        public $avatar;
        public $tipodeusuario;

        public function Guardar(){
            $this -> prepararInsert();
            $this -> sentencia -> execute();

            if($this -> sentencia -> error){
                throw new Exception("Hubo un problema al cargar el usuario: " . $this -> sentencia -> error);
            }
        }

        public function Actualizar(){
            $this -> prepararUpdate();
            $this -> sentencia -> execute();

            if($this -> sentencia -> error){
                throw new Exception("Hubo un problema al cargar el usuario: " . $this -> sentencia -> error);
            }
        }

        public function Autenticar(){
            $this -> prepararAutenticacion();
            $this -> sentencia -> execute();

            $resultado = $this -> sentencia -> get_result() -> fetch_assoc();

            if($this -> sentencia -> error){
                throw new Exception("Error al obtener el usuario: " . $this -> sentencia -> error);
            }


            if($resultado){
                $comparacion = $this -> compararPasswords($resultado['passwordhash'], $resultado['tipodeusuario']);
                if($comparacion){
                   $this -> asignarDatosDeUsuario($resultado);
                }   
                else{
                    error_log("Contraseña incorrecta " . $this -> id);
                    throw new Exception("Contraseña incorrecta");
                }
            }
            
            else{
                error_log("No se encontraron usuarios con id " . $this -> id);
                throw new Exception("Error al iniciar sesion");
            }
        }

        private function prepararAutenticacion(){
            $sql = "SELECT id, passwordhash, nombre, apellido, email, avatar, tipodeusuario FROM user WHERE aprovado='true' AND id=?";
            $this -> sentencia = $this -> conexion -> prepare($sql);
            $this -> sentencia -> bind_param("s",
                $this -> id
            );
        }

        private function prepararUpdate(){
            $this -> password = $this -> hashearPassword($this -> password);
            $sql = "UPDATE user set passwordhash = ?, nombre = ?, apellido = ?, email = ?, avatar = ?, tipodeusuario = ? WHERE id=?";
            $this -> sentencia = $this -> conexion -> prepare($sql);
            $this -> sentencia -> bind_param("ssssiss",
                $this -> password,
                ucwords(strtolower($this -> nombre)),
                ucwords(strtolower($this -> apellido)),
                $this -> checkEmail(),
                $this -> avatar,
                $this -> tipodeusuario,
                $this -> id,
            );

        }
        private function prepararInsert(){
            $falso = "false";
            $this -> password = $this -> hashearPassword($this -> password);
            $sql = "INSERT INTO user(id, passwordhash, nombre, apellido, email, avatar, tipodeusuario, aprovado) VALUES (?,?,?,?,?,?,?,?)";
            $this -> sentencia = $this -> conexion -> prepare($sql);
            $this -> sentencia -> bind_param("sssssiss",
                $this -> id,
                $this -> password,
                ucwords(strtolower($this -> nombre)),
                ucwords(strtolower($this -> apellido)),
                $this -> checkEmail(),
                $this -> avatar,
                $this -> tipodeusuario,
                $falso
            );
        }

        public function AprovarGrupo($id,$grupo){
            $this -> password = $this -> hashearPassword($this -> password);
            $sql = "UPDATE alumnoAnotaGrupo set aprovado = 'true' WHERE id=? AND nombregrupo=?";
            $this -> sentencia = $this -> conexion -> prepare($sql);
            $this -> sentencia -> bind_param("ss",
                $id,
                $grupo
            );
        }

        public function AprovarAsignatura($id,$asignatura){
            $this -> password = $this -> hashearPassword($this -> password);
            $sql = "UPDATE docenteAnotaAsignatura set aprovado = 'true' WHERE id=? AND nombreAsignatura=?";
            $this -> sentencia = $this -> conexion -> prepare($sql);
            $this -> sentencia -> bind_param("ss",
                $id,
                $asignatura
            );
        }

        private function checkEmail(){
            if ($this -> email !== ""){
                return $this -> email;
            }
            return NULL;
        }

        public function getDatosConId(){
            $sql = "SELECT id, nombre, apellido, email, avatar, tipodeusuario FROM user WHERE id = ?";
            $this -> sentencia = $this -> conexion -> prepare($sql);
            $this -> sentencia -> bind_param("s", $this -> id);
            $this -> sentencia -> execute();

            $resultado = $this -> sentencia -> get_result() -> fetch_assoc();

            if($this -> sentencia -> error){
                throw new Exception("Error al obtener el usuario: " . $this -> sentencia -> error);
            }

            if($resultado){
                $this -> asignarDatosDeUsuario($resultado);
            }
        }

        private function asignarDatosDeUsuario($resultado){
            $this -> id = $resultado['id'];
            $this -> nombre = $resultado['nombre'];
            $this -> apellido = $resultado['apellido'];
            $this -> email = $resultado['email'];
            $this -> avatar = $resultado['avatar'];
            $this -> tipodeusuario = $resultado['tipodeusuario'];
        }
        
        public function AsignarGruposAlumno($nuevosgrupos){
            $grupos = $this -> getGrupos();
            $gruposviejos = $this -> GetGruposDeAlumno();
            foreach($grupos as $key => $valor){
                foreach($nuevosgrupos as $grupoalumno){
                    if ($key == $grupoalumno){
                        $grupos[$key] = True;
                    }
                }
            }
            foreach($grupos as $key => $valor){
                if($valor){
                    if(!($gruposviejos[$key])){
                        $this -> asignarGrupo($key);
                    }
                }
                else{
                    if(($gruposviejos[$key])){
                        $this -> eliminarDeGrupo($key);
                    } 
                }
            }
        }

        public function GetGruposDeAlumno(){
            $grupos = $this -> getGrupos();
            $sql = 'SELECT nombreGrupo FROM alumnoAnotaGrupo WHERE userId = ? ORDER BY nombreGrupo';
            $this -> sentencia = $this -> conexion -> prepare($sql);
            $this -> sentencia -> bind_param("s", $this -> id);
            $this -> sentencia -> execute();

            $resultado = $this -> sentencia -> get_result() -> fetch_all(MYSQLI_ASSOC);
            foreach($grupos as $key => $valor){
                foreach($resultado as $grupoalumno){
                    if ($key == $grupoalumno['nombreGrupo']){
                        $grupos[$key] = True;
                    }
                }
            }
            return $grupos;
        }

        private function getGrupos(){
            $grupos = array();
            $sql = "SELECT nombreGrupo FROM grupo ORDER BY nombreGrupo";
            foreach($this -> conexion -> query($sql) -> fetch_all(MYSQLI_ASSOC) as $fila){
                $grupos[$fila['nombreGrupo']] = False;
            }
            return $grupos;
        }

        private function eliminarDeGrupo($grupo){
            $sql = "DELETE FROM alumnoAnotaGrupo WHERE userId = ? and nombreGrupo = ?";
            $this -> sentencia = $this -> conexion -> prepare($sql);
            $this -> sentencia -> bind_param("ss", $this -> id, $grupo);
            $this -> sentencia -> execute();
        }

        private function asignarGrupo($grupo){
            $sql = "INSERT INTO alumnoAnotaGrupo(userId,nombreGrupo) VALUES (?,?)";
            $this -> sentencia = $this -> conexion -> prepare($sql);
            $this -> sentencia -> bind_param("ss",
                $this -> id,
                $grupo
            );
            $this -> sentencia -> execute();
        }

        private function compararPasswords($passwordHasheado, $respuestausuario){
            return (password_verify($this -> password, strval($passwordHasheado))&&($respuestausuario === $this -> tipodeusuario));
        }

        private function hashearPassword($password){
            return password_hash($password,PASSWORD_DEFAULT);
        }
    }

