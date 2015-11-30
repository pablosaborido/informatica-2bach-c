{% extends "layout.php" %}

{% block cuerpo %}
<div class="jumbotron">
	<div class="alert alert-success" role="alert"> 
		Se le ha enviado un enlace a <strong>{{email}}</strong><br>
		Utilícelo para iniciar sesión<br>
		Gracias<br>
	</div>
</div>
{% endblock cuerpo %}
