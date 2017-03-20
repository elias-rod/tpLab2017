<?php
class AccesoDatos
{
    private static $ObjetoAccesoDatos;
    private $objetoPDO;
    
    private function __construct()
    {
        try {
/*          //CREACIÓN DE LA CONEXIÓN CON EL SERVIDOR EXTERNO
            $servername = 'mysql.hostinger.es';
            $dbname = 'u636713032_traba';
            $username = 'u636713032_elias';
            $password = 'garbarino';
*/
            //CREACIÓN DE LA CONEXIÓN CON EL SERVIDOR LOCAL
            $servername = 'localhost';
            $dbname = 'trabajo';
            $username = 'root';
            $password = '';

            $this->objetoPDO = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password, 
					array(PDO::ATTR_EMULATE_PREPARES => false,PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
            $this->objetoPDO->exec("SET CHARACTER SET utf8");
            }
        catch (PDOException $e) {
            print "Error!: " . $e->getMessage();
            exit();
        }
    }
 
    public function ObtenerConsulta($sql)
    {
        return $this->objetoPDO->prepare($sql);
    }
 
    public static function ObtenerObjetoAccesoDatos()
    {
        if (!isset(self::$ObjetoAccesoDatos)) {
            self::$ObjetoAccesoDatos = new AccesoDatos();
        }
        return self::$ObjetoAccesoDatos;
    }
 
    //EVITA QUE EL OBJETO SE PUEDA CLONAR
    public function __clone()
    {
        trigger_error('La clonación de este objeto no está permitida', E_USER_ERROR);
    }
}