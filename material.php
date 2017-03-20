<?php
require_once('accesoDatos.php');
/**
* CLASE QUE REALIZA ABM DE MATERIALES CON BASE DE DATOS
*/
class Material
{
	public $nombre;
	public $precio;
	public $tipo;

	function __construct($nombre, $precio, $tipo)
	{
		$this->nombre = $nombre;
		$this->precio = $precio;
		$this->tipo = $tipo;
	}
	//CREACION DE UN MATERIAL EN LA BASE DE DATOS
	public static function CrearMaterial($material){
		//VERIFICACION DE EXISTENCIA DEL MATERIAL
		$sql = 'SELECT * FROM materiales WHERE nombre = :nombre';
        $consulta = AccesoDatos::ObtenerObjetoAccesoDatos()->ObtenerConsulta($sql);
		$consulta->bindParam(':nombre', $material->nombre);
	    $consulta->execute();
		if ($consulta->fetch() != NULL) {
			return false;//EL MATERIAL YA EXISTIA PREVIAMENTE EN LA BASE DE DATOS
		}
		else{
			//CREACION DEL MATERIAL EN LA BASE DE DATOS
			$sql = 'INSERT INTO materiales (nombre, precio, tipo) VALUES (:nombre, :precio, :tipo)';
	        $consulta = AccesoDatos::ObtenerObjetoAccesoDatos()->ObtenerConsulta($sql);
			$consulta->bindParam(':nombre', $material->nombre);
			$consulta->bindParam(':precio', $material->precio);
			$consulta->bindParam(':tipo', $material->tipo);
		    $consulta->execute();
		    return true;//ALTA EXITOSA
		}
	}
	//MODIFICACION DE UN MATERIAL EN LA BASE DE DATOS
	public static function ModificarMaterial($material){
		$sql = 'UPDATE materiales SET precio = :precio, tipo = :tipo WHERE nombre = :nombre';
        $consulta = AccesoDatos::ObtenerObjetoAccesoDatos()->ObtenerConsulta($sql);
		$consulta->bindParam(':nombre', $material->nombre);
		$consulta->bindParam(':precio', $material->precio);
		$consulta->bindParam(':tipo', $material->tipo);
	    $consulta->execute();
	}
	//ELIMINACION DE UN MATERIAL DE LA BASE DE DATOS
	public static function EliminarMaterial($nombre){
		$sql = 'DELETE FROM materiales WHERE nombre = :nombre';
        $consulta = AccesoDatos::ObtenerObjetoAccesoDatos()->ObtenerConsulta($sql);
		$consulta->bindParam(':nombre', $nombre);
	    $consulta->execute();
	}
	//OBTENCION DE TODOS LOS MATERIALES DE LA BASE DE DATOS
	public static function ObtenerTodosMateriales(){
		$sql = 'SELECT nombre, precio, tipo FROM materiales';
        $consulta = AccesoDatos::ObtenerObjetoAccesoDatos()->ObtenerConsulta($sql);
	    $consulta->execute();
	    return $consulta->fetchAll(PDO::FETCH_ASSOC);//OBTENCION COMO UN ARRAY ASOCIATIVO
	}
}