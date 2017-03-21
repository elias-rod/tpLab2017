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

//CREACION DE UN USUARIO
$app->post('/crearUsuario', function (Request $request, Response $response) {
	//DECODIFICACION DE DATOS DE FORMULARIO Y ALMACENAMIENTO EN ARRAY ASOCIATIVO
	$datosForm = $request->getParsedBody();
	//DECLARACION DEL ARRAY RESPUESTA (VACIO)
	$respuesta = [];
	//VALIDACION DEL TAMAÑO DE LA IMAGEN
	if ($_FILES['foto']['size'] > (1 /*1MB*/ * 1024 * 1024)) {
		$respuesta['mensaje'] = 'Cambie la imagen, solo se permiten tamaños imagenes de tamaño inferior a 1 MB';
	}
	//VALIDACION DE TIPO DE IMAGEN MEDIANTE EL INTENTO DE PROCESARLA COMO IMAGEN, SI IMAGENINICIAL ES FALSE, FALLO LA VALIDACION
	else if(!($imagenInicial = imagecreatefromstring(file_get_contents($_FILES['foto']['tmp_name'])))) {
		$respuesta['mensaje'] = 'Cambie la imagen, sólo se permiten imágenes con extensión .jpg .jpeg .bmp .gif o .png';
	}
	//CREACION DE USUARIO CON FOTO
	else if(Usuario::CrearUsuario(new Usuario($datosForm['email'], $datosForm['password'], $datosForm['tipo']))){
		//OBTENCION DEL ID DEL USUARIO CREADO
		$idUsuario = Usuario::ObtenerUsuario($datosForm['email'], $datosForm['password'])['id'];
		//OBTENCION DE LAS DIMENSIONES DE LA IMAGEN INICIAL
		$imagenInicialAncho = imagesx($imagenInicial);
		$imagenInicialAlto = imagesy($imagenInicial);
		//CREACION DE UNA IMAGEN VACIA CON LAS DIMENSIONES DE LA IMAGEN INCIAL
		$imagenFinal = imagecreatetruecolor($imagenInicialAncho, $imagenInicialAlto);
		//COPIA DE LA IMAGEN INCIAL EN LA FINAL
		imagecopy($imagenFinal, $imagenInicial, 0, 0, 0, 0, $imagenInicialAncho, $imagenInicialAlto);
		//LIBERACION DE LA MEMORIA DE LA IMAGEN INICIAL
		imagedestroy($imagenInicial);
		//GUARDADO DEFINITIVO DE LA IMAGEN EN EL SERVIDOR CONVIRTIENDOLA EN FORMATO PNG
		imagepng($imagenFinal, 'fotosUsuarios/' . $idUsuario . '.png');
		//LIBERACION DE LA MEMORIA DE LA IMAGEN FINAL
		imagedestroy($imagenFinal);
		//ALMACENAMIENTO DE LA PAGINA PRINCIPAL DEL ADMIN EN LA RESPUESTA HTML
		$respuesta['html'] = file_get_contents('principalAdmin.html');
	}
	//RESPUESTA POR USUARIO DUPLICADO
	else{
		$respuesta['mensaje'] = 'La persona ya existía previamente en la base de datos';
	}
	//CODIFICACION DE LA RESPUESTA
	return $response->withJson($respuesta);
});
//GENERACION DEL HTML PARA LA TABLA PRINCIPAL DEL ADMIN
$app->get('/tablaPrincipalAdmin', function (Request $request, Response $response) {
	//GENERACION DE ARRAY DE USUARIOS
	$contenido = Usuario::ObtenerTodosUsuarios();
	//CREACION DE ARRAY VACIO QUE CONTENDRA LA TABLA
	$respuesta['tabla'] = '';

	for ($fila=0; $fila < count($contenido); $fila++) {
		$respuesta['tabla'] .= 
		'<tr>
			<td><img class="fotoPrevia" src="/fotosUsuarios/' . $contenido[$fila]['id'] . '.png?' . microtime()/*ESTO GENERA UN PATH UNICO (PREVINIENDO CACHEADO)*/ . '" /></td>
			<td>' . $contenido[$fila]['email'] . '</td>
			<td>' . $contenido[$fila]['password'] . '</td>
			<td>' . $contenido[$fila]['tipo'] . '</td>
			<td><button type="button" onclick="EliminarUsuario(\'' . $contenido[$fila]['email'] . '\', \'' . $contenido[$fila]['password'] . '\')">Eliminar</button></td>
			<td><button type="button" onclick="CargarModificacionUsuario(\'' . $contenido[$fila]['id'] . '\', \'' . $contenido[$fila]['email'] . '\', \'' . $contenido[$fila]['password'] . '\', \'' . $contenido[$fila]['tipo'] . '\')">Modificar</button></td>
		</tr>';
	}
	return $response->withJson($respuesta);
});
//LOGUEO SI EXISTE COMBINACION DE EMAIL Y CONTRASEÑA
$app->post('/loguear', function (Request $request, Response $response) {
		//DECODIFICACION DE DATOS DE FORMULARIO Y ALMACENAMIENTO EN ARRAY ASOCIATIVO
		$datosForm = $request->getParsedBody();
		//DECLARACION DEL ARRAY RESPUESTA (VACIO)
		$respuesta = [];
		//BUSQUEDA DE COMBINACION DE EMAIL CON PASSWORD
		$usuario = Usuario::ObtenerUsuario($datosForm['emailIngresado'], $datosForm['passwordIngresado']);
		$tipoUsuario = $usuario['tipo'];
	    //INCLUSION DE LA PAGINA CORRECTA SI SE ENCONTRO ALGUNA COMBINACION
	    if ($tipoUsuario != NULL){
	    	//GENERACION DE LA SESION
			$_SESSION['sesion'] = rand(0, 50000);
			$_SESSION['email'] = $datosForm['emailIngresado'];
			$_SESSION['password'] = $datosForm['passwordIngresado'];
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
		if ($datosForm['recordar'] == 'true') {
			setcookie('email', $datosForm['emailIngresado'], time() + (86400 * 30), "/"); // 86400 = 1 day
		}
		//CODIFICACION DE LA RESPUESTA
		return $response->withJson($respuesta);
});

$app->run();