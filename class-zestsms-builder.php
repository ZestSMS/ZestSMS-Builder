<?php

add_action('updated_postmeta', 'ZestSMSBuilder::save_fl_builder_data', 10, 4 );
add_filter('fl_builder_settings_form_defaults', 'ZestSMSBuilder::set_defaults', 10, 2);

final class ZestSMSBuilder {

	static public function set_defaults( $defaults, $form_type ) {
		global $post;

		$modules = FLBuilderModel::$modules;
		$all_fields = self::find_fields($modules, 'defaults');

		if(is_array($all_fields)) {
			foreach($all_fields as $parent_form => $types) {
				foreach($types as $type => $fields) {
					foreach($fields as $field => $default) {
						if($parent_form == $form_type) {
							if($type == 'meta') {
								$defaults->$field = get_post_meta($post->ID, $default['key'], true);
							}
							if($type == 'taxonomy') {
								$defaults->$field = wp_get_post_terms($post->ID, $default['key'], array("fields" => "ids"));
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

		if(!is_null($modules)) {
			foreach($modules as $module) {
				foreach($module->form as $tabs => $tab_args) {
					if(!is_array($tab_args['sections'])) continue;
					foreach($tab_args['sections'] as $section => $section_args) {
						if(!is_array($section_args['fields'])) continue;
						foreach($section_args['fields'] as $field => $field_args) {
							if($field_args['meta'] || $field_args['taxonomy']) {
								if($field_args['meta']) {
									$type = 'meta';
									$key = ($field_args['meta']['key']) ? $field_args['meta']['key'] : $field;
									$val = ($field_args['meta']['value']) ? $field_args['meta']['value'] : $module->settings->$field;
								}
								if($field_args['taxonomy']) {
									$type = 'taxonomy';
									$key = $field_args['taxonomy'];
									$val = $module->settings->$field;
								}

								// Update the database
								$fields[$module->node][$type][$field] = array(
									'key'	=> $key,
									'val'	=> $val
								);
								// Set defaults
								$fields['defaults'][$module->slug . '-module'][$type][$field] = array(
									'key' => $key,
									'multiple' => ($field_args['multiple']) ? false : true
								);
							}
						}
					}
				}
			}
		}

		if($return == 'defaults') {
			return $fields['defaults'];
		} else {
			unset($fields['defaults']);
			return $fields;
		}
	}

	static public function save_fl_builder_data( $meta_id, $post_id, $meta_key, $meta_value ){

		if('_fl_builder_data' === $meta_key) {
			$modules = FLBuilderModel::get_all_modules();
			$nodes = self::find_fields($modules);

			if(!empty($nodes)) {
				$draft = maybe_unserialize(get_post_meta($post_id, '_fl_builder_draft', true));
				$meta_value = maybe_unserialize( $meta_value );
				foreach($nodes as $node_id => $types) {
				 	foreach($types as $type => $fields) {
						foreach($fields as $field => $args) {
							if('fl-builder-template' !== get_post_type($post_id)) {
								if($type == 'meta') {
									update_post_meta($post_id, $args['key'], $args['val']);
								}
								if($type == 'taxonomy') {
									wp_set_post_terms($post_id, $args['val'], $args['key']);
								}
							}

							if(isset($meta_value[$node_id]->settings->$field)) unset($meta_value[$node_id]->settings->$field);
							if(isset($draft[$node_id]->settings->$field)) unset($draft[$node_id]->settings->$field);
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
