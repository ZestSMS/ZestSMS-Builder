# ZestSMS-Builder
Create post_meta or set terms for a post in a Beaver Builder module.

## Usage
Include class-zestsms-builder.php before your module and add a 'meta' attribute to your field settings array. 'meta' can be a string to use the field name as the meta key or a string for a custom key.
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
  'meta'    => 'my-meta-key'
)
```
