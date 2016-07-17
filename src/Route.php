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
        ':alnum' => '([a-zA-Z0-9-]+)',
        ':alpha' => '([a-zA-Z]+)',
        ':digit' => '([0-9]+)'
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
     * Start with the route mapping by accepting the arguments from app route.
     *
     * @param array $args
     */
    public function init(array $args)
    {
        if (count($args) !== 2) {
            throw new \InvalidArgumentException('Route method is expecting two arguments.');
        }

        if (!is_callable($args[1])) {
            throw new \InvalidArgumentException('Second argument must be callable.');
        }

        $this->rule = $args[0];
        $this->filters = $args[1]();

        if ($this->fulfill = $this->matchRoute()) {
            $this->setRouteParams()->initController();
        }
    }

    /**
     * Match the route URI with regular expression.
     * Return true if the particular route is matched.
     */
    protected function matchRoute()
    {
        if (!is_array($this->filters)) {
            throw new \LogicException('Route callback should be returning array.');
        }

        // replace with the regexp patterns
        $ruleRegex = strtr(strtr($this->rule, $this->filters), $this->regex);
        $ruleRegex = str_replace(')', ')?', $ruleRegex);
        $this->pathInfo = $this->app->request->getPathInfo();

        return preg_match('#^/?' . $ruleRegex . '/?$#', $this->pathInfo);
    }

    /**
     * Set respective route params with actual value.
     */
    protected function setRouteParams()
    {
        $this->rule = str_replace(')', '', str_replace('(', '', $this->rule));
        $fs = '/';

        if (strpos($this->rule, $fs) !== false && strpos($this->pathInfo, $fs) !== false) {
            $arrUri = explode($fs, $this->rule);
            $arrPathInfo = explode($fs, $this->pathInfo);

            // iterate over and set respective values to token
            for ($i = 0, $len = count($arrUri); $i < $len; $i++) {

                if (!empty($arrPathInfo[$i]) && strpos($arrUri[$i], '@') !== false) {
                    $key = $arrUri[$i];
                    $value = $arrPathInfo[$i];
                    $this->setParam($key, $this->app->escape($value));
                }
            }
        }

        // set directory
        $this->setParam('@directory', $this->app->arrGet('@directory', $this->filters, ''));

        // set default controller if empty
        if (empty($this->getParams('@controller'))) {
            $this->setParam('@controller', $this->app->getDefaultController());
        }

        // set default action if empty
        if (empty($this->getParams('@action'))) {
            $this->setParam('@action', static::DEFAULT_ACTION);
        }

        return $this;
    }

    /**
     * Get the controller namespace and do the instantiation.
     *
     * @throws \LogicException
     */
    protected function initController()
    {
        $ControllerClass = $this->getControllerNamespace();
        $ControllerClassParents = class_parents($ControllerClass);

        // throw exception if no controller class found
        if (!class_exists($ControllerClass)) {
            $this->app->response->withStatus(404);
            throw new \LogicException(sprintf('Controller [%s] not found.', $ControllerClass));
        }

        // check if controller class has parent controller
        if (!isset($ControllerClassParents[static::BASE_CONTROLLER])) {
            $this->app->response->withStatus(400);
            throw new \LogicException('Controller must be extending the base controller!');
        }

        return new $ControllerClass($this->app);
    }

    /**
     * Build the controller namespace for the matched route.
     * If no controller is specified, the default controller will always be used.
     */
    protected function getControllerNamespace()
    {
        $fs = '/';
        $bs = '\\';
        $namespace = '';
        $directory = $this->getParams('@directory');
        $controller = $this->getParams('@controller');
        $controller = ucfirst($controller . static::CONTROLLER_SUFFIX);

        // check directory
        if (!empty($directory)) {
            if (strpos($directory, $fs) !== false) {
                $namespace .= implode($bs, array_map('ucfirst', explode($fs, $directory))) . $bs;
            } else {
                $namespace .= ucfirst($directory) . $bs;
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
        return $this->params[$key] = $value;
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
}