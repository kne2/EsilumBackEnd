<?php 

    require '../utils/autoloader.php';
    
    class Modelo{
        protected $IpDB;
        protected $NombreUsuarioDB;
        protected $PasswordDB;
        protected $NombreDB;
        protected $PuertoDB;
        protected $conexion;
        protected $sentencia;

        public function __construct(){
            $this -> inicializarParametrosDeConexion();
            $this -> conexion = new mysqli(
                $this -> IpDB,
                $this -> NombreUsuarioDB,
                $this -> PasswordDB,
                $this -> NombreDB,
                $this -> PuertoDB
            );

            if($this -> conexion -> connect_error){
                throw new Exception("No se pudo conectar");
            }
        }

        protected function inicializarParametrosDeConexion(){
            $this -> IpDB = IP_DB_ALUMNO;
            $this -> NombreUsuarioDB = USUARIO_DB_ALUMNO;
            $this -> PasswordDB = PASSWORD_DB_ALUMNO;
            $this -> NombreDB = NOMBRE_DB_ALUMNO;
            $this -> PuertoDB = PUERTO_DB_ALUMNO;
        }
    }