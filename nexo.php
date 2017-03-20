<?php
//CREA LA SESION
session_start();
//DEMANDA DEL LA LIBRERIA NUSOAP
require_once('lib/nusoap.php');
//CREACION DEL CLIENTE NUSOAP
$cliente = new nusoap_client('http://localhost/webService.php?wsdl');
//CHECKEO DE POSIBLES ERRORES EN LA CONSTRUCCION DEL WEBSERVICE
if ($cliente->getError()) {
	echo '<h2>Error en la construcción del WebService:</h2><pre>' . $error . '</pre>';
	exit();
}
//VERIFICACION DE CORRESPONDENCIA DE LA SESION DEL USUARIO ACTUAL CON LA GUARDADA EN LA BASE DE DATOS
if ($_POST['accion'] != 'obtenerCookie' &&
	$_POST['accion'] != 'loguear' &&
	//LA SESION ACTUAL ES DIFERENTE DE LA GUARDADA EN BASE DE DATOS
	$_SESSION['sesion'] != $cliente->call('ObtenerUsuario', array($_SESSION['email'], $_SESSION['password']))['sesion']) {
		$respuesta['salir'] = true;
		//CODIFICACION DE LA RESPUESTA
		echo json_encode($respuesta);
		exit();
}
//SWITCH PRINCIPAL
switch ($_POST['accion']) {
	//BORRADO DE LA COOKIE MEDIANTE UN VALOR NEGATIVO
	case 'borrarCookie':
		setcookie('email', '', time() - 1, "/");
		break;
	//CREACION DE UN MATERIAL
	case 'crearMaterial':
		if($cliente->call('CrearMaterial', array($_POST['nombre'], $_POST['precio'], $_POST['tipo']))){
			include('principalUsuario.html');
		}
		else{
			echo 'resultadoDuplicado';
		}
		break;
	//CREACION DE UN USUARIO
	case 'crearUsuario':
		//DECLARACION DEL ARRAY RESPUESTA (VACIO)
		$respuesta = array();
		//VALIDACION DEL TAMAÑO DE LA IMAGEN
		if ($_FILES['foto']['size'] > (1 /*1MB*/ * 1024 * 1024)) {
			$respuesta['mensaje'] = 'Cambie la imagen, solo se permiten tamaños imagenes de tamaño inferior a 1 MB';
		}
		//VALIDACION DE TIPO DE IMAGEN MEDIANTE EL INTENTO DE PROCESARLA COMO IMAGEN, SI IMAGENINICIAL ES FALSE, FALLO LA VALIDACION
		else if(!($imagenInicial = imagecreatefromstring(file_get_contents($_FILES['foto']['tmp_name'])))) {
			$respuesta['mensaje'] = 'Cambie la imagen, sólo se permiten imágenes con extensión .jpg .jpeg .bmp .gif o .png';
		}
		//CREACION DE USUARIO CON FOTO
		else if($cliente->call('CrearUsuario', array($_POST['email'], $_POST['password'], $_POST['tipo']))){
			//OBTENCION DEL ID DEL USUARIO CREADO
			$idUsuario = $cliente->call('ObtenerUsuario', array($_POST['email'], $_POST['password']))['id'];
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
		echo json_encode($respuesta);
		break;
	//ELIMINACION DE UN USUARIO DE LA BASE DE DATOS
	case 'eliminarMaterial':
		$cliente->call('EliminarMaterial', array($_POST['nombre']));
		include('principalUsuario.html');
		break;
	//ELIMINACION DE UN USUARIO DE LA BASE DE DATOS
	case 'eliminarUsuario':
		//OBTENCION DEL ID DEL USUARIO CREADO
		$idUsuario = $cliente->call('ObtenerUsuario', array($_POST['email'], $_POST['password']))['id'];
		//ELIMINACION DE LA BASE DE DATOS
		$cliente->call('EliminarUsuario', array($_POST['email']));
		//ELIMINACION DE LA FOTO DEL USUARIO
		unlink('fotosUsuarios/' . $idUsuario . '.png');
		//INCLUSION DE LA PAGINA PRINCIPALADMIN
		include('principalAdmin.html');
		break;
	//LOGUEO SI EXISTE COMBINACION DE EMAIL Y CONTRASEÑA
	case 'loguear':
		//DECLARACION DEL ARRAY RESPUESTA (VACIO)
		$respuesta = array();
		//BUSQUEDA DE COMBINACION DE EMAIL CON PASSWORD Y OBTENCION DEL TIPO
		$tipoUsuario = $cliente->call('ObtenerUsuario', array($_POST['emailIngresado'], $_POST['passwordIngresado']))['tipo'];
	    //INCLUSION DE LA PAGINA CORRECTA SI SE ENCONTRO ALGUNA COMBINACION
	    if ($tipoUsuario != NULL){
	    	//GENERACION DE LA SESION
			$_SESSION['sesion'] = rand(0, 50000);
			$_SESSION['email'] = $_POST['emailIngresado'];
			$_SESSION['password'] = $_POST['passwordIngresado'];
			//UPDATEO DE LA SESION EN LA BASE DE DATOS
			$cliente->call('UpdateSesionUsuario', array($_POST['emailIngresado'], $_POST['passwordIngresado'], $_SESSION['sesion']));
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
	    		//ALMACENAMIENTO DE LA PAGINA PRINCIPAL DEL USUARIO EN LA RESPUESTA HTML
				$respuesta['html'] = file_get_contents('principalAdmin.html');
	    	}
			//OBTENCION DEL ID DEL USUARIO CREADO
			$respuesta['idUsuario'] = $cliente->call('ObtenerUsuario', array($_POST['emailIngresado'], $_POST['passwordIngresado']))['id'];
	    }
	    else{
	    	//LA COMBINACION DE EMAIL Y CONTRASEÑA NO SE ENCUENTRA EN LA BASE DE DATOS
	    	$respuesta['mensaje'] = 'La combinación de usuario/contraseña no se encuentra en la base de datos.';
	    }
	    //CREACION DE COOKIE
		if ($_POST['recordar'] == 'true') {
			setcookie('email', $_POST['emailIngresado'], time() + (86400 * 30), "/"); // 86400 = 1 day
		}
		//CODIFICACION DE LA RESPUESTA
		echo json_encode($respuesta);
		break;
	//MODIFICACION DE UN MATERIAL
	case 'modificarMaterial':
	    $cliente->call('ModificarMaterial', array($_POST['nombre'], $_POST['precio'], $_POST['tipo']));
		include('principalUsuario.html');
		break;
	//MODIFICACION DE UN USUARIO
	case 'modificarUsuario':
	    $cliente->call('ModificarUsuario', array($_POST['email'], $_POST['password'], $_POST['tipo']));
	    //VERIFICACION DE QUE SE HAYA SUBIDO UNA NUEVA FOTO (ES OPCIONAL CAMBIAR LA FOTO)
	    if (isset($_FILES['foto'])) {
	    	//DECLARACION DEL ARRAY RESPUESTA (VACIO)
			$respuesta = array();
	    	//OBTENCION DEL ID DEL USUARIO CREADO
			$idUsuario = $cliente->call('ObtenerUsuario', array($_POST['email'], $_POST['password']))['id'];
			//ELIMINACION DE LA FOTO DEL USUARIO
			unlink('fotosUsuarios/' . $idUsuario . '.png');
			//VALIDACION DEL TAMAÑO DE LA IMAGEN
			if ($_FILES['foto']['size'] > (1 /*1MB*/ * 1024 * 1024)) {
				$respuesta['mensaje'] = 'Cambie la imagen, solo se permiten tamaños imagenes de tamaño inferior a 1 MB';
			}
			//VALIDACION DE TIPO DE IMAGEN MEDIANTE EL INTENTO DE PROCESARLA COMO IMAGEN, SI IMAGENINICIAL ES FALSE, FALLO LA VALIDACION
			else if(!($imagenInicial = imagecreatefromstring(file_get_contents($_FILES['foto']['tmp_name'])))) {
				$respuesta['mensaje'] = 'Cambie la imagen, sólo se permiten imágenes con extensión .jpg .jpeg .bmp .gif o .png';
			}
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
	    }
		//ALMACENAMIENTO DE LA PAGINA PRINCIPAL DEL ADMIN EN LA RESPUESTA HTML
		$respuesta['html'] = file_get_contents('principalAdmin.html');
		//CODIFICACION DE LA RESPUESTA
		echo json_encode($respuesta);
		break;
	//DEVOLUCION DE COOKIE
	case 'obtenerCookie':
		//DEVOLUCION DE COOKIE SI EXISTE
		if (isset($_COOKIE['email'])){
			echo $_COOKIE['email'];
		}
		else{
			echo 'desconocido';
		}
		break;
	//DESTRUCCION DE LA SESION
	case 'salir':
		$cliente->call('UpdateSesionUsuario', array($_SESSION['email'], $_SESSION['password'], '0'));
		session_unset();
		session_destroy();
		include('login.html');
		break;
	//GENERACION DEL HTML PARA LA TABLA PRINCIPAL DEL ADMIN
	case 'tablaPrincipalAdmin':
		$contenido = $cliente->call('ObtenerTodosUsuarios');
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
		echo json_encode($respuesta);
		break;
	//GENERACION DEL HTML PARA LA TABLA PRINCIPAL DEL USUARIO
	case 'tablaPrincipalUsuario':
		$contenido = $cliente->call('ObtenerTodosMateriales');
		$respuesta['tabla'] = '';
		for ($fila=0; $fila < count($contenido); $fila++) { 
			$respuesta['tabla'] .= 
			'<tr>
				<td>' . $contenido[$fila]['nombre'] . '</td>
				<td>' . $contenido[$fila]['precio'] . '</td>
				<td>' . $contenido[$fila]['tipo'] . '</td>
				<td><button type="button" onclick="EliminarMaterial(\'' . $contenido[$fila]['nombre'] . '\')">Eliminar</button></td>
				<td><button type="button" onclick="CargarModificacionMaterial(\'' . $contenido[$fila]['nombre'] . '\', \'' . $contenido[$fila]['precio'] . '\', \'' . $contenido[$fila]['tipo'] . '\')">Modificar</button></td>
			</tr>';
		}
		echo json_encode($respuesta);
		break;
	//NO SE DEBERÍA LLEGAR AQUI
	default:
		alert('Error en switch: ningún case aplica');
		break;
}