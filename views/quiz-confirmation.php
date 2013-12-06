<div class="quiz-results">
	<h3><?php echo $vars['heading']; ?></h3>
	<p><?php echo $vars['content']; ?></p>

	<div class="quiz-stats">
		<div class="quiz-user-score <?php echo $vars['class']; ?>">
			<i class="icon user"></i>
			<strong>Your Score</strong>
			<em><?php echo $vars['number_correct']; ?> out of <?php echo $vars['number_questions']; ?> correct</em>
			<span class="points-block"><strong><?php echo $vars['user_score']; ?></strong> pts</span>
		</div>
		<div class="quiz-community-score">
			<i class="icon group"></i>
			<strong>Community average</strong>
			<span class="points-block"><strong><?php echo $vars['community_average']; ?></strong> pts</span>
		</div>
	</div>

	<a href="/dashboard/#report-card" class="button">View Report Card</a>
</div>
