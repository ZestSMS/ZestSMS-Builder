# ZestSMS-Builder
Create post_meta or set terms for a post in a Beaver Builder module.

## Usage
Include class-zestsms-builder.php before your module.

### Meta Data
Add a 'meta' attribute to your field settings array. 'meta' can be boolean or an array. If 'meta' is true, the meta key will be the name of the field -- 'my_field' in the example below.
```
'my_field'    => array(
  'type'    => 'text',
  'label'   => __('My Field'),
  'meta'    => true
)
```
Or set a custom key with an array:
```
'my_field'    => array(
  'type'    => 'text',
  'label'   => __('My Field'),
  'meta'    => array(
    'key'     => 'my-field-key'
  )
)
```
#### You can now save meta directly to posts on fields that save post_ids (good for bidirectional saving between BB and ACF):
```
'my_posts_field'	=> array(
	'type'	=> 'select',
	'label'	=> __('My Posts'),
	'options' => array(
		'1' => __('Page ID 1'),
		'2' => __('Page ID 2'),
		'3' => __('Page ID 3')
	),
	'multiple'	=> true,
	'meta'	=> array(
		'key'	=> 'my-field-key',
		'save_to_post'	=> array(
			'key'	=> 'my-posts-meta'
		)
	)
)
```
This will allow you to keep updates synced between posts. You can use the code below in your theme/plugin to update the from ACF to BB:
```
function zestsms_bidirectional_acf_update_value( $value, $post_id, $field ) {
	global $post;

	// vars
	$field_name  = $field['name'];
	$field_key   = $field['key'];
	$global_name = 'is_updating_' . $field_name;

	// bail early if this filter was triggered from the update_field() function called within the loop below
	// - this prevents an inifinte loop
	if ( ! empty( $GLOBALS[ $global_name ] ) ) {
		return $value;
	}


	// set global variable to avoid inifite loop
	// - could also remove_filter() then add_filter() again, but this is simpler
	$GLOBALS[ $global_name ] = 1;

	$old_value = get_post_meta( $post_id, $field_name, true );
	if ( ! $old_value ) {
		$old_value = array();
	}
	if ( ! $value ) {
		$value = array();
	}

	if ( is_array( $value ) ) {
		if ( $compare_to_old = array_diff( $old_value, $value ) ) {
			foreach ( $compare_to_old as $id ) {
				$post_meta = get_post_meta( $id, 'team_member', true );

				$remove_key = array_search( $post_id, $post_meta );
				unset( $post_meta[ $remove_key ] );

				update_post_meta( $id, 'team_member', $post_meta );
			}
		}

		if ( $compare_to_new = array_diff( $value, $old_value ) ) {
			foreach ( $compare_to_new as $id ) {
				$post_meta   = get_post_meta( $id, 'team_member', true );
				$post_meta[] = $post_id;

				update_post_meta( $id, 'team_member', $post_meta );
			}
		}
	}


	// reset global varibale to allow this filter to function as per normal
	$GLOBALS[ $global_name ] = 0;


	// return
	return $value;

}

add_filter( 'acf/update_value/name=related_industry', 'zestsms_bidirectional_acf_update_value', 10, 3 );
```

### WP Option
You can also save to the wp_options table which is helpful for modules that are used on multiple pages with different options set. Like the 'meta' attribute, 'wp_option' can be boolean or an array. If 'wp_option' is true, the meta key will be the name of the field -- 'my_field' in the example below.
```
'my_field'    => array(
  'type'    => 'text',
  'label'   => __('My Field'),
  'wp_option'    => true
)
```
Or set a custom key with an array:
```
'my_field'    => array(
  'type'    => 'text',
  'label'   => __('My Field'),
  'wp_option'    => array(
    'key'     => 'my-field-key'
  )
)
```

### Taxonomy
Add the 'taxonomy' attribute to your field settings array. 'taxonomy' should be a string. The value of the field should be a comma separated list of taxonomy IDs. Works great with my [select2 field](https://github.com/ZestSMS/BB-fields/tree/master/fields/select2)
```
'my_field'    => array(
  'type'    => 'text',
  'label'   => __('My Field'),
  'taxonomy'=> 'my-taxonomy'
)
```
