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
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/card-service', null, array( 'no' => ef_service_position() ) );
				endwhile;
				?>
			</div>
		<?php else : ?>
			<p><?php esc_html_e( 'サービスがまだ登録されていません。', 'eight-fields' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<!-- COMBINATION -->
<section class="ef-section ef-section--sand">
	<div class="ef-container">
		<div class="ef-head ef-head--center" data-reveal>
			<span class="ef-eyebrow">Combination</span>
			<h2 class="ef-h2"><?php esc_html_e( '組み合わせると、効果が変わります', 'eight-fields' ); ?></h2>
			<p class="ef-lead">
				<?php esc_html_e( '単体でも効果はありますが、組み合わせることで「つくる・ためる・かしこく使う」が完成します。ご家庭の電気の使い方を伺ったうえで、必要なものだけをご提案します。', 'eight-fields' ); ?>
			</p>
		</div>
		<div class="ef-steps" data-reveal data-reveal-delay="1">
			<?php foreach ( ef_combination_steps() as $ef_step ) : ?>
				<div class="ef-steps__item">
					<span class="ef-steps__no"><?php echo esc_html( $ef_step['no'] ); ?></span>
					<h3 class="ef-steps__title"><?php echo esc_html( $ef_step['title'] ); ?></h3>
					<p class="ef-steps__text"><?php echo esc_html( $ef_step['text'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
		<ul class="ef-notes ef-notes--center" data-reveal>
			<li><?php esc_html_e( '設置条件・電力プラン・ご家庭の使用量によって効果は異なります。現地調査のうえシミュレーションをご提示します。', 'eight-fields' ); ?></li>
		</ul>
	</div>
</section>

<?php
get_footer();
