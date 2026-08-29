<?php
/**
 * Template Name: お問い合わせ
 *
 * Matches the fixed design for /contact/: the three ways to reach us, then the
 * form. Picked up automatically for a page with the slug `contact`.
 *
 * The form itself is the page's editor content — put a Contact Form 7 (or
 * similar) shortcode there. `contact-form-7.txt` in the theme folder holds a
 * ready-made definition that reproduces the designed form. Until a form is
 * placed, the section falls back to the phone and address rather than showing
 * an inert set of inputs.
 *
 * @package eight-fields
 */

get_header();

while ( have_posts() ) :
	the_post();

	ef_page_hero(
		array(
			'title'    => get_the_title(),
			'en'       => get_post_meta( get_the_ID(), 'ef_page_en', true ),
			'lead'     => get_post_meta( get_the_ID(), 'ef_page_lead', true ),
			'image_id' => get_post_thumbnail_id(),
		)
	);
	ef_breadcrumbs();

	$ef_has_form = (bool) trim( get_the_content() );
	?>

	<section class="ef-section ef-section--tight">
		<div class="ef-container">
			<div class="ef-contactways" data-reveal>
				<div class="ef-way">
					<span class="ef-way__icon ef-ico"><?php ef_icon( 'phone' ); ?></span>
					<h2 class="ef-way__title"><?php esc_html_e( 'お電話', 'eight-fields' ); ?></h2>
					<p class="ef-way__text"><?php esc_html_e( 'その場で概算をお伝えできる場合もあります。', 'eight-fields' ); ?></p>
					<a class="ef-way__value" href="tel:<?php echo esc_attr( ef_tel_digits() ); ?>"><?php echo esc_html( ef_info( 'tel', '03-6670-5540' ) ); ?></a>
					<p class="ef-help"><?php echo esc_html( ef_info( 'hours', '平日 9:00 - 18:00' ) ); ?></p>
				</div>
				<div class="ef-way">
					<span class="ef-way__icon ef-ico"><?php ef_icon( 'mail' ); ?></span>
					<h2 class="ef-way__title"><?php esc_html_e( 'フォーム', 'eight-fields' ); ?></h2>
					<p class="ef-way__text"><?php esc_html_e( '24時間受付。2営業日以内にご返信します。', 'eight-fields' ); ?></p>
					<a class="ef-btn ef-btn--dark ef-btn--sm ef-mt-24" href="#form"><?php esc_html_e( '入力フォームへ', 'eight-fields' ); ?></a>
				</div>
				<div class="ef-way">
					<span class="ef-way__icon ef-ico"><?php ef_icon( 'pin' ); ?></span>
					<h2 class="ef-way__title"><?php esc_html_e( '来社・訪問', 'eight-fields' ); ?></h2>
					<p class="ef-way__text">〒<?php echo esc_html( ef_info( 'zip', '131-0042' ) ); ?><br><?php echo esc_html( ef_info( 'address', '東京都墨田区東墨田2-12-20' ) ); ?></p>
					<a class="ef-btn ef-btn--outline ef-btn--sm ef-mt-24" href="<?php echo esc_url( home_url( '/company/' ) ); ?>"><?php esc_html_e( 'アクセスを見る', 'eight-fields' ); ?></a>
				</div>
			</div>
		</div>
	</section>

	<section class="ef-section" id="form">
		<div class="ef-container ef-container--narrow">
			<div class="ef-head" data-reveal>
				<span class="ef-eyebrow">Form</span>
				<h2 class="ef-h2"><?php esc_html_e( 'お問い合わせフォーム', 'eight-fields' ); ?></h2>
				<p class="ef-lead">
					<span class="ef-req"><?php esc_html_e( '必須', 'eight-fields' ); ?></span>
					<?php esc_html_e( 'の項目をご入力のうえ、送信してください。検針票（電気ご使用量のお知らせ）をお手元にご用意いただけると、より具体的なご提案が可能です。', 'eight-fields' ); ?>
				</p>
			</div>

			<?php if ( $ef_has_form ) : ?>
				<div class="ef-form" data-reveal data-reveal-delay="1">
					<?php the_content(); ?>
				</div>
			<?php else : ?>
				<div class="ef-callout" data-reveal data-reveal-delay="1">
					<h3 class="ef-callout__title"><span class="ef-callout__icon"><?php ef_icon( 'phone' ); ?></span><?php esc_html_e( 'お電話でも承ります', 'eight-fields' ); ?></h3>
					<p class="ef-callout__text">
						<?php esc_html_e( 'ご相談・現地調査・お見積りは無料です。お急ぎの場合はお電話ください。', 'eight-fields' ); ?>
					</p>
					<p class="ef-mt-24">
						<a class="ef-btn ef-btn--primary" href="tel:<?php echo esc_attr( ef_tel_digits() ); ?>">
							<?php ef_icon( 'phone' ); ?><span><?php echo esc_html( ef_info( 'tel', '03-6670-5540' ) ); ?></span>
						</a>
					</p>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php
endwhile;

get_footer();
