<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 24.06.18
 * Time: 13:33
 */

namespace Log;

use Log\LogInterface\LogInterface;

class LogPost implements LogInterface
{
    public static $DATETIME_FORMAT = 'Y-m-d H:i:s';
    public static $REGISTRATION;
    public static $POST_TYPE;

    public $post_id;
    public $running;
    public $starting_time;
    public $total_time;
    public $data;

    public function __construct($post_id = NULL)
    {
        $this->post_id = $post_id;
    }

    public function start(): void
    {
        $this->running = true;
        $starting_time = date(static::$DATETIME_FORMAT);

        $postarr = array(
            'post_date'         => $starting_time,
            'comment_status'    => 'closed',
            'meta_input'        => array(
                'starting_time'     => $starting_time,
                'running'           => true,
            ),
        );
        if ($this->isFresh()) {
            // in case this is new post creating a new wordpress post
            $postarr['meta_input']['total_time'] = '-';
            $this->post_id = wp_insert_post($postarr);
        } else {
            wp_update_post($this->post_id, $postarr);
        }
    }

    public function getTotalTime()
    {
        return $this->total_time;
    }

    public function getPastEntries(int $number=0)
    {
        // All the entries in case of null, which is also the default
        if ($number==0) {
            return $this->data;
        } else {
            // In case of positive number get from the end on
            $entries = array();
            $range = range(($number > 0 ? -1 : 0), -(1 + $number));
            $range = ($number > 0 ? array_reverse($range) : $range);
            foreach ($range as $index) {
                $entry = $this->data[$index];
                $entries[] = $entry;
            }
            return $entries;
        }
    }

    public function stop(): void
    {
        if ($this->isFresh()) {
            throw new \BadMethodCallException('Cannot stop a newly created log, that is not running');
        } else {
            if ($this->isRunning()) {
                $this->running = false;
                // Loading the old total time
                $old_total_time = get_post_meta($this->post_id, 'total_time', true);
                // Calculating the new total time
                $total_time = strtotime(date(static::$DATETIME_FORMAT)) - strtotime($this->starting_time);
                $args = array(
                    'meta_input' => array(
                        'total_time' => ($old_total_time == '-' ? $total_time : $old_total_time + $total_time),
                        'running'=> false
                    )
                );
                wp_update_post($args);

            } else {
                throw new \BadMethodCallException('Cannot stop a Log, that is not running');
            }
        }
    }

    public function load(): void
    {
        /*
         *
         */
        if ($this->isFresh()) {
            throw new \BadMethodCallException('The Log cannot be loaded without post id given!');
        } else {
            // Loading the meta values
            $this->running = get_post_meta($this->post_id, 'running', true);
            $this->starting_time = get_post_meta($this->post_id, 'starting_time', true);
            $this->total_time = get_post_meta($this->post_id, 'total_time', true);

            // Loading the actual data
            /* @var $term WP_Term */
            $terms = wp_get_post_terms($this->post_id, $this->getDataTaxonomy());
            foreach ($terms as $term) {
                $content = $term->name;
                $this->data[] = $content;
            }
        }
    }

    private function log(string $type, string $message): void
    {
        if ($this->isRunning()) {
            // Assembling the actual message
            $type = strtoupper($type);
            $time = date($this->getDatetimeFormat());
            $content = implode(' ', array($type, $time, $message));
            // Adding the line to the local array
            $this->data[] = $content;
            // Actually saving it as a taxonomy term
            wp_set_object_terms($this->post_id, $content, $this->getDataTaxonomy());
        } else {
            throw new \BadMethodCallException('Cannot log, if the log is not running!');
        }
    }

    private function isRunning(): bool
    {
        return $this->running;
    }

    private function isFresh(): bool
    {
        return ($this->post_id == NULL);
    }

    private function getDatetimeFormat():string
    {
        return static::$DATETIME_FORMAT;
    }

    private function getPostType(): string
    {
        return static::$POST_TYPE;
    }

    private function getDataTaxonomy(): string
    {
        return $this->getPostType() . '_data';
    }

    public function info(string $message)
    {
        $this->log('info', $message);
    }

    public function debug(string $message)
    {
        $this->log('debug', $message);
    }

    public function error(string $message)
    {
        $this->log('error', $message);
    }

    public function warning(string $message)
    {
        $this->log('warning', $message);
    }

    public static function register(string $post_type): void
    {
        /*
         * Here the static member of the class, which will define and be used as the name of the post type throughout
         * the class will be set via a late static binding, so that if another class inherits from this one it can be
         * used as a separate post type.
         */
        static::$POST_TYPE = $post_type;

        $registration = new LogPostRegistration($post_type);
        $registration->register();
        static::$REGISTRATION = $registration;
    }

}