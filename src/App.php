<?php
namespace Avenue;

use Avenue\Request;
use Avenue\Response;
use Avenue\Route;
use Avenue\View;
use Avenue\Exception;
use Avenue\Mcrypt;
use Avenue\Helpers\HelperBundleTrait;
use Avenue\Interfaces\AppInterface;

class App implements AppInterface
{
    use HelperBundleTrait;

    /**
     * Request instance.
     *
     * @var object
     */
    public $request;

    /**
     * Response instance.
     *
     * @var object
     */
    public $response;

    /**
     * View instsance.
     *
     * @var object
     */
    public $view;

    /**
     * Route instance;
     *
     * @var object
     */
    public $route;

    /**
     * Exception instance.
     *
     * @var object
     */
    public $exception;

    /**
     * App instance.
     *
     * @var object
     */
    protected static $app;

    /**
     * List of respective services.
     *
     * @var array
     */
    protected static $services = [];

    /**
     * List of respective class instances.
     *
     * @var array
     */
    protected static $instances = [];

    /**
     * List of respective configurations.
     *
     * @var array
     */
    protected static $config = [];

    /**
     * App constructor
     */
    public function __construct()
    {
        if (empty(static::$app)) {
            static::$app = $this;
        }

        $this->registerExceptionHandler()->registerErrorHandler();
        $this->registerServices()->factory();
    }

    /**
     * Adding route's rule for particular request.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\AppInterface::addRoute()
     */
    public function addRoute()
    {
        if (!$this->route->isFulfilled()) {
            return $this->route->init(func_get_args());
        }

        return true;
    }

    /**
     * Container that registers specific service for later usage.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\AppInterface::container()
     */
    public function container($name, \Closure $callback)
    {
        if (!$this->isAlnum($name)) {
            throw new \InvalidArgumentException('Invalid registered name! Alphanumeric only.');
        }

        static::$services[$name] = $callback;
    }

    /**
     * Resolving registered service via callback.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\AppInterface::resolve()
     */
    public function resolve($name)
    {
        if (!array_key_exists($name, static::$services)) {
            $this->response->withStatus(500);
            throw new \OutOfBoundsException(sprintf('Service [%s] is not registered!', $name));
        }

        $service = static::$services[$name];
        return $service(static::$app);
    }

    /**
     * Making sure only one class instance created at one time.
     * Class instance returned by resolving the registered service.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\AppInterface::singleton()
     */
    public function singleton($name)
    {
        if (!array_key_exists($name, static::$instances)) {
            static::$instances[$name] = $this->resolve($name);
        }

        if (!is_object(static::$instances[$name])) {
            throw new \InvalidArgumentException(sprintf('Non-object returned for [%s] singleton.', $name));
        }

        return static::$instances[$name];
    }

    /**
     * Running by rendering the response body output.
     *
     * {@inheritDoc}
     * @see \Avenue\AppInterface::run()
     */
    public function run()
    {
        $this->resolve('routes');

        // throw page not found exception, if any
        if (!$this->route->isFulfilled()) {
            $this->response->withStatus(404);
            throw new \Exception('Page not found!');
        }

        // exit if request is via ajax
        // this is to avoid entire view to be re-rendered
        // ajax output can be printed out using standard echo
        // or write into response and rendered immediately via response render method
        if ($this->request->isAjax()) {
            return;
        }

        // print out the response body for normal request
        $this->response->render();
        exit(0);
    }

    /**
     * Register core exception handler.
     * Exception instance can be accessed in errorHandler service.
     *
     * @return \Avenue\App
     */
    protected function registerExceptionHandler()
    {
        set_exception_handler(function(\Exception $exception) {
            $this->exception = $exception;
            return $this->resolve('errorHandler');
        });

        return $this;
    }

    /**
     * Register core error handler.
     *
     * @throws \ErrorException
     * @return \Avenue\App
     */
    protected function registerErrorHandler()
    {
        set_error_handler(function($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) {
                return;
            }

            if (is_object($this->response)) {
                $this->response->withStatus(500);
            }

            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        return $this;
    }

    /**
     * Register respective core component services.
     */
    protected function registerServices()
    {
        $this->container('request', function($app) {
            return new Request($app);
        });

        $this->container('response', function($app) {
            return new Response($app);
        });

        $this->container('route', function($app) {
            return new Route($app);
        });

        $this->container('view', function($app) {
            return new View($app);
        });

        $this->container('exception', function($app) {
            return new Exception($app, $this->exception);
        });

        $this->container('mcrypt', function($app) {
            return new Mcrypt($app, $this->getConfig('encryption'));
        });

        return $this;
    }

    /**
     * Retrieve respective class instances via singleton method.
     */
    protected function factory()
    {
        $this->request = $this->request();
        $this->response = $this->response();
        $this->route = $this->route();
        $this->view = $this->view();
    }

    /**
     * Retrieving config value based on the key.
     * Return all configurations instead if key is empty.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\AppInterface::getConfig()
     */
    public function getConfig($key = null)
    {
        if (empty(static::$config)) {
            $appDefaultConfig = $this->getDefaultConfig();
            $userDefinedConfig = require_once AVENUE_CONFIG_DIR . '/app.php';

            // merged with user defined
            static::$config = array_merge($appDefaultConfig, $userDefinedConfig);
            unset($appDefaultConfig, $userDefinedConfig);
        }

        // simply return all if empty key provided
        if (empty($key)) {
            return static::$config;
        }

        // return particular config
        if (!array_key_exists($key, static::$config)) {
            static::$config[$key] = null;
        }

        return static::$config[$key];
    }

    /**
     * List of default value for configurations.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\AppInterface::getDefaultConfig()
     */
    public function getDefaultConfig()
    {
        return [
            // default current application's version
            'appVersion' => '1.0',

            // default http version that is used
            'httpVersion' => '1.1',

            // default application's timezone
            'timezone' => 'UTC',

            // default application's environment mode
            'environment' => 'development',

            // default controller to be assigned when @controller param is empty
            'defaultController' => 'default',

            // default encryption configuration
            'encryption' => [],

            // default database configuration
            'database' => [],

            // default logging configuration
            'logging' => []
        ];
    }

    /**
     * Retrieving avenue application version config.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\AppInterface::getAppVersion()
     */
    public function getAppVersion()
    {
        return $this->getConfig('appVersion');
    }

    /**
     * Retrieving http version config.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\AppInterface::getHttpVersion()
     */
    public function getHttpVersion()
    {
        return $this->getConfig('httpVersion');
    }

    /**
     * Retrieving timezone config.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\AppInterface::getTimezone()
     */
    public function getTimezone()
    {
        return $this->getConfig('timezone');
    }

    /**
     * Set the application default timezone based on the config.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\AppInterface::setTimezone()
     */
    public function setTimezone()
    {
        date_default_timezone_set($this->getTimezone());
        return $this;
    }

    /**
     * Retrieving application environment config.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\AppInterface::getEnvironment()
     */
    public function getEnvironment()
    {
        return $this->getConfig('environment');
    }

    /**
     * Retrieving default controller config.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\AppInterface::getDefaultController()
     */
    public function getDefaultController()
    {
        return $this->getConfig('defaultController');
    }

    /**
     * Retrieving app instance.
     */
    public static function getInstance()
    {
        return static::$app;
    }

    /**
     * App call magic method.
     * Shortcut of creating instance via singleton,
     * and also the user defined function, if any
     *
     * @param mixed $name
     * @param array $params
     * @throws \LogicException
     * @return NULL|mixed
     */
    public function __call($name, array $params = [])
    {
        if (array_key_exists($name, static::$services)) {
            return $this->singleton($name);
        }

        if (is_callable($name)) {
            return call_user_func_array($name, $params);
        }

        throw new \LogicException(sprintf('Method [%s] does not exist!', $name));
    }
}