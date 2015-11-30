{% extends "layout.php" %}

{% block tabActivo %}crear{% endblock tabActivo %}

{% block cuerpo %}

<h1>Nuevo Dictado</h1>
	<form method="post" action="/dictado/guardar">
		<fieldset>
			<legend>Texto del dictado</legend>
			<textarea style="width:100%" rows="8" cols="50" name="dictado">El 'locutor' hará una pausa tras cada salto de línea</textarea>
			<br>
			<input type="submit" value="Guardar"  class="btn"><br>
		</fieldset>
	</form>
{% endblock cuerpo %}
