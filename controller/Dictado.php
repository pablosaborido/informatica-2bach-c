<?php

// Documentación Swift_Message [http://goo.gl/Z12Bo]
	
class Dictado {

	// Anota que un usuario ha realizado todos los dictados para poder avisarle cuando se cree uno nuevo
	
	public static function avisar($pdo, $email){
		
		global $twig;
		
		// Comprobamos que realmente ha hecho todos los dictados
		
		if(!is_null(Dictado::escuchar($pdo,$email))) {
			$valores['error']="Todavía le quedan dictados por realizar.<br>Agradeceríamos que no siguiese 'jugando' con la aplicación ;)";
			Email::enviar(Email::ADMIN_EMAIL,"Posible ataque 'hacker'",Email::getMessageActividadSospechosa($email,"Dictado/avisar"));
		}
		else{
			Dictado::anotarAviso($pdo, $email);
			$valores['message']="Hemos anotado su solicitud.<br>Le avisaremos tan pronto tengamos un dictado nuevo.<br>¿Se animaría a <a href='/dictado/crear'>crear un dictado</a>?";
		}
		
		echo $twig->render('inicio.php',$valores);
		
	}
	
	// Anota en la BD que el usuario ha indicado que ha tenido pocas faltas en el dictado
	// con objeto de no volver a ofrecerle en un futuro el mismo dictado
	
	public static function anotarAviso($pdo, $email){
		
		$sql = "INSERT INTO aburridos (email) VALUES (:email)";
		$q = $pdo->prepare($sql);
		return $q->execute(array(':email'=>strtoupper($email)));
	}
	
	// Obtiene el siguiente dictado que realizará el alumno aleatoriamente
	
    public static function escuchar($pdo, $email) {
		
		$r = $pdo->query("SELECT * FROM DICTADO WHERE ID NOT IN (SELECT DICTADO FROM AUDICION WHERE EMAIL='".$email."') ORDER BY random() LIMIT 1;")->fetch(PDO::FETCH_ASSOC);
		
		if($r){
			$l = explode("\n", $r['LINEAS']);
			$r['LINEAS']=$l;
		}
		else
			$r=null;
			
		return $r; 
	}
	
	// Anota en la BD que el usuario ha indicado que ha tenido pocas faltas en el dictado
	// con objeto de no volver a ofrecerle en un futuro el mismo dictado
	
	public static function anotarAprobado($pdo, $email, $id_dictado){
		
		$sql = "INSERT INTO audicion (email, dictado) VALUES (:email,:dictado)";
		$q = $pdo->prepare($sql);
		return $q->execute(array(':email'=>strtoupper($email),
								 ':dictado'=>$id_dictado));
	}

	// IDEA si falla el guardado, enviar email al administrador con los datos para que compruebe qué ha pasado
	
	public static function guardar($pdo,$email,$texto) {
		$sql = "INSERT INTO dictado (autor,lineas) VALUES (:email,:lineas)";
		$q = $pdo->prepare($sql);
		return $q->execute(array(':email'=>strtoupper($email),
								 ':lineas'=>$texto));
	}
	
	// Prepara la vista encargada de dictar el dictado
	
	public static function mostrar($mensaje='') {

		// IDEA obtener la instancia de Slim (como se hace aquí) en todos los métodos que la requieran para evitar tener que pasar la referencia en todas las llamadas
		
		$app = \Slim\Slim::getInstance();
		global $twig;
		
		$r=Dictado::escuchar($app->db,Login::getEmail());
		
		if(is_null($r)){
			
			$valores['message']="¡<b>Enhorabuena</b>! <br>A día de hoy ha realizado correctamente todos nuestros dictados. <br> ¿Se animaría a <a href='/dictado/crear'>crear uno nuevo</a>?";
			
			// Avisamos al administrador que alguien ha conseguido realizar todos los dictados para que cree alguno nuevo
			
			Email::enviar(Email::ADMIN_EMAIL,'Dictados agotados',Email::getMessageDictadosTerminados(Login::getEmail()));
		}
		else{
			$valores=array(
				'dictado'=>json_encode($r),
				'id_dictado'=>$r['ID']
			);
			
			// IDEA Sería interesante que este mensaje desapareciese al rato de aparecer en pantalla
			
			if($mensaje!='')
				$valores['message']=$mensaje;

			if(!Login::isLogged())
				$valores['consejo']="Para evitar dictados repetidos, <a href='/usuario/login'><strong>identifícate</strong></a>";
		}
		
		echo $twig->render('dictadoEscuchar.php',$valores);
	}
	
}

?>
