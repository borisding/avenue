<?php
namespace Avenue;

use Avenue\App;
use Avenue\Interfaces\RouteInterface;

class Route implements RouteInterface
{
    /**
     * App instance.
     *
     * @var object
     */
    protected $app;

    /**
     * The route rule.
     *
     * @var mixed
     */
    protected $rule;

    /**
     * The actual matched rule regular expression.
     *
     * @var mixed
     */
    protected $ruleRegex;

    /**
     * The route filters.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * The route params.
     *
     * @var array
     */
    protected $params = [];

    /**
     * Flag to indicate route is fulfullied.
     *
     * @var boolean
     */
    protected $fulfill = false;

    /**
     * Current http path info.
     *
     * @var mixed
     */
    protected $pathInfo;

    /**
     * @var Route's regular expression patterns to be matched.
     */
    protected $regex = [
        ':alnum'    => '([a-zA-Z0-9-]+)',
        ':alpha'    => '([a-zA-Z]+)',
        ':lower'    => '([a-z]+)',
        ':lowernum' => '([a-z0-9-]+)',
        ':upper'    => '([A-Z]+)',
        ':uppernum' => '([A-Z0-9-]+)',
        ':digit'    => '([0-9]+)'
    ];

    /**
     * Prefix of controller namespace.
     *
     * @var string
     */
    const NAMESPACE_PREFIX = 'App\Controllers';

    /**
     * Base controller namespace.
     *
     * @var string
     */
    const BASE_CONTROLLER = 'Avenue\Controller';

    /**
     * Suffix of controller.
     *
     * @var string
     */
    const CONTROLLER_SUFFIX = 'Controller';

    /**
     * Default action method.
     *
     * @var string
     */
    const DEFAULT_ACTION = 'index';

    /**
     * Route class constructor.
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * Dispatch route's rule and arguments for matching.
     *
     * @param array $args
     */
    public function dispatch(array $args)
    {
        if (count($args) !== 2) {
            throw new \InvalidArgumentException('Route method is expecting two arguments.');
        }

        if (!$this->app->arrIsAssoc($args[1])) {
            throw new \InvalidArgumentException('Invalid data type. Second argument must be associative array.');
        }

        $this->rule = $args[0];
        $this->filters = $args[1];

        if ($this->fulfill = $this->matchRoute()) {
            $this->setRouteParams()->initController();
        }
    }

    /**
     * Match the route URI with regular expression.
     * Return true if the particular route is matched.
     */
    public function matchRoute()
    {
        // replace with the regexp patterns
        $this->ruleRegex = strtr(strtr($this->rule, $this->filters), $this->regex);
        $this->ruleRegex = '#^/?' . str_replace(')', ')?', $this->ruleRegex) . '/?$#';
        $this->pathInfo = $this->app->request->getPathInfo();

        return preg_match($this->ruleRegex, $this->pathInfo);
    }

    /**
     * Set respective route params with actual value.
     */
    public function setRouteParams()
    {
        $fs = '/';
        $arrRule = [];
        $arrPathInfo = [];
        $this->rule = str_replace(')', '', str_replace('(', '', $this->rule));

        if (strpos($this->rule, $fs) !== false && strpos($this->pathInfo, $fs) !== false) {
            $arrRule = explode($fs, $this->rule);
            $arrPathInfo = explode($fs, $this->pathInfo);
        }

        foreach ($this->filters as $token => $regx) {

            if (substr($token, 0, 1) === '@') {
                $key = substr($token, 1, strlen($token));

                // replaced with the actual value, if found any in the matched rule
                if (in_array($token, $arrRule)) {
                    $index = array_search($token, $arrRule);

                    (isset($arrPathInfo[$index]))
                    ? $this->setParam($key, $arrPathInfo[$index])
                    : $this->setParam($key, null);
                } else {
                    $this->setParam($key, null);
                }
            }
        }

        $this->setDefaultRouteParams();
        return $this;
    }

    /**
     * Check and reset respective default route params if not provided or exist.
     *
     * @return \Avenue\Route
     */
    public function setDefaultRouteParams()
    {
        // set default prefix if empty
        if (empty($this->getParams('prefix'))) {
            $this->setParam('prefix', '');
        }

        // set default controller if empty
        if (empty($this->getParams('controller'))) {
            $this->setParam('controller', $this->app->getDefaultController());
        }

        // set default action if empty
        if (empty($this->getParams('action'))) {
            $this->setParam('action', static::DEFAULT_ACTION);
        }

        // proceed to resource mapping if token exist
        if (!isset($this->filters['@resource'])) {
            $this->setParam('resource', false);
        } else {
            $this->mapResourceMethod();
        }

        return $this;
    }

    /**
     * Check against resource token in route's filter and,
     * overwrite the controller action based on the http request method
     * when resource token is true or targeted controller is matched.
     *
     * example of @resource token format:
     *
     * @resource => true (applied to any controllers that fulfilled the route mapping)
     * @resource => 'default' (only applied to 'DefaultController' class)
     * @resource => 'default|dummy' (only applied to 'DefaultController' and 'DummyController' classes)
     *
     */
    public function mapResourceMethod()
    {
        $delimiter = '|';
        $controller = $this->getParams('controller');
        $requestMethod = $this->app->request->getRequestMethod(true);

        $resource = $this->filters['@resource'];
        $this->setParam('resource', $resource);

        // if true or with targeted controller
        if ($resource === true || (!strpos($resource, $delimiter) && $resource === $controller)) {
            $this->setParam('action', $requestMethod);
            return;
        }

        // if more than one controller
        if (strpos($resource, $delimiter) !== false) {
            $arrControllers = explode($delimiter, $resource);
            $filteredArrControllers = array_values(array_filter($arrControllers));

            if (count($arrControllers) !== count($filteredArrControllers)) {
                throw new \InvalidArgumentException(sprintf('Invalid format of @resource token value [%s]!', $resource));
            }

            if (in_array($controller, $arrControllers)) {
                $this->setParam('action', $requestMethod);
            }
        }
    }

    /**
     * Get the controller namespace and do the instantiation.
     *
     * @throws \LogicException
     */
    public function initController()
    {
        $controllerNamespace = $this->getControllerNamespace();
        $this->setParam('namespace', $controllerNamespace);

        // throw exception if no controller class found
        if (!class_exists($controllerNamespace)) {
            throw new \LogicException(
                sprintf('Controller [%s] not found.', $controllerNamespace),
                404
            );
        }

        // check if controller class has parent controller
        if (!isset(class_parents($controllerNamespace)[static::BASE_CONTROLLER])) {
            throw new \LogicException('Controller must be extending the base controller!', 400);
        }

        return new $controllerNamespace($this->app);
    }

    /**
     * Build the controller namespace for the matched route.
     * If no controller is specified, the default controller will always be used.
     */
    public function getControllerNamespace()
    {
        $fs = '/';
        $bs = '\\';
        $namespace = '';
        $prefix = $this->getParams('prefix');
        $controller = $this->getParams('controller');
        $controller = ucfirst($controller . static::CONTROLLER_SUFFIX);

        // check prefix
        if (!empty($prefix)) {
            if (strpos($prefix, $fs) !== false) {
                $namespace .= implode($bs, array_map('ucfirst', explode($fs, $prefix))) . $bs;
            } else {
                $namespace .= ucfirst($prefix) . $bs;
            }
        }

        $namespace .= $controller;
        return static::NAMESPACE_PREFIX . $bs . $namespace;
    }

    /**
     * Set the particular URI token with a value.
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function setParam($key, $value)
    {
        return $this->params[$key] = urldecode($value);
    }

    /**
     * Get the particular token value based on the key.
     * Return all params if key is not provided.
     *
     * @param mixed $key
     */
    public function getParams($key = null)
    {
        if (empty($key)) {
            return $this->params;
        }

        return $this->app->arrGet($key, $this->params, null);
    }

    /**
     * Check if particular route is fulfilled.
     */
    public function isFulfilled()
    {
        return $this->fulfill;
    }

    /**
     * Get the matched rule's regular expression.
     *
     * @return mixed
     */
    public function getMatchedRuleRegexp()
    {
        return $this->ruleRegex;
    }
}
