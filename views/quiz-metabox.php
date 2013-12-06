<div class="cgcci-fields">
	<p class="form-row form-row-single">
		<label>
			<span class="label"><?php _e( 'Quiz Form', 'cgcci' ); ?></span>
			<span class="field"><select name="<?php echo CGCCI_PREFIX; ?>quiz_form" id="<?php echo CGCCI_PREFIX; ?>quiz_form">
				<?php foreach( $forms as $form ): ?>
					<option value="<?php echo $form['value']; ?>" <?php selected( $quiz_form, $form['value'] ); ?>><?php echo $form['label']; ?></option>
				<?php endforeach; ?>
			</select></span>
		</label>
	</p>

	<p class="form-row form-row-single">
		<label>
			<span class="label"><?php _e( 'Number of Questions to display', 'cgcci' ); ?></span>
			<span class="field"><input type="text" name="<?php echo CGCCI_PREFIX; ?>number_questions" id="<?php echo CGCCI_PREFIX; ?>number_questions" value="<?php echo esc_attr( $number_questions ); ?>" />
				&nbsp; <em><?php _e( 'Default: 3', 'cgcci' ); ?></em></span>
		</label>
	</p>

	<p class="form-row form-row-single">
		<label>
			<span class="label"><?php _e( 'Time Limit', 'cgcci' ); ?></span>
			<span class="field"><input type="text" name="<?php echo CGCCI_PREFIX; ?>time_limit" id="<?php echo CGCCI_PREFIX; ?>time_limit" value="<?php echo esc_attr( $time_limit ); ?>" /> <?php _e( 'minutes', 'cgcci' ); ?>
				&nbsp; <em><?php _e( 'Default: 5', 'cgcci' ); ?></em></span>
		</label>
	</p>

	<p class="form-row form-row-single">
		<label>
			<span class="label"><?php _e( 'Wait period between attempts', 'cgcci' ); ?></span>
			<span class="field"><input type="text" name="<?php echo CGCCI_PREFIX; ?>wait_period" id="<?php echo CGCCI_PREFIX; ?>wait_period" value="<?php echo esc_attr( $wait_period ); ?>" /> <?php _e( 'minutes', 'cgcci' ); ?>
				&nbsp; <em><?php _e( 'Default: 120 (2 hours)', 'cgcci' ); ?></em></span>
		</label>
	</p>

	<p class="form-row form-row-single">
		<label>
			<span class="label"><?php _e( 'Correct Answer Point Value', 'cgcci' ); ?></span>
			<span class="field"><input type="text" name="<?php echo CGCCI_PREFIX; ?>point_value" id="<?php echo CGCCI_PREFIX; ?>point_value" value="<?php echo esc_attr( $point_value ); ?>" /> <?php _e( 'points', 'cgcci' ); ?>
				&nbsp; <em><?php _e( 'Default: 10', 'cgcci' ); ?></em></span>
		</label>
	</p>

	<p class="form-row form-row-single">
		<span class="label"><label for="<?php echo CGCCI_PREFIX; ?>passing"><?php _e( 'Passing Grade', 'cgcci' ); ?></label></span>
		<span class="field"><input type="text" name="<?php echo CGCCI_PREFIX; ?>passing" id="<?php echo CGCCI_PREFIX; ?>passing" value="<?php echo esc_attr( $passing ); ?>" />
			<select name="<?php echo CGCCI_PREFIX; ?>passing_measurement" id="<?php echo CGCCI_PREFIX; ?>passing_measurement">
				<option value="" <?php selected( $passing_measurement, '' ); ?>><?php _e( 'Default', 'cgcci' ); ?></option>
				<option value="points" <?php selected( $passing_measurement, 'points' ); ?>><?php _e( 'Points', 'cgcci' ); ?></option>
				<option value="percent" <?php selected( $passing_measurement, 'percent' ); ?>><?php _e( 'Percent', 'cgcci' ); ?></option>
			</select>
			<?php _e( ' needed to pass', 'cgcci' ); ?>
			&nbsp; <em><?php _e( 'Default: Total of Points Possible (100%)', 'cgcci' ); ?></em></span>
	</p>

	<?php do_action( 'cgcci_quiz_metabox' ); ?>
</div>