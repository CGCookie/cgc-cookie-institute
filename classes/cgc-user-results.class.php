<?php

if( class_exists( 'CGC_User_Results' ) ) return;

class CGC_User_Results {

	var $quiz;
	var $user_id;

	var $results = array();

	var $last_attempt;
	var $last_attempted;
	var $time_until_retake = 0;

	function __construct( $quiz, $user_id = NULL ){
		$this->user_id = $user_id ? $user_id : get_current_user_ID();
		$this->quiz = $quiz;

		$this->_init();
	}

	private function _init(){
		$this->get_results();
	}

	function get_results(){
		if( $this->results )
			return $this->results;

		$this->results = $this->quiz->get_results( $this->user_id );

		if( count( $this->results ) ){
			$this->last_attempt = current( $this->results );
		}

		if( $this->attempted() ){
			$diff = current_time( 'timestamp' ) - $this->attempted();
			if( $diff >= $this->quiz->wait_period( 'seconds' ) )
				$this->time_until_retake = 0;
			else
				$this->time_until_retake = $this->quiz->wait_period( 'seconds' ) - $diff;
		}

		return $this->results;
	}

	public function attempted(){
		if( $this->last_attempted )
			return $this->last_attempted;

		$last_attempt = 0;
		foreach( $this->results as $result ){ // don't use get_results() here.
			if( isset( $result['99'] ) && $result['99'] > $last_attempt )
				$last_attempt = $result['99'];
		}

		$this->last_attempted = intval( $last_attempt );
		return $this->last_attempted;
	}

	function attempts( $type = NULL ){
		if( ! $type )
			return count( $this->get_results() );

		$passed = 0;
		$failed = 0;

		foreach( $this->get_results() as $result ){
			$amount_correct = $result['gquiz_score'];
			$score = $amount_correct * $this->quiz->get_point_value();
			if( $this->quiz->passing_measurement == 'percent' ){
				$score = round( $amount_correct / $this->quiz->get_number_questions(), 2 ) * 100;
			}

			if( $this->quiz->passing <= $score )
				$passed++;
			else
				$failed++;
		}

		if( $type == 'passed' )
			return $passed;

		if( $type == 'failed' )
			return $failed;
	}

	public function can_retake(){
		$retake = true;
		if( $this->get_wait_time( 'seconds' ) )
			$retake = false;

		return $retake;
	}

	public function get_time_until_retake(){
		return $this->time_until_retake;
	}

	public function get_wait_time( $format = 'clock' ){
		$seconds = $this->get_time_until_retake();

		if( $format == 'seconds' )
			return $seconds;

		if( $seconds < 86400 ){
			$wait_time = gmdate( 'H:i:s', $seconds );
		} else {
			$wait_time = sprintf( '%02d:%02d:%02d', floor( $seconds / 3600 ), ( $seconds / 60 ) % 60, $seconds % 60 );
		}

		return $wait_time;
	}

	public function get_last_attempt(){
		return $this->last_attempt;
	}

	function get_number_correct(){
		return $this->last_attempt['gquiz_score'];
	}

	function calculate_score( $type = 'percent' ){
		$amount_correct = $this->get_number_correct();
		$score = $amount_correct * $this->quiz->get_point_value();

		if( $type == 'percent' )
			return round( $amount_correct / $this->quiz->get_number_questions(), 2 ) * 100;

		return $score;
	}

	function passed(){
		if( $this->quiz->passing_measurement == 'percent' ){
			$score = $this->calculate_score();
		} else {
			$score = $this->calculate_score( 'points' );
		}

		if( $this->quiz->passing <= $score )
			return true;

		return false;
	}

	function get_start_time(){
		if( isset( $this->last_attempt[98] ) )
			return $this->last_attempt[98];

		return false;
	}

	function get_end_time(){
		if( isset( $this->last_attempt[99] ) )
			return $this->last_attempt[99];

		return false;
	}
}
