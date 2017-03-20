<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';

require_once('usuario.php');
require_once('material.php');

//ORIGINAL
//$app = new \Slim\App;
//CONFIGURACION PARA QUE SLIM DETALLE LOS ERRORES
$app = new \Slim\App(['settings' => ['displayErrorDetails' => true]]);

//METODOS
$app->get('/hello/{name}', function (Request $request, Response $response) {
    $name = $request->getAttribute('name');
    $response->getBody()->write("Hello, $name");

    return $response;
});

$app->post('/loguear', function (Request $request, Response $response) {
		//DECODIFICACION DE DATOS DE FORMULARIO Y ALMACENAMIENTO EN ARRAY ASOCIATIVO
		$datosLogin = $request->getParsedBody();
		//DECLARACION DEL ARRAY RESPUESTA (VACIO)
		$respuesta = [];
		//BUSQUEDA DE COMBINACION DE EMAIL CON PASSWORD
		$usuario = Usuario::ObtenerUsuario($datosLogin['emailIngresado'], $datosLogin['passwordIngresado']);
		$tipoUsuario = $usuario['tipo'];
	    //INCLUSION DE LA PAGINA CORRECTA SI SE ENCONTRO ALGUNA COMBINACION
	    if ($tipoUsuario != NULL){
	    	//GENERACION DE LA SESION
			$_SESSION['sesion'] = rand(0, 50000);
			$_SESSION['email'] = $datosLogin['emailIngresado'];
			$_SESSION['password'] = $datosLogin['passwordIngresado'];
			//UPDATEO DE LA SESION EN LA BASE DE DATOS
			Usuario::UpdateSesionUsuario($_SESSION['email'], $_SESSION['password'], $_SESSION['sesion']);
		    //INCLUSION DE LA PAGINA CORRECTA SEGUN EL TIPO DE USUARIO
	    	if ($tipoUsuario == 'comprador') {
	    		//ALMACENAMIENTO DE LA PAGINA PRINCIPAL DEL USUARIO EN LA RESPUESTA HTML
				$respuesta['html'] = file_get_contents('principalUsuario.html');
	    	}
	    	else if($tipoUsuario == 'vendedor'){
	    		//ALMACENAMIENTO DE LA PAGINA PRINCIPAL DEL USUARIO EN LA RESPUESTA HTML
				$respuesta['html'] = file_get_contents('principalUsuario.html');
	    	}
	    	else if($tipoUsuario == 'admin'){
	    		//ALMACENAMIENTO DE LA PAGINA PRINCIPAL DEL ADMIN EN LA RESPUESTA HTML
				$respuesta['html'] = file_get_contents('principalAdmin.html');
	    	}
			//OBTENCION DEL ID DEL USUARIO CREADO
			$respuesta['idUsuario'] = $usuario['id'];
	    }
	    else{
	    	//LA COMBINACION DE EMAIL Y CONTRASEÑA NO SE ENCUENTRA EN LA BASE DE DATOS
	    	$respuesta['mensaje'] = 'La combinación de usuario/contraseña no se encuentra en la base de datos.';
	    }
	    //CREACION DE COOKIE
		if ($datosLogin['recordar'] == 'true') {
			setcookie('email', $datosLogin['emailIngresado'], time() + (86400 * 30), "/"); // 86400 = 1 day
		}
		//CODIFICACION DE LA RESPUESTA
		echo json_encode($respuesta);
});

$app->run();