<?php
/**
 * The template for displaying a single entry called via url redirect
 *
 *
 * @package WordPress
 * @subpackage FAU
 * @since FAU 1.0
 */

namespace RRZE\Lectures;

use RRZE\Lectures\Main;

$thisThemeGroup = Main::getThemeGroup();

get_header();
if ($thisThemeGroup == 'fauthemes') {?>

    <section id="hero" class="hero-small">
	<div class="container hero-content">
		<div class="row">
		    <div class="col-xs-12">
			<?php
fau_breadcrumb();
    ?>

		    </div>
		</div>
		<div class="row" aria-hidden="true" role="presentation">
		    <div class="col-xs-12">
			<p class="presentationtitle"><?php _e('DIP', 'rrze-lectures');?></p>
		    </div>
		</div>
	</div>
    </section>

	<div id="content">
		<div class="content-container">
		    <div class="content-row">
			    <main>
				<h1 class="screen-reader-text"><?php _e('DIP', 'rrze-lectures');?></h1>
					<div class="inline-box">
					    <?php get_template_part('template-parts/sidebar', 'inline');
    echo '<div class="content-inline">';

} elseif ($thisThemeGroup == 'rrzethemes') {

    if (!is_front_page()) {?>
	    <div id="sidebar" class="sidebar">
		<?php get_sidebar('page');?>
	    </div><!-- .sidebar -->
	<?php }?>

	<div id="primary" class="content-area">
	    <div id="content" class="site-content" role="main">

<?php } else {?>

    <div id="sidebar" class="sidebar">

	<?php get_sidebar();?>

    </div>
    <div id="primary" class="content-area">
	<main id="main" class="site-main">

<?php }

echo $data;

?>
<nav class="rrze-lectures navigation">
    <div class="nav-previous">
        <a href="<?php echo get_permalink(); ?>"><span class="meta-nav">&laquo;</span> <?php _e('Back to overview', 'rrze-lectures');?></a>
    </div>
</nav>

<?php

if ($thisThemeGroup == 'fauthemes') {?>

			</div>
		    </div>
	        </main>
	    </div>
	</div>
    </div>

    <?php
get_template_part('template-parts/footer', 'social');

} elseif ($thisThemeGroup == 'rrzethemes') {?>

    </div>
</div>

<?php } else {?>
</main>
</div>
<?php }

get_footer();