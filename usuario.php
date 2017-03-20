<?php
require_once('accesoDatos.php');
/**
* CLASE QUE REALIZA ABM DEL USUARIO BASE DE DATOS
*/
class Usuario
{
	public $email;
	public $password;
	public $tipo;

	function __construct($email, $password, $tipo)
	{
		$this->email = $email;
		$this->password = $password;
		$this->tipo = $tipo;
	}
	//CREACION DE UN USUARIO EN LA BASE DE DATOS
	public static function CrearUsuario($usuario){
		//VERIFICACION DE EXISTENCIA DEL USUARIO
		$sql = 'SELECT * FROM usuarios WHERE email = :email';
        $consulta = AccesoDatos::ObtenerObjetoAccesoDatos()->ObtenerConsulta($sql);
		$consulta->bindParam(':email', $usuario->email);
	    $consulta->execute();
		if ($consulta->fetch() != NULL) {
			return false;//EL USUARIO YA EXISTIA PREVIAMENTE EN LA BASE DE DATOS
		}
		else{
			//CREACION DEL USUARIO EN LA BASE DE DATOS
			$sql = 'INSERT INTO usuarios (email, password, tipo) VALUES (:email, :password, :tipo)';
	        $consulta = AccesoDatos::ObtenerObjetoAccesoDatos()->ObtenerConsulta($sql);
			$consulta->bindParam(':email', $usuario->email);
			$consulta->bindParam(':password', $usuario->password);
			$consulta->bindParam(':tipo', $usuario->tipo);
		    $consulta->execute();
		    return true;//ALTA EXITOSA
		}
	}
	//MODIFICACION DE UN USUARIO EN LA BASE DE DATOS
	public static function ModificarUsuario($usuario){
		$sql = 'UPDATE usuarios SET password = :password, tipo = :tipo WHERE email = :email';
        $consulta = AccesoDatos::ObtenerObjetoAccesoDatos()->ObtenerConsulta($sql);
		$consulta->bindParam(':email', $usuario->email);
		$consulta->bindParam(':password', $usuario->password);
		$consulta->bindParam(':tipo', $usuario->tipo);
	    $consulta->execute();
	}
	//ELIMINACION DE UN USUARIO DE LA BASE DE DATOS
	public static function EliminarUsuario($email){
		$sql = 'DELETE FROM usuarios WHERE email = :email';
        $consulta = AccesoDatos::ObtenerObjetoAccesoDatos()->ObtenerConsulta($sql);
		$consulta->bindParam(':email', $email);
	    $consulta->execute();
	}
	//OBTENCION DE TODOS LOS USUARIOS DE LA BASE DE DATOS
	public static function ObtenerTodosUsuarios(){
		$sql = 'SELECT * FROM usuarios';
        $consulta = AccesoDatos::ObtenerObjetoAccesoDatos()->ObtenerConsulta($sql);
	    $consulta->execute();
	    return $consulta->fetchAll(PDO::FETCH_ASSOC);//OBTENCION COMO UN ARRAY ASOCIATIVO
	}
	//OBTENCION DE UN USUARIO
	public static function ObtenerUsuario($email, $password){
		$sql = 'SELECT * FROM usuarios WHERE email = :email AND password = :password';
        $consulta = AccesoDatos::ObtenerObjetoAccesoDatos()->ObtenerConsulta($sql);
        $consulta->bindParam(':email', $email);
		$consulta->bindParam(':password', $password);
	    $consulta->execute();
	    return $consulta->fetch(PDO::FETCH_ASSOC);
	}
	//UPDATEO DE LA SESION DE UN USUARIO EN LA BASE DE DATOS
	public static function UpdateSesionUsuario($email, $password, $sesion){
		$sql = 'UPDATE usuarios SET sesion = :sesion WHERE email = :email AND password = :password';
        $consulta = AccesoDatos::ObtenerObjetoAccesoDatos()->ObtenerConsulta($sql);
		$consulta->bindParam(':email', $email);
		$consulta->bindParam(':password', $password);
		$consulta->bindParam(':sesion', $sesion);
	    $consulta->execute();
	}
}