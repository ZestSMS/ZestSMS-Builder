# ZestSMS-Builder
Create post_meta or set terms for a post in a Beaver Builder module. Include this to your plugin and include before your module files.

## Usage
__Please view examples.php__

To create post_meta, pass an array with keys or key/value pairs. If only keys are supplied, the value of that Beaver Builder module field will be used.

To set terms, pass an array with keys of Beaver Builder fields where a taxonomy is set (like the suggest field).