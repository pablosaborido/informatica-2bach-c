		  var reproductor;

        //----------------------------------------------------------
		// Class ReproductorUI
		//
		// Encargada de gestionar la interfaz gráfica (ocultar y/o
		// mostrar elementos según lo que se esté haciendo)
		//----------------------------------------------------------
		
        function ReproductorUI () {
			
			// Referencia para que los métodos privados puedan invocar métodos públicos
			var self=this;
			
		}
		
		//----------------------------------------------------------
		// Métodos estáticos REPRODUCTORUI
		//----------------------------------------------------------
		
		// Prepara la UI para atender las acciones del usuario
		
		ReproductorUI.inicializar = function(jsonDictado) {
			
			dictado=JSON.parse(jsonDictado);
			reproductor=new Reproductor(dictado.LINEAS,4000);

			// Indicamos quién atenderá los eventos del Reproductor
			
			reproductor.notifyWhenFinished(
				function(){
					ReproductorUI.dictadoTerminado();
				});

			reproductor.notifyWhenStopped(
				function(){
					ReproductorUI.dictadoParado();
				});
				
			reproductor.notifyWhenPlaying(
				function(){
					ReproductorUI.dictadoIniciado();
				});
				
			reproductor.notifyWhenPaused(
				function(){
					ReproductorUI.dictadoPausado();
				});
		
			// Asociamos eventos a los botones de UI
			
			$("#btnPlay").click(
				function(){
					if(reproductor && reproductor.isPlaying())
						alert('no deberías invocarme otra vez');
					else 	
						reproductor.play();
				});
				
			$("#btnStop").click(
				function(){
					reproductor.stop();
				});
				
			$("#btnPause").click(
				function(){
					reproductor.pause();
				});

			$("#btnReplay").click(
				function(){
					reproductor.replay();
				});
		}

		// Método invocado cuando comienza a sonar el dictado
		
		ReproductorUI.dictadoIniciado = function() {
				$("#btnPlay").hide();
				$("#btnPause").show();
				$("#btnStop").show();
				$("#btnReplay").show();
				
				$("#acciones_dictado").show();
				$("#opciones_dictado").hide();
				$("#texto_dictado").hide();
		}
		
		// Método invocado cuando termina de sonar el dictado completo
		
		ReproductorUI.dictadoTerminado = function() {
			
				$("#btnPlay").show();
				$("#btnPause").hide();
				$("#btnStop").hide();
				$("#btnReplay").hide();
				
				$("#acciones_dictado").hide();
				$("#opciones_dictado").show();
				$("#texto_dictado").hide();
		}
		
		// Método invocado cuando el usuario aborta el dictado parándo el reproductor
		
		ReproductorUI.dictadoParado = function() {
			
				$("#btnPlay").show();
				$("#btnPause").hide();
				$("#btnStop").hide();
				$("#btnReplay").hide();
				
				$("#acciones_dictado").show();
				$("#opciones_dictado").hide();
				$("#texto_dictado").hide();
		}
		
		// Método invocado cuando detienen temporalmente el dictado
		
		ReproductorUI.dictadoPausado = function() {
				$("#btnPlay").show();
				$("#btnPause").hide();
				$("#btnStop").show();
				$("#btnReplay").hide();
		}
		
		// Método invocado cuando detienen temporalmente el dictado
		
		ReproductorUI.corregirDictado = function(dictado) {
				jsonDictado=JSON.parse(dictado);
				$("#contenido_dictado").text(jsonDictado.LINEAS.join(" "));
				
				$("#acciones_dictado").hide();
				$("#opciones_dictado").hide();
				$("#texto_dictado").show();
		}
		
		//----------------------------------------------------------
		// Class Reproductor
		//
		// Encargada de realizar el Texto to Speech del dictado
		// usando Google Translate
		//----------------------------------------------------------
		
		function Reproductor (pistas,delayPistas) {
			
			// Referencia para que los métodos privados puedan invocar métodos públicos
			
			var self=this;
			
			// Temporizador que gestiona las pausas entre frases dictadas
			
			var timer;
			
			// Referencia a las funciones callback
			
			var callback_finished;
			var callback_playing;
			var callback_paused;
			var callback_stopped;
			
			// Cadena aleatoria que permite saber dónde termina la lista de pistas a reproducir
			// permitiendo anotar tras ella las pistas que se hayan reproducido evitando que se
			// pierdan
			
			var TOKEN_DELIMITADOR = "finListaReproduccion-" +Math.random().toString(36).slice(2);
			
			//----------------------------------------------------------
			// Atributos privados
			//----------------------------------------------------------
			
			// Referencia al reproductor de sonido
			var el=this;	
			// Tiempo de silencio (en milisengundos) que debe esperarse antes de reproducir la siguiente pista
			var delay = delayPistas;
			
			// Anotamos el array de pistas a reproducir
			var pistasAudio = pistas; 
			pistasAudio.push(TOKEN_DELIMITADOR);
			
			//----------------------------------------------------------
			// Métodos privados
			//----------------------------------------------------------
			
			// Comprueba si el reproductor está listo para ser utilizado
			
			var isInitialized = function() {
				//return el.mp3;
				console.log('Comprobamos si el reproductor está inicializado');
				return (el != null) && !(typeof el.mp3 === 'undefined')
			}
			
			// Carga el reproductor con la siguiente pista de audio
			
			var initializePlayer = function() {
				
				// Si existe, eliminamos el objeto utilizado con la pista anterior
				
				if(isInitialized())
					delete el.mp3;
				
				// Si no quedan pistas que reproducir, hemos terminado
				
				if(self.isFinished()) {
					rewindTracks();
					callback_finished.call();
					return;
				}
				
				// Cargamos la nueva pista
				
				el.mp3 = new Audio(getSiguientePista());
				el.mp3.addEventListener('ended', function() {
					timer = setTimeout(function(){
								if(isInitialized())
									initializePlayer();
							}, delay);
				}, false);
				el.mp3.addEventListener('error', function() {
					console.log('Fallo al reproducir la pista');
					self.replay();
				}, false);
				el.mp3.play();
			}
			
			// Obtiene la próxima pista a reproducir
			
			var getSiguientePista = function() {
				
				// Si no quedan pistas que reproducir, no hay nada que hacer
				
				if(self.isFinished()) 
					return;
				
				// Obtenemos la nueva pista
				t=rtrim(pistasAudio.shift());
				// La ponemos al final de la cola para que no se pierda
				pistasAudio.push(t);
				console.log('Pista a reproducir "'+t+'"'); //encodeURIComponent(t));
				// Devolvemos la pista solicitada
				return '/tts.php?ie=UTF-8&q=' + encodeURIComponent(t)+'&tl=es&client=t&prev=input';
			}
			
			// Coloca las pistas como estaban inicialmente
			// (en el orden que nos la dieron y el TOKEN_DELIMITADOR al final del todo)
			
			var rewindTracks = function() {
				pos=pistasAudio.indexOf(TOKEN_DELIMITADOR);
				pendiente=pistasAudio.splice(0,pos+1);
				pistasAudio=pistasAudio.concat(pendiente);
			}
			
			//----------------------------------------------------------
			// Métodos públicos
			//----------------------------------------------------------
			
			// Iniciamos/reanudamos la reproducción de las pistas
			
			this.play = function() {
				
				// Si ya está tocando, salimos directamente
				
				if(this.isPlaying()) return;
				
				if(this.isPaused()) {
					console.log('Como estaba en pause, continuamos por la siguiente pista');
					
				} else if (this.isStopped()) {
					console.log('Como estaba parado, empezamos desde el principio');
				}
				
				initializePlayer();
				
				// Avisamos que comienza el dictado
				
				callback_playing.call();
				
			};
			
			// Finaliza la reproducción
			
			this.stop = function() {
				
				// Si está tocando hacemos que deje de sonar la pista
				if(this.isPlaying())
					this.pause();
				
				// Colocamos la primera pista al principio por si vuelve a reproducir la lista	
				rewindTracks();
				
				// Destruimos el reproductor
				delete el.mp3;
				
				// Avisamos que han parado el dictado
				
				callback_stopped.call();
			}
			
			// Interrumpe temporalmente la reproducción (si procede, en caso contrario ignora la petición)
			
			this.pause = function(frase) {
				if(this.isPlaying()) {
					el.mp3.pause();
					delete el.mp3;
					callback_paused.call();
				}
			};
			
			// Reproduce nuevamente la última pista que se dictó
			
			this.replay = function() {
					
					if(self.isStopped() || self.isLastTrack()) return;
					
					// No dejamos que termine de sonar la pista actual
					
					this.pause();
					clearTimeout(timer); // Anulamos el temporizador
					
					// Quitamos la pista que nos interesa del final
					p=pistasAudio.slice(-1)[0];
					pistasAudio.pop();
					
					// Y la ponemos al principio
					pistasAudio.unshift(p);
					
					// Reproducimos nuevamente la pista
					
					this.play();
			};
			
			// Indica si el reproductor está "tocando"
			
			this.isPlaying = function() {
				return isInitialized() && !self.isPaused();
			};
			
			// Indica si ha terminado de reproducir todas las pistas de audio
			
			// DUDA cuando llega al final marca correctamente el final de la reproducción
			
			this.isFinished = function() {
				return pistasAudio[0]==TOKEN_DELIMITADOR;
			}
			
			// Indica si se está reproduciendo la última pista de la lista
			
			this.isLastTrack = function() {
				return pistasAudio[1]==TOKEN_DELIMITADOR;
			}
			
			// Indica si el reproductor ha sido pausado
			
			this.isPaused = function() {
				return isInitialized() && el.mp3.paused && el.mp3.played.length<1;
			}
			
			// Indica si el reproductor está detenido
			
			this.isStopped = function() {
				// Comprobamos que esté sin inicializar y el último elemento sea el TOKEN 
				// (ordenado listo para ser reproducido íntegramente)
				
				return !isInitialized() && pistasAudio.slice(-1)[0]==TOKEN_DELIMITADOR;
			}
				
			// Avisa cuando se haya terminado de dictar el texto
			
			this.notifyWhenFinished = function(callback) {
				callback_finished=callback;
			}
			
			// Avisa cuando el usuario para el dictado
			
			this.notifyWhenStopped = function(callback) {
				callback_stopped=callback;
			}
			
			// Avisa cuando comience a dictar el texto
			
			this.notifyWhenPlaying = function(callback) {
				callback_playing=callback;
			}
			
			// Avisa cuando detengan temporalmente el dictado
			
			this.notifyWhenPaused = function(callback) {
				callback_paused=callback;
			}
		}
		
		function rtrim(stringToTrim) {
			return stringToTrim.replace(/\s+$/,"");
		}

