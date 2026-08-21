<?php
/**
 * Service icons, keyed by the service post slug.
 *
 * @package eight-fields
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inline SVG for a service, chosen by post slug.
 *
 * @param string $slug Post slug.
 * @return string
 */
function ef_service_icon( $slug ) {
	$icons = array(
		'solar' => '<svg viewBox="0 0 32 32" fill="none" aria-hidden="true"><path d="M16 2v4M27 7l-2.8 2.8M30 18h-4M6 18H2M7.8 9.8 5 7" stroke="#FFBC2C" stroke-width="2" stroke-linecap="round"/><circle cx="16" cy="18" r="4.4" fill="#FFBC2C"/><path d="m8 30 3-9h10l3 9H8Z" fill="#43ACDC"/><path d="M9.6 26.5h12.8M14.9 21v9M17.1 21v9" stroke="#fff" stroke-width="1.3"/></svg>',
		'storage_battery' => '<svg viewBox="0 0 32 32" fill="none" aria-hidden="true"><rect x="5" y="6" width="22" height="21" rx="3.5" fill="#43ACDC"/><path d="M11 3.5h10" stroke="#0B2E42" stroke-width="2.2" stroke-linecap="round"/><path d="m17.4 10.5-5.2 7.3h3.6l-1.2 5.4 5.4-7.6h-3.7l1.1-5.1Z" fill="#FFBC2C"/></svg>',
		'allelectric' => '<svg viewBox="0 0 32 32" fill="none" aria-hidden="true"><path d="M4 14 16 4l12 10" stroke="#43ACDC" stroke-width="2.2" stroke-linejoin="round"/><path d="M7 14v13h18V14" stroke="#43ACDC" stroke-width="2.2" stroke-linejoin="round"/><circle cx="16" cy="20" r="4.6" fill="#FFBC2C"/><path d="M13.6 20h4.8M16 17.6v4.8" stroke="#fff" stroke-width="1.6" stroke-linecap="round"/></svg>',
		'wall_painting' => '<svg viewBox="0 0 32 32" fill="none" aria-hidden="true"><path d="M4 12 16 3l12 9" stroke="#FFBC2C" stroke-width="2.4" stroke-linejoin="round"/><rect x="9" y="14" width="14" height="7" rx="2" fill="#43ACDC"/><path d="M16 21v4.5" stroke="#43ACDC" stroke-width="2" stroke-linecap="round"/><rect x="13.4" y="25" width="5.2" height="5.5" rx="1.6" fill="#0B2E42"/></svg>',
		'ev' => '<svg viewBox="0 0 32 32" fill="none" aria-hidden="true"><path d="M3 20.5h20v5H3z" fill="#43ACDC"/><path d="m5.5 20.5 2.6-6.2A2 2 0 0 1 10 13h6.4a2 2 0 0 1 1.8 1.2l2.4 6.3" fill="#43ACDC"/><circle cx="8" cy="26" r="2.4" fill="#0B2E42"/><circle cx="18" cy="26" r="2.4" fill="#0B2E42"/><path d="M25 8v6.5a3.5 3.5 0 0 1-3.5 3.5" stroke="#FFBC2C" stroke-width="2" stroke-linecap="round"/><path d="M23 3.5V8M27 3.5V8" stroke="#FFBC2C" stroke-width="2" stroke-linecap="round"/></svg>',
		'maintenance' => '<svg viewBox="0 0 32 32" fill="none" aria-hidden="true"><path d="M20.2 4.6a6.6 6.6 0 0 0-8.4 8.4L4.4 20.4a2.6 2.6 0 0 0 0 3.7l3.5 3.5a2.6 2.6 0 0 0 3.7 0l7.4-7.4a6.6 6.6 0 0 0 8.4-8.4l-4 4-3.6-3.6 4-4Z" fill="#43ACDC"/><circle cx="9.6" cy="22.4" r="1.7" fill="#fff"/><path d="M24.5 21.5 28 25M22 24l3.5 3.5" stroke="#FFBC2C" stroke-width="2.4" stroke-linecap="round"/></svg>',
	);

	return isset( $icons[ $slug ] ) ? $icons[ $slug ] : '';
}
