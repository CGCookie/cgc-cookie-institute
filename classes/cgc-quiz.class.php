<?php

if( class_exists( 'CGC_Quiz' ) ) return;

class CGC_Quiz {

	var $initialized = false;

	var $course_id;
	var $lesson_id;
	var $quiz_id;
	var $form_id;
	var $form;
	var $permalink;

	var $time_limit;
	var $number_questions;
	var $point_value;
	var $passing;
	var $passing_measurement;

	var $start_time;
	var $end_time;

	var $retake_available = false;

	var $results = array();

	var $defaults = array(
		'number_questions' => 3,
		'time_limit' => 300, // 5 minutes
		//'wait_period' => 7200, // 2 hours
		'wait_period' => 600, // 2 hours
		'point_value' => 10,
		'passing' => 100,
		'passing_measurement' => 'percent'
	);

	function __construct( $quiz_id = NULL ){
		$this->quiz_id = $quiz_id;
		$this->_init();
	}

	function init_by_form_id( $form_id ){
		$this->form_id = $form_id;

		$query = new WP_Query( array(
			'post_type' => CGCCI_QUIZ_CPT,
			'posts_per_page' => 1,
			'meta_key' => CGCCI_PREFIX . 'quiz_form',
			'meta_value' => $this->form_id
		) );

		if( $query->have_posts() ):
			while( $query->have_posts() ): $query->the_post();
				$this->quiz_id = get_the_ID();
			endwhile;
			wp_reset_postdata();
		endif;

		$this->_init();
	}

	function _init(){
		if( $this->quiz_id && ! $this->initialized ){
			if( ! $this->form_id )
				$this->form_id = get_post_meta( $this->quiz_id, CGCCI_PREFIX . 'quiz_form', true );

			if( ! $this->permalink )
				$this->permalink = get_permalink( $this->quiz_id );

			$this->time_limit = get_post_meta( $this->quiz_id, CGCCI_PREFIX . 'time_limit', true );
			if( ! $this->time_limit )
				$this->time_limit = $this->defaults['time_limit'];

			$this->number_questions = get_post_meta( $this->quiz_id, CGCCI_PREFIX . 'number_questions', true );
			if( ! $this->number_questions )
				$this->number_questions = $this->defaults['number_questions'];

			$this->point_value = get_post_meta( $this->quiz_id, CGCCI_PREFIX . 'point_value', true );
			if( ! $this->point_value )
				$this->point_value = $this->defaults['point_value'];

			$this->passing = get_post_meta( $this->quiz_id, CGCCI_PREFIX . 'passing', true );
			if( ! $this->passing )
				$this->passing = $this->defaults['passing'];

			$this->passing_measurement = get_post_meta( $this->quiz_id, CGCCI_PREFIX . 'passing_measurement', true );
			if( ! $this->passing_measurement )
				$this->passing_measurement = $this->defaults['passing_measurement'];

			$this->initialized = true;
		}
	}

	function is_valid(){
		if( ! $this->quiz_id || ! $this->form_id )
			return false;

		return true;
	}

	function can_take( $results = NULL ){
		if( ! $this->is_valid() )
			return false;

		$can_take = true;

		if( ! get_current_user_ID() )
			$can_take = false;

		if( $results && ! $results->can_retake() )
			$can_take = false;

		$can_take = apply_filters( 'cgcci_can_take', $can_take, $this->quiz_id );

		return $can_take;
	}

	function load_result( $result ){
		$this->results[] = $result;
	}

	function get_results( $user_id = NULL){

		if( $this->results && ! $user_id )
			return $this->results;

		$results = array();

		if( ! $this->results && class_exists( 'RGFormsModel' ) )
			$this->results = RGFormsModel::get_leads( $this->form_id );

		/* sort all results by best score, then date */
		$sort = array();
		foreach( $this->results as $k => $result ){
			if( isset( $result['gquiz_score'] ) ){
				$sort['gquiz_score'][$k] = $result['gquiz_score']; // quiz score
				$sort['99'][$k] = $result['99']; // end time
			}
		}
		if( count( $sort ) ){
			array_multisort( $sort['gquiz_score'], SORT_DESC, $sort['99'], SORT_DESC, $this->results );
		}

		if( $user_id ){
			$user_results = array();
			foreach( $this->results as $result ){
				if( $result['created_by'] == $user_id ){
					$user_results[] = $result;
				}
			}

			return $user_results;
		}

		return $this->results;
	}

	function wait_period( $return = 'minutes' ){
		$wait_period = get_post_meta( $this->quiz_id, CGCCI_PREFIX . 'wait_period', true );
		if( ! $wait_period )
			$wait_period = $this->defaults['wait_period'];

		if( $return == 'minutes')
			return round( $wait_period / 60, 0 );

		if( $return == 'hours' )
			return round( $wait_period / 60 / 60, 0 );

		return $wait_period;
	}

	function get_max_score(){
		return $this->get_number_questions() * $this->get_point_value();
	}

	function get_permalink(){
		return $this->permalink;
	}

	function get_time_limit( $format = 'clock' ){

		if( ! isset( $_POST ) || count( $_POST ) <= 0 ){

			$quiz_started = $this->get_start_time();
			$quiz_ended = $this->get_end_time();

			if( $quiz_started && ! $quiz_ended ){
				$elapsed = current_time( 'timestamp' ) - $quiz_started;
				if( $this->time_limit - $elapsed <= 0 ){
					$this->end();
				} else {
					$this->time_limit -= $elapsed;
				}
			} elseif( $quiz_started && $quiz_ended ){
				$this->restart();
			} elseif( ! $quiz_started ) {
				$this->start();
			}
		}

		if( $format == 'seconds')
			return $this->time_limit;

		if( $format == 'minutes' )
			return $this->time_limit / 60;


		if( $this->time_limit < 86400 ){ // less than 24 hours
			$time = ltrim( gmdate( 'i:s', $this->time_limit ), 0 );
		} else {
			$time = sprintf( '%d:%02d', ( $this->time_limit / 60 ) % 60, $this->time_limit % 60 );
		}

		return $time;
	}

	function get_point_value(){
		return $this->point_value;
	}

	function get_number_questions(){
		return $this->number_questions;
	}

	function start(){
		update_post_meta( $this->quiz_id, CGCCI_PREFIX . 'start_time_' . get_current_user_ID(), current_time( 'timestamp' ) );
	}

	function end(){
		$this->time_limit = 0;
		update_post_meta( $this->quiz_id, CGCCI_PREFIX . 'end_time_' . get_current_user_ID(), current_time( 'timestamp' ) );
		do_action( 'cgcci_quiz_end', $this );
	}

	function restart(){
		delete_post_meta( $this->quiz_id, CGCCI_PREFIX . 'end_time_' . get_current_user_ID() );
		$this->start();
	}

	function get_start_time(){
		if( $this->start_time )
			return $this->start_time;

		$this->start_time = get_post_meta( $this->quiz_id, CGCCI_PREFIX . 'start_time_' . get_current_user_ID(), true );

		return $this->start_time;
	}

	function get_end_time(){
		if( $this->end_time )
			return $this->end_time;

		$this->end_time = get_post_meta( $this->quiz_id, CGCCI_PREFIX . 'end_time_' . get_current_user_ID(), true );

		return $this->end_time;
	}

	function get_lesson_id(){
		if( $this->lesson_id )
			return $this->lesson_id;

		$query = new WP_Query( array(
			'post_type' => 'cgc_lessons',
			'posts_per_page' => 1,
			'meta_key' => CGCCI_PREFIX . 'lesson_quiz',
			'meta_value' => $this->quiz_id
		) );

		if( $query->have_posts() ):
			while( $query->have_posts() ): $query->the_post();
				$this->lesson_id = get_the_ID();
			endwhile;
			wp_reset_postdata();
		endif;

		return $this->lesson_id;
	}

	function get_course_id(){
		if( $this->course_id )
			return $this->course_id;

		$lesson_id = $this->get_lesson_id();

		$query = new WP_Query( array(
			'post_type' => 'cgc_courses',
			'posts_per_page' => 1,
			'connected_type' => 'lessons_to_courses',
			'connected_items' => $lesson_id
		) );

		if( $query->have_posts() ):
			while( $query->have_posts() ): $query->the_post();
				$this->course_id = get_the_ID();
			endwhile;
			wp_reset_postdata();
		endif;

		return $this->course_id;
	}

	function get_average( $type = 'points' ){

		$users = array();
		$total = 0;

		foreach( $this->get_results() as $result ){
			if( ! in_array( $result['created_by'], $users ) ){
				$users[] = $result['created_by'];
				$total += $result['gquiz_score'];
			}
		}

		$average = count( $users ) ? round( $total / count( $users ), 0 ) : 0;

		if( $type == 'points' )
			return $average * $this->point_value;

		if( $type == 'percent' )
			return round( $average / $this->quiz->get_number_questions(), 2 ) * 100;

		return $average;
	}
}
