# ZestSMS-Builder
Create post_meta or set terms for a post in a Beaver Builder module.

## Usage
Include class-zestsms-builder.php before your module.

### Meta Data
Add the 'meta' attribute to your field settings array. 'meta' can be a an array to use the field name as the meta key or a string for a custom key.
```
'my_field'    => array(
  'type'    => 'text',
  'label'   => __('My Field'),
  'meta'    => true
)
```
Or
```
'my_field'    => array(
  'type'    => 'text',
  'label'   => __('My Field'),
  'meta'    => array(
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
