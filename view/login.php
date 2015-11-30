{% extends "layout.php" %}

{% block cuerpo %}

	{% if not login.isLogged() %}
		<div class="alert alert-success" role="alert">
			La opción que ha solicitado requiere que se identifique. <br> 
			Indique su dirección de correo electrónico y le enviaremos un enlace para poder entrar.
		</div>
	{% endif %}
	
	<h1>Login</h1>
				
	{% if error %}
		<div class="alert alert-danger" role="alert"> {{ error|raw}}</div>
	{% endif %}

	{% if message %}
		<div class="alert alert-success" role="alert"> {{ message|raw}}</div>
	{% endif %}
	
	<form method="post" action="/usuario/autorizar">
		Email: <input type="text" name="email" value="ejemplo@dominio.com"><br>
		<input type="submit" value="Solicitar token"><br>
	</form>

{% endblock cuerpo %}

