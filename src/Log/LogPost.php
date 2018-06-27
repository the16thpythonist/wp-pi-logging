<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 24.06.18
 * Time: 13:33
 */

namespace Log;

use Log\LogInterface;


/**
 * A class to be used as a log, which maps the persistent storage of log files as wordpress posts
 *
 * @author Jonas Teufel <jonseb1998@gmail.com>
 *
 * @package Log
 * @since 0.0.0.0
 *
 *
 */
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

    /**
     * LogPost constructor.
     *
     * CHANGELOG
     *
     * Added 24.06.2018
     *
     * @param int $post_id OPTIONAL if given a wordpress post id, this log post will be loaded and continued
     */
    public function __construct($post_id = NULL)
    {
        $this->post_id = $post_id;
        if (!$this->isFresh()) {
            $this->load();
        }
    }

    /**
     * Starts the logging functionality
     *
     * This function starts the logging functionality, which means if this is a new log a wordpress post will be created
     * and if this is the continuation of an existing log, the meta data and publishing data of that post will be
     * updated
     *
     * CHANGELOG
     *
     * Added 24.06.2018
     *
     * @since 0.0.0.0
     *
     * @returns void
     */
    public function start()
    {
        $this->running = true;
        $starting_time = date(static::$DATETIME_FORMAT);

        $postarr = array(
            'post_type'         => $this->getPostType(),
            'post_date'         => $starting_time,
            'post_author'       => 5,
            'post_status'       => 'publish',
            'post_title'        => 'test',
            'post_content'      => 'test',
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

    /**
     * returns the total time the log was active in seconds
     *
     * CHANGELOG
     *
     * Added 24.06.2018
     *
     * @since 0.0.0.0
     *
     * @return string the total amount of seconds for which the log was active
     */
    public function getTotalTime()
    {
        return $this->total_time;
    }

    /**
     * returns the last few entries of the log
     *
     * Returns the given amount of past entries, when given a positive number. When given a negative number, the given
     * amount of entries is returned, although starting at the beginning. Negative numbers returns the first few
     * entries of the log
     *
     * CHANGELOG
     *
     * Added 24.06.2018
     *
     * @param int $number
     *
     * @since 0.0.0.0
     *
     * @return array
     */
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

    /**
     * halts the logging functionality
     *
     * In case the log is currently running, it will be stopped. The total time, for which the log ran will be
     * calculated and then saved to the wordpress post. Also updates the running flag in the wordpress post to be false
     *
     * CHANGELOG
     *
     * Added 24.06.2018
     *
     * @throws \BadMethodCallException when the method is invokes, while the log has not even been started yet
     *
     * @since 0.0.0.0
     *
     * @returns void
     */
    public function stop()
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
                    'ID'         => $this->post_id,
                    'meta_input' => array(
                        'total_time'    => ($old_total_time == '-' ? $total_time : $old_total_time + $total_time),
                        'running'       => false,
                    )
                );
                $post_id = wp_update_post($args);
                var_dump($post_id);

            } else {
                throw new \BadMethodCallException('Cannot stop a Log, that is not running');
            }
        }
    }

    /**
     * loads the meta data and log messages from a past log
     *
     * If a wordpress id has been given during the construction of the object, the data saved in this post will be
     * used to load the saved log messages into the local array.
     *
     * This function will be used by the constructor in case a wordpress id has been passed, but using it again will
     * not break the object.
     *
     * CHANGELOG
     *
     * Added 24.06.2018
     *
     * Changed 27.06.2018
     * The log messages are now not longer stored in custom tax. terms, but in post meta elements and thus they will
     * now be loaded from the post meta as well.
     *
     * @since 0.0.0.0
     *
     * @throws \BadMethodCallException if the message is invoked without a wordpress id been specified in constructor
     */
    public function load()
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

            /*
             * Loading the log messages as an array of post meta elements all with the same key 'data'.
             * The false parameter here signals, that we want to get an array of values here instead of singular ones
             * like the post meta above.
             */
            $terms = get_post_meta($this->post_id, 'data', false);
            foreach ($terms as $term) {
                $this->data[] = $term;
            }
        }
    }

    /**
     * Saves a log message of arbitrary type to the log
     *
     * CHANGELOG
     *
     * Added 24.06.2018
     *
     * Changed 25.06.2018
     * Now passing the content of the log line as the only argument of an array to the wp set object terms function,
     * because before when passing it as string there was the bug, that if the line contained commas it would be split
     * by those and the parts being interpreted as separate terms.
     *
     * Changed 27.06.2018
     * The log messages are no longer saved as terms to a custom taxonomy, but are saved as post meta elements
     *
     * @param string $type
     * @param string $message
     *
     * @since 0.0.0.0
     */
    private function log(string $type, string $message)
    {
        if ($this->isRunning()) {
            // Assembling the actual message
            $type = strtoupper($type);
            $time = date($this->getDatetimeFormat());
            $content = implode(' ', array($type, $time, $message));
            // Adding the line to the local array
            $this->data[] = $content;

            /*
             * In wordpress there are two different possibilities of saving array like data in association with a post.
             * This will be using the technique of saving each array item, in this case log post as a separate meta
             * value of the post, but all will be using the same key. When calling the get for this meta key the whole
             * array of all those elements will be returned.
             *
             * The last false parameter is the key to this, because that is the $unique parameter. If it is true, there
             * can only be a single element per key.
             */
            add_post_meta($this->post_id, 'data', $content, false);
        } else {
            throw new \BadMethodCallException('Cannot log, if the log is not running!');
        }
    }

    /**
     * whether the log is currently running or not
     *
     * CHANGELOG
     *
     * Added 24.06.2018
     *
     * @since 0.0.0.0
     *
     * @return bool
     */
    private function isRunning(): bool
    {
        return $this->running;
    }

    /**
     * Whether a post id has been passed to the constructor
     *
     * CHANGELOG
     *
     * Added 24.06.2018
     *
     * @since 0.0.0.0
     *
     * @return bool
     */
    private function isFresh(): bool
    {
        return ($this->post_id == NULL);
    }

    /**
     * Returns the date time format to be used for all time/string operations in this class
     *
     * CHANGELOG
     *
     * Added 24.06.2018
     *
     * @since 0.0.0.0
     *
     * @return string
     */
    private function getDatetimeFormat():string
    {
        return static::$DATETIME_FORMAT;
    }

    /**
     * Returns the wordpress post type name chosen for this class in the register method
     *
     * CHANGELOG
     *
     * Added 24.06.2018
     *
     * @see LogPost::register()
     *
     * @return string
     */
    private function getPostType(): string
    {
        return static::$POST_TYPE;
    }

    /**
     * Returns the taxonomy name, that is used to store the log messages
     *
     * In the first version of this class an additional taxonomy was registered for the log post type and the actual
     * log messages would be saved as individual taxonomy terms.
     * The idea was dismissed after the first tests, as the inserting of each term took more than one second, which
     * is unacceptable for a mere logging process. The second disadvantage was that even after deleting a log post the
     * used terms ie log messages still remained as terms of this taxonomy and would overflow the wordpress database
     *
     * CHANGELOG
     *
     * Added 24.06.2018
     *
     * Deprecated 27.06.2018
     *
     * @since 0.0.0.0
     * @deprecated 0.0.0.1
     *
     * @return string
     */
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

    /**
     * Registers all the wordpress functionality
     *
     * @see LogPostRegistration this class actually contains all the functionality to register the wordpress post type
     *
     * @param string $post_type The internal wordpress id/name this post type is supposed to have
     *
     * @since 0.0.0.0
     */
    public static function register(string $post_type)
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