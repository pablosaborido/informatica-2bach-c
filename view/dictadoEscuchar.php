{#
ICONOS
---
Play	http://goo.gl/msnfA5
Pause	http://goo.gl/vT71g5
Stop    http://goo.gl/F9Uk1F
Replay	http://goo.gl/J4Vixw

CUENTA ATRÁS
---
CountDown jQuery 	http://goo.gl/L6fqA
Html5 Canvas		http://goo.gl/RsvArF

OTROS
Multimedia en HTML5  http://goo.gl/SpmxaT
Reproductor compelto http://goo.gl/X6Xk
#}

{% extends "layout.php" %}

{% block cabecera %}
	{{ parent() }}
    <script src="/js/dictados.js"></script>
{% endblock cabecera %}

{% block tabActivo %}oir{% endblock tabActivo %}

{% block cuerpo %}

<span id="dummy">
	{% if consejo %}
	<div class="row">
		<div class="alert alert-info" role="alert">{{ consejo|raw }}</div>
	</div>
	{% endif %}
	
	{% if message %}
	<div class="row">
		<div class="alert alert-success" role="alert">{{ message|raw }}</div>
	</div>
	{% endif %}
	
	{% if not dictado %}
		<a href="/dictado/avisar" class="btn btn-success btn-large"><i class="icon-white icon-envelope"></i> Avísame cuando haya nuevos dictados</a>
		<a href="/dictado/crear" class="btn btn-warning btn-large"><i class="icon-white icon-pencil"></i> Crear dictado</a>
	{% else %}
		<div class="row">
		  <div class="span9">
			<div class="row">
				
				<div class="span2">
					<img src="/img/heroe-02.png" id="personaje" border="0" />
				</div>
				
				<div class="span5">
					<div style="display:none;" id="opciones_dictado">
						<div class="page-header">
							<h1>Dictado finalizado</h1>
						</div>
						<div class="span5">Decide si deseas volver a oír el dictado (Repasar) o ver si has cometido alguna falta de ortografía (Corregir).<br><br></div>
						<div class="span2"></div>
						<button class="btn btn-info btn-large" onclick="ReproductorUI.dictadoParado()"><i class="icon-white icon-repeat"></i> Repasar</button>
						<button class="btn btn-success btn-large" onclick="ReproductorUI.corregirDictado(textoDictado)"><i class="icon-white icon-ok"></i> Corregir</button>
					</div>
					<div id="acciones_dictado">
						<div class="page-header">
							<h1>Dictando</h1>
						</div>
						<img src="/img/media_playback_start.png" title="Comenzar a oír el dictado" border="0" id="btnPlay" />
						<img style="display:none;" src="/img/media_playback_pause.png" title="Para el dictado un momento" border="0" id="btnPause"/>
						<img style="display:none;" src="/img/media_playback_stop.png" title="Abandonar el dictado" border="0" id="btnStop"/>
						<img style="display:none;" src="/img/stock_repeat.png" title="Repetir la última frase dictada" border="0" id="btnReplay"/>
					</div>
					<div style="display:none;" id="texto_dictado">
						<div class="page-header">
							<h1>Corrigiendo</h1>
						</div>
						<div class="well">
							<p id="contenido_dictado">Aquí se cargará el texto del dictado.</p>
						</div>
						<div class="alert alert-success" role="alert">Comprueba si has tenido algún "despiste" anotándolo en tu dictado</div>
						{% if login.isLogged() %}
						<a href="/dictado/suspenso" class="btn btn-danger btn-large"><i class="icon-white icon-thumbs-down"></i> Algunas faltas</a>
						<a href="/dictado/aprobado/{{id_dictado}}" class="btn btn-success btn-large"><i class="icon-white icon-thumbs-up"></i> Bastante bien</a>
						{% endif %}
					</div>
				</div>
			</div>
		  </div>
		</div>
	{% endif %}

</span>
<script type="text/javascript">
	$(document).ready(function() {
		textoDictado='{{dictado|escape("js")}}';
		<!--textoDictado='{"ID":"6","AUTOR":"JASVAZQUEZ@GMAIL.COM","LINEAS":["Terminaba ya el verano ","y V\u00edctor recordaba "]}';-->
		ReproductorUI.inicializar(textoDictado);
	});
</script>

{% endblock cuerpo %}
