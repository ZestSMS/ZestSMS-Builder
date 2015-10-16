<?php

class ZestSMSExampleModule extends FLBuilderModule {
    public function __construct() {
        parent::__construct(array(
            'name'          => __('Name / Title', 'fl-builder'),
            'description'   => __('Display name and title for a team member', 'fl-builder'),
            'category'		=> __('Custom Modules', 'fl-builder'),
            'dir'           => ZESTSMS_MODULES_DIR . 'title/',
            'url'           => ZESTSMS_MODULES_URL . 'title/'
        ));
    }

    public function update( $settings ) {
        $settings = ZestSMSBuilder::create_post_meta($settings, array(
            'example', // meta_key = example, meta_value = (value of 'example' field)
            'a-test' => 'testing' // meta_key = a-test, meta_value = testing
            'zestsms_test' => $settings->example // meta_key = zestsms_test, meta_value = (value of 'example' field)
        ));

        $settings = ZestSMSBuilder::set_terms($settings, array(
        		'taxonomy'
        ));

        return $settings;
    }
}

FLBuilder::register_module('ZestSMSExampleModule', array(
	'general'       => array( // Tab
        'title'         => __('General', 'fl-builder'), // Tab title
        'sections'      => array( // Tab Sections
            'title'    => array(
                'title'         => __('Example'),
                'fields'        => array(
                    'example'      => array(
                        'type'      => 'text',
                        'label'     => __('Example Text')
                    ),
                    'taxonomy'		 => array(
                    		'type'			=> 'suggest',
                    		'label'			=> __('Post Taxonomy'),
                    		'action'    => 'fl_as_posts',
    										'data'      => 'posts'
                    )
                )
            )
        )
    )
));