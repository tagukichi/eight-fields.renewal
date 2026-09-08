<?php
/**
 * Template Name: 会社概要
 *
 * Matches the fixed design for /company/: 企業理念 → グループ2社 → 会社概要表 →
 * アクセス. It is picked up automatically for a page with the slug `company`,
 * and can also be assigned by name from 固定ページ → ページ属性.
 *
 * The page's own editor content, when present, is shown between the philosophy
 * statement and the group section, so extra copy can be added without editing
 * this file.
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

	// A 会社概要 page carried over from an older site keeps its details as lines
	// of 「ラベル：値」 in the editor. Those rows are read into the table below,
	// so the same information is not printed twice — once as a list and once as
	// the table.
	$ef_groups    = ef_profile_groups_for_page( get_the_ID() );
	$ef_from_body = (bool) ef_body_profile_rows( get_the_ID() );
	$ef_show_body = ! $ef_from_body && '' !== trim( get_the_content() );
	?>

	<!-- PHILOSOPHY -->
	<section class="ef-section">
		<div class="ef-container ef-container--narrow">
			<div class="ef-head ef-head--center" data-reveal>
				<span class="ef-eyebrow">Philosophy</span>
				<h2 class="ef-h2"><?php esc_html_e( '企業理念', 'eight-fields' ); ?></h2>
			</div>
			<blockquote class="ef-callout ef-callout--quote" data-reveal data-reveal-delay="1">
				<?php echo wp_kses( ef_philosophy(), array( 'br' => array() ) ); ?>
			</blockquote>

		</div>

		<?php if ( $ef_show_body ) : ?>
			<div class="ef-mt-64">
				<?php get_template_part( 'template-parts/content-body' ); ?>
			</div>
		<?php endif; ?>
	</section>

	<!-- GROUP -->
	<section class="ef-section ef-section--tight ef-section--sand">
		<div class="ef-container">
			<div class="ef-head ef-head--center" data-reveal>
				<span class="ef-eyebrow">Group</span>
				<h2 class="ef-h2"><?php esc_html_e( '2社の体制で、営業から施工まで', 'eight-fields' ); ?></h2>
				<p class="ef-lead">
					<?php esc_html_e( '提案を行うエイトフィールズと、施工を担うグループ会社の金山製作所。この体制が「営業会社＝施工会社」を実現し、安心と適正価格につながっています。', 'eight-fields' ); ?>
				</p>
			</div>
			<figure class="ef-partners" data-reveal data-reveal-delay="1">
				<picture>
					<source media="(max-width: 700px)"
						srcset="<?php echo esc_url( get_theme_file_uri( '/assets/img/slide-partners-sp.jpg' ) ); ?>">
					<img src="<?php echo esc_url( get_theme_file_uri( '/assets/img/slide-partners.jpg' ) ); ?>"
						alt="<?php esc_attr_e( 'エイトフィールズ株式会社 × 有限会社 金山製作所', 'eight-fields' ); ?>"
						width="1920" height="762" loading="lazy" decoding="async">
				</picture>
			</figure>
		</div>
	</section>

	<!-- PROFILE -->
	<section class="ef-section">
		<div class="ef-container ef-container--narrow">
			<div class="ef-head" data-reveal>
				<span class="ef-eyebrow">Profile</span>
				<h2 class="ef-h2"><?php esc_html_e( '会社概要', 'eight-fields' ); ?></h2>
			</div>
			<ul class="ef-facts" data-reveal data-reveal-delay="1">
				<?php foreach ( ef_company_highlights() as $ef_fact ) : ?>
					<li class="ef-fact">
						<span class="ef-fact__label"><?php echo esc_html( $ef_fact[0] ); ?></span>
						<span class="ef-fact__value"><?php echo esc_html( $ef_fact[1] ); ?><small><?php echo esc_html( $ef_fact[2] ); ?></small></span>
					</li>
				<?php endforeach; ?>
			</ul>

			<div class="ef-profile" data-reveal data-reveal-delay="2">
				<?php foreach ( $ef_groups as $ef_group ) : ?>
					<div class="ef-profile__group">
						<?php if ( '' !== $ef_group['title'] ) : ?>
							<h3 class="ef-profile__title"><?php echo esc_html( $ef_group['title'] ); ?></h3>
						<?php endif; ?>
						<table class="ef-table">
							<tbody>
								<?php foreach ( $ef_group['rows'] as $ef_row ) : ?>
									<tr>
										<th scope="row"><?php echo esc_html( $ef_row[0] ); ?></th>
										<td><?php echo wp_kses_post( $ef_row[1] ); ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- ACCESS -->
	<section class="ef-section ef-section--sand">
		<div class="ef-container">
			<div class="ef-head" data-reveal>
				<span class="ef-eyebrow">Access</span>
				<h2 class="ef-h2"><?php esc_html_e( 'アクセス', 'eight-fields' ); ?></h2>
			</div>
			<div class="ef-split" data-reveal data-reveal-delay="1">
				<div class="ef-split__media">
					<?php echo ef_map_block(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in ef_map_block(). ?>
				</div>
				<div>
					<h3 class="ef-h3"><?php esc_html_e( '本社', 'eight-fields' ); ?></h3>
					<table class="ef-table ef-mt-24">
						<tbody>
							<?php foreach ( ef_access_rows() as $ef_row ) : ?>
								<tr>
									<th scope="row"><?php echo esc_html( $ef_row[0] ); ?></th>
									<td><?php echo wp_kses_post( $ef_row[1] ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</section>

	<?php
endwhile;

get_footer();
