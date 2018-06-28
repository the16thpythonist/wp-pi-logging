<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 24.06.18
 * Time: 15:33
 */

namespace Log;

/**
 * Registers the log CPT with wordpress
 *
 * @see LogPost
 *
 * @author Jonas Teufel <jonseb1998@gmail.com>
 *
 * @package Log
 * @since 0.0.0.0
 */
class LogPostRegistration
{
    public $post_type;
    public $label;

    /**
     * LogPostRegistration constructor.
     *
     * CHANGELOG
     *
     * Added 24.06.2018
     *
     * @since 0.0.0.0
     *
     * @param string $post_type the name of the new post type to be registered with wordpress
     * @param string $label     the label of this new post type, to be displayed int the admin dashboard section
     */
    public function __construct(string $post_type, string $label='Log') {
        $this->post_type = $post_type;
        $this->label = $label;
    }

    /**
     * Registers the log post type with wordpress
     *
     * CHANGELOG
     *
     * Changed 24.06.2018 - 0.0.0.0
     * Previously the 'register_post_type' and 'register_taxonomy' functions were directly called in this methid, which
     * is obviously not possible. Those calls have been moved to methods and these methods were hooked into the
     * wordpress init instead.
     *
     * Changed 27.06.2018
     * Removed the deprecated method 'register_taxonomy' from being registered with wordpress, as the log messages are
     * no longer being stored as taxonomy terms.
     *
     * @since 0.0.0.0
     *
     */
    public function register() {
        // Register the actual post type with wordpress
        add_action('init', array($this, 'register_post_type'));

        add_action('add_meta_boxes', array($this, 'register_meta_box'));
    }

    /**
     * Registers the actual post type with wordpress
     *
     * CHANGELOG
     *
     * Added 24.06.2018
     *
     * Changed 27.06.2018
     * Removed the 'custom post meta' from the array of supported widgets in the edit screen within the admin area,
     * because since the log messages are now being saved as post meta data, this widget will get really crowded.
     *
     * @since 0.0.0.0
     */
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
            )

        );
        register_post_type(
            $this->post_type,
            $args
        );
    }

    /**
     * Registers the taxonomy to be used for storing the log messages with wordpress
     *
     * CHANGELOG
     *
     * Added 24.06.2018
     *
     * Deprecated 27.06.2018
     *
     * @since 0.0.0.0
     * @deprecated 0.0.0.1
     */
    public function register_data_taxonomy()
    {
        $args = array(
            'labels'                => array(
                'name'      => 'Data',
            ),
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

    /**
     * Registers the metabox for displaying the log messages with wordpress
     *
     * CHANGELOG
     *
     * Added 24.06.2018
     *
     * @since 0.0.0.0
     */
    public function register_meta_box() {
        add_meta_box(
            $this->post_type . '-meta',
            'Log Data',
            array($this, 'meta_box_callback'),
            $this->post_type,
            'normal',
            'high'
        );
    }

    /**
     * The callback to actually display the HTML code for the log post metabox
     *
     * CHANGELOG
     *
     * Added 24.06.2018
     *
     * Changed 27.06.2018
     * Previously the log messages to be displayed were being fetched by getting all the custom taxonomy terms for
     * for that post type, but since the log messages are now being stored in log meta, the log meta array will be
     * used as log messages to display
     *
     * Changed 28.06.2018
     * Added an additional span element before each message, which displays the line number
     *
     * @see LogPostRegistration::getLog() returns all the log messages for the given log post
     *
     * @since 0.0.0.0
     *
     * @param WP_Post $post the post object for the post that is currently being edited
     */
    public function meta_box_callback($post) { ?>
        <div class="log-meta-wrapper">
            <?php
            $log = $this->getLog($post);
            $count = 0;
            foreach ($log as $message):
            ?>
                <?php $line = $count + ' ' * (5 - strlen(strval($count))); ?>
                <p><span style="color: dimgrey; font-size: 80%"><?php echo $line; ?></span><?php echo $message; ?></p>
                <?php $count+= 1; ?>
            <?php endforeach; ?>
        </div>
    <?php }

    public function getDataTaxonomy(): string
    {
        return $this->post_type . '_data';
    }

    /**
     * Returns the complete log message array for the given log post object
     *
     * @since 0.0.0.3
     *
     * @param WP_Post $post the log post object fro which to get the log messages
     * @return array the array of log messages
     */
    private function getLog($post) {
        $post_id = $post->ID;
        return get_post_meta($post_id, 'data', false);
    }

}