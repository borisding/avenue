<?php
namespace Avenue;

use Avenue\App;

abstract class Controller
{
    /**
     * Avenue class instance.
     *
     * @var mixed
     */
    protected $app;

    /**
     * Request class instance.
     *
     * @var mixed
     */
    protected $request;

    /**
     * Response class instance.
     *
     * @var mixed
     */
    protected $response;

    /**
     * Route class instance.
     *
     * @var mixed
     */
    protected $route;

    /**
     * View class instance.
     *
     * @var mixed
     */
    protected $view;

    /**
     * Magic method parameter.
     *
     * @var array
     */
    protected $params = [];

    /**
     * Suffix of controller action.
     *
     * @var string
     */
    const ACTION_SUFFIX = 'Action';

    /**
     * Controller class constructor.
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->factory()->invoke();
    }

    /**
     * Controller's before action method.
     * Invoked before the controller action is called.
     */
    protected function beforeAction()
    {
        // do nothing
    }

    /**
     * Controller's after action method.
     * Invoke after the controller action is called.
     */
    protected function afterAction()
    {
        // do nothing
    }

    /**
     * Invoke targeted controller's action.
     */
    protected function controllerAction()
    {
        $action = $this->request->getAction() . static::ACTION_SUFFIX;

        // check if controller action does exit before invoking action
        if (!method_exists($this, $action)) {
            $this->response->withStatus(404);
            throw new \BadMethodCallException(sprintf('Controller action method [%s] not found.', $action));
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
    protected function invoke()
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

    /**
     * Controller index abstract method.
     * This to ensure child controller class has at least index method.
     */
    abstract function indexAction();
}