<?php
require_once('lib/nusoap.php');
require_once('usuario.php');
require_once('material.php');
//CREACION DEL SERVIDOR NUSOAP
$server = new nusoap_server();

$server->configureWSDL('WebServer', 'urn:WS');
/**********************************USUARIOS***************************************/
/**********REGISTROS**********/
//REGISTRO DE LA FUNCION CREAR UN USUARIO
$server->register('CrearUsuario',
	array('email' => 'xsd:string',
		'password' => 'xsd:string',
		'tipo' => 'xsd:string'
		),
	array('return' => 'xsd:boolean'),
	'urn:WS',
	'urn:WS#CrearUsuario',
	'rpc',
	'encoded',
	'Crea un usuario'
	);
//REGISTRO DE LA FUNCION ELIMINAR UN USUARIO
$server->register('EliminarUsuario',
	array('email' => 'xsd:string'),
	array(),
	'urn:WS',
	'urn:WS#EliminarUsuario',
	'rpc',
	'encoded',
	'Elimina un usuario'
	);
//REGISTRO DE LA FUNCION MODIFICAR UN USUARIO
$server->register('ModificarUsuario',
	array('email' => 'xsd:string',
		'password' => 'xsd:string',
		'tipo' => 'xsd:string'
		),
	array(),
	'urn:WS',
	'urn:WS#ModificarUsuario',
	'rpc',
	'encoded',
	'Modifica un usuario'
	);
//REGISTRO DE LA FUNCION OBTENER TODOS LOS USUARIOS
$server->register('ObtenerTodosUsuarios',
	array(),
	array('return' => 'xsd:Array'),
	'urn:WS',
	'urn:WS#ObtenerTodosUsuarios',
	'rpc',
	'encoded',
	'Obtiene todos los usuarios'
	);
//REGISTRO DE LA FUNCION OBTENER USUARIO
$server->register('ObtenerUsuario',
	array('email' => 'xsd:string',
		'password' => 'xsd:string'),
	array('return' => 'xsd:Array'),
	'urn:WS',
	'urn:WS#ObtenerUsuario',
	'rpc',
	'encoded',
	'Obtiene un usuario'
	);
//REGISTRO DE LA FUNCION UPDATE SESION DE USUARIO
$server->register('UpdateSesionUsuario',
	array('email' => 'xsd:string',
		'password' => 'xsd:string',
		'sesion' => 'xsd:string'
		),
	array(),
	'urn:WS',
	'urn:WS#UpdateSesionUsuario',
	'rpc',
	'encoded',
	'Updatea la sesion del usuario'
	);
/**********FUNCIONES**********/
//CREACION DE UN USUARIO
function CrearUsuario($email, $password, $tipo)
{
	$usuario = new Usuario($email, $password, $tipo);
	return Usuario::CrearUsuario($usuario);
}
//ELIMINACION DE UN USUARIO
function EliminarUsuario($email)
{
	Usuario::EliminarUsuario($email);
}
//MODIFICACION DE UN USUARIO
function ModificarUsuario($email, $password, $tipo)
{
	$usuario = new Usuario($email, $password, $tipo);
	Usuario::ModificarUsuario($usuario);
}
//OBTENCION DE TODOS LOS USUARIOS
function ObtenerTodosUsuarios(){
	return Usuario::ObtenerTodosUsuarios();
}
//OBTENCION DE UN USUARIO
function ObtenerUsuario($email, $password){
	return Usuario::ObtenerUsuario($email, $password);
}
//UPDATEO DE SESION DE UN USUARIO
function UpdateSesionUsuario($email, $password, $sesion){
	Usuario::UpdateSesionUsuario($email, $password, $sesion);
}
/**********************************MATERIALES***************************************/
/**********REGISTROS**********/
//REGISTRO DE LA FUNCION CREAR UN MATERIAL
$server->register('CrearMaterial',
	array('nombre' => 'xsd:string',
		'precio' => 'xsd:string',
		'tipo' => 'xsd:string'
		),
	array('return' => 'xsd:boolean'),
	'urn:WS',
	'urn:WS#CrearUsuario',
	'rpc',
	'encoded',
	'Crea un material'
	);
//REGISTRO DE LA FUNCION ELIMINAR UN MATERIAL
$server->register('EliminarMaterial',
	array('nombre' => 'xsd:string'),
	array(),
	'urn:WS',
	'urn:WS#EliminarMaterial',
	'rpc',
	'encoded',
	'Elimina un material'
	);
//REGISTRO DE LA FUNCION MODIFICAR UN MATERIAL
$server->register('ModificarMaterial',
	array('nombre' => 'xsd:string',
		'precio' => 'xsd:string',
		'tipo' => 'xsd:string'
		),
	array(),
	'urn:WS',
	'urn:WS#ModificarMaterial',
	'rpc',
	'encoded',
	'Modifica un material'
	);
//REGISTRO DE LA FUNCION OBTENER TODOS LOS MATERIALES
$server->register('ObtenerTodosMateriales',
	array(),
	array('return' => 'xsd:Array'),
	'urn:WS',
	'urn:WS#ObtenerTodosMateriales',
	'rpc',
	'encoded',
	'Obtiene todos los materiales'
	);
/**********FUNCIONES**********/
//CREACION DE UN USUARIO
function CrearMaterial($nombre, $precio, $tipo)
{
	$material = new Material($nombre, $precio, $tipo);
	return Material::CrearMaterial($material);
}
//ELIMINACION DE UN USUARIO
function EliminarMaterial($nombre)
{
	Material::EliminarMaterial($nombre);
}

//MODIFICACION DE UN USUARIO
function ModificarMaterial($nombre, $precio, $tipo)
{
	$material = new Material($nombre, $precio, $tipo);
	Material::ModificarMaterial($material);
}//OBTENCION DE TODOS LOS USUARIOS
function ObtenerTodosMateriales(){
	return Material::ObtenerTodosMateriales();
}
/*******************************************************************************************/
//COSA QUE DEBE IR PARA QUE FUNCIONE ESTA PORQUERIA DE WEBSERVICE
$HTTP_RAW_POST_DATA = file_get_contents("php://input");
$server->service($HTTP_RAW_POST_DATA);