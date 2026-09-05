<?php

/**
 * Utility class of various static functions
 *
 * This class helps to manipulate with arrays
 *
 * @since      1.0.0
 * @package    WPF
 * @subpackage WPF/includes
 * @author     Themify
 */
class WPF_Utils {

    /**
     * Returns the current language code
     *
     * @since 1.0.0
     *
     * @return string the language code, e.g. "en"
     */
    public static function get_current_language_code() {
        static $language_code = false;

        if ($language_code) {
            return $language_code;
        }
        if (defined('ICL_LANGUAGE_CODE')) {
            $language_code = ICL_LANGUAGE_CODE;
        } elseif (function_exists('qtrans_getLanguage')) {
            $language_code = qtrans_getLanguage();
        }
        if ( ! $language_code ) {
            $language_code = substr( get_bloginfo('language'), 0, 2 );
        }
        $language_code = strtolower(trim($language_code));

        return $language_code;
    }

    /**
     * Returns the site languages
     *
     * @since 1.0.0
     *
     * @return array the languages code, e.g. "en",name e.g English
     */
    public static function get_all_languages() {

        static $languages = array();
        if (!empty($languages)) {
            return $languages;
        }
        if ( defined('ICL_LANGUAGE_CODE') ) {
            $lng = self::get_current_language_code();
            if ($lng == 'all') {
                $lng = self::get_default_language_code();
            }
            $all_lang = icl_get_languages('skip_missing=0&orderby=KEY&order=DIR&link_empty_to=str');
            foreach ($all_lang as $key => $l) {
                if ($lng == $key) {
                    $languages[$key]['selected'] = true;
                }
                $languages[$key]['name'] = $l['native_name'];
            }
        } elseif (function_exists('qtrans_getLanguage')) {
            $languages = qtrans_getSortedLanguages();
        }
        if(empty($languages)) {
            $all_lang = self::get_default_language_code();
            $languages[$all_lang]['name'] = '';
            $languages[$all_lang]['selected'] = true;
        }
        return $languages;
    }

    /**
     * Returns the default language code
     *
     * @since 1.0.0
     *
     * @return string the language code, e.g. "en"
     */
    public static function get_default_language_code() {
        static $language_code = false;
        if ($language_code === false) {
            global $sitepress;
            if (isset($sitepress)) {
                $language_code = $sitepress->get_default_language();
            }

            $language_code = empty($language_code) ? substr(get_bloginfo('language'), 0, 2) : $language_code;
            $language_code = strtolower(trim($language_code));
        }
        return $language_code;
    }

    public static function get_label($label) {
        if (!is_array($label)) {
            return esc_attr($label);
        }
        static $lng = false;
        if ($lng === false) {
            $lng = self::get_current_language_code();
        }
        $value = '';
        if (isset($label[$lng]) && $label[$lng]) {
            $value = $label[$lng];
        } else {
            static $default_lng = false;
            if ($default_lng === false) {
                $default_lng = self::get_default_language_code();
            }
            $value = isset($label[$default_lng]) && $label[$default_lng] ? $label[$default_lng] : current($label);
        }
        return esc_attr($value);
    }

    /**
     * Echo multilanguage html text for template
     *
     * @since 1.0.0
     *
     * @param number $id input id
     * @param array $data saved data
     * @param array $languages languages array
     * @param string $key
     * @param string $name
     */
    public static function module_multi_text($id, array $data, array $languages, $key, $name, $input = 'text', $placeholder = false) {
        ?>
        <div class="wpf_back_active_module_row">
            <?php if (!$placeholder): ?>
                <div class="wpf_back_active_module_label">
                    <label for="wpf_<?php echo $id ?>_<?php echo $key ?>"><?php echo $name; ?></label>
                </div>
            <?php endif; ?>
            <?php self::module_language_tabs($id, $data, $languages, $key, $input, $placeholder); ?>
        </div>
        <?php
    }

    /**
     * Echo multilanguage html text for template
     *
     * @since 1.0.0
     *
     * @param number $id input id
     * @param array $data saved data
     * @param array $languages languages array
     * @param string $key
     */
    public static function module_language_tabs($id, array $data, array $languages, $key, $input = 'text', $placeholder = false, $as_array = false) {
        ?>
        <?php if (!empty($languages)): ?>
            <div class="wpf_back_active_module_input">
                <?php if (count($languages) > 1): ?>
                    <ul class="wpf_language_tabs">
                        <?php foreach ($languages as $code => $lng): ?>
                            <li <?php if (isset($lng['selected'])): ?>class="wpf_active_tab_lng"<?php endif; ?>>
                                <a class="wpf_lng_<?php echo $code ?>"  title="<?php echo $lng['name'] ?>" href="#"><?php echo $code ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <?php
                $name = $as_array ? $id : '[' . $id . ']';
                if ( $key ) {
                    $name .= '[' . $key . ']';
					$values = isset( $data[ $key ] ) ? $data[ $key ] : array();
                } else {
					$values = isset( $data[ $id ] ) ? $data[ $id ] : array();
				}
                ?>
                <ul class="wpf_language_fields">
                    <?php foreach ($languages as $code => $lng ) :
						$value = isset( $values[ $code ] ) ? $values[ $code ] : '';
						?>
                        <li data-lng="wpf_lng_<?php echo $code ?>" <?php if (isset($lng['selected'])): ?>class="wpf_active_lng"<?php endif; ?>>
                            <?php
                            switch ($input) {
                                case 'text':
                                    ?>
                                    <input id="wpf_<?php echo $id ?><?php if ($key): ?>_<?php echo $key ?><?php endif; ?>" <?php if ($placeholder): ?>placeholder="<?php echo $placeholder ?>"<?php endif; ?> type="text" class="wpf_towidth"
                                           name="<?php echo $name ?>[<?php echo $code ?>]"
                                           <?php if ( $value ) : ?>value="<?php esc_attr_e( $value ) ?>"<?php endif; ?>/>
                                           <?php
                                           break;
                                       case 'textarea':
                                           ?>
                                    <textarea id="wpf_<?php echo $id ?><?php if ($key): ?>_<?php echo $key ?><?php endif; ?>" <?php if ($placeholder): ?>placeholder="<?php echo $placeholder ?>"<?php endif; ?> class="wpf_towidth"
                                              name="<?php echo $name ?>[<?php echo $code ?>]"><?php if ( $value ): ?> <?php echo stripslashes_deep(esc_textarea(trim( $value ))) ?><?php endif; ?></textarea>
                                              <?php
                                              break;
                                          case 'wp_editor':
                                              $id = 'wpf_' . $id;
                                              if ($key) {
                                                  $id.='_' . $key;
                                              }
                                              $tname = $name . '[' . $code . ']';
                                              wp_editor($value, $id, array('textarea_name' => $tname, 'media_buttons' => false));
                                              break;
                                      }
                                      ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php
    }

    public static function get_default_fields() {
        static $labels = array();
        if (empty($labels)) {
            $labels = array(
                'title' => __('Product Title', 'wpf'),
                'sku' => __('SKU', 'wpf'),
                'wpf_cat' => __('Category', 'wpf'),
                'wpf_tag' => __('Tag', 'wpf'),
                'price' => __('Price', 'wpf'),
                'instock' => __('In Stock', 'wpf'),
                'onsale' => __('On Sale', 'wpf'),
                'submit' => __('Submit Button', 'wpf')
            );
        }

        return $labels;
    }

    public static function get_wc_taxonomies() {
		$product_taxonomies = array();
		foreach ( get_object_taxonomies( 'product', 'objects' ) as $tax ) {
			$product_taxonomies[ $tax->name ] = $tax->label ? $tax->label : $tax->name;
		}

		return $product_taxonomies;
	}

	/**
	 * Returns a list of all field types in WPF
	 *
	 * @return array
	 */
    public static function get_all_field_types() {
		$wc_taxonomies = WPF_Utils::get_wc_taxonomies();
		unset( $wc_taxonomies['product_cat'], $wc_taxonomies['product_tag'], $wc_taxonomies['product_type'], $wc_taxonomies['product_visibility'] );
        $sort_cmb = array_merge( $wc_taxonomies, WPF_Utils::get_default_fields() );
		return $sort_cmb;
    }

    public static function get_current_page() {
        static $page = NULL;
        if (is_null($page)) {
            $page = is_shop() ? wc_get_page_id('shop') : (is_page()?get_the_ID():false);
        }
        return $page;
    }

    public static function strtolower( $text, $escape = true ) {
        $text = function_exists('mb_strtolower') ? mb_strtolower($text) : strtolower($text);
        if ( $escape ) {
            $text = sanitize_title($text);
        }

		if ( substr( $text, 0, 4 ) !== 'wpf_' ) {
			$text = 'wpf_' . $text;
		}

		return $text;
    }

    public static function get_field_name(array $item, $orig_name) {

        $title = ! empty( $item['field_title'] ) ? WPF_Utils::get_label( $item['field_title'] ) : $orig_name;
        if ( empty( $title ) ) {
            $title = $orig_name;
        }
        return sanitize_text_field( $title );
    }

    public static function format_price($price,$args=array()){
        if($price===''){
            return $price;
        }
        $price = floatval($price);
        if(strpos($price,'.',1)===false){
            $price = intval($price);
            $args['decimals'] =0;
        }
        return wc_price($price,$args);
    }
    
    /**
     * Check if ajax request
     *
     * @param void
     *
     * return boolean
     */
    public static function is_ajax() {
        static $is_ajax = null;
        if(is_null($is_ajax)){
            $is_ajax = defined('DOING_AJAX') || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
        }
        return $is_ajax;
    }

	/**
	 * Current product_cat archive scope for taxonomy facets (child_of). During facet AJAX, falls back to wpf_arc_* hidden fields.
	 *
	 * @return int Positive term ID or 0 if not on a resolved women-style category archive context.
	 */
	public static function get_active_product_cat_archive_term_id() {
		if ( function_exists( 'is_product_category' ) && is_product_category() ) {
			$id = (int) get_queried_object_id();
			return $id > 0 ? $id : 0;
		}

		if ( empty( $_REQUEST['wpf_arc_tax'] ) || empty( $_REQUEST['wpf_arc_term'] ) ) {
			return 0;
		}

		$tax = sanitize_key( wp_unslash( $_REQUEST['wpf_arc_tax'] ) );
		if ( 'product_cat' !== $tax ) {
			return 0;
		}

		$tid = absint( wp_unslash( $_REQUEST['wpf_arc_term'] ) );
		if ( $tid < 1 ) {
			return 0;
		}

		$term = get_term( $tid );
		if ( ! $term || is_wp_error( $term ) || $term->taxonomy !== 'product_cat' ) {
			return 0;
		}

		return (int) $term->term_id;
	}

	/**
	 * product_cat IDs for an archive subtree: the archive term plus all descendant categories.
	 *
	 * @param int $parent_term_id
	 * @return int[]
	 */
	public static function get_product_cat_subtree_term_ids( $parent_term_id ) {
		$parent_term_id = (int) $parent_term_id;
		if ( $parent_term_id < 1 ) {
			return array();
		}

		if ( ! taxonomy_exists( 'product_cat' ) ) {
			return array();
		}

		$t = get_term( $parent_term_id, 'product_cat' );
		if ( ! $t || is_wp_error( $t ) ) {
			return array();
		}

		$ids   = array( $parent_term_id );
		$child = get_term_children( $parent_term_id, 'product_cat' );
		if ( ! is_wp_error( $child ) && ! empty( $child ) ) {
			foreach ( $child as $cid ) {
				$cid = (int) $cid;
				if ( $cid > 0 ) {
					$ids[] = $cid;
				}
			}
		}

		return array_values( array_unique( $ids ) );
	}

	/**
	 * Count the number of published posts in a given $post_type
	 *
	 * return int
	 */
	public static function count_posts( $post_type ) {
		global $wpdb, $sitepress;

		if ( function_exists( 'wpml_prepare_in' ) ) {
			$query = $wpdb->get_results(
				$wpdb->prepare( "
					SELECT language_code, COUNT(p.ID) AS c
					FROM {$wpdb->prefix}icl_translations t
					JOIN {$wpdb->posts} p
						ON t.element_id=p.ID
							AND t.element_type = CONCAT('post_', p.post_type)
					WHERE p.post_type=%s
					AND t.language_code IN (" . wpml_prepare_in( array_keys( $sitepress->get_active_languages() ) ) . ")
					AND post_status IN ( 'publish' )
					GROUP BY language_code",
					$post_type
				)
			);
			if ( is_array( $query ) ) {
				$current_language_code = self::get_current_language_code();
				foreach ( $query as $language_count ) {
					if ( $language_count->language_code === $current_language_code ) {
						return $language_count->c;
					}
				}
			}
		} else {
			return wp_count_posts( $post_type )->publish;
		}
	}

	/**
	 * Gets an object ID (term, post) and returns a list of IDs for that object in all languages
	 *
	 * @return array
	 */
	public static function get_object_id_in_all_languages( $object_id, $type ) {
		$ids = array();
		$languages = WPF_Utils::get_all_languages();
		foreach ( $languages as $code => $language ) {
			$id = apply_filters( 'wpml_object_id', $object_id, $type, false, $code );
			if ( $id ) {
				$ids[ $code ] = $id;
			}
		}

		return $ids;
	}

	/**
	 * Returns the URL to WC's Shop page
	 *
	 * @return string
	 */
	public static function get_shop_page_url() {
		$shop_page_id = (int) wc_get_page_id( 'shop' );
		if ( $shop_page_id <= 0 ) {
			$shop_page = get_post_type_archive_link( 'product' );
		} else {
			$shop_page = urldecode( get_permalink( $shop_page_id ) );
		}

		return $shop_page;
	}

	/**
	 * Determine whether $query needs filtering by WPF
	 *
	 * @param WP_Query $query
	 * @return bool
	 */
	public static function is_wpf_query( $query ) {
		$is =
			( is_post_type_archive( 'product' ) && $query->is_main_query() ) // Shop page
			|| ( $query->is_tax( get_object_taxonomies( 'product' ) ) ) // product category and tag archive pages. Note that $query->get('post_type') is empty
			|| isset( $query->query['wpf_rand'] ) // [products] shortcode
			|| isset( $query->query['tf_wc_query'] ) // Themify WooCommerce module
			|| ( isset( $query->query['tbp_aap'] ) && $query->query['post_type'] === 'product' ) // Themify Builder Pro modules
			|| ( isset( $query->query['themify_wpf'] ) && $query->query['themify_wpf'] === true ) // custom flag in query_args
		;

		/**
		 * Whether $query should be filtered by WPF or not.
		 *
		 * @since 1.3.2
		 *
		 * @param bool     $is_wpf
		 * @param WP_Query $query
		 */
		$is = apply_filters( 'wpf_is_product_query', $is, $query );

		return $is;
	}

	/**
	 * Outputs pagination links for WPF
	 *
	 * @return void
	 */
	public static function pagination( $type = 'pagination' ) {
		echo '<div class="wpf-pagination">';

		if ( $type === 'infinity_auto' || $type === 'infinity' ) {
			$total_pages = wc_get_loop_prop( 'total_pages' );
			$current_page = isset( $_REQUEST['wpf_page'] ) ? (int)( $_REQUEST['wpf_page'] ) : 1;
			if ( $total_pages > 1 && $total_pages > $current_page ):
				?>
				<div class="wpf_infinity<?php if ( $type === 'infinity_auto' ): ?> wpf_infinity_auto<?php endif; ?>">
					<a data-max="<?php echo $total_pages ?>" data-current="<?php echo( $current_page + 1 ) ?>"
					   href="javascript:void(0);"><?php _e( 'Load More', 'wpf' ) ?></a>
				</div>
			<?php
			endif;
		} else if ( $type === 'pagination' ) {
			$base = remove_query_arg( array( 'wpf_ajax', 'append' ) );
			$args = array(
				'total'   => wc_get_loop_prop( 'total_pages' ),
				'current' => wc_get_loop_prop( 'current_page' ),
				'base'    => esc_url_raw( add_query_arg( 'wpf_page', '%#%', $base ) ),
				'format'  => '?wpf_page=%#%',
			);
			wc_get_template( 'loop/pagination.php', $args );
		}

		echo '</div>';
	}

	/**
	 * Get current page number in a paginated loop
	 *
	 * @return int
	 */
	public static function get_paged() {
		if ( ! empty( $_GET['wpf_page'] ) ) {
			$page = intval( $_GET['wpf_page'] );
		} else {
			if ( is_front_page() ) {
				$page = get_query_var( 'page', 1 );
			} else {
				$page = get_query_var( 'paged', 1 );
			}
		}

		if ( empty( $page ) ) {
			$page = 1;
		}

		return $page;
	}

	/**
	 * Map of term_id => product count from WooCommerce's filterer (respects main product query / layered context when available).
	 *
	 * @param array  $term_ids   Term IDs.
	 * @param string $taxonomy   Taxonomy name.
	 * @param string $query_type and|or (multi-term behaviour within the same taxonomy, per WC layered nav).
	 * @return array<int,int>|null
	 */
	public static function wc_get_filtered_term_product_counts( array $term_ids, $taxonomy, $query_type = 'and' ) {
		if ( ! function_exists( 'wc_get_container' ) || empty( $term_ids ) ) {
			return null;
		}
		$query_type = ( 'or' === strtolower( (string) $query_type ) ) ? 'or' : 'and';
		$class_name = 'Automattic\\WooCommerce\\Internal\\ProductAttributesLookup\\Filterer';
		if ( ! class_exists( $class_name ) ) {
			return null;
		}
		try {
			$filterer = wc_get_container()->get( $class_name );
			if ( ! is_object( $filterer ) || ! method_exists( $filterer, 'get_filtered_term_product_counts' ) ) {
				return null;
			}
			$out = $filterer->get_filtered_term_product_counts( $term_ids, $taxonomy, $query_type );
			if ( ! is_array( $out ) || empty( $out ) ) {
				return null;
			}
			return self::normalize_wc_term_count_map( $term_ids, $out );
		} catch ( \Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			return null;
		}
	}

	/**
	 * @param array $term_ids
	 * @param array $out      Raw return from WC (assoc term_id => count or ordered list).
	 * @return array<int,int>
	 */
	private static function normalize_wc_term_count_map( array $term_ids, array $out ) {
		$map = array();
		foreach ( $term_ids as $tid ) {
			if ( isset( $out[ $tid ] ) ) {
				$map[ $tid ] = (int) $out[ $tid ];
			} elseif ( isset( $out[ (string) $tid ] ) ) {
				$map[ $tid ] = (int) $out[ (string) $tid ];
			}
		}
		if ( ! empty( $map ) ) {
			return $map;
		}
		$vals = array_values( $out );
		if ( count( $vals ) === count( $term_ids ) ) {
			foreach ( $term_ids as $i => $tid ) {
				$map[ $tid ] = (int) $vals[ $i ];
			}
		}
		return $map;
	}

	/**
	 * Replace term->count with WooCommerce faceted counts when possible.
	 *
	 * @param array|WP_Error $terms
	 * @param string         $taxonomy
	 * @param string         $query_type and|or from WPF field logic.
	 * @return array|WP_Error
	 */
	public static function maybe_apply_wc_filtered_counts( $terms, $taxonomy, $query_type ) {
		if ( ! apply_filters( 'wpf_use_woocommerce_filtered_counts', true ) ) {
			return $terms;
		}
		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return $terms;
		}
		$ids = wp_list_pluck( $terms, 'term_id' );
		$counts = self::wc_get_filtered_term_product_counts( $ids, $taxonomy, $query_type );
		if ( empty( $counts ) ) {
			return $terms;
		}
		foreach ( $terms as $t ) {
			if ( isset( $counts[ $t->term_id ] ) ) {
				$t->count = (int) $counts[ $t->term_id ];
			}
		}
		return $terms;
	}

	/**
	 * Strip one facet module from the inbound request array (facet counts ignore that facet).
	 *
	 * @param array  $request
	 * @param array  $layout
	 * @param string $facet_type_to_strip
	 */
	public static function strip_request_fields_for_facets( array $request, array $layout, $facet_type_to_strip ) {
		foreach ( $layout as $type => $item ) {
			if ( 'submit' === $type || $facet_type_to_strip !== $type || ! is_array( $item ) ) {
				continue;
			}
			if ( 'price' === $type ) {
				$name = urldecode( WPF_Utils::strtolower( WPF_Utils::get_field_name( $item, $type ) ) );
				unset( $request[ $name . '-from' ], $request[ $name . '-to' ], $request[ $name ] );
				continue;
			}

			$key_base = urldecode( WPF_Utils::strtolower( WPF_Utils::get_field_name( $item, $type ) ) );
			unset( $request[ $key_base ], $request[ $key_base . '[]' ] );
			foreach ( array_keys( $request ) as $rk ) {
				$rk = (string) $rk;
				if ( $rk === $key_base || 0 === strpos( $rk, $key_base . '[' ) ) {
					unset( $request[ $rk ] );
				}
			}
		}

		return $request;
	}

	/**
	 * When multiple hierarchical terms use AND semantics, Woo expects every term assignment on the same product.
	 * Selecting both parent + child resolves to descendants only so products only in the child still match.
	 *
	 * @param string               $taxonomy e.g. product_cat or pa_*.
	 * @param string[]             $slug_list Term slugs (may include invalid slugs — ignored).
	 * @return string[] Unique slugs to use in tax_query / counts.
	 */
	public static function prune_ancestor_terms_for_and_query( $taxonomy, array $slug_list ) {
		$taxonomy = sanitize_key( $taxonomy );
		if ( empty( $slug_list ) || ! taxonomy_exists( $taxonomy ) || ! is_taxonomy_hierarchical( $taxonomy ) ) {
			return array_values( array_unique( array_filter( array_map( 'sanitize_title', $slug_list ) ) ) );
		}
		$slug_list = array_values( array_unique( array_filter( array_map( 'sanitize_title', $slug_list ) ) ) );
		if ( count( $slug_list ) < 2 ) {
			return $slug_list;
		}
		$term_objs = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'slug'       => $slug_list,
				'hide_empty' => false,
			)
		);
		if ( is_wp_error( $term_objs ) || empty( $term_objs ) ) {
			return $slug_list;
		}
		$by_slug = array();
		foreach ( $term_objs as $t ) {
			$by_slug[ $t->slug ] = $t;
		}

		$keepers = array();
		foreach ( $slug_list as $slug ) {
			if ( empty( $by_slug[ $slug ] ) ) {
				continue;
			}
			$tid  = (int) $by_slug[ $slug ]->term_id;
			$drop = false;
			foreach ( $slug_list as $other_slug ) {
				if ( $other_slug === $slug || empty( $by_slug[ $other_slug ] ) ) {
					continue;
				}
				$other_id = (int) $by_slug[ $other_slug ]->term_id;
				if ( $tid !== $other_id && term_is_ancestor_of( $tid, $other_id, $taxonomy ) ) {
					$drop = true;
					break;
				}
			}
			if ( ! $drop ) {
				$keepers[] = $slug;
			}
		}
		return ! empty( $keepers ) ? array_values( array_unique( $keepers ) ) : $slug_list;
	}

	/**
	 * Whether current faceted catalogue (excluding the on-sale filter) intersects WooCommerce sale metadata.
	 *
	 * @param array $request Incoming $_REQUEST-style params.
	 * @param array $form    Form bundle with layout + data keys.
	 * @return bool
	 */
	public static function faceted_query_has_on_sale_products( array $request, array $form ) {
		if ( empty( $form['layout']['onsale'] ) || ! apply_filters( 'wpf_hide_onsale_when_not_in_faceted_pool', true ) ) {
			return true;
		}
		try {
			$public = WPF_Public::get_instance();
		} catch ( \Throwable $e ) {
			return true;
		}
		if ( empty( $form['layout'] ) ) {
			return true;
		}
		$stripped = self::strip_request_fields_for_facets( $request, $form['layout'], 'onsale' );
		$stripped = self::normalize_request_for_and_facet_pool( $stripped );
		WPF_Public::set_silent_parse_query( true );
		try {
			$query_args = $public->parse_query( $stripped, $form, true );
		} finally {
			WPF_Public::set_silent_parse_query( false );
		}
		if ( empty( $query_args ) || ! is_array( $query_args ) ) {
			return false;
		}
		$query_args['posts_per_page'] = 1;
		$query_args['fields']         = 'ids';
		$query_args['no_found_rows']  = false;
		$query_args['paged']          = 1;
		$query_args['meta_query'][]   = array(
			'relation' => 'OR',
			array(
				'key'     => '_sale_price',
				'value'   => 0,
				'compare' => '>',
				'type'    => 'NUMERIC',
			),
			array(
				'key'     => '_min_variation_sale_price',
				'value'   => 0,
				'compare' => '>',
				'type'    => 'NUMERIC',
			),
		);
		$q     = new WP_Query( $query_args );
		$found = (int) $q->found_posts > 0;
		wp_reset_postdata();
		return $found;
	}

	/**
	 * @var array<string, array<string, mixed>>
	 */
	private static $and_facet_pool_cache = array();

	/**
	 * Drop AJAX / pagination clutter; keep active filters intact for pooled counts.
	 *
	 * @param array<string,mixed> $request
	 * @return array<string,mixed>
	 */
	public static function normalize_request_for_and_facet_pool( array $request ) {
		$normalized = $request;
		unset( $normalized['wpf_ajax'], $normalized['append'] );
		$normalized['wpf_page'] = '1';
		return $normalized;
	}

	/**
	 * @param array $arr
	 */
	private static function recursive_ksort( array &$arr ) {
		foreach ( $arr as &$v ) {
			if ( is_array( $v ) ) {
				self::recursive_ksort( $v );
			}
		}
		ksort( $arr );
	}

	/**
	 * Conditional counts between taxonomies (AND): product pool respects other active facets.
	 * OR fields exclude themselves from the pool so sibling terms stay available.
	 *
	 * @param array<string,mixed> $request
	 * @param array               $form Contains layout & data keys.
	 * @param string              $facet_type Layout key of the field being counted.
	 * @param string              $taxonomy
	 * @param int[]               $term_ids
	 * @return array<int,int>|null
	 */
	public static function get_and_facet_term_counts( array $request, array $form, $facet_type, $taxonomy, array $term_ids ) {
		$budget = absint( apply_filters( 'wpf_and_facet_max_matching_products', 12000 ) );
		if ( $budget < 200 ) {
			$budget = 200;
		}
		if ( empty( $request['wpf'] ) || ! is_scalar( $request['wpf'] ) || empty( $form['layout'] ) || empty( $term_ids ) ) {
			return null;
		}

		try {
			$public = WPF_Public::get_instance();
		} catch ( \Throwable $e ) {
			return null;
		}

		$normalized = self::normalize_request_for_and_facet_pool( $request );
		if ( empty( $normalized['wpf'] ) && ! empty( $request['wpf'] ) && is_scalar( $request['wpf'] ) ) {
			$normalized['wpf'] = sanitize_key( (string) $request['wpf'] );
		}

		$field_logic = 'or';
		if ( ! empty( $form['layout'][ $facet_type ] ) && is_array( $form['layout'][ $facet_type ] )
			&& ! empty( $form['layout'][ $facet_type ]['logic'] ) ) {
			$field_logic = strtolower( (string) $form['layout'][ $facet_type ]['logic'] );
		}
		$strip_current = apply_filters(
			'wpf_and_facet_strip_current_field',
			'and' !== $field_logic,
			$facet_type,
			$field_logic,
			$form,
			$request
		);
		if ( $strip_current ) {
			$normalized = self::strip_request_fields_for_facets( $normalized, $form['layout'], $facet_type );
		}

		$key_bits = $normalized;
		self::recursive_ksort( $key_bits );
		$pool_key = sanitize_key( (string) $request['wpf'] ) . '|' . md5( (string) wp_json_encode( $key_bits ) ) . '|' . $budget;

		if ( isset( self::$and_facet_pool_cache[ $pool_key ] ) ) {
			$bucket = self::$and_facet_pool_cache[ $pool_key ];
			if ( ! empty( $bucket['empty'] ) ) {
				return array_fill_keys( array_map( 'intval', $term_ids ), 0 );
			}
			if ( ! empty( $bucket['over_budget'] ) ) {
				return apply_filters(
					'wpf_and_facet_term_counts_over_budget',
					null,
					$facet_type,
					$taxonomy,
					$term_ids,
					isset( $bucket['found'] ) ? (int) $bucket['found'] : 0,
					$budget,
					$normalized,
					$form
				);
			}
			if ( isset( $bucket['ids'] ) && is_array( $bucket['ids'] ) ) {
				return self::facet_count_terms_among_products_sql(
					array_map( 'intval', $bucket['ids'] ),
					sanitize_text_field( $taxonomy ),
					array_map( 'intval', $term_ids )
				);
			}
		}

		WPF_Public::set_silent_parse_query( true );
		try {
			$query_args = $public->parse_query( $normalized, $form, true );
		} finally {
			WPF_Public::set_silent_parse_query( false );
		}
		if ( empty( $query_args ) || ! is_array( $query_args ) ) {
			return null;
		}

		$query_args['paged'] = 1;
		unset( $query_args['offset'] );

		$force_ppp = static function ( $q ) {
			if ( $q instanceof WP_Query && $q->get( 'wpf_facet_pool' ) ) {
				$ppp = (int) $q->get( 'wpf_facet_pool' );
				if ( $ppp > 0 ) {
					$q->set( 'posts_per_page', $ppp );
					$q->set( 'nopaging', false );
					$q->set( 'offset', '' );
				}
			}
		};
		add_action( 'pre_get_posts', $force_ppp, 999 );
		$list = array();

		try {
			$measure = wp_parse_args(
				array(
					'posts_per_page' => 1,
					'paged'          => 1,
					'fields'         => 'ids',
					'no_found_rows'  => false,
					'wpf_facet_pool' => 1,
				),
				$query_args
			);

			$m     = new WP_Query( $measure );
			$found = (int) $m->found_posts;
			wp_reset_postdata();

			if ( $found < 1 ) {
				self::$and_facet_pool_cache[ $pool_key ] = array( 'empty' => true );
				return array_fill_keys( array_map( 'intval', $term_ids ), 0 );
			}
			if ( $found > $budget ) {
				self::$and_facet_pool_cache[ $pool_key ] = array(
					'over_budget' => true,
					'found'       => $found,
				);
				return apply_filters(
					'wpf_and_facet_term_counts_over_budget',
					null,
					$facet_type,
					$taxonomy,
					$term_ids,
					$found,
					$budget,
					$normalized,
					$form
				);
			}

			$list             = array();
			$post_batch       = apply_filters( 'wpf_and_facet_id_batch_size', 650 );
			$post_batch       = max( 100, absint( $post_batch ) );
			$gather_base      = wp_parse_args(
				array(
					'posts_per_page'         => $post_batch,
					'paged'                  => 1,
					'fields'                 => 'ids',
					'no_found_rows'          => true,
					'update_post_term_cache' => false,
					'update_post_meta_cache' => false,
					'wpf_facet_pool'         => $post_batch,
				),
				$query_args
			);
			unset( $gather_base['offset'] );

			$page_num         = 1;
			$effective_target = min( $found, $budget );
			while ( $page_num <= 250 && count( $list ) < $effective_target ) {
				$page_args = wp_parse_args(
					array(
						'paged'          => $page_num,
						'posts_per_page' => $post_batch,
						'wpf_facet_pool' => $post_batch,
					),
					$gather_base
				);
				unset( $page_args['offset'] );

				$qq           = new WP_Query( $page_args );
				$got_products = empty( $qq->posts ) ? 0 : count( $qq->posts );

				foreach ( array_map( 'intval', $qq->posts ) as $pid ) {
					if ( $pid > 0 ) {
						$list[ $pid ] = 1;
					}
				}
				wp_reset_postdata();
				if ( $got_products < 1 ) {
					break;
				}
				++$page_num;
			}
		} finally {
			remove_action( 'pre_get_posts', $force_ppp, 999 );
		}

		$ids_sorted = array_values( array_map( 'intval', array_keys( $list ) ) );
		self::$and_facet_pool_cache[ $pool_key ] = array( 'ids' => $ids_sorted );
		return self::facet_count_terms_among_products_sql(
			$ids_sorted,
			sanitize_text_field( $taxonomy ),
			array_map( 'intval', $term_ids )
		);
	}

	/**
	 * @param int[]  $product_ids
	 * @param string $taxonomy
	 * @param int[]  $term_ids
	 * @return array<int,int>
	 */
	private static function facet_count_terms_among_products_sql( array $product_ids, $taxonomy, array $term_ids ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return array_fill_keys( array_map( 'intval', $term_ids ), 0 );
		}
		if ( empty( $product_ids ) || empty( $term_ids ) ) {
			return array_fill_keys( array_map( 'intval', $term_ids ), 0 );
		}

		global $wpdb;

		$product_ids = array_values( array_unique( array_filter( array_map( 'intval', $product_ids ) ) ) );
		$term_ids    = array_values( array_unique( array_filter( array_map( 'intval', $term_ids ) ) ) );

		if ( empty( $product_ids ) || empty( $term_ids ) ) {
			return array_fill_keys( $term_ids, 0 );
		}

		$aggregate = array_fill_keys( $term_ids, 0 );

		foreach ( array_chunk( $product_ids, 700 ) as $chunk ) {
			if ( taxonomy_exists( $taxonomy ) && is_taxonomy_hierarchical( $taxonomy ) ) {
				foreach ( $term_ids as $display_tid ) {
					$display_tid      = (int) $display_tid;
					$expanded_term_ids = array( $display_tid );
					$children           = get_term_children( $display_tid, $taxonomy );
					if ( ! is_wp_error( $children ) && ! empty( $children ) ) {
						$expanded_term_ids = array_merge( $expanded_term_ids, array_map( 'intval', $children ) );
					}
					$expanded_term_ids = array_values( array_unique( array_filter( array_map( 'intval', $expanded_term_ids ) ) ) );
					if ( empty( $expanded_term_ids ) ) {
						continue;
					}
					$posts_placeholder = implode( ',', array_fill( 0, count( $chunk ), '%d' ) );
					$terms_placeholder = implode( ',', array_fill( 0, count( $expanded_term_ids ), '%d' ) );

					$sql          = "
				SELECT COUNT(DISTINCT tr.object_id) AS cnt
				FROM {$wpdb->term_relationships} AS tr
				INNER JOIN {$wpdb->term_taxonomy} AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = %s
				WHERE tr.object_id IN ({$posts_placeholder})
				AND tt.term_id IN ({$terms_placeholder})";

					$params       = array_merge( array( $taxonomy ), array_values( $chunk ), array_values( $expanded_term_ids ) );
					$sql_prepared = call_user_func_array( array( $wpdb, 'prepare' ), array_merge( array( $sql ), $params ) );
					$cnt          = (int) $wpdb->get_var( $sql_prepared );
					if ( array_key_exists( $display_tid, $aggregate ) ) {
						$aggregate[ $display_tid ] += $cnt;
					}
				}
				continue;
			}

			$posts_placeholder = implode( ',', array_fill( 0, count( $chunk ), '%d' ) );
			$terms_placeholder = implode( ',', array_fill( 0, count( $term_ids ), '%d' ) );

			$sql = "
				SELECT tt.term_id, COUNT(DISTINCT tr.object_id) AS cnt
				FROM {$wpdb->term_relationships} AS tr
				INNER JOIN {$wpdb->term_taxonomy} AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = %s
				WHERE tr.object_id IN ({$posts_placeholder})
				AND tt.term_id IN ({$terms_placeholder})
				GROUP BY tt.term_id";

			$params       = array_merge( array( $taxonomy ), array_values( $chunk ), array_values( $term_ids ) );
			$sql_prepared = call_user_func_array( array( $wpdb, 'prepare' ), array_merge( array( $sql ), $params ) );
			$rows         = $wpdb->get_results( $sql_prepared, ARRAY_A );

			if ( is_array( $rows ) ) {
				foreach ( $rows as $row ) {
					$tid = (int) $row['term_id'];
					if ( array_key_exists( $tid, $aggregate ) ) {
						$aggregate[ $tid ] += (int) $row['cnt'];
					}
				}
			}
		}

		return $aggregate;
	}

    // clear previous WPF parameters and unsafe query parameter names from $url
    public static function get_unfiltered_url( $url = false ) : string {
		$wpf_parameters = array();
		if ( ! empty( $_GET ) && is_array( $_GET ) ) {
			foreach ( $_GET as $key => $value ) {
				if ( ! is_string( $key ) || ! self::is_safe_query_param_name( $key ) ) {
					continue;
				}
				if ( substr( $key, 0, 3 ) === 'wpf' ) {
					$wpf_parameters[] = $key;
				}
			}
		}

		if ( ! empty( $wpf_parameters ) ) {
			$clean = remove_query_arg( $wpf_parameters, $url );
		} elseif ( false === $url ) {
			$clean = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		} else {
			$clean = $url;
		}

		return self::strip_unsafe_query_param_names( is_string( $clean ) ? $clean : '' );
    }

	/**
	 * Whether a query parameter name is safe to keep in a generated URL.
	 */
	private static function is_safe_query_param_name( $key ) : bool {
		return is_string( $key ) && (bool) preg_match( '/^[A-Za-z0-9_\-\[\]]+$/', $key );
	}

	/**
	 * Drop query pairs whose names can break out of HTML attributes.
	 */
	private static function strip_unsafe_query_param_names( string $url ) : string {
		$qpos = strpos( $url, '?' );
		if ( false === $qpos ) {
			return $url;
		}

		$base  = substr( $url, 0, $qpos );
		$query = substr( $url, $qpos + 1 );
		if ( $query === '' ) {
			return $base;
		}

		$fragment = '';
		$hash     = strpos( $query, '#' );
		if ( false !== $hash ) {
			$fragment = substr( $query, $hash );
			$query    = substr( $query, 0, $hash );
		}

		$kept = array();
		foreach ( explode( '&', $query ) as $pair ) {
			if ( $pair === '' ) {
				continue;
			}
			$eq  = strpos( $pair, '=' );
			$raw = false === $eq ? $pair : substr( $pair, 0, $eq );
			$key = rawurldecode( str_replace( '+', ' ', $raw ) );
			if ( $key === '' || ! self::is_safe_query_param_name( $key ) ) {
				continue;
			}
			$kept[] = $pair;
		}

		if ( empty( $kept ) ) {
			return $base . $fragment;
		}

		return $base . '?' . implode( '&', $kept ) . $fragment;
	}
}