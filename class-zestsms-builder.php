<?php

add_action('updated_postmeta', 'ZestSMSBuilder::save_fl_builder_data', 10, 4 );
add_filter('fl_builder_settings_form_defaults', 'ZestSMSBuilder::set_defaults', 10, 2);

final class ZestSMSBuilder {

	static public function set_defaults( $defaults, $form_type ) {
		global $post;

		$modules = FLBuilderModel::$modules;
		$all_field_meta = self::find_meta_fields($modules, 'defaults');

		if(array_key_exists($form_type, $all_field_meta)) {
			foreach($all_field_meta[$form_type] as $field => $key) {
				if($field_meta = get_post_meta($post->ID, $key, true)) {
					$defaults->$field = $field_meta;
				}
			}
		}

		return $defaults;
	}

	static public function find_meta_fields( $modules = null, $return = 'meta' ) {
		$meta = array();

		if(!is_null($modules)) {
			foreach($modules as $module) {
				foreach($module->form as $tabs => $tab_args) {
					if(!is_array($tab_args['sections'])) continue;
					foreach($tab_args['sections'] as $section => $section_args) {
						if(!is_array($section_args['fields'])) continue;
						foreach($section_args['fields'] as $field => $field_args) {
							if($field_args['meta']) {
								$key = (!is_bool($field_args['meta'])) ? $field_args['meta'] : $field;
								if($return == 'meta') {
									$meta[$module->node][$field] = array(
										'key'	=> $key,
										'val'	=> $module->settings->$field
									);
								} else if($return == 'defaults') {
									$meta[$module->slug . '-module'][$field] = $key;
								}
							}
						}
					}
				}
			}
		}

		return $meta;
	}

	static public function save_fl_builder_data( $meta_id, $post_id, $meta_key, $meta_value ){

		if('_fl_builder_data' === $meta_key) {
			$modules = FLBuilderModel::get_all_modules();
			$meta = self::find_meta_fields($modules, 'meta');

			if(!empty($meta)) {
				$draft = maybe_unserialize(get_post_meta($post_id, '_fl_builder_draft', true));
				$meta_value = maybe_unserialize( $meta_value );
				foreach($meta as $node_id => $fields) {
					foreach($fields as $field => $args) {
						update_post_meta($post_id, $args['key'], $args['val']);
						unset($meta_value[$node_id]->settings->$field);
						unset($draft[$node_id]->settings->$field);
					}
				}
				update_post_meta( $post_id, $meta_key, $meta_value );
				update_post_meta( $post_id, '_fl_builder_draft', $draft );
			}

		}
	}

}

?>
