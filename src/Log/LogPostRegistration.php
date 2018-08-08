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
     * Changed 08.08.2018
     * Registering the Ajax method for getting the new logs now
     *
     * @since 0.0.0.0
     *
     */
    public function register() {
        // Register the actual post type with wordpress
        add_action('init', array($this, 'register_post_type'));

        add_action('add_meta_boxes', array($this, 'register_meta_box'));

        add_action('wp_ajax_new_logs', array($this, 'ajax_new_logs'));
        add_action('wp_ajax_no_priv_new_logs', array($this, 'ajax_new_logs'));
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
     * Changed 28.06.2018
     * Added the 'menu_icon' attribute for the post type registration.
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
            'menu_icon'             => 'dashicons-book-alt',
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
     * Ajax callback: Returns all the new log lines when called from a LogPost page within the admin panel
     *
     * CHANGELOG
     *
     * Added 08.08.2018
     *
     * @since 0.0.0.7
     */
    public function ajax_new_logs() {
        $count = $_GET['count'];
        $post_id = $_GET['post'];
        // Getting all the log entries from the post
        $logs = get_post_meta($post_id, 'data', false);
        $new_logs = array_slice($logs, $count, count($logs) - $count);
        echo json_encode($new_logs);
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
     * Changed 08.08.2018 - 0.0.0.7
     * The line number will now be displayed with leading zeros, so that every number has 3 digits. This is to counter
     * the bad formatting from having different length line numbers.
     * Added an additional script element, that schedules a function for every second, which will make an ajax call
     * to wordpress and get all the log lines, that are new, but are not already displayed and add those to the list
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
                <p><span style="color: dimgrey; font-size: 80%; margin-right: 4px;"><?php echo $this->leadingZeros($count); ?></span><?php echo $message; ?></p>
                <?php $count+= 1; ?>
            <?php endforeach; ?>
        </div>
        <script>
            function loadNewLogs() {
                // Getting the last count
                var last_count = jQuery('div.log-meta-wrapper:last-child span').html();
                console.log(last_count);
                var container = jQuery('div.log-meta-wrapper');
                console.log(container);
                jQuery.ajax({
                    url:        ajaxurl,
                    type:       'Get',
                    timeout:    2,
                    dataType:   'html',
                    async:      true,
                    data:       {
                        'action':   'new_logs',
                        'count':    last_count
                    },
                    error:      function(response) {
                        console.log(response);
                    },
                    success:    function(response) {
                        console.log(response);
                        var new_logs = JSON.parse(response.slice(0, -1));
                        console.log(new_logs);
                        var count = last_count + 1;
                        new_logs.forEach(function (log) {
                            var element = jQuery(String("<p><span style=\"color: dimgrey; font-size: 80%; margin-right: 4px;\">{0}</span>{1}</p>").format(count, log));
                            console.log(element);
                            container.append(element);
                            count += 1;
                        })
                    }
                });
                setTimeout(loadNewLogs, 2);
            }
            loadNewLogs();
        </script>
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

    /**
     * Adds leading zeros to a integer to return a string of the integer with the requested length
     *
     * CHANGELOG
     *
     * Added 08.08.2018
     *
     * @since 0.0.0.7
     *
     * @param int $count    the number to add the zeros to
     * @param int $length   the total length to be achieved with the zeros
     * @return string
     */
    private function leadingZeros(int $count, $length=4): string {
        $count_string = (string)$count;
        $length_difference = $length - strlen($count_string);
        if ($length_difference > 0) {
            return str_repeat('0', $length_difference) + $count_string;
        } else {
            return $count_string;
        }
    }

}