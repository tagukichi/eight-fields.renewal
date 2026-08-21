<?php
/**
 * Site header.
 *
 * @package eight-fields
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="format-detection" content="telephone=no">
<?php wp_head(); ?>
</head>
<body id="top" <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="ef-skip" href="#main"><?php esc_html_e( '本文へスキップ', 'eight-fields' ); ?></a>

<header class="ef-header<?php echo is_front_page() ? ' ef-header--overlay' : ''; ?>" data-header>
	<div class="ef-header__inner">

		<a class="ef-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<img class="ef-logo__mark" src="<?php echo esc_url( ef_logo_url() ); ?>"
				alt="" width="376" height="214" decoding="async">
			<span class="ef-logo__text">
				<span class="ef-logo__type">EIGHT FIELDS</span>
				<span class="ef-logo__sub"><?php bloginfo( 'name' ); ?></span>
			</span>
		</a>

		<nav class="ef-nav" aria-label="<?php esc_attr_e( 'グローバルナビゲーション', 'eight-fields' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'ef-nav__list',
					'depth'          => 2,
					'fallback_cb'    => 'ef_nav_fallback',
					'walker'         => new EF_Nav_Walker(),
				)
			);
			?>
		</nav>

		<div class="ef-header__actions">
			<a class="ef-header__tel" href="tel:<?php echo esc_attr( ef_tel_digits() ); ?>">
				<b><?php echo esc_html( ef_info( 'tel', '03-6670-5540' ) ); ?></b>
				<small><?php echo esc_html( ef_info( 'hours', '平日 9:00 - 18:00' ) ); ?></small>
			</a>
			<a class="ef-btn ef-btn--primary ef-btn--sm" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">
				<?php esc_html_e( '無料相談・お見積り', 'eight-fields' ); ?>
			</a>
			<button class="ef-burger" type="button" data-burger
				aria-label="<?php esc_attr_e( 'メニューを開く', 'eight-fields' ); ?>"
				aria-expanded="false" aria-controls="ef-drawer">
				<span></span><span></span><span></span>
			</button>
		</div>

	</div>
</header>

<div class="ef-drawer" id="ef-drawer" data-drawer aria-hidden="true">
	<?php
	wp_nav_menu(
		array(
			'theme_location' => 'primary',
			'container'      => false,
			'menu_class'     => 'ef-drawer__list',
			'depth'          => 2,
			'fallback_cb'    => 'ef_nav_fallback',
			'walker'         => new EF_Drawer_Walker(),
		)
	);
	?>
	<div class="ef-drawer__foot">
		<a class="ef-btn ef-btn--primary ef-btn--block" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">
			<?php esc_html_e( '無料相談・お見積り', 'eight-fields' ); ?>
		</a>
		<a class="ef-btn ef-btn--outline ef-btn--block" href="tel:<?php echo esc_attr( ef_tel_digits() ); ?>">
			<?php ef_icon( 'phone' ); ?><span><?php echo esc_html( ef_info( 'tel', '03-6670-5540' ) ); ?></span>
		</a>
		<p class="ef-help ef-center"><?php echo esc_html( ef_info( 'hours', '平日 9:00 - 18:00' ) ); ?></p>
	</div>
</div>

<main id="main">
