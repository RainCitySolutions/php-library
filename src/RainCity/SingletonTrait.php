<?php declare(strict_types=1);
namespace RainCity;

/**
 * An alternative to extending the Singleton class.
 *
 * Allows the use of a singleton pattern when extending a class that isn't a
 * singleton. Simply "use SingletonTrait;" and make the class constructor
 * protected.
 *
 */
trait SingletonTrait {
    protected static $instance;

    /**
     * Initializes an instance of the called class or returned an existing
     * instance if one already exists.
     *
     * @param array An array of parameters for the class constructor
     *
     * @return object Instance of the called class.
     */
    final public static function instance(...$args)
    {
        if (!isset(self::$instance))
        {
            self::$instance = new static($args);
        }

        return self::$instance;
    }

    /**
     * A dummy magic method to prevent from being cloned
     */
    final public function __clone() {
        Singleton::triggerIncorrectUseWarning(__FUNCTION__);
    }

    /**
     * A dummy magic method to prevent from being unserialized
     */
    final public function __wakeup() {
        Singleton::triggerIncorrectUseWarning(__FUNCTION__);
    }


    /**
     * Constructor which can be overwritten by child classes. They should
     * still call this constructor so that logging is initialized.
     *
     * Care should be taken in the constructor to avoid doing anything that
     * might call instance() on the singleton as this will lead to a
     * recursive loop. It is preferred to do any initialization of the
     * instance in the initializeInstance() method.
     */
    final protected function __construct() {
        $this->initializeInstance();
    }


    /**
     * Called after a new instance of the singleton has been created. It is
     * preferred to do any initialization of the instance in this method and
     * not in the constructor.
     *
     * @since 1.0.0
     * @method
     * @access protected
     */
    protected function initializeInstance () {
    }
}
