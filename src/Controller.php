<?php
namespace Avenue;

use Avenue\App;
use Avenue\Interfaces\ControllerInterface;

abstract class Controller implements ControllerInterface
{
    /**
     * Avenue class instance.
     *
     * @var \Avenue\App
     */
    protected $app;

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
     * View class instance.
     *
     * @var \Avenue\View
     */
    protected $view;

    /**
     * Magic method parameter.
     *
     * @var array
     */
    protected $params = [];

    /**
     * Prefix of controller namespace.
     *
     * @var string
     */
    const NAMESPACE_PREFIX = 'App\Controllers';

    /**
     * Suffix of controller.
     *
     * @var string
     */
    const CONTROLLER_SUFFIX = 'Controller';

    /**
     * Suffix of controller action.
     *
     * @var string
     */
    const ACTION_SUFFIX = 'Action';

    /**
     * Default action method.
     *
     * @var string
     */
    const DEFAULT_ACTION = 'index';

    /**
     * Controller class constructor.
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->factory()->invokeActions();
    }

    /**
     * Controller's before action method.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ControllerInterface::beforeAction()
     */
    public function beforeAction()
    {
        return true;
    }

    /**
     * Controller's after action method.
     * Invoke after the controller action is called.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ControllerInterface::afterAction()
     */
    public function afterAction()
    {
        return true;
    }

    /**
     * Invoke targeted controller's action.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ControllerInterface::controllerAction()
     */
    public function controllerAction()
    {
        $action = $this->request->getAction() . static::ACTION_SUFFIX;

        // check if controller action does exit before invoking action
        if (!method_exists($this, $action)) {
            throw new \LogicException(sprintf('Controller action method [%s] not found.', $action), 404);
        }

        return call_user_func_array([$this, $action], []);
    }

    /**
     * Create respective class instances for controller usage.
     * Mapped and re-assign to the core instances.
     */
    protected function factory()
    {
        $this->request = $this->app->request();
        $this->response = $this->app->response();
        $this->route = $this->app->route();
        $this->view = $this->app->view();

        return $this;
    }

    /**
     * Invoke respective controller actions in sequence.
     */
    protected function invokeActions()
    {
        $this->beforeAction();
        $this->controllerAction();
        $this->afterAction();
    }

    /**
     * Set magic method of controller.
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        $this->params[$key] = $value;
    }

    /**
     * Get magic method of controller.
     *
     * @param mixed $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->app->arrGet($key, $this->params);
    }
}