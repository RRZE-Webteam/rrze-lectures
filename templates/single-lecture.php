<?php

get_header();
?>

			<?php get_template_part('template-parts/hero', 'small');?>

			<div id="content">
				<div class="container">
					<div class="row">
						 <div <?php post_class("entry-content");?>>
						    <main>
	<?php
if ($lectureContent) {
    echo $lectureContent;
} else {?>
							<h1 id="droppoint" class="mobiletitle"><?php _e('Error', 'fau');?></h1>
							<p class="hinweis">
							<strong><?php _e('We are sorry', 'rrze-lecture');?></strong><br>
						<?php _e('No information can be retrieved for the specified lecture.', 'rrze-lectures');?>
							</p>
						    <?php }?>
						    </main>
					    </div>

					</div>
				</div>
			</div>

	<?php
get_template_part('template-parts/footer', 'social');
get_footer();
