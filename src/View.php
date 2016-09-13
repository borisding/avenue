<?php
namespace Avenue;

use Avenue\App;
use Avenue\Helpers\HelperBundleTrait;
use Avenue\Interfaces\ViewInterface;

class View implements ViewInterface
{
    use HelperBundleTrait;

    /**
     * Avenue class instance.
     *
     * @var mixed
     */
    protected $app;

    /**
     * List of view helpers.
     *
     * @var array
     */
    protected $helpers = [];

    /**
     * Parameters for magic methods.
     *
     * @var array
     */
    protected $params = [];

    /**
     * Layouts directory.
     *
     * @var string
     */
    const LAYOUT_DIR = 'layouts';

    /**
     * Partials directory.
     *
     * @var string
     */
    const PARTIAL_DIR = 'partials';

    /**
     * View class constructor.
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * Fetching the view file and getting the contents.
     *
     * @param mixed $filename
     * @param array $params
     * @return string
     */
    public function fetch($filename, array $params = [])
    {
        // store file name to temp variable and later usage
        $AVENUE_FILENAME = $filename;
        unset($filename);

        ob_start();

        // merge with direct variables assignment to object
        // the latter will overwrite the first
        extract(array_merge($this->params, $params));
        require $this->getViewFile($AVENUE_FILENAME);

        return ob_get_clean();
    }

    /**
     * Alias method. Fetching layout view file by omitting directory name.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ViewInterface::layout()
     */
    public function layout($filename, array $params = [])
    {
        $layout = static::LAYOUT_DIR . '/'. $filename;
        return $this->fetch($layout, $params);
    }

    /**
     * Alias method. Fetching partial view file by omitting directory name.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ViewInterface::partial()
     */
    public function partial($filename, array $params = [])
    {
        $partial = static::PARTIAL_DIR . '/'. $filename;
        return $this->fetch($partial, $params);
    }

    /**
     * Retrieve the view file.
     *
     * @param mixed $name
     * @throws \Exception
     * @return string
     */
    protected function getViewFile($filename)
    {
        // assign with default .php extension
        // if there is no extension specified in file name
        if (empty(pathinfo($filename, PATHINFO_EXTENSION))) {
            $filename = $filename . '.php';
        }

        $PATH_TO_VIEW_FILE = AVENUE_APP_DIR . '/views/' . $filename;

        if (!file_exists($PATH_TO_VIEW_FILE)) {
            throw new \Exception(sprintf('View [%s] not found!', $PATH_TO_VIEW_FILE));
        }

        return $PATH_TO_VIEW_FILE;
    }

    /**
     * Register the helper for view template engine.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ViewInterface::register()
     */
    public function register($name, \Closure $callback)
    {
        if (array_key_exists($name, $this->helpers) || method_exists($this, $name)) {
            throw new \InvalidArgumentException('Helper name already registered!');
        }

        if (!$this->app->isValidMethodName($name)) {
            throw new \InvalidArgumentException('Invalid helper function name!');
        }

        $this->helpers[$name] = $callback;
    }

    /**
     * Magic call method for invoking added method.
     *
     * @param mixed $name
     * @param array $params
     * @throws \LogicException
     * @return mixed
     */
    public function __call($name, array $params = [])
    {
        if (!array_key_exists($name, $this->helpers)) {
            throw new \LogicException(sprintf('Calling invalid helper [%s]', $name));
        }

        return call_user_func_array($this->helpers[$name], $params);
    }

    /**
     * Set magic method for view.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function __set($key, $value)
    {
        return $this->params[$key] = $value;
    }

    /**
     * Get magic method for view.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->app->arrGet($key, $this->params);
    }
}