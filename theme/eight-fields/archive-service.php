<?php
/**
 * Service archive — maps to /service_/.
 *
 * @package eight-fields
 */

get_header();

ef_page_hero(
	array(
		'title' => __( 'サービス', 'eight-fields' ),
		'en'    => 'SERVICE',
		'lead'  => __( '光熱費の削減・家の修繕・電気自動車のご相談等、エイトフィールズは出来ないことは無いくらい幅広く皆様に喜んでもらえる商材を扱っております。', 'eight-fields' ),
		'image_url' => get_theme_file_uri( '/assets/img/painting.jpg' ),
	)
);
ef_breadcrumbs();
?>

<section class="ef-section">
	<div class="ef-container">
		<div class="ef-head" data-reveal>
			<span class="ef-eyebrow">Lineup</span>
			<h2 class="ef-h2"><?php esc_html_e( '6つの分野を、ひとつの窓口で', 'eight-fields' ); ?></h2>
			<p class="ef-lead">
				<?php esc_html_e( '設備ごとに業者を探し、そのたびに同じ説明を繰り返す——そんな手間をなくします。組み合わせて導入するほど効果が出るものだからこそ、まとめてご相談ください。', 'eight-fields' ); ?>
			</p>
		</div>

		<?php if ( have_posts() ) : ?>
			<div class="ef-grid ef-grid--3">
				<?php
				$ef_i = 0;
				while ( have_posts() ) :
					the_post();
					++$ef_i;
					get_template_part( 'template-parts/card-service', null, array( 'no' => $ef_i ) );
				endwhile;
				?>
			</div>
		<?php else : ?>
			<p><?php esc_html_e( 'サービスがまだ登録されていません。', 'eight-fields' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
