<div class="cgcci-fields">
	<p class="form-row form-row-single">
		<label>
			<span class="label">Lesson Quiz</span>
			<span class="field"><select name="<?php echo CGCCI_PREFIX; ?>lesson_quiz" id="<?php echo CGCCI_PREFIX; ?>lesson_quiz">
				<?php foreach( $quizzes as $quiz ): ?>
					<option value="<?php echo $quiz['value']; ?>" <?php selected( $lesson_quiz, $quiz['value'] ); ?>><?php echo $quiz['label']; ?></option>
				<?php endforeach; ?>
			</select></span>
		</label>
	</p>

	<?php do_action( 'cgcci_lesson_metabox' ); ?>
</div>