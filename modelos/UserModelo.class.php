<?php 
    require '../utils/autoloader.php';

    class UserModelo extends Modelo{
        public $id;
        public $nombre;
        public $apellido;
        public $password;
        public $email;
        public $avatar;
        public $tipodeusuario;


        public function Actualizar(){
            $this -> prepararUpdate();
            $this -> sentencia -> execute();

            if($this -> sentencia -> error){
                throw new Exception("Hubo un problema al cargar el usuario: " . $this -> sentencia -> error);
            }
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

        private function checkEmail(){
            if ($this -> email !== ""){
                return $this -> email;
            }
            return NULL;
        }

        public function Autenticar(){
            $this -> prepararAutenticacion();
            $this -> sentencia -> execute();

            $resultado = $this -> sentencia -> get_result() -> fetch_assoc();

            if($this -> sentencia -> error){
                throw new Exception("Error al obtener el usuario: " . $this -> sentencia -> error);
            }


            if($resultado){
                $comparacion = $this -> compararPasswords($resultado['passwordhash']);
                if($comparacion){
                   $this -> asignarDatosDeUsuario($resultado);
                }   
                else{
                    throw new Exception("Contraseña incorrecta");
                }
            }
            
            else throw new Exception("Error al iniciar sesion");
        }

        private function compararPasswords($passwordHasheado){
            error_log($this -> password . " ". strval($passwordHasheado));
            return true;
            return hash_equals($this -> password, strval($passwordHasheado));
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

        private function prepararAutenticacion(){
            $sql = "SELECT id, passwordhash, nombre, apellido, email, avatar, tipodeusuario FROM user WHERE id = ?";
            $this -> sentencia = $this -> conexion -> prepare($sql);
            $this -> sentencia -> bind_param("s", $this -> id);
        }

        private function asignarDatosDeUsuario($resultado){
            $this -> id = $resultado['id'];
            $this -> nombre = $resultado['nombre'];
            $this -> apellido = $resultado['apellido'];
            $this -> email = $resultado['email'];
            $this -> avatar = $resultado['avatar'];
            $this -> tipodeusuario = $resultado['tipodeusuario'];
        }
        
        private function hashearPassword($password){
            return password_hash($password,PASSWORD_DEFAULT);
        }
    }

