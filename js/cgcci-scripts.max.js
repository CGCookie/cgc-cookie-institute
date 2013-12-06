var cgcci;

(function ($) {

	cgcci = {
		new_button: '',

		init: function(){
			$('.cgcci-quiz-button').on('click', function(){
				var $btn = $(this);
				if( $btn.hasClass( 'please-wait' ) )
					return false;

				// old ajax code. Can't use because of Gravity Forms limitations.
				/*var quiz_id = $btn.data('quiz-id');

				$.ajax({
					type: "POST",
					url: cgc_scripts.ajaxurl,
					data: {
						quiz_id: quiz_id,
						action:'cgcci_get_quiz'
					},
					success: function( response ){
						var $target = $('article.cgc_lessons .cgcci-quiz');
						if( ! $target.length ){
							$target = cgcci.build_target();
							$('article.cgc_lessons').prepend( $target );
						}
						var $response = $( response );
						$response.find('input, select, textarea').removeAttr('disabled');
						$response.find('.gfield_admin_icons').remove();

						$target.find('.cgcci-quiz-content').html( $response );

						cgcci.start_timers();
						cgcci.disable_button( $btn );
					}
				});

				return false;*/
			});

			var $timer = $('.quiz-timer');
			if( $timer.length && $timer.data( 'in-seconds' ) <= 0 ){
				cgcci.end_quiz();
			}

			cgcci.start_timers();
		},

		disable_button: function( $btn ){
			$btn.html( 'Quiz in Progress' ).addClass('taking-quiz');
			$btn.off( 'click' ).on( 'click', function(){
				return false;
			});
		},

		build_target: function(){
			var $target = $('<div />').addClass('cgcci-quiz');
			var $close = $('<a />').attr( 'href', '#close-quiz' );
			$close.on('click', function(){
				if( confirm('Are you sure you want to quit this quiz?') )
					$(this).parents('.cgcci-quiz').slideUp('fast');

				return false;
			});

			$target.append( $close );
			$target.append( $('<div />').addClass('cgcci-quiz-content' ) );

			return $target;
		},

		start_timers: function(){
			$.each( $('.countdown-timer'), function(i, el){
				var $el = $(el),
					countdown_format = $el.data('format') ? $el.data('format') : 'MS',
					timer_seconds = $el.data('in-seconds') ? $el.data('in-seconds') : new Date(),
					expiry_callback = $el.data('expiry-callback') ? $el.data('expiry-callback') : '';

				var opt = {
					until: timer_seconds,
					compact: true,
					format: countdown_format,
					onTick: cgcci.tick_callback
				};

				if( $el.hasClass( 'quiz-timer' ) ){
					opt.onExpiry = cgcci.end_quiz;
				} else if( $el.hasClass( 'wait-timer' ) ){
					opt.onExpiry = cgcci.enable_quiz;
				}

				$el.countdown(opt);
			});
		},

		enable_quiz: function(){
			if( cgcci.get_new_button() )
				$('cgcci-quiz-button.please-wait').replaceWith( cgcci.get_new_button() );
		},

		end_quiz: function(){
			var $form = $('.cgcci-quiz-form:first');
			if( $form.length ){
				var $quiz_over = $('<div />').addClass( 'cgcci-quiz-ended' ).html( 'The quiz has ended.' );
				$form.before( $quiz_over );
				$quiz_over.slideDown( 'fast' );
				$form.slideUp( 'fast' );
				$form.submit();
			}
		},

		tick_callback: function( periods ){
			var seconds = $.countdown.periodsToSeconds( periods );
			$('input[type="hidden"][name="cgcci_time_remaining"]').val( seconds );

			var $timer = $('.countdown-timer');
			if( seconds == 5 ){
				$timer.addClass( 'ending-soon' );
				var $wait_button = $timer.parents('.please-wait');
				if( $wait_button.length ){
					cgcci.setup_button();
				}
			} else if ( seconds < 1 ){
				$timer.removeClass( 'ending-soon' ).addClass( 'ended' );
			}
		},

		get_new_button: function(){
			if( cgcci.new_button )
				return cgcci.new_button;

			return $( $.ajax({
				type: "POST",
				async: false,
				url: cgc_scripts.ajaxurl,
				data: {
					quiz_id: quiz_id,
					action:'cgcci_get_button'
				}
			}).responseText );
		},

		setup_button: function(){
			$.ajax({
				type: "POST",
				async: false,
				url: cgc_scripts.ajaxurl,
				data: {
					quiz_id: quiz_id,
					action:'cgcci_get_button'
				},
				success: function( response ){
					cgcci.new_button = $( response );
				}
			});
		}

	};

	$(function(){
		cgcci.init();
	});

}(jQuery));
