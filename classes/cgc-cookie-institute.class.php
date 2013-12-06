<?php

if( class_exists( 'CGC_Cookie_Institute' ) ) return;

class CGC_Cookie_Institute {

	var $results = array();

	function __construct(){

	}

	function initialize(){
		add_action( 'init', array( $this, '_init') );
		add_action( 'init', array( $this, 'register_cpt_cgc_quiz' ) );
	}

	function _init(){
		add_action( 'wp_ajax_cgcci_get_quiz', array( $this, 'ajax_get_quiz' ) );
		add_action( 'cgcci_quiz_title_template', array( $this, 'quiz_title_template' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'resources' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_resources' ) );

		add_filter( 'get_post_metadata', array( $this, 'filter_special_meta' ), 10, 4 );

		add_filter( 'the_content', array( $this, 'quiz_content') );

		add_filter( 'gform_form_tag', array( $this, 'form_quiz_class' ), 10, 2 );
		add_filter( 'gform_pre_render', array( $this, 'randomize_questions' ) );
		add_filter( 'gform_pre_submission_filter', array( $this, 'add_additional_fields' ) );
		add_filter( 'gform_entry_meta', array( $this, 'add_additional_data' ), 10, 2 );
		add_filter( 'gform_confirmation', array( $this, 'quiz_completion_message'), 10, 4 );
		add_filter( 'gform_submit_button', array( $this, 'quiz_submit_button' ), 10, 2 );
	}

	function filter_special_meta( $check, $object_id, $meta_key, $single ){
		if( is_singular( CGCCI_QUIZ_CPT ) && $meta_key == 'cgc_image_gallery' ) {
			return true; // disables the image gallery and uploads in the footer.
		}
		return $check;
	}

	function register_cpt_cgc_quiz(){
		$labels = array(
			'name' => _x( 'Quizzes', 'cgc_quiz' ),
			'singular_name' => _x( 'Quiz', 'cgc_quiz' ),
			'add_new' => _x( 'Add New', 'cgc_quiz' ),
			'add_new_item' => _x( 'Add New Quiz', 'cgc_quiz' ),
			'edit_item' => _x( 'Edit Quiz', 'cgc_quiz' ),
			'new_item' => _x( 'New Quiz', 'cgc_quiz' ),
			'view_item' => _x( 'View Quiz', 'cgc_quiz' ),
			'search_items' => _x( 'Search Quizzes', 'cgc_quiz' ),
			'not_found' => _x( 'No quizzes found', 'cgc_quiz' ),
			'not_found_in_trash' => _x( 'No quizzes found in Trash', 'cgc_quiz' ),
			'parent_item_colon' => _x( 'Parent Quiz:', 'cgc_quiz' ),
			'menu_name' => _x( 'Quizzes', 'cgc_quiz' ),
		);

		$args = array(
		'labels' => $labels,
			'hierarchical' => true,

			'supports' => array( 'title', 'editor', 'revisions', 'page-attributes' ),

			'public' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'menu_position' => 15,

			'show_in_nav_menus' => false,
			'publicly_queryable' => true,
			'exclude_from_search' => true,
			'has_archive' => false,
			'query_var' => true,
			'can_export' => true,
			'rewrite' => array(
				'slug' => 'quiz',
				'with_front' => false,
				'feeds' => true,
				'pages' => true
			),
			'capability_type' => 'post'
		);

		register_post_type( CGCCI_QUIZ_CPT, $args );
	}

	function resources(){
		wp_register_script( 'cgcci-countdown', CGCCI_DIR . 'js/jquery.countdown.min.js', array( 'jquery' ), CGCCI_VERSION );
		wp_register_script( 'cgcci-scripts', CGCCI_DIR . 'js/cgcci-scripts.js', array( 'cgcci-countdown' ), CGCCI_VERSION );
		wp_register_style( 'cgcci-frontend', CGCCI_DIR . 'css/cgcci-frontend.css', array(), CGCCI_VERSION );

		if( is_singular( CGCCI_QUIZ_CPT ) ) {
			$this->enable();
		}
	}

	function admin_resources(){
		wp_register_style( 'cgcci-admin', CGCCI_DIR . 'css/cgcci-admin.css', array(), CGCCI_VERSION );

		wp_enqueue_style( 'cgcci-admin' );
	}

	function enable(){
		wp_enqueue_script( 'cgcci-scripts' );
		wp_enqueue_style( 'cgcci-frontend' );
	}

	function ajax_get_quiz(){
		$quiz_id = isset( $_POST['quiz_id'] ) ? sanitize_text_field( $_POST['quiz_id'] ) : '';
		if( $quiz_id ){
			$quiz = new CGC_Quiz( $quiz_id );
			$user_results = new CGC_User_Results( $quiz );
			if( $quiz->is_valid() && $quiz->can_take() && $user_results->can_retake() ){
				$this->quiz_title_template( $quiz->quiz_id );
				gravity_form( $quiz->form_id, false, false, true, array(), false, '30' );
			}
		}

		exit();
	}

	function quiz_title_template( $quiz_id = NULL ){

		if( ! is_int( $quiz_id ) )
			$quiz_id = get_queried_object_id();

		$quiz = new CGC_Quiz( $quiz_id );

		if( ! $quiz->is_valid() || ! $quiz->can_take() )
			return;

		$user_results = new CGC_User_Results( $quiz );

		if( ! $user_results->can_retake() )
			return;

		echo '<div class="quiz-header">';
			if( ! isset( $_POST ) || count( $_POST ) <= 0 ){
				echo '<div class="quiz-timer-wrap">' . $this->quiz_countdown_timer() . '<span class="timer-description">' . __( 'Time to answer', 'cgcci' ). '</span></div>';
			}
			echo '<div class="quiz-title-wrap">' . $this->quiz_title() . '</div>';
		echo '</div>';
	}

	function quiz_title(){
		$user_id = get_current_user_ID();
		if( ! $user_id )
			return;

		if( ! is_singular( CGCCI_QUIZ_CPT ) )
			return;

		$quiz = new CGC_Quiz( get_queried_object_id() );

		$lesson_id = $quiz->get_lesson_id();
		$course_id = $quiz->get_course_id();

		if( $lesson_id && $course_id ){

			$lesson_title = get_the_title( $lesson_id );
			$course_title = get_the_title( $course_id );

			$html = '<div class="quiz-title">
				<h2 class="course-title"><strong>Course Title:</strong> %s</div>
				<h3 class="lesson-title"><strong>Lesson:</strong> %s</div>
			</div>';

			$title = sprintf( $html, $course_title, $lesson_title );
		} else {
			$title = '<h1 class="entry-title quiz-title">' . get_the_title() . '</h1>';
		}

		return $title;
	}

	function quiz_button( $lesson_id = NULL ){

		$user_id = get_current_user_ID();

		if( ! $user_id )
			return;

		if( ! is_singular( 'cgc_lessons' ) && ! $lesson_id )
			return;

		if( ! $lesson_id )
			$lesson_id = get_queried_object_id();

		$quiz_id = get_post_meta( $lesson_id, CGCCI_PREFIX . 'lesson_quiz', true );

		if( ! $quiz_id )
			return;

		$quiz = new CGC_Quiz( $quiz_id );

		if( ! $quiz->is_valid() || ! $quiz->can_take() )
			return;

		$this->enable();

		$user_results = new CGC_User_Results( $quiz );

		$class = '';
		$extra = '';

		if( $user_results->attempted() ) {
			$seconds = $user_results->get_wait_time( 'seconds' );
			$last_score = $user_results->calculate_score( 'points' );
			$max_score = $quiz->get_max_score();

			if( $last_score >= $max_score ) {
				$text = __( 'Perfect Score!', 'cgcci' );
				$class = ' perfect';
				$extra = __( 'You scored ', 'cgcci' ) . $last_score . __( 'pts out of ', 'cgcci' ) . $max_score . __( 'pts', 'cgcci' );
			} elseif( $seconds > 0 ){
				$text = __( 'Please wait', 'cgcci' );
				$class = ' please-wait';
				$wait_time = $user_results->get_wait_time();

				$extra = __( 'Retake is available in: ', 'cgcci') . '<span class="countdown-timer wait-timer" data-in-seconds="' . $seconds . '" data-format="HMS">' . $wait_time . '</span>';
			} elseif( $user_results->passed() ){
				$text = __( 'Passed with a score of ', 'cgcci' ) . $last_score . __( 'pts', 'cgcci' );
				$class = ' retake passed';
				$extra = __( 'Retake Quiz to get a better score', 'cgcci' );
			} else {
				$text = __( 'Retake Quiz', 'cgcci' );
				$class = ' retake';
				$extra = __( 'You previously scored ', 'cgcci' ) . $last_score . __( 'pts', 'cgcci' );
			}
		} else {
			$text = __( 'Take Quiz', 'cgcci' );
		}

		$button = '<a href="' . $quiz->get_permalink() . '" class="button cgcci-quiz-button' . $class . '" data-quiz-id="' . $quiz_id . '">';
			$button .= $text;
			if( $extra )
				$button .= '<span class="extra">' . $extra . '</span>';
		$button .= '</a>';

		return $button;
	}

	function quiz_countdown_timer(){
		$user_id = get_current_user_ID();
		if( ! $user_id )
			return;

		if( ! is_singular( CGCCI_QUIZ_CPT ) )
			return;

		$quiz = new CGC_Quiz( get_the_ID() );

		$limit = $quiz->get_time_limit();
		$limit_seconds = $quiz->get_time_limit( 'seconds' );

		return '<span class="countdown-timer quiz-timer" data-in-seconds="' . $limit_seconds . '">' . $limit .'</span>';
	}

	function quiz_content( $content ){
		if( ! is_singular( CGCCI_QUIZ_CPT ) )
			return $content;

		$quiz = new CGC_Quiz( get_the_ID() );

		if( ! $quiz ){
			$content = __( 'Oops. There is a problem with this quiz. Sorry \'bout that. :(', 'cgcci' );
			return $content;
		}

		if( ! $quiz->can_take() ){
			ob_start();
			get_template_part( 'no-post-access' );
			$content = ob_get_clean();
			return $content;
		}

		$user_results = new CGC_User_Results( $quiz );

		if( ! $user_results->can_retake() ){
			ob_start();
			include( CGCCI_PATH . 'views/quiz-unavailable.php' );
			$content = ob_get_clean();
			return $content;
		}

		$form = do_shortcode( '[gravityform id="' . $quiz->form_id . '" title="false" description="false" ajax="false"]' );

		if( ! isset( $_POST ) || count( $_POST ) <= 0 ){
			$content .= $form;
		} else {
			$content = $form;
		}

		return $content;
	}

	function form_quiz_class( $form_tag, $form ){
		$quiz = new CGC_Quiz();
		$quiz->init_by_form_id( $form['id'] );

		if( ! $quiz->is_valid() )
			return $form_tag;

		if( strpos( $form_tag, 'class=' ) === false ) {
			$form_tag = str_replace( '<form', '<form class="cgcci-quiz-form"', $form_tag );
		} else {
			$form_tag = str_replace( 'class="', 'class="cgcci-quiz-form ', $form_tag );
		}

		return $form_tag;
	}

	function randomize_questions( $form ){
		$quiz = new CGC_Quiz();
		$quiz->init_by_form_id( $form['id'] );

		if( ! $quiz->is_valid() )
			return $form;

		$original_fields = $form['fields'];
		$total_questions = count( $original_fields );
		$form['fields'] = array();

		$limit = $quiz->get_number_questions() <= $total_questions ? $quiz->get_number_questions() : $total_questions;

		$keys = range( 0, $total_questions - 1 );
		shuffle( $keys ); // randomize the keys
		$keys = array_splice( $keys, 0, $limit ); // return only needed keys.

		// put the questions back into the array
		foreach( $keys as $key ){
			$form['fields'][] = $original_fields[ $key ];
		}

		return $form;
	}

	function add_additional_fields( $form ){
		$quiz = new CGC_Quiz();
		$quiz->init_by_form_id( $form['id'] );

		if( ! $quiz->is_valid() )
			return $form;

		$quiz->end();

		$form['fields'][] = array(
			'id' => '98',
			'label' => 'Quiz Start Time',
			'type' => 'cgcci',
			'defaultValue' => $quiz->get_start_time()
		);

		$form['fields'][] = array(
			'id' => '99',
			'label' => 'Quiz End Time',
			'type' => 'cgcci',
			'defaultValue' => $quiz->get_end_time()
		);

		$_POST['input_98'] = $quiz->get_start_time();
		$_POST['input_99'] = $quiz->get_end_time();

		return $form;
	}

	function add_additional_data( $custom_entry_properties, $form_id ){
		$quiz = new CGC_Quiz();
		$quiz->init_by_form_id( $form_id );

		if( ! $quiz->is_valid() )
			return $custom_entry_properties;

		$custom_entry_properties['cgcci_start'] = array(
			'label'							=> 'Quiz Start Time',
			'is_numeric'					=> true,
			'is_default_column'				=> true,
			'update_entry_meta_callback'	=> array( $this, 'display_additional_data' )
		);

		$custom_entry_properties['cgcci_end'] = array(
			'label'							=> 'Quiz End Time',
			'is_numeric'					=> true,
			'is_default_column'				=> true,
			'update_entry_meta_callback'	=> array( $this, 'display_additional_data' )
		);

		return $custom_entry_properties;
	}

	function display_additional_data( $key, $lead, $form ) {
		$value = '';

		$quiz = new CGC_Quiz();
		$quiz->init_by_form_id( $form['id'] );

		if( ! $quiz->is_valid() )
			return $value;

		$quiz->load_result( $lead );
		$user_results = new CGC_User_Results( $quiz );

		if ( $key == 'cgcci_start' )
			$value = date( 'n/j/Y g:i a', $user_results->get_start_time() );
		elseif ($key == 'cgcci_end')
			$value = date( 'n/j/Y g:i a', $user_results->get_end_time() );

		return $value;
	}

	function quiz_completion_message( $confirmation, $form, $lead, $ajax ){

		$quiz = new CGC_Quiz();
		$quiz->init_by_form_id( $form['id'] );

		if( ! $quiz->is_valid() )
			return $form;

		$quiz->load_result( $lead );
		$user_results = new CGC_User_Results( $quiz );

		$vars = array(
			'number_questions' => $quiz->get_number_questions(),
			'community_average' => $quiz->get_average( 'points' ),
			'user_score' => $user_results->calculate_score( 'points' ),
			'number_correct' => $user_results->get_number_correct()
		);

		if( $user_results->passed() ){
			$vars['class'] = 'passed';
			$vars['heading'] = 'Yay! You passed the quiz!';
			$vars['content'] = 'Congratulations! You are getting smarter! Be sure to checkout your report card, and head on to the next lesson! You are ready for it!';
		} else {
			$vars['class'] = 'failed';
			$vars['heading'] = 'Quiz Failed';
			$vars['content'] = sprintf( 'It looks like you didn\'t pass this quiz. Don\'t worry, take a deep breath, go for a walk, review the material and then come back to retake the quiz in %s hours. We know you can do it!', $quiz->wait_period( 'hours' ) );
		}

		ob_start();
		include( CGCCI_PATH . 'views/quiz-confirmation.php' );
		$confirmation = ob_get_clean();

		return $confirmation;
	}

	function quiz_submit_button( $button, $form ){

		$quiz = new CGC_Quiz();
		$quiz->init_by_form_id( $form['id'] );

		if( ! $quiz->is_valid() )
			return $button;

		$button = str_replace( array( 'value="Submit"', 'value=\'Submit\'' ), 'value="' . __( 'Submit your answers', 'cgcci' ) . '"', $button );
		$button .= '<input type="hidden" name="cgcci_time_remaining" />';

		return $button;
	}

	function report_card(){
		$frag = false;
		$report_card = false;
		if( class_exists( 'CWS_Fragment_Cache' ) ){
			$frag = new CWS_Fragment_Cache( 'cgcci-report-card', 3600 );
			$report_card = $frag->get_output();
		}

		if ( ! $report_card ) {
			$sites = get_blogs_of_user( get_current_user_ID(), false );

			$vars = array(
				'cookie_score' => 0,
				'cookie_average' => 0,
				'active_enrollments' => array(),
				'available_enrollments' => array()
			);

			$all_quizzes = 0;
			$all_users = array();

			foreach( $sites as $site ){

				switch_to_blog( $site->userblog_id );

				if( ! in_array( $site->userblog_id, array( 2, 9, 15, 3 ) ) ){
					echo'<pre>';var_dump($site->userblog_id);echo'</pre>';
					exit();
				}

				$quizzes = $this->get_quizzes();

				$enrollment = array(
					'title' => $site->blogname,
					'description' => get_bloginfo( 'description' ),
					'enroll_url' => 'http://' . $site->domain . substr( $site->path , 0, -1 ),

					'user_score' => 0,
					'average' => 0,
					'taken' => 0,
					'passed' => 0,
					'failed' => 0,
					'max_points' => 0
				);

				$total_quizzes = 0;
				$found_users = array();

				while( $quizzes->have_posts() ): $quizzes->the_post();
					$quiz = new CGC_Quiz( get_the_ID() );
					if( ! $quiz->is_valid() )
						continue;

					$enrollment['average'] += $quiz->get_average();
					$enrollment['max_points'] += $quiz->get_max_score();

					$user_results = new CGC_User_Results( $quiz );

					if( $user_results->attempted() ){
						if( ! in_array( $user_results->user_id, $found_users ) )
							$found_users[] = $user_results->user_id;

						if( ! in_array( $user_results->user_id, $all_users ) )
							$all_users[] = $user_results->user_id;

						$enrollment['taken'] += $user_results->attempts();
						$enrollment['user_score'] += $user_results->calculate_score( 'points' );
						$enrollment['passed'] += $user_results->attempts('passed');
						$enrollment['failed'] += $user_results->attempts('failed');
					}
					$total_quizzes++;
					$all_quizzes++;

				endwhile;
				wp_reset_postdata();

				$vars['cookie_score'] += $enrollment['user_score'];
				$vars['cookie_average'] += $enrollment['user_score'];

				if( count( $found_users ) ){
					// setup averages
					$enrollment['average'] = round( $enrollment['average'] / count( $found_users ), 0 );
				}

				if( $total_quizzes ){
					if( $enrollment['user_score'] ){
						$vars['active_enrollments'][] = $enrollment;
					} else {
						$vars['available_enrollments'][] = $enrollment;
					}
				}

			}

			if( count( $all_users ) ){
				$vars['cookie_average'] = round( $vars['cookie_average'] / count( $all_users ), 0 );
			}

			restore_current_blog();

			ob_start();
			include( CGCCI_PATH . 'views/report-card.php' );
			$report_card = ob_get_clean();

			if( $frag ){
				$frag->store( $report_card );
			}
		}

		return $report_card;
	}

	function get_quizzes( $limit = -1, $offset = NULL ){
		$args = array(
			'post_type' => CGCCI_QUIZ_CPT,
			'posts_per_page' => $limit
		);
		if( $offset )
			$args['offset'] = $offset;

		$quizzes = new WP_Query( $args );

		return $quizzes;
	}

	function leaderboard(){
		$leaders = array();

		ob_start();
		include( CGCCI_PATH . 'views/leaderboard.php' );
		$leaderboard = ob_get_clean();
		return $leaderboard;
	}
}
