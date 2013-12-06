<div class="cgcci-report-card">

	<div class="cookie-score">
		<h3>Your Cookie Score</h3>
		<div class="score community-average">
			<p>Community Average</p>
			<strong><?php echo $vars['cookie_average']; ?></strong>
		</div>
		<div class="score user-score">
			<p>Your Score</p>
			<strong><?php echo $vars['cookie_score'] ? $vars['cookie_score'] : '??'; ?></strong>
		</div>
		<p>Your collective score across all enrolled sites below</p>
		<p><a href="#leaderboard">View Leaderboard</a>
			<?php if( $vars['cookie_score'] ): ?>
				 | <a href="#download">Download Report Card</a>
			<?php endif; ?></p>
	</div>

	<?php if( $vars['active_enrollments'] ): ?>

		<h2>Active Enrollments</h2>
		<p>Below are the active sites you are pursuing education in. Active site scores contribute to your overall cookie rating on the site.</p>

		<div class="active-enrollments">
			<ul class="enrolled">
				<?php foreach( $vars['active_enrollments'] as $enrollment ): ?>
					<li>
						<div class="single-item">
							<div class="institute">
								<h4><?php echo $enrollment['title']; ?></h4>
								<span><?php echo $enrollment['description']; ?></span>
							</div>
							<div class="scores">
								<div class="score community-average">
									<p>Community</p>
									<strong><?php echo $enrollment['average']; ?></strong>
								</div>
								<div class="score user-score">
									<p>Your Score</p>
									<strong><?php echo $enrollment['user_score']; ?></strong>
								</div>
							</div>
						</div>
						<div class="single-stats">
							<i class="icon-stats"></i>
							<span class="stats-title">Quiz stats</span>
							<span class="stats-taken">Taken: <?php echo $enrollment['taken']; ?></span>
							<span class="stats-passed">Passed: <?php echo $enrollment['passed']; ?></span>
							<span class="stats-failed">Failed: <?php echo $enrollment['failed']; ?></span>
							<span class="stats-max-points">Available Points: <?php echo $enrollment['max_points']; ?></span>
							<!-- <a href="#drop" class="drop-enrollment">Drop Enrollment</a> -->
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

	<?php endif; ?>

	<?php if( $vars['available_enrollments'] ): ?>
		<h2>Available Enrollments</h2>
		<p>The sites below are open for enrollment at any time. If you are serious about learning the software be sure to enroll in the relative site below.</p>

		<div class="available-enrollments">
			<ul class="not-enrolled">
				<?php foreach( $vars['available_enrollments'] as $enrollment ): ?>
					<li>
						<a href="<?php echo $enrollment['enroll_url']; ?>" class="button"><strong>Enroll</strong><span>( start your learning )</span></a>
						<div class="institute">
							<h4><?php echo $enrollment['title']; ?></h4>
							<span><?php echo $enrollment['description']; ?></span>
						</div>
						<div class="score">
							<strong>No Score</strong>
							<span>You are currently not enrolled in this site</span>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>
</div>
