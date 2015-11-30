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
	<h1>Dictados para Primaria y Secundaria</h1>
	<p class="lead">Diviértete al tiempo que mejoras tu ortografía</p>
</div>
{% endblock cuerpo %}

