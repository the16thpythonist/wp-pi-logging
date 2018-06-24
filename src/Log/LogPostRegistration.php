<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 24.06.18
 * Time: 15:33
 */

namespace Log\LogInterface;


class LogPostRegistration
{
    public $post_type;
    public $label;

    public function __construct(string $post_type, string $label='Log') {
        $this->post_type = $post_type;
        $this->label = $label;
    }

    public function register() {
        // Register the actual post type with wordpress
        $args = array(
            'label'                 => $this->label,
            'description'           => 'This post type describes a "Log" data model. The logs contain information about 
                                        different stages of internal workings of wordpress, possible debug and error info...',
            'public'                => true,
            'exclude_from_search'   => true,
            'publicly_queryable'    => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'show_in_nav_menus'     => false,
            'menu_position'         => 10,
            'taxonomies'            => array(
                $this->getDataTaxonomy(),
            ),
            'has_archive'           => false,
            'map_meta_cap'          => true,
            'supports'              => array(
                'title',
                'editor',
                'custom-fields'
            )

        );
        register_post_type(
            $this->post_type,
            $args
        );

        // Register the taxonomy
        $args = array(
            'description'           => 'This taxonomy is affiliated with a log CPT and it will actually store all the 
                                        lines ever written to a log post as terms',
            'public'                => true,
        );
        register_taxonomy(
            $this->getDataTaxonomy(),
            $this->post_type,
            $args
        );
    }

    public function getDataTaxonomy(): string
    {
        return $this->post_type . '_data';
    }

}