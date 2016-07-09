<?php
namespace Avenue;

use Closure;
use Avenue\Request;
use Avenue\Response;
use Avenue\Route;
use Avenue\View;
use Avenue\Log;
use Avenue\Exception;
use Avenue\Helpers\HelperBundleTrait;
use Avenue\AppInterface;

class App implements AppInterface
{
    use HelperBundleTrait;

    /**
     * Avenue version.
     *
     * @var string
     */
    const AVENUE_VERSION = '1.0';

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
    public $exc;

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

        $this->setTimezone()->setErrorHandler();
        $this->registry()->factory();
    }

    /**
     * Adding route's rule for particular request.
     *
     * @see \Avenue\AppInterface::addRoute()
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
     * @see \Avenue\AppInterface::container()
     */
    public function container($name, Closure $callback)
    {
        if (!$this->isInputAlnum($name)) {
            throw new \InvalidArgumentException('Invalid registered name! Alphanumeric only.');
        }

        static::$services[$name] = $callback;
    }

    /**
     * Resolving registered services via callback.
     *
     * @see \Avenue\Interfaces\AppInterface::resolve()
     */
    public function resolve($name)
    {
        if (!array_key_exists($name, static::$services)) {
            $this->response->withStatus(500);
            throw new \OutOfBoundsException(sprintf('Service [%s] is not registered!', $name));
        }

        $resolver = static::$services[$name];
        return $resolver(static::$app);
    }

    /**
     * Making sure only one class instance created at one time.
     * Class instance returned by resolving the registered service.
     *
     * @see \Avenue\Interfaces\AppInterface::singleton()
     */
    public function singleton($name)
    {
        if (!array_key_exists($name, static::$instances)) {
            static::$instances[$name] = $this->resolve($name);
        }

        if (!is_object(static::$instances[$name])) {
            return null;
        }

        return static::$instances[$name];
    }

    /**
     * Rendering the response body output.
     *
     * @see \Avenue\Interfaces\AppInterface::render()
     */
    public function render()
    {
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
        if (!empty($this->response->getBody())) {
            $this->response->render();
        }
    }

    /**
     * Set the error and exception handlers.
     * Error messages are render via error service.
     *
     * @throws \ErrorException
     */
    protected function setErrorHandler()
    {
        set_exception_handler(function(\Exception $exc) {
            $this->exc = $exc;
            return $this->error();
        });

        set_error_handler(function($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) {
                return;
            }

            $this->response->withStatus(500);
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        return $this;
    }

    /**
     * Add the respective application registries.
     */
    protected function registry()
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

        $this->container('log', function($app) {
            return new Log($app);
        });

        $this->container('exception', function($app) {
            return new Exception($app, $this->exc);
        });

        return $this;
    }

    /**
     * Retrieving config value based on the key.
     *
     * @param mixed $key
     */
    public function getConfig($key)
    {
        if (empty(static::$config)) {
            static::$config = require_once AVENUE_CONFIG_DIR . '/app.php';
        }

        if (!array_key_exists($key, static::$config)) {
            static::$config[$key] = null;
        }

        return static::$config[$key];
    }

    /**
     * Retrieving avenue version.
     */
    public function getVersion()
    {
        return static::AVENUE_VERSION;
    }

    /**
     * Retrieving avenue application version.
     */
    public function getAppVersion()
    {
        return $this->getConfig('version');
    }

    /**
     * Retrieving http version setting.
     */
    public function getHttpVersion()
    {
        return $this->getConfig('http');
    }

    /**
     * Retrieving timezone setting.
     */
    public function getTimezone()
    {
        return $this->getConfig('timezone');
    }

    /**
     * Set the application default timezone based on the setting.
     */
    public function setTimezone()
    {
        date_default_timezone_set($this->getTimezone());
        return $this;
    }

    /**
     * Retrieving application environment setting.
     */
    public function getEnvironment()
    {
        return $this->getConfig('environment');
    }

    /**
     * Retrieving default controller setting.
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