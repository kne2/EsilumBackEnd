<?php 

    require '../EsilumBackEnd/utils/autoloader.php';

    class UserController{
        public static function IniciarSesion($id,$password){
            try{
                $u = new UserModelo();
                $u -> id = $id;
                $u -> password = $password;
                error_log(APP_USUARIO);
                $u -> tipodeusuario = APP_USUARIO;
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

        public static function AltaDeUsuario($id,$nombre,$apellido,$email,$password1,$password2,$tipodeusuario){

            if($id !== "" && $password1 !== "" && $nombre !== "" && $apellido !== "" && $tipodeusuario !== "" && $password1 == $password2){
                if($tipodeusuario == "alumno" || $tipodeusuario == "docente" || $tipodeusuario == "admin"){    
                    try{
                        $u = new UserModelo();
                        $u -> id = $id;   
                        $u -> nombre = $nombre;
                        $u -> apellido = $apellido;
                        $u -> email = $email;
                        $u -> password = $password1;
                        $u -> avatar = rand(1,10);
                        $u -> tipodeusuario = $tipodeusuario;
                        $u -> Guardar();
                        return header("Location: /login");
                    }
                    catch(Exception $e){
                        error_log($e -> getMessage());
                        return generarHtml('registro',["falla" => true]);
                    }
                }
            }
            return generarHtml('registro',["falla" => true]);
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
            header("Location: /login");
        }

        public static function DevolverUserConId($id){
            $u = new UserModelo();
            $u -> id = $id;
            $u -> getDatosConId();
            return $u;
        }

        public static function MostrarConsulta($id){
            generarHtml("consulta",["mostrarConsultaId" => $id]);
        }

        public static function MostrarChat(){
            if(!isset($_SESSION['autenticado'])) header("Location: /login");
            else return cargarVista("chat");
        }

        public static function MostrarLogin(){
            session_start();
            if(isset($_SESSION['autenticado'])) header("Location: /principal");
            else return cargarVista("login");
        }

        public static function MostrarRegistro(){
            session_start();
            if(isset($_SESSION['autenticado'])) header("Location: /principal");
            else return cargarVista("registro");
        }

        public static function MostrarMenuPrincipal(){
            session_start();
            if(!isset($_SESSION['autenticado'])) header("Location: /login");
            else return cargarVista("bienvenida");
        }

        public static function MostrarEditarPerfil(){
            session_start();
            if(!isset($_SESSION['autenticado'])) header("Location: /login");
            else return cargarVista("perfiledicion");
        }

        public static function MostrarPerfil(){
            session_start();
            if(!isset($_SESSION['autenticado'])) header("Location: /login");
            else return cargarVista("perfil");
        }

        public static function MostrarGrupos(){
            session_start();
            if(!isset($_SESSION['autenticado'])) header("Location: /login");
            return generarHtml('grupos',['grupos' => AlumnoController::DevolverGruposDeAlumno()]);
        }

        public static function MostrarRealizarConsulta(){
            session_start();
            if(!isset($_SESSION['autenticado'])) header("Location: /login");
            elseif($_SESSION['usuarioTipodeusuario'] !== "alumno") header("Location: /principal");
            else return cargarVista("realizarconsulta");
        }
    }