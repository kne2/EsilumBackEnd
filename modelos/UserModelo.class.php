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
            $sql = "SELECT id, passwordhash, nombre, apellido, email, avatar, tipodeusuario FROM user WHERE id=?";
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
                $_SESSION['usuarioTipodeusuario'],
                $this -> id,
            );

        }
        private function prepararInsert(){
            $this -> password = $this -> hashearPassword($this -> password);
            $sql = "INSERT INTO user(id, passwordhash, nombre, apellido, email, avatar, tipodeusuario) VALUES (?,?,?,?,?,?,?)";
            $this -> sentencia = $this -> conexion -> prepare($sql);
            $this -> sentencia -> bind_param("sssssis",
                $this -> id,
                $this -> password,
                ucwords(strtolower($this -> nombre)),
                ucwords(strtolower($this -> apellido)),
                $this -> checkEmail(),
                $this -> avatar,
                $this -> tipodeusuario, 
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
        
        public static function AsignarGruposAlumno($grupos){
            
        }

        public function GetGruposDeAlumno(){
            $grupos = $this -> getGrupos();
            $arraydegruposalumno = array();
            $sql = "SELECT nombreGrupo FROM alumnoAnotaGrupo WHERE userId = ? ORDER BY nombreGrupo";
            $this -> sentencia = $this -> conexion -> prepare($sql);
            $this -> sentencia -> bind_param("s", $this -> id);
            $this -> sentencia -> execute();
            $resultado = $this -> conexion -> query($sql) -> fetch_all(MYSQLI_ASSOC);

            if($this -> sentencia -> error){
                throw new Exception("Error al obtener el usuario: " . $this -> sentencia -> error);
            }

            if(count($resultado) > 0){
                foreach($grupos as $grupo){
                    foreach($resultado as $grupoalumno){
                        if($grupo['nombreGrupo'] === $grupoalumno['nombreGrupo']){
                            $arraydegruposalumno[$grupo['nombreGrupo']] = True;
                            break;
                        }
                        else{
                            $arraydegruposalumno[$grupo['nombreGrupo']] = False;
                        }
                    }
                }
            }
            error_log(print_r($arraydegruposalumno, True));
            return $arraydegruposalumno;

        }

        private function getGrupos(){
            $grupos = array();
            $sql = "SELECT nombreGrupo FROM grupo ORDER BY nombreGrupo";
            foreach($this -> conexion -> query($sql) -> fetch_all(MYSQLI_ASSOC) as $fila){
                array_push($grupos,$fila['nombreGrupo']);
            }
            return $grupos;
        }

        private function compararPasswords($passwordHasheado, $respuestausuario){
            return (password_verify($this -> password, strval($passwordHasheado))&&($respuestausuario === $this -> tipodeusuario));
        }

        private function hashearPassword($password){
            return password_hash($password,PASSWORD_DEFAULT);
        }
    }

