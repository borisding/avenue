<?php
namespace Avenue;

use Avenue\Request;
use Avenue\Response;
use Avenue\Route;
use Avenue\View;
use Avenue\Exception;
use Avenue\Crypt;
use Avenue\State\Cookie;
use Avenue\State\Session;
use Avenue\State\SessionDatabaseHandler;
use Avenue\Helpers\HelperBundleTrait;
use Avenue\Interfaces\AppInterface;

/**
 * Autocomplete hints for core services to map respective classes:
 *
 * 1. Method helpers for overloading via call static magic method.
 *
 * @method static \Avenue\Request request()
 * @method static \Avenue\Response response()
 * @method static \Avenue\Route route()
 * @method static \Avenue\View view()
 * @method static \Avenue\Crypt crypt()
 * @method static \Avenue\State\Cookie cookie()
 * @method static \Avenue\State\Session session()
 * @method static \Avenue\Exception exception()
 *
 * 2. Method helpers for overloading via call magic method.
 *
 * @method \Avenue\Request request()
 * @method \Avenue\Response response()
 * @method \Avenue\Route route()
 * @method \Avenue\View view()
 * @method \Avenue\Crypt crypt()
 * @method \Avenue\State\Cookie cookie()
 * @method \Avenue\State\Session session()
 * @method \Avenue\Exception exception()
 *
 * 3. Method helpers for singletons.
 *
 * @method \Avenue\Request singleton('request')
 * @method \Avenue\Response singleton('response')
 * @method \Avenue\Route singleton('route')
 * @method \Avenue\View singleton('view')
 * @method \Avenue\Crypt singleton('crypt')
 * @method \Avenue\State\Cookie singleton('cookie')
 * @method \Avenue\State\Session singleton('session')
 * @method \Avenue\Exception singleton('exception')
 *
 * 4. Method helpers for resolvers.
 *
 * @method \Avenue\Request resolve('request')
 * @method \Avenue\Response resolve('response')
 * @method \Avenue\Route resolve('route')
 * @method \Avenue\View resolve('view')
 * @method \Avenue\Crypt resolve('crypt')
 * @method \Avenue\State\Cookie resolve('cookie')
 * @method \Avenue\State\Session resolve('session')
 * @method \Avenue\Exception resolve('exception')
 */

class App implements AppInterface
{
    use HelperBundleTrait;

    /**
     * Request instance.
     *
     * @var \Avenue\Request
     */
    protected $request;

    /**
     * Response instance.
     *
     * @var \Avenue\Response
     */
    protected $response;

    /**
     * Route instance;
     *
     * @var \Avenue\Route
     */
    protected $route;

    /**
     * Exception instance.
     *
     * @var \Avenue\Exception
     */
    protected $exception;

    /**
     * List of respective configurations.
     *
     * @var array
     */
    protected $config = [];

    /**
     * App ID.
     *
     * @var mixed
     */
    protected static $id;

    /**
     * App instances.
     *
     * @var object
     */
    protected static $apps = [];

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
     * App default timezone.
     *
     * @var string
     */
    const DEFAULT_TIMEZONE = 'UTC';

    /**
     * App class constructor.
     *
     * @param array $config
     * @param string $id
     */
    public function __construct(array $config = [], $id = 'default-app')
    {
        $this
        ->registerApp($config, $id)
        ->registerServices()
        ->registerTimezone()
        ->registerExceptionHandler()
        ->registerErrorHandler();
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
            return $this->route->dispatch(func_get_args());
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
        $services = &$this->getServices();

        if (array_key_exists($name, $services)) {
            throw new \LogicException(sprintf('Duplicate service name [%s]. It is already taken!', $name));
        }

        if (!$this->isValidMethodName($name)) {
            throw new \InvalidArgumentException('Invalid registered name for container!');
        }

        return $services[$name] = $callback;
    }

    /**
     * Resolving registered service via callback.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\AppInterface::resolve()
     */
    public function resolve($name)
    {
        $services = &$this->getServices();

        if (!array_key_exists($name, $services)) {
            throw new \LogicException(sprintf('Service [%s] is not registered!', $name));
        }

        return $services[$name](static::getInstance());
    }

    /**
     * Making sure only one class instance created at one time.
     * This allows specific class instance can be reached globally.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\AppInterface::singleton()
     */
    public function singleton($name)
    {
        $singletons = &$this->getSingletons();

        if (!array_key_exists($name, $singletons)) {
            $singletons[$name] = $this->resolve($name);
        }

        if (!is_object($singletons[$name])) {
            throw new \InvalidArgumentException(sprintf('Non-object returned for [%s] singleton.', $name));
        }

        return $singletons[$name];
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
            throw new \Exception('Page not found!', 404);
        }

        // exit if request is via ajax
        // this is to avoid entire view to be re-rendered
        // ajax output can be printed out using standard echo
        // or write into response and rendered immediately via response render method
        if ($this->request->isAjax()) {
            return;
        }

        // print out the response body for normal request if auto rendering is true
        if ($this->getConfig('autoRender') === true) {
            return $this->response->render();
        }
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
        if (empty($this->config)) {
            throw new \InvalidArgumentException('App config must not be empty!');
        }

        // simply return all if empty key provided
        if (empty($key)) {
            return $this->config;
        }

        // return particular config
        if (!array_key_exists($key, $this->config)) {
            $this->config[$key] = null;
        }

        return $this->config[$key];
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
     * Retrieving secret key config.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\AppInterface::getSecret()
     */
    public function getSecret()
    {
        return $this->getConfig('secret');
    }

    /**
     * Retrieving app ID.
     *
     * @return mixed
     */
    public function getId()
    {
        return static::$id;
    }

    /**
     * Retrieving app instance.
     *
     * @return NULL|object
     */
    public static function getInstance()
    {
        return isset(static::$apps[static::$id]) ? static::$apps[static::$id] : null;
    }

    /**
     * Return the list of registered services.
     *
     * @throws \InvalidArgumentException
     * @return mixed
     */
    protected function &getServices()
    {
        if (!isset(static::$services[$this->getId()])) {
            throw new \InvalidArgumentException('Failed to retrieve services.');
        }

        return static::$services[$this->getId()];
    }

    /**
     * Return the list of singleton.
     *
     * @throws \InvalidArgumentException
     * @return mixed
     */
    protected function &getSingletons()
    {
        if (!isset(static::$instances[$this->getId()])) {
            throw new \InvalidArgumentException('Failed to retrieve instances for singleton.');
        }

        return static::$instances[$this->getId()];
    }

    /**
     * Register app configurations that bound with current app ID.
     *
     * @param mixed $config
     * @param mixed $id
     * @return object
     */
    protected function registerApp($config, $id)
    {
        $this->config = $config;
        static::$id = $id;

        if (!isset(static::$services[$id])) {
            static::$services[$id] = [];
        }

        if (!isset(static::$instances[$id])) {
            static::$instances[$id] = [];
        }

        if (!isset(static::$apps[$id])) {
            static::$apps[$id] = $this;
        }

        return static::$apps[$id];
    }

    /**
     * Register application default timezone based on the config.
     * If not present, use the default timezone setting.
     *
     * @return \Avenue\App
     */
    protected function registerTimezone()
    {
        $timezone = $this->getTimezone();

        if (empty($timezone)) {
            $timezone = static::DEFAULT_TIMEZONE;
        }

        date_default_timezone_set($timezone);
        return $this;
    }

    /**
     * Register core exception handler and http status code hanlding.
     * Exception instance can be accessed in errorHandler service.
     *
     * @return \Avenue\App
     */
    protected function registerExceptionHandler()
    {
        set_exception_handler(function(\Exception $exception) {
            $this->exception = $exception;
            $code = $this->exception->getCode();
            $statusCode = $this->response->getStatusCode();

            if (!is_int($code) || $code < 400 || $code > 599) {
                $code = 500;
            }

            // overwrite with user defined
            if ((int)$statusCode >= 400) {
                $code = $statusCode;
            }

            $this->response->withStatus($code);
            $this->resolve('errorHandler');
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
            return new Exception($app, $app->exception);
        });

        $this->container('crypt', function($app) {
            return new Crypt($app->getSecret());
        });

        $this->container('cookie', function($app) {
            return new Cookie($app, $app->getConfig('state')['cookie']);
        });

        $this->container('session', function($app) {
            return new Session(new SessionDatabaseHandler($app, $app->getConfig('state')['session']));
        });

        return $this->factory();
    }

    /**
     * Retrieve respective class instances via singleton method.
     *
     * @return \Avenue\App
     */
    protected function factory()
    {
        $this->request = $this->request();
        $this->response = $this->response();
        $this->route = $this->route();

        return $this;
    }

    /**
     * App call magic method. Shortcut of singleton.
     *
     * @param mixed $name
     * @param array $params
     * @throws \LogicException
     * @return NULL|mixed
     */
    public function __call($name, array $params = [])
    {
        if (array_key_exists($name, $this->getServices())) {
            return $this->singleton($name);
        }

        throw new \LogicException(sprintf('Method [%s] does not exist!', $name));
    }

    /**
     * App static call magic method.
     * Provide singleton method call via static behavior.
     *
     * Eg:
     * App::request() will be the same with $this->request() (in App class itself) or,
     * $this->app->request() where invoked from other class that has $app property.
     *
     * @param mixed $name
     * @param array $params
     */
    public static function __callStatic($name, array $params = [])
    {
        if (array_key_exists($name, static::getInstance()->getServices())) {
            return static::getInstance()->singleton($name);
        }

        throw new \LogicException(sprintf('Static method [%s] does not exist!', $name));
    }
}
