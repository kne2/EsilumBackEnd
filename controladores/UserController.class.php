<?php 

    require '../utils/autoloader.php';

    class UserController{
        public static function IniciarSesion($id,$hash){
            try{
                $u = new UserModelo();
                $u -> id = $id;
                $u -> password = $hash;
                $u -> Autenticar();
                self::crearSesion($u);
                //cargarVista("menuPrincipal");
                
                header("Location: /principal");
            }
            catch (Exception $e) {
                error_log("Fallo login del usuario " . $id);
                generarHtml("login",["falla" => true]);
            }

        }

        public static function MostrarConsulta($id){
            generarHtml("consulta",["mostrarConsultaId" => $id]);
        }
        public static function DevolverUserConId($id){
            $u = new UserModelo();
            $u -> id = $id;
            $u -> getDatosConId();
            return $u;
        }

        public static function MostrarMenuPrincipal(){
            session_start();
            if(!isset($_SESSION['autenticado'])) header("Location: http://".$_SERVER['SERVER_NAME']);
            else return cargarVista("bienvenida");
        }

        public static function MostrarEditarPerfil(){
            session_start();
            if(!isset($_SESSION['autenticado'])) header("Location: http://".$_SERVER['SERVER_NAME']);
            else return cargarVista("perfiledicion");
        }

        public static function MostrarPerfil(){
            session_start();
            if(!isset($_SESSION['autenticado'])) header("Location: http://".$_SERVER['SERVER_NAME']);
            else return cargarVista("perfil");
        }

        public static function MostrarRealizarConsulta(){
            session_start();
            if(!isset($_SESSION['autenticado'])) header("Location: http://".$_SERVER['SERVER_NAME']);
            elseif($_SESSION['usuarioTipodeusuario'] !== "alumno") header("Location: /principal");
            else return cargarVista("realizarconsulta");
        }

        private static function crearSesion($usuario){
            session_start();
            ob_start();
            $_SESSION['usuarioId'] = $usuario -> id;
            $_SESSION['usuarioNombre'] = $usuario -> nombre;
            $_SESSION['usuarioApellido'] = $usuario -> apellido;
            $_SESSION['usuarioEmail'] = $usuario -> email;
            $_SESSION['usuarioAvatar'] = $usuario -> avatar;
            $_SESSION['usuarioTipodeusuario'] = $usuario -> tipodeusuario;
            $_SESSION['autenticado'] = true;

        }

        public static function CerrarSesion(){
            session_start();
            ob_start();
            unset($_SESSION['autenticado']);
            unset($_SESSION['usuarioId']);
            unset($_SESSION['usuarioNombre']);
            unset($_SESSION['usuarioApellido']);
            unset($_SESSION['usuarioEmail']);
            unset($_SESSION['usuarioAvatar']);
            unset($_SESSION['usuarioTipodeusuario']);
            header("Location: http://".$_SERVER['SERVER_NAME']."/cerrarsesion");
        }

        public static function EditarUser($id,$nombre,$apellido,$password1,$password2,$email,$avatar){
            if($id !== "" && $password1 !== "" && $nombre !== "" && $apellido !== "" && $avatar !== "" && $password1 == $password2){
                    try{
                        $u = new UserModelo();
                        $u -> id = $id;   
                        $u -> nombre = $nombre;
                        $u -> apellido = $apellido;
                        $u -> password = $password1;
                        $u -> email = $email;
                        $u -> avatar = $avatar;
                        $u -> tipodeusuario = $_SESSION['usuarioTipodeusuario'];
                        $u -> Actualizar();
                        self::crearSesion($u);
                        return cargarVista("perfil");
                    }
                    catch(Exception $e){
                        error_log($e -> getMessage());
                        return generarHtml('perfil',["falla" => true]);
                    }
            }
            return generarHtml('perfil',["falla" => true]);
        }
    }