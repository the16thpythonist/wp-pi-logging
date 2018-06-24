<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 24.06.18
 * Time: 15:33
 */

namespace Log;


class LogPostRegistration
{
    public $post_type;
    public $label;

    public function __construct(string $post_type, string $label='Log') {
        $this->post_type = $post_type;
        $this->label = $label;
    }

    /**
     * CHANGELOG
     *
     * Changed 24.06.2018 - 0.0.0.0
     *
     * Previously the 'register_post_type' and 'register_taxonomy' functions were directly called in this methid, which
     * is obviously not possible. Those calls have been moved to methods and these methods were hooked into the
     * wordpress init instead
     *
     */
    public function register() {
        // Register the actual post type with wordpress
        add_action(
            'init',
            array(
                $this,
                'register_post_type'
            )
        );

        // Register the taxonomy
        add_action(
            'init',
            array(
                $this,
                'register_data_taxonomy'
            )
        );
    }

    public function register_post_type()
    {
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
    }

    public function register_data_taxonomy()
    {
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