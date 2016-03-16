{% extends "layout.php" %}

{% block tabActivo %}inicio{% endblock tabActivo %}

{% block cuerpo %}

{% if message %}
	<div class="alert alert-success" role="alert"> {{ message|raw}}</div>
{% endif %}

{% if error %}
	<div class="alert alert-error" role="alert"> {{ error|raw}}</div>
{% endif %}

<div class="jumbotron">
	<h1>Control de partes</h1>
	<p class="hola">'La más excelente de todas las virtudes es la justicia'. Aristóteles</p>
	{% for comentario in comentarios %}
		{% for campo, valor in comentario %}
		{{campo}} : {{valor}} <br>
		{% endfor %}
		----------------<br>
		{% endfor %}
  
</div>

{% endblock cuerpo %}

