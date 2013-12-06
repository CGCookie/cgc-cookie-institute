<?php

if( class_exists( 'CGC_CI_Admin' ) ) return;

class CGC_CI_Admin {

	var $slug = 'cgc-cookie-institute';

	function __construct( ){

	}

	function initialize(){
		add_action( 'admin_init', array( $this, '_init' ) );
	}

	function _init(){
		add_action( 'add_meta_boxes', array( $this, 'metaboxes' ) );
		add_action( 'save_post', array( $this, 'save_lesson_meta' ) );
		add_action( 'save_post', array( $this, 'save_quiz_meta' ) );
	}

	function metaboxes(){
		add_meta_box( $this->slug, __( 'Cookie Institute' ), array( $this, 'lesson_metabox' ), 'cgc_lessons', 'normal', 'high' );
		add_meta_box( $this->slug, __( 'Lesson Quiz Settings' ), array( $this, 'quiz_metabox' ), CGCCI_QUIZ_CPT, 'normal', 'high' );
		//add_meta_box( $this->stats_slug, __( 'Quiz Stats' ), array( $this, 'quiz_stats' ), CGCCI_QUIZ_CPT, 'normal', 'high' );
	}

	function lesson_metabox( $post, $metabox ){
		$quizzes = $this->get_quizzes();

		wp_nonce_field( CGCCI_PREFIX . 'lesson_metabox', CGCCI_PREFIX . 'lesson_metabox_nonce' );

		$lesson_quiz = get_post_meta( $post->ID, CGCCI_PREFIX . 'lesson_quiz', true );
		if( isset( $_POST[ CGCCI_PREFIX . 'lesson_quiz' ] ) ){
			$lesson_quiz = $_POST[ CGCCI_PREFIX . 'lesson_quiz' ];
		}

		include( CGCCI_PATH . '/views/lesson-metabox.php' );
	}

	function save_lesson_meta( $post_id ){
		if( ! isset( $_POST[ CGCCI_PREFIX . 'lesson_metabox_nonce' ] ) )
			return $post_id;

		$nonce = $_POST[ CGCCI_PREFIX . 'lesson_metabox_nonce' ];

		if( ! wp_verify_nonce( $nonce, CGCCI_PREFIX . 'lesson_metabox' ) )
			return $post_id;

		if ( ! current_user_can( 'edit_post', $post_id ) )
			return $post_id;

		$lesson_quiz = isset( $_POST[ CGCCI_PREFIX . 'lesson_quiz' ] ) ? sanitize_text_field( $_POST[ CGCCI_PREFIX . 'lesson_quiz' ] ) : '';

		update_post_meta( $post_id, CGCCI_PREFIX . 'lesson_quiz', $lesson_quiz );
	}

	function quiz_metabox( $post, $metabox ){
		$forms = $this->get_quiz_gravity_forms();

		wp_nonce_field( CGCCI_PREFIX . 'quiz_metabox', CGCCI_PREFIX . 'quiz_metabox_nonce' );

		$quiz_form = get_post_meta( $post->ID, CGCCI_PREFIX . 'quiz_form', true );
		if( isset( $_POST[ CGCCI_PREFIX . 'quiz_form' ] ) ){
			$quiz_form = sanitize_text_field( $_POST[ CGCCI_PREFIX . 'quiz_form' ] );
		}

		$number_questions = get_post_meta( $post->ID, CGCCI_PREFIX . 'number_questions', true );
		if( isset( $_POST[ CGCCI_PREFIX . 'number_questions' ] ) ){
			$number_questions = sanitize_text_field( $_POST[ CGCCI_PREFIX . 'number_questions' ] );
		}

		$time_limit = get_post_meta( $post->ID, CGCCI_PREFIX . 'time_limit', true );
		if( isset( $_POST[ CGCCI_PREFIX . 'time_limit' ] ) ){
			$time_limit = sanitize_text_field( $_POST[ CGCCI_PREFIX . 'time_limit' ] );
		}
		if( $time_limit )
			$time_limit = $time_limit / 60; // convert seconds to minutes

		$wait_period = get_post_meta( $post->ID, CGCCI_PREFIX . 'wait_period', true );
		if( isset( $_POST[ CGCCI_PREFIX . 'wait_period' ] ) ){
			$wait_period = sanitize_text_field( $_POST[ CGCCI_PREFIX . 'wait_period' ] );
		}
		if( $wait_period )
			$wait_period = $wait_period / 60; // convert seconds to minutes

		$point_value = get_post_meta( $post->ID, CGCCI_PREFIX . 'point_value', true );
		if( isset( $_POST[ CGCCI_PREFIX . 'point_value' ] ) ){
			$point_value = sanitize_text_field( $_POST[ CGCCI_PREFIX . 'point_value' ] );
		}

		$passing = get_post_meta( $post->ID, CGCCI_PREFIX . 'passing', true );
		if( isset( $_POST[ CGCCI_PREFIX . 'passing' ] ) ){
			$passing = sanitize_text_field( $_POST[ CGCCI_PREFIX . 'passing' ] );
		}

		$passing_measurement = get_post_meta( $post->ID, CGCCI_PREFIX . 'passing_measurement', true );
		if( isset( $_POST[ CGCCI_PREFIX . 'passing_measurement' ] ) ){
			$passing_measurement = sanitize_text_field( $_POST[ CGCCI_PREFIX . 'passing_measurement' ] );
		}

		include( CGCCI_PATH . '/views/quiz-metabox.php' );
	}

	function save_quiz_meta( $post_id ){
		if( ! isset( $_POST[ CGCCI_PREFIX . 'quiz_metabox_nonce' ] ) )
			return $post_id;

		$nonce = $_POST[ CGCCI_PREFIX . 'quiz_metabox_nonce' ];

		if( ! wp_verify_nonce( $nonce, CGCCI_PREFIX . 'quiz_metabox' ) )
			return $post_id;

		if ( ! current_user_can( 'edit_post', $post_id ) )
			return $post_id;

		$quiz_form = isset( $_POST[ CGCCI_PREFIX . 'quiz_form' ] ) ? sanitize_text_field( $_POST[ CGCCI_PREFIX . 'quiz_form' ] ) : '';
		update_post_meta( $post_id, CGCCI_PREFIX . 'quiz_form', $quiz_form );

		if( isset( $_POST[ CGCCI_PREFIX . 'number_questions' ] ) ){
			update_post_meta( $post_id, CGCCI_PREFIX . 'number_questions', sanitize_text_field( $_POST[ CGCCI_PREFIX . 'number_questions' ] ) );
		}

		if( isset( $_POST[ CGCCI_PREFIX . 'time_limit' ] ) ){
			$wait_period = sanitize_text_field( $_POST[ CGCCI_PREFIX . 'time_limit' ] );
			if( $time_limit )
				$time_limit = $time_limit * 60; // convert minutes to seconds
			update_post_meta( $post_id, CGCCI_PREFIX . 'time_limit', $time_limit );
		}

		if( isset( $_POST[ CGCCI_PREFIX . 'wait_period' ] ) ){
			$wait_period = sanitize_text_field( $_POST[ CGCCI_PREFIX . 'wait_period' ] );
			if( $wait_period )
				$wait_period = $wait_period * 60; // convert minutes to seconds
			update_post_meta( $post_id, CGCCI_PREFIX . 'wait_period',  $wait_period );
		}

		if( isset( $_POST[ CGCCI_PREFIX . 'point_value' ] ) ){
			update_post_meta( $post_id, CGCCI_PREFIX . 'point_value', sanitize_text_field( $_POST[ CGCCI_PREFIX . 'point_value' ] ) );
		}
	}

	function get_quiz_gravity_forms(){
		$forms = array(
			array( 'label' => __( 'No Form', 'cgcci' ), 'value' => '' )
		);

		if( class_exists( 'RGFormsModel' ) && method_exists( 'RGFormsModel', 'get_forms' ) ){
			$gforms = RGFormsModel::get_forms( null, 'title' );
			if( count( $gforms ) ){
				foreach( $gforms as $gform ){
					if( ! $gform->is_active )
						continue;

					$the_form = RGFormsModel::get_form_meta_by_id( $gform->id );

					if( ! $the_form || ! GFCommon::get_fields_by_type( $the_form[0], array( 'quiz' ) ) )
						continue;

					$forms[] = array( 'label' => $gform->title, 'value' => $gform->id );
				}
			} else {
				$forms[] = array( 'label' => __( 'No forms were found.', 'cgcci' ), 'value' => '' );
			}
		} else {
			$forms[] = array( 'label' => __( 'Could not call get_forms on RGFormsModel. Is Gravity Forms installed?', 'cgcci' ), 'value' => '' );
		}

		return $forms;
	}

	function get_quizzes(){
		$quizzes = array(
			array( 'label' => __( 'No Quiz Selected', 'cgcci' ), 'value' => '' )
		);

		$quiz_query = new WP_Query(array(
			'post_type' => CGCCI_QUIZ_CPT,
			'posts_per_page' => '-1',
			'order_by' => 'post_title',
			'order' => 'asc'
		));

		while( $quiz_query->have_posts() ): $quiz_query->the_post();
			$quizzes[] = array( 'label' => get_the_title(), 'value' => get_the_ID() );
		endwhile;
		wp_reset_postdata();

		return $quizzes;
	}

}
