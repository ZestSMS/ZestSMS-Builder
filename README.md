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
