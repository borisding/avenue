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
     * List of respective configurations.
     *
     * @var array
     */
    protected $config = [];

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
     * App class constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        static::$app = $this;
        $this->config = $config;

        $this
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
        if (!$this->isValidMethodName($name)) {
            throw new \InvalidArgumentException('Invalid registered name for container!');
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
            throw new \LogicException(sprintf('Service [%s] is not registered!', $name));
        }

        $callback = static::$services[$name];
        return $callback(static::$app);
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
            $this->response->render();
            exit(0);
        }
    }

    /**
     * Register application default timezone based on the config.
     */
    protected function registerTimezone()
    {
        $timezone = $this->getTimezone();

        if (empty($timezone)) {
            throw new \InvalidArgumentException('Timezone is not specified!');
        }

        date_default_timezone_set($timezone);
        return $this;
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
        $this->view = $this->view();

        return $this;
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
     * Retrieving app instance.
     *
     * @throws \InvalidArgumentException
     * @return object
     */
    public static function getInstance()
    {
        if (!static::$app instanceof AppInterface) {
            throw new \InvalidArgumentException('Invalid App instance!');
        }

        return static::$app;
    }

    /**
     * App call magic method.
     * Shortcut of creating instance via singleton.
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

        throw new \LogicException(sprintf('Method [%s] does not exist!', $name));
    }

    /**
     * App static call magic method.
     * Provide alternative to singleton method call via static behavior.
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
        if (array_key_exists($name, static::$services)) {
            return static::getInstance()->singleton($name);
        }

        throw new \LogicException(sprintf('Static method [%s] does not exist!', $name));
    }
}
