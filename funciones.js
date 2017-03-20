//BORRADO DE COOKIE
function BorrarCookie(){
	$.ajax({
		url : 'nexo.php',
		method : 'POST',
		data : {'accion' : 'borrarCookie'}
	}).then(
		function(){
			alert("Cookie borrada");
		},
		function(jqXHR, textStatus, errorThrown){
			$("#contenedor").html(jqXHR.responseText + '\n' + textStatus + '\n' + errorThrown);
		}
	);
}
//CARGA DEL ARCHIVO EN EL CONTENEDOR
function Cargar(archivo){
	$('#contenedor').load(archivo + '?' + (new Date()).getTime());//ESTO GENERA UN PATH UNICO
}
//CARGA Y RELLENO DEL FORMULARIO DE MODIFICACION DE MATERIAL
function CargarModificacionMaterial(nombre, precio, tipo){
	$('#contenedor').load('modificacionMaterial.html' + '?' + (new Date()).getTime(),//ESTO GENERA UN PATH UNICO (PREVINIENDO CACHEADO)
		function(){
			$('#nombre').val(nombre);
			$('#precio').val(precio);
			$('#' + tipo).prop('checked', true);
		}
	);
}
//CARGA Y RELLENO DEL FORMULARIO DE MODIFICACION DE USUARIO
function CargarModificacionUsuario(id, email, password, tipo){
	$('#contenedor').load('modificacionUsuario.html' + '?' + (new Date()).getTime(),//ESTO GENERA UN PATH UNICO (PREVINIENDO CACHEADO)
		function(){
			$('#fotoPrevia').attr('src', '/fotosUsuarios/' + id + '.png' + '?' + (new Date()).getTime());
			$('#email').val(email);
			$('#password').val(password);
			$('#' + tipo).prop('checked', true);
		}
	);
}
//RELLENO DEL FORMULARIO LOGIN PARA TESTEO
function CompletarLogIn(quien){
	if(quien == 'comprador'){
		$('#email').val('a@a.com');
		$('#password').val('1234');
	}
	else if(quien == 'vendedor'){
		$('#email').val('b@b.com');
		$('#password').val('1234');
	}
	else if(quien == 'admin'){
		$('#email').val('c@c.com');
		$('#password').val('1234');
	}
}
//COMPLETAMIENTO DE LA TABLA
function CompletarTabla(tabla){
	$.ajax({
		url : 'nexo.php',
		method : 'POST',
		data : {'accion' : tabla}
	}).then(
		function(data){
			//CONVERSION DEL STRING RECIBIDO A OBJETO
			data = JSON.parse(data);
			//VERIFICACION DE INSTRUCCION DE SALIR
			if (data.salir == true) {
				window.location.replace('index.html');
			}
			//CARGA DE LA TABLA
			else{
				$('#cuerpoTabla').html(data.tabla);	
			}
		},
		function(jqXHR, textStatus, errorThrown){
			$("#contenedor").html(jqXHR.responseText + '\n' + textStatus + '\n' + errorThrown);
		}
	);
}
//CREACION DE UN MATERIAL
function CrearMaterial(){
	var nombre = $('#nombre').val();
	var precio = $('#precio').val();
	var tipo = $('input[name=tipo]:checked').val();
    //VERIFICACION DE QUE EL NOMBRE NO ESTE VACIO
	if(nombre == ''){
		$('#mensajeError').html('Debe ingresar el nombre');
		return;
	}
	//VERIFICACION DE QUE EL PRECIO NO ESTE VACIO
	if(precio == ''){
		$('#mensajeError').html('Debe ingresar el precio');
		return;
	}
	//VERIFICACION DE QUE SE HAYA SELECCIONADO UN TIPO
	if(tipo == undefined){
		$('#mensajeError').html('Debe seleccionar el tipo');
		return;
	}
	$.ajax({
		url : 'nexo.php',
		method : 'POST',
		data : {'accion' : 'crearMaterial', 'nombre' : nombre, 'precio' : precio, 'tipo' : tipo}
	}).then(
		function(data){
			if (data == 'resultadoDuplicado') {
				$('#mensajeError').html('El material ya existía previamente en la base de datos');
				return;
			}
			else{
				$('#contenedor').html(data);
			}
		},
		function(jqXHR, textStatus, errorThrown){
			$("#contenedor").html(jqXHR.responseText + '\n' + textStatus + '\n' + errorThrown);
		}
	);
}
//CREACION DE UN USUARIO
function CrearUsuario(){
	var foto = $("#foto").val();
	var email = $('#email').val();
	var password = $('#password').val();
	var tipo = $('input[name=tipo]:checked').val();
	var regularExpressionMail = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    var regularExpressionPassword = /^[0-9]{4}$/;
    //VERIFICACION DE SELECCION DE ARCHIVO
	if (foto === '') {
		$('#mensajeError').html('Debe seleccionar una foto');
		return;
	}
    //VERIFICACION DE QUE EL EMAIL NO ESTE VACIO
	if(email == ''){
		$('#mensajeError').html('Debe ingresar el correo');
		return;
	}
	//VERIFICACION DE QUE EL FORMATO CORRECTO DEL EMAIL INGRESADO
	if(!regularExpressionMail.test(email)){
		$('#mensajeError').html('Formato de correo electrónico incorrecto');
		return;
	}
	//VERIFICACION DE QUE LA CONTRASEÑA NO ESTE VACIA
	if(password == ''){
		$('#mensajeError').html('Debe ingresar la contraseña');
		return;
	}
	//VERIFICACION DE QUE SE INGRESEN SOLO NUMEROS PARA LA CONTRASEÑA
	if(!regularExpressionPassword.test(password)){
		$('#mensajeError').html('Debe ingresar 4 números en la contraseña');
		return;
	}
	//VERIFICACION DE QUE SE HAYA SELECCIONADO UN TIPO
	if(tipo == undefined){
		$('#mensajeError').html('Debe seleccionar el tipo');
		return;
	}
	//CREACION DE OBJETO FORMDATA QUE CONTENDRA LA INFO DEL FORMULARIO
	var formData = new FormData();
	//AGREGADO DE LA FOTO AL FORMADATA
	formData.append('foto', $('#foto')[0].files[0]);
	//AGREGADO DE PARES CLAVE/VALOR AL FORMDATA
	formData.append('accion', 'crearUsuario');
	formData.append('email', email);
	formData.append('password', password);
	formData.append('tipo', tipo);

	$.ajax({
		url : 'nexo.php',
		method : 'POST',
		data : formData,
		//ESTAS OPCIONES DEBEN IR PARA QUE FORMDATA FUNCIONE
		contentType: false,
		processData: false
	}).then(
		function(data){
			//CONVERSION DEL STRING RECIBIDO A OBJETO
			data = JSON.parse(data);
			//SI EL MENSAJE ES DISTINTO DE INDEFINIDO, EXISTE Y POR LO TANTO LO MUESTRA
			if (data.mensaje != undefined) {
				$('#mensajeError').html(data.mensaje);
				return;
			}
			//SINO CARGA EL HTML
			else{
				$('#contenedor').html(data.html);
			}
		},
		function(jqXHR, textStatus, errorThrown){
			$("#contenedor").html(jqXHR.responseText + '\n' + textStatus + '\n' + errorThrown);
		}
	);
}
//ELIMINACION DE UN MATERIAL
function EliminarMaterial(nombre){
	$.ajax({
		url : 'nexo.php',
		method : 'POST',
		data : {'accion' : 'eliminarMaterial', 'nombre': nombre}
	}).then(
		function(data){
			$('#contenedor').html(data);
		},
		function(jqXHR, textStatus, errorThrown){
			$("#contenedor").html(jqXHR.responseText + '\n' + textStatus + '\n' + errorThrown);
		}
	);
}
//ELIMINACION DE UN USUARIO
function EliminarUsuario(email, password){
	$.ajax({
		url : 'nexo.php',
		method : 'POST',
		data : {'accion' : 'eliminarUsuario', 'email' : email, 'password' : password}
	}).then(
		function(data){
			$('#contenedor').html(data);
		},
		function(jqXHR, textStatus, errorThrown){
			$("#contenedor").html(jqXHR.responseText + '\n' + textStatus + '\n' + errorThrown);
		}
	);
}
//LOGUEO CON VERIFICACIONES
function Loguear()
{
	var email = $('#email').val();
	var password = $('#password').val();
	var recordarme = $('#recordarme').prop('checked');
	var regularExpressionMail = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    var regularExpressionPassword = /^[0-9]{4}$/;
    //VERIFICACION DE QUE EL EMAIL NO ESTE VACIO
	if(email == ''){
		$('#mensajeError').html('Debe ingresar el correo');
		return;
	}
	//VERIFICACION DE QUE EL FORMATO CORRECTO DEL EMAIL INGRESADO
	if(!regularExpressionMail.test(email)){
		$('#mensajeError').html('Formato de correo electrónico incorrecto');
		return;
	}
	//VERIFICACION DE QUE LA CONTRASEÑA NO ESTE VACIA
	if(password == ''){
		$('#mensajeError').html('Debe ingresar la contraseña');
		return;
	}
	//VERIFICACION DE QUE SE INGRESEN SOLO NUMEROS PARA LA CONTRASEÑA
	if(!regularExpressionPassword.test(password)){
		$('#mensajeError').html('Debe ingresar 4 números en la contraseña');
		return;
	}
	//CARGA DE PAGINA CORRESPONDIENTE AL TIPO DE USUARIO
	$.ajax({
		url : 'slim.php/loguear',
		method : 'POST',
		data : {'emailIngresado' : email, 'passwordIngresado' : password, 'recordar' : recordarme}
	}).then(
		function(data){
			console.log(data);
			//CONVERSION DEL STRING RECIBIDO A OBJETO
			data = JSON.parse(data);
			if (data.mensaje != undefined) {
				$('#mensajeError').html(data.mensaje);
				return;
			}
			else{
				//CARGA DEL HEADER EN TODAS LAS PAGINAS QUE NO SEAN LOGIN
				$('#header').html(
					'<img class="fotoPreviaMini" src="/fotosUsuarios/' + data.idUsuario + '.png?' +(new Date()).getTime() +/*ESTO GENERA UN PATH UNICO (PREVINIENDO CACHEADO)*/ '" /> ' +
					email +
					' <button type="button" onclick="BorrarCookie();">Borrar cookie</button> ' +
					'<button type="button" id="botonSalir" onclick="Salir();">Salir</button><br><br>'
				);
				//CARGA DE PAGINA CORRESPONDIENTE AL TIPO DE USUARIO
				$('#contenedor').html(data.html);
			}
		},
		function(jqXHR, textStatus, errorThrown){
			$("#contenedor").html(jqXHR.responseText + '\n' + textStatus + '\n' + errorThrown);
		}
	);
}
//MODIFICACION DE UN MATERIAL
function ModificarMaterial(){
	var nombre = $('#nombre').val();
	var precio = $('#precio').val();
	var tipo = $('input[name=tipo]:checked').val();
	//VERIFICACION DE QUE EL PRECIO NO ESTE VACIO
	if(precio == ''){
		$('#mensajeError').html('Debe ingresar el precio');
		return;
	}
	
	$.ajax({
		url : 'nexo.php',
		method : 'POST',
		data : {'accion' : 'modificarMaterial', 'nombre' : nombre, 'precio' : precio, 'tipo' : tipo}
	}).then(
		function(data){
			$('#contenedor').html(data);
		},
		function(jqXHR, textStatus, errorThrown){
			$("#contenedor").html(jqXHR.responseText + '\n' + textStatus + '\n' + errorThrown);
		}
	);
}
//MODIFICACION DE UN USUARIO
function ModificarUsuario(){
	var foto = $('#foto').val();
	var email = $('#email').val();
	var password = $('#password').val();
	var tipo = $('input[name=tipo]:checked').val();
	var regularExpressionMail = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    var regularExpressionPassword = /^[0-9]{4}$/;
	//VERIFICACION DE QUE LA CONTRASEÑA NO ESTE VACIA
	if(password == ''){
		$('#mensajeError').html('Debe ingresar la contraseña');
		return;
	}
	//VERIFICACION DE QUE SE INGRESEN SOLO NUMEROS PARA LA CONTRASEÑA
	if(!regularExpressionPassword.test(password)){
		$('#mensajeError').html('Debe ingresar 4 números en la contraseña');
		return;
	}
	//CREACION DE OBJETO FORMDATA QUE CONTENDRA LA INFO DEL FORMULARIO
	var formData = new FormData();
	//VERIFICACION DE SELECCION DE ARCHIVO PARA AGREGARLO AL FORMDATA
	if (foto != '') {
		formData.append('foto', $('#foto')[0].files[0]);
	}
	//AGREGADO DE PARES CLAVE/VALOR AL FORMDATA
	formData.append('accion', 'modificarUsuario');
	formData.append('email', email);
	formData.append('password', password);
	formData.append('tipo', tipo);

	$.ajax({
		url : 'nexo.php',
		method : 'POST',
		data : formData,
		//ESTAS OPCIONES DEBEN IR PARA QUE FORMDATA FUNCIONE
		contentType: false,
		processData: false
	}).then(
		function(data){
			//CONVERSION DEL STRING RECIBIDO A OBJETO
			data = JSON.parse(data);
			//SI EL MENSAJE ES DISTINTO DE INDEFINIDO, EXISTE Y POR LO TANTO LO MUESTRA
			if (data.mensaje != undefined) {
				$('#mensajeError').html(data.mensaje);
				return;
			}
			//SINO CARGA EL HTML
			else{
				$('#contenedor').html(data.html);
			}
		},
		function(jqXHR, textStatus, errorThrown){
			$("#contenedor").html(jqXHR.responseText + '\n' + textStatus + '\n' + errorThrown);
		}
	);
}
//OBTENCION DE COOKIE PARA COMPLETAR EL EMAIL DEL LOGIN
function ObtenerCookie(){
	$.ajax({
		url : 'nexo.php',
		method : 'POST',
		data : {'accion' : 'obtenerCookie'}
	}).then(
		function(data){
			if (data != 'desconocido') {
				$('#email').val(data);
			}
		},
		function(jqXHR, textStatus, errorThrown){
			$("#contenedor").html(jqXHR.responseText + '\n' + textStatus + '\n' + errorThrown);
		}
	);
}
//SIMULACION DE SALIDA MEDIANTE REFRESH DE LA PAGINA
function Salir(email, password){
	$.ajax({
		url : 'nexo.php',
		method : 'POST',
		data : {'accion' : 'salir', 'email' : email, 'password' : password}
	}).then(
		function(data){
			$('#header').html('');
			$('#contenedor').html(data);
		},
		function(jqXHR, textStatus, errorThrown){
			$("#contenedor").html(jqXHR.responseText + '\n' + textStatus + '\n' + errorThrown);
		}
	);
}
//PREVISUALIZACION DE FOTO
function PrevisualizarFoto(){
	//VERIFICACION DE VALIDACION
	if(!ValidarFoto()){
		$("#fotoPrevia").attr('src', null);
		return;
	}
	//1)CREACION DEL OBJETO QUE LEE EL ARCHIVO
	var miLector = new FileReader();
	//3)SETEO DE LA FUNCION QUE SE EJECUTARA AL FINALIZAR LA LECTURA
	miLector.onload = function() {
		$("#fotoPrevia").attr('src', miLector.result);
	}
	//2)LECTURA DEL ARCHIVO Y ALMACENAMIENTO COMO URL EN LA PROPIEDAD "RESULT"
	miLector.readAsDataURL($('#foto')[0].files[0]);
}
//VALIDACION DE FOTO PREVISUALIZADA EN EXTENSION Y TAMAÑO
function ValidarFoto(){
	//OBTENCION DE LA FOTO SELECCIONADA
	var archivo = $('#foto')[0].files[0];
	//EXPRESION REGULAR QUE EVALUA LA PRESENCIA DE CUALQUIERA DE LOS FORMATOS ACEPTADOS
	var re = /(\.jpg|\.jpeg|\.png|\.bmp|\.gif)$/i;
	//VERIFICACION DEL TIPO DE ARCHIVO
	if(!re.exec(archivo.name))
	{
		$("#mensajeError").html("Cambie la imagen, sólo se permiten imágenes con extensión .jpg .jpeg .bmp .gif o .png");
		return false;
	}
	//VERIFICACION DEL TAMAÑO DEL ARCHIVO
	if(archivo.size > (9 /*1MB*/ * 1024 * 1024)) {//La propiedad size devuelve el tamaño en bytes. Multiplicacion de los mb deseados por 1024 para convertir a bytes
		$("#mensajeError").html("Cambie la imagen, solo se permiten tamaños imagenes de tamaño inferior a 1 MB");
		return false;
	}
	$("#mensajeError").html("");
	return true;
}