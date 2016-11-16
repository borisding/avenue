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
     * Request class instance.
     *
     * @var \Avenue\Request
     */
    protected $request;

    /**
     * Response class instance.
     *
     * @var \Avenue\Response
     */
    protected $response;

    /**
     * Route class instance.
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
     * Locale of application
     *
     * @var mixed
     */
    protected $locale;

    /**
     * Language source.
     *
     * @var mixed
     */
    protected $language;

    /**
     * App ID.
     *
     * @var mixed
     */
    protected static $id;

    /**
     * Configuration of application
     *
     * @var array
     */
    protected $config = [];

    /**
     * List of App class instances.
     *
     * @var array
     */
    protected static $apps = [];

    /**
     * List of registered services.
     *
     * @var array
     */
    protected static $services = [];

    /**
     * List of registered singletons.
     *
     * @var array
     */
    protected static $singletons = [];

    /**
     * List of singleton object instances.
     *
     * @var array
     */
    protected static $instances = [];

    /**
     * Default timezone of application.
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
     * Stop to proceed once rule is matched.
     */
    public function addRoute()
    {
        if (!$this->route->isFulfilled()) {
            return $this->route->dispatch(func_get_args());
        }

        return true;
    }

    /**
     * Container to register specific service for application's usage.
     *
     * @param  string  $name
     * @param  Closure $callback
     * @return Closure
     */
    public function container($name, \Closure $callback)
    {
        $id = $this->getId();

        if (!$this->isValidMethodName($name)) {
            throw new \InvalidArgumentException('Invalid registered name for service container!');
        }

        if (!isset(static::$services[$id])) {
            throw new \InvalidArgumentException('Failed to retrieve registered services.');
        }

        if (array_key_exists($name, static::$services[$id])) {
            throw new \LogicException(sprintf('Duplicate service name [%s]. It is already taken!', $name));
        }

        return static::$services[$id][$name] = $callback;
    }

    /**
     * Container to register singleton.
     * Always returning one object instance when it is resolved.
     * This allows specific class instance to be reached globally.
     *
     * @param  string $name
     * @param  Closure $callback
     * @return Closure
     */
    public function singleton($name, \Closure $callback)
    {
        $id = $this->getId();

        if (!$this->isValidMethodName($name)) {
            throw new \InvalidArgumentException('Invalid registered name for singleton container!');
        }

        if (!isset(static::$singletons[$id])) {
            throw new \InvalidArgumentException('Failed to retrieve registered singletons.');
        }

        if (array_key_exists($name, static::$singletons[$id])) {
            throw new \LogicException(sprintf('Duplicate singleton name [%s]. It is already taken!', $name));
        }

        return static::$singletons[$id][$name] = $callback;
    }

    /**
     * Resolve registered service via callback by providing its name.
     *
     * @param  string $name
     * @param array $params
     * @return mixed
     */
    public function resolve($name, array $params = [])
    {
        $id = $this->getId();

        if (!array_key_exists($name, static::$services[$id])) {
            throw new \LogicException(sprintf('Service [%s] is not registered!', $name));
        }

        // prepend app class instance to the beginning of params
        array_unshift($params, static::getInstance());

        return call_user_func_array(static::$services[$id][$name], $params);
    }

    /**
     * Resolve registered singleton via container.
     *
     * @param  mixed $name
     * @return object
     */
    public function resolveSingleton($name)
    {
        $singletons = static::$singletons[$this->getId()];

        if (!array_key_exists($name, static::$instances)) {
            static::$instances[$name] = $singletons[$name](static::getInstance());
        }

        if (!is_object(static::$instances[$name])) {
            throw new \InvalidArgumentException(sprintf('Non-object returned for [%s] singleton.', $name));
        }

        return static::$instances[$name];
    }

    /**
     * Running application by rendering the output.
     *
     * @return mixed
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
        // ajax output can be printed out using standard echo or response
        if ($this->request->isAjax()) {
            return;
        }

        // print out by rendering the output without manually invoking `render` method
        if ($this->getConfig('autoRender') === true) {
            return $this->response->render();
        }
    }

    /**
     * Retrieving config value based on the key.
     * Return all configurations instead if key is empty.
     *
     * @param  mixed $key
     * @return mixed
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
     * Retrieving application version as configured.
     *
     * @return mixed
     */
    public function getAppVersion()
    {
        return $this->getConfig('appVersion');
    }

    /**
     * Retrieving http version as configured.
     *
     * @return mixed
     */
    public function getHttpVersion()
    {
        return $this->getConfig('httpVersion');
    }

    /**
     * Retrieving application's default timezone as configured.
     *
     * @return mixed
     */
    public function getTimezone()
    {
        return $this->getConfig('timezone');
    }

    /**
     * Retrieving application's environment mode as configured.
     *
     * @return mixed
     */
    public function getEnvironment()
    {
        return $this->getConfig('environment');
    }

    /**
     * Retrieving application's default controller name as configured.
     *
     * @return mixed
     */
    public function getDefaultController()
    {
        return $this->getConfig('defaultController');
    }

    /**
     * Retrieving application's secret value as configured.
     *
     * @return mixed
     */
    public function getSecret()
    {
        return $this->getConfig('secret');
    }

    /**
     * Retrieving application ID.
     *
     * @return mixed
     */
    public function getId()
    {
        return static::$id;
    }

    /**
     * Retrieving registered app instance based on ID.
     *
     * @return mixed
     */
    public static function getInstance()
    {
        return isset(static::$apps[static::$id]) ? static::$apps[static::$id] : null;
    }

    /**
     * Retrieving current locale of application.
     *
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set locale for internationalization and localization.
     * Language file content must be returning array.
     *
     * @param mixed $locale
     * @param mixed $languageFile
     * @return \Avenue\App
     */
    public function setLocale($locale, $languageFile = null)
    {
        $this->locale = $locale;

        if (empty($locale)) {
            throw new \InvalidArgumentException('Locale is required for internationalization & localization!');
        }

        if (empty($languageFile)) {
            $languageFile = sprintf('%s/%s.php', AVENUE_I18N_DIR, $locale);
        }

        if (!file_exists($languageFile)) {
            throw new \RuntimeException(sprintf('Language file [%s] not found!', $languageFile));
        }

        $this->language = require $languageFile;
        return $this;
    }

    /**
     * Translate source into targeted language.
     * Values can be passed as indexed array for source that has placeholder(s).
     * Support one level where indicated by a 'dot' in source label. Deep nesting not supported.
     *
     * @param  mixed $source
     * @param  array $values
     * @return mixed
     */
    public function t($source, array $values = []) {
        $translated = $source;
        $arrSource = [];

        if (is_null($this->language) || strpos($source, '.') === false) {
            return $source;
        }

        $arrSource = explode('.', $source);

        // simply return as source label if deep nesting intended
        if (count($arrSource) !== 2) {
            return $source;
        }

        // get the source plain text and translate it
        list($type, $name) = $arrSource;
        $translated = $this->arrGet($name, $this->arrGet($type, $this->language, []), $source);

        // replace placeholder(s) with values, if any
        if (!empty($values)) {

            foreach ($values as $index => $value) {
                $translated = str_replace(sprintf('{%d}', $index), $value, $translated);
            }
        }

        return $translated;
    }

    /**
     * Register app service and singleton containers that bound with current app ID.
     * Exit application if empty ID provided.
     *
     * @param  mixed $config
     * @param  mixed $id
     * @return mixed
     */
    protected function registerApp($config, $id)
    {
        if (empty(trim($id))) {
            exit('App ID must not be empty!');
        }

        $this->config = $config;
        static::$id = $id;

        if (!isset(static::$services[$id])) {
            static::$services[$id] = [];
        }

        if (!isset(static::$singletons[$id])) {
            static::$singletons[$id] = [];
        }

        if (!isset(static::$apps[$id])) {
            static::$apps[$id] = $this;
        }

        return static::$apps[$id];
    }

    /**
     * Set application's default timezone based on the config.
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
        $this->singleton('request', function($app) {
            return new Request($app);
        });

        $this->singleton('response', function($app) {
            return new Response($app);
        });

        $this->singleton('route', function($app) {
            return new Route($app);
        });

        $this->singleton('view', function($app) {
            return new View($app);
        });

        $this->singleton('exception', function($app) {
            return new Exception($app, $app->exception);
        });

        $this->singleton('crypt', function($app) {
            return new Crypt($app->getSecret());
        });

        $this->singleton('cookie', function($app) {
            return new Cookie($app, $app->getConfig('state')['cookie']);
        });

        $this->singleton('session', function($app) {
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
     * App call magic method and also shortcut of singleton.
     *
     * @param  mixed $name
     * @param  array $params
     * @return mixed
     */
    public function __call($name, array $params = [])
    {
        if (array_key_exists($name, static::$singletons[static::$id])) {
            return $this->resolveSingleton($name);
        }

        throw new \RuntimeException(sprintf('Method [%s] does not exist!', $name));
    }

    /**
     * App static call magic method. Provide singleton method call via static behavior.
     *
     * Eg:
     * App::request() will be the same with $this->request() (in App class itself) or,
     * $this->app->request() where invoked from other class that has $app property.
     *
     * @param  mixed $name
     * @param  array $params
     * @return mixed
     */
    public static function __callStatic($name, array $params = [])
    {
        if (array_key_exists($name, static::$singletons[static::$id])) {
            return static::getInstance()->resolveSingleton($name);
        }

        throw new \RuntimeException(sprintf('Static method [%s] does not exist!', $name));
    }
}
