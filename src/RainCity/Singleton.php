<?php
declare(strict_types=1);
namespace RainCity;

use Psr\Log\LoggerInterface;
use RainCity\Logging\Logger;

/**
 * A base Singleton class.
 *
 * Inheriting this class enforces singleton behavior.
 *
 * Also intializes logging for use by the child class(es).
 *
 * @abstract
 * @since      1.0.0
 * @
 */
abstract class Singleton {
    /** Singleton *************************************************************/

    /** @var array<string, object> */
    private static array $instance = array();

    /** @var LoggerInterface */
    protected LoggerInterface $log;

    public static function triggerIncorrectUseWarning(string $function): void
    {
        trigger_error($function . ' should not be called on singleton class', E_USER_WARNING);
    }

    /**
     * Initializes an instance of the called class or returned an existing
     * instance if one already exists.
     *
     * @param mixed ...$args
     *
     * @access public
     * @since    1.0.0
     * @return object Instance of the called class.
     */
    public static function instance(...$args): object
    {
        $class = get_called_class();
        if (!isset(self::$instance[$class]))
        {
            self::$instance[$class] = new $class($args);

            self::$instance[$class]->initializeInstance();
        }

        return self::$instance[$class];
    }


    /**
     * Retrieves the single instance of a class if one has been created.
     *
     * @access public
     *
     * @param string $class The name of the class being requested. If no
     *      is specified uses the calling class.
     *
     * @return NULL|Singleton Null if there is no instance of the class or
     *      the singleton instance.
     */
    public static function getInstance($class = null)
    {
        $inst = null;
        if (null === $class) {
            $class = get_called_class();
        }

        if (isset(self::$instance[$class]))
        {
            $inst = self::$instance[$class];
        }

        return $inst;
    }

    /**
     * A dummy magic method to prevent from being cloned
     */
    public function __clone()
    {
        self::triggerIncorrectUseWarning(__FUNCTION__);
    }

    /**
     * A dummy magic method to prevent from being unserialized
     */
    public function __wakeup()
    {
        self::triggerIncorrectUseWarning(__FUNCTION__);
    }


    /**
     * Constructor which can be overwritten by child classes. They should
     * still call this constructor so that logging is initialized.
     *
     * Care should be taken in the constructor to avoid doing anything that
     * might call instance() on the singleton as this will lead to a
     * recursive loop. It is preferred to do any initialization of the
     * instance in the initializeInstance() method.
     *
     * @access protected
     * @since    1.0.0
     */
    protected function __construct()
    {
        $this->log = Logger::getLogger(get_class($this));
    }

    /**
     * Called after a new instance of the singleton has been created. It is
     * preferred to do any initialization of the instance in this method and
     * not in the constructor.
     *
     * @since 1.0.0
     * @access protected
     */
    protected function initializeInstance (): void
    {
    }
}
