<?php

add_action( 'updated_postmeta', 'ZestSMSBuilder::save_fl_builder_data', 10, 4 );
add_filter( 'fl_builder_settings_form_defaults', 'ZestSMSBuilder::set_defaults', 10, 2 );

final class ZestSMSBuilder {

	static public function set_defaults( $defaults, $form_type ) {
		global $post;

		$modules    = FLBuilderModel::$modules;
		$all_fields = self::find_fields( $modules, 'defaults' );

		if ( is_array( $all_fields ) ) {
			foreach ( $all_fields as $parent_form => $types ) {
				foreach ( $types as $type => $fields ) {
					foreach ( $fields as $field => $default ) {
						if ( $parent_form == $form_type ) {
							if ( $type == 'meta' ) {
								$defaults->$field = get_post_meta( $post->ID, $default['key'], true );
							}
							if ( $type == 'taxonomy' ) {
								$defaults->$field = wp_get_post_terms( $post->ID, $default['key'], array( "fields" => "ids" ) );
							}
							if ( $type == 'wp_option' ) {
								$defaults->$field = get_option( $default['key'] );
							}
						}
					}
				}
			}
		}

		return $defaults;
	}

	static public function find_fields( $modules = null, $return = null ) {
		$fields = array();

		if ( ! is_null( $modules ) ) {
			foreach ( $modules as $module ) {
				foreach ( $module->form as $tabs => $tab_args ) {
					if ( isset( $tab_args['sections'] ) ) {
						if ( ! is_array( $tab_args['sections'] ) ) {
							continue;
						}
						foreach ( $tab_args['sections'] as $section => $section_args ) {
							if ( isset( $section_args['fields'] ) ) {
								if ( ! is_array( $section_args['fields'] ) ) {
									continue;
								}
								foreach ( $section_args['fields'] as $field => $field_args ) {
									if ( isset( $field_args['meta'] ) || isset( $field_args['taxonomy'] ) || isset( $field_args['wp_option'] ) ) {
										if ( isset( $field_args['meta'] ) || isset( $field_args['wp_option'] ) ) {
											$type         = ( isset( $field_args['meta'] ) ) ? 'meta' : 'wp_option';
											$key          = ( isset( $field_args[ $type ]['key'] ) ) ? $field_args[ $type ]['key'] : $field;
											$val          = '';
											$save_to_post = ( $field_args[ $type ]['save_to_post'] ) ? true : false;
											if ( $save_to_post ) {
												$save_to_post = ( isset( $field_args[ $type ]['save_to_post']['key'] ) ) ? $field_args[ $type ]['save_to_post']['key'] : $field_args[ $type ]['key'];
											}

											if ( isset( $field_args[ $type ]['value'] ) ) {
												$val = $field_args[ $type ]['value'];
											} else {
												if ( is_object( $module->settings ) ) {
													$val = $module->settings->$field;
												}
											}
										}
										if ( isset( $field_args['taxonomy'] ) ) {
											$type = 'taxonomy';
											$key  = $field_args['taxonomy'];
											if ( is_object( $module->settings ) ) {
												$val = $module->settings->$field;
											}
										}

										// Update the database
										$fields[ $module->node ][ $type ][ $field ] = array(
											'key'          => $key,
											'val'          => $val,
											'save_to_post' => $save_to_post
										);
										// Set defaults
										$fields['defaults'][ $module->slug . '-module' ][ $type ][ $field ] = array(
											'key'      => $key,
											'multiple' => ( isset( $field_args['multiple'] ) ) ? false : true
										);
									}
								}
							}
						}
					}
				}
			}
		}

		if ( isset( $fields['defaults'] ) ) {
			if ( $return == 'defaults' ) {
				return $fields['defaults'];
			} else {
				unset( $fields['defaults'] );

				return $fields;
			}
		}

		return false;

	}

	static public function save_fl_builder_data( $meta_id, $post_id, $meta_key, $meta_value ) {

		if ( '_fl_builder_data' === $meta_key ) {
			$modules = FLBuilderModel::get_all_modules();
			$nodes   = self::find_fields( $modules );

			if ( ! empty( $nodes ) ) {
				$draft      = maybe_unserialize( get_post_meta( $post_id, '_fl_builder_draft', true ) );
				$meta_value = maybe_unserialize( $meta_value );
				foreach ( $nodes as $node_id => $types ) {
					foreach ( $types as $type => $fields ) {
						foreach ( $fields as $field => $args ) {
							if ( 'fl-builder-template' !== get_post_type( $post_id ) ) {
								if ( $type == 'meta' ) {
									if ( isset( $args['save_to_post'] ) ) {
										$key               = $args['save_to_post'];
										$new_save_to_posts = $args['val'];
										$old_save_to_posts = get_post_meta( $post_id, $args['key'], $args['val'] );
										if ( ! $old_save_to_posts ) {
											$old_save_to_posts = array();
										}
										if ( ! $new_save_to_posts ) {
											$new_save_to_posts = array();
										}

										// Loop through old posts, unset if removed
										if ( $compare_to_old = array_diff( $old_save_to_posts, $new_save_to_posts ) ) {
											foreach ( $compare_to_old as $id ) {
												if ( $post_meta = get_post_meta( $id, $key, true ) ) {

													$remove_key = array_search( $post_id, $post_meta );
													unset( $post_meta[ $remove_key ] );

													update_post_meta( $id, $key, $post_meta );
												}
											}
										}

										// Loop through updated posts, set new ones
										if ( $compare_to_new = array_diff( $new_save_to_posts, $old_save_to_posts ) ) {
											foreach ( $compare_to_new as $id ) {
												$post_meta   = array();
												$post_meta   = get_post_meta( $id, $key, true );
												$post_meta[] = $post_id;

												update_post_meta( $id, $key, $post_meta );
											}
										}
									}

									update_post_meta( $post_id, $args['key'], $args['val'] );
								}
								if ( $type == 'taxonomy' ) {
									wp_set_post_terms( $post_id, $args['val'], $args['key'] );
								}
								if ( $type == 'wp_option' ) {
									update_option( $args['key'], $args['val'] );
								}
							}

							if ( isset( $meta_value[ $node_id ]->settings->$field ) ) {
								unset( $meta_value[ $node_id ]->settings->$field );
							}
							if ( isset( $draft[ $node_id ]->settings->$field ) ) {
								unset( $draft[ $node_id ]->settings->$field );
							}
						}
					}
				}

				update_post_meta( $post_id, $meta_key, $meta_value );
				update_post_meta( $post_id, '_fl_builder_draft', $draft );
			}

		}
	}

}

?>
