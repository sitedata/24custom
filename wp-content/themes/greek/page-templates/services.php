<?php
/**
 * Template Name: Services
 *
 * Description: Services page template
 *
* @package    VG Greek
 * @author     VinaGecko <support@vinagecko.com>
 * @copyright  Copyright (C) 2015 VinaGecko.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://vinagecko.com
 */
global $greek_options;

get_header();
?>
<div class="main-container default-page services-page">
	<div class="container">
		<div class="row">
			<div class="col-xs-12">
				<?php greek_breadcrumb(); ?>
			</div>
			<div class="col-xs-12">
				<div class="page-content">
					<?php while (have_posts()) : the_post(); ?>
						<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
							<header class="entry-header">
								<h1 class="entry-title"><?php the_title(); ?></h1>
							</header>
							<div class="entry-content">
								<?php the_content(); ?>
							</div><!-- .entry-content -->
						</article><!-- #post -->
					<?php endwhile; // end of the loop. ?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php get_footer(); ?>