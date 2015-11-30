
<?php 

// ------------------------------------------------------------------------------------------------
// SLIM

// [http://goo.gl/7KR4vx] Documentación oficial 
// [http://goo.gl/E2hriJ] Uso avanzado de Slim 
// [http://goo.gl/KMglou] Añadir Middleware a determinada ruta (o cómo comprobar está logado)
// [http://goo.gl/n2Q2Zk] Métodos (get, post, delete, ...) válidos en el enrutamiento
// [http://goo.gl/DYkgGd] Cómo mostrar flash() y error() en la Vista
// [http://goo.gl/UzoaCi] Slim MVC framework

// VISTA

// [http://goo.gl/hU1AVD] BootStrap
// [http://goo.gl/ikw3Cv] Herencia en las Vistas con Twing

// VARIOS

// [http://goo.gl/wxnSy]  PDO
// [http://goo.gl/pAsYSf] swiftmailer/swiftmailer con composer y "composer update"
// [http://goo.gl/Ld7VXw] Servidor NGINX

// GESTION DE USUARIOS

// [http://goo.gl/8GIxET] Gestión de sesión de usuario con Slim
// [http://goo.gl/sSJYcd] Control clásico de sesión de usuario en PHP
// [http://goo.gl/meF6p8] Autenticación y XSS con SlimExtra
// [http://goo.gl/PylJvT] Basic HTTP Authentication
// [http://goo.gl/ZZSBk8] PROBLEMA con usuario/clave sin SSL
// [http://goo.gl/9Wa71B] Librería uLogin para autenticación PHP

// [http://goo.gl/sShWmQ] Proteger API con oAuth2.0 (incluye ejemplo)
// [http://goo.gl/uhVAf]  Estudio sobre cómo proteger API sin oAuth
// [http://goo.gl/53iEcN] oAuth con Slim
// [http://goo.gl/PXt2YG] Otra implementación de oAuth
// ------------------------------------------------------------------------------------------------

// DUDA funcionará flash() y error() tras poner session_start() antes de header()

session_cache_limiter(false);	
session_start();
header('Content-type: text/html; charset=utf-8');

require 	 'vendor/autoload.php';

require_once 'controller/Dictado.php';
require_once 'controller/Email.php';
require_once 'controller/Login.php';
require_once 'controller/Utils.php';

Twig_Autoloader::register();  

$app = new \Slim\Slim(
		array(
			//'debug' => true,
			'templates.path' => './view'
		)
	);
	
$loader = new Twig_Loader_Filesystem('./view');  
$twig = new Twig_Environment($loader, array(  
//'cache' => 'cache',  
));  
$twig->addGlobal('login', new Login()); // Para poder consultar si existe sesión de usuario abierta
      
$app->container->singleton('db', function () {
    return new \PDO('sqlite:model/dictados.db');
});
	
$app->get('/', function() use ($app){
    global $twig;
    echo $twig->render('inicio.php');  
}); 

$app->group('/usuario', function() use ($app){
	
	// Acción asociada al formulario de login
	
	$app->post('/autorizar', function() use ($app){
		global $twig;
		$user=$app->request->post('email');
		// IDEA comprobar que el correo es correcto y no nos la intentan "colar" (PHP tiene métodos 'sanitize')
		Email::enviar($user, 'Solicitud de acceso',Email::getMessageAutenticacion($user,Login::generarTokenAutenticacion($app->db,$user)));
		echo $twig->render('usuarioLogin.php', array('email'=>$user));
	});
	
	$app->get('/login','Login::forzarLogin',function() use ($app){
	});
	
	// Cierra la sesión de usuario
	
	$app->get('/logout',function() use ($app){
		global $twig;
		unset($_SESSION['user']);
		session_destroy();
		echo $twig->render('inicio.php');  
	});
	
	// Accion asociada al email de login
	
	$app->get('/autenticar/:token',function($token) use ($app){
		
		$email=$app->request->get('email');
		
		// Si intentan autenticarse (estando ya logados en el sistema) ignoramos el token vílmente
		// (si quieres entrar, estando ya dentro... pues disfruta)
		
		if(Login::isLogged()) $email=Login::getEmail();
		
		if(Login::isLogged() || Login::autenticar($app->db,$email, $token)){
			global $twig;
			echo $twig->render('inicio.php',array('message'=>"Bienvenido/a <b>$email</b>"));  
		}
		else {
			global $twig;
			echo $twig->render('login.php',array('error'=>'El enlace de acceso utilizado ya <strong>no está en vigor</strong>.<br>Indique su dirección de correo electrónico y le enviaremos uno válido.<br>Disculpe las molestias.'));
		}
	});
});
	
$app->group('/dictado', function() use ($app){
	
	// Anota que un usuario ha realizado todos los dictados para poder avisarle cuando se cree uno nuevo

	$app->get('/avisar', 'Login::forzarLogin', function()  use ($app){
		Dictado::avisar($app->db,Login::getEmail());
	}); 
		
	$app->get('/escuchar', function()  use ($app){
		Dictado::mostrar();
	}); 
	
	// IDEA añadir reproductor para escuchar cómo queda el dictado que se está introduciendo
	
	$app->get('/crear', 'Login::forzarLogin', function() use ($app){
		global $twig;
		echo $twig->render('dictadoCrear.php');
	}); 
	
	$app->post('/guardar', 'Login::forzarLogin', function() use ($app){
		global $twig;
		$r=Dictado::guardar($app->db, Login::getEmail(),$app->request()->post('dictado'));
		// TODO notificar por email la disponibilidad de un nuevo dictado a los usuarios que los terminaron todos
		if($r)
			$valores=array('message'=>'Dictado guardado');
		else
			$valores=array('error'=>'Problemas al guardar el dictado');
			
		echo $twig->render('inicio.php',$valores);
	});
	
	// IDEA no usar el ID directamente en la url para evitar nos "frían" a peticiones
	
	$app->get('/aprobado/:id', 'Login::forzarLogin', function($id_dictado)  use ($app){
		Dictado::anotarAprobado($app->db, Login::getEmail(), $id_dictado);
		Dictado::mostrar("¡Buen trabajo!");
	}); 
	
	$app->get('/suspenso', function()  use ($app){
		Dictado::mostrar("No te preocupes, verás como poco a poco mejoras tu ortografía");
	}); 
	
});

// Ponemos en marcha el router
$app->run();

?>

