<?php

/**
 * Themify Builder Pro — Advanced / Archive Products loop integration.
 */
class Themify_WPF_Plugin_Compat_themifyBuilderPro {

	public static function init() {
		add_filter( 'tbp_archive_products_query', array( __CLASS__, 'tbp_archive_products_query' ), 10, 2 );
		add_filter( 'themify_builder_module_container_props', array( __CLASS__, 'module_container_props' ), 10, 4 );
	}

	/**
	 * Apply active WPF filter args to Builder Pro product module queries.
	 *
	 * @param array $query_args
	 * @param array $fields_args
	 * @return array
	 */
	public static function tbp_archive_products_query( array $query_args, array $fields_args ): array {
		$slug = WPF_Public::request_wpf_slug();
		if ( '' === $slug ) {
			return $query_args;
		}

		$public = WPF_Public::get_instance();
		$form   = $public->get_form( $slug );
		if ( empty( $form ) ) {
			return $query_args;
		}

		$preserve = array();
		foreach ( array( 'tbp_aap', 'ptb_disable' ) as $key ) {
			if ( isset( $query_args[ $key ] ) ) {
				$preserve[ $key ] = $query_args[ $key ];
			}
		}

		$wpf_args = $public->parse_query( wp_unslash( $_REQUEST ), $form, true );
		if ( empty( $wpf_args ) || ! is_array( $wpf_args ) ) {
			return $query_args;
		}

		$wpf_args = self::narrow_taxonomy_archive( $wpf_args );

		$query_args = array_merge( $query_args, $wpf_args, $preserve );
		$query_args['tbp_aap']     = true;
		$query_args['wpf_merged']  = true;

		return apply_filters( 'wpf_tbp_archive_products_query', $query_args, $fields_args, $form );
	}

	/**
	 * Mirror WPF_Public::change_query() archive narrowing for direct query merges.
	 *
	 * @param array $args
	 * @return array
	 */
	private static function narrow_taxonomy_archive( array $args ): array {
		if ( ! is_tax( get_object_taxonomies( 'product' ) ) ) {
			return $args;
		}

		$queried_object          = get_queried_object();
		$taxonomy_filter_present = false;

		if ( ! empty( $args['tax_query'] ) && is_array( $args['tax_query'] ) && isset( $queried_object->taxonomy ) ) {
			foreach ( $args['tax_query'] as $k => $tq ) {
				if ( 'relation' === $k ) {
					continue;
				}
				if ( is_array( $tq ) && ! empty( $tq['taxonomy'] ) && $tq['taxonomy'] === $queried_object->taxonomy ) {
					$taxonomy_filter_present = true;
					break;
				}
			}
		}

		if ( ! $taxonomy_filter_present && isset( $queried_object->taxonomy, $queried_object->term_id ) ) {
			if ( ! isset( $args['tax_query'] ) ) {
				$args['tax_query'] = array();
			}
			$args['tax_query'][] = array(
				'taxonomy' => $queried_object->taxonomy,
				'field'    => 'term_id',
				'terms'    => $queried_object->term_id,
			);
		}

		return $args;
	}

	/**
	 * Mark Builder Pro product loops so WPF AJAX can find and replace them.
	 *
	 * @param array  $props
	 * @param array  $fields_args
	 * @param string $mod_name
	 * @param string $element_id
	 * @return array
	 */
	public static function module_container_props( array $props, array $fields_args, string $mod_name, string $element_id ): array {
		$slug = WPF_Public::request_wpf_slug();
		if ( '' === $slug ) {
			return $props;
		}

		if ( $mod_name !== 'advanced-products' && $mod_name !== 'archive-products' ) {
			return $props;
		}

		$class = isset( $props['class'] ) ? $props['class'] : '';
		if ( strpos( $class, 'wpf-search-container' ) === false ) {
			$props['class'] = trim( $class . ' wpf-search-container' );
		}
		$props['data-slug'] = $slug;

		return $props;
	}

	/**
	 * Whether the current request should render products via Builder Pro modules.
	 *
	 * @return bool
	 */
	public static function should_render_with_builder_pro(): bool {
		if ( ! class_exists( 'Tbp_Public', false ) ) {
			return false;
		}

		if ( Tbp_Public::get_location( 'product_archive' ) ) {
			return true;
		}

		$slug = WPF_Public::request_wpf_slug();
		if ( '' === $slug ) {
			return false;
		}

		$public = WPF_Public::get_instance();
		$form   = $public->get_form( $slug );
		if ( empty( $form['data']['page'] ) ) {
			return false;
		}

		$page_id = (int) $form['data']['page'];
		if ( $page_id < 1 || ! class_exists( 'Themify_Builder', false ) ) {
			return false;
		}

		$modules = Themify_Builder::get_builder_modules_list( $page_id );
		if ( empty( $modules ) || ! is_array( $modules ) ) {
			return false;
		}

		foreach ( $modules as $module ) {
			if ( ! empty( $module['mod_name'] ) && in_array( $module['mod_name'], array( 'advanced-products', 'archive-products' ), true ) ) {
				return true;
			}
		}

		return false;
	}
}
