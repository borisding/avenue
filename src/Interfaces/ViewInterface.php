<?php
namespace Avenue\Interfaces;

interface ViewInterface
{
    /**
     * Fetch the targeted view template file with the name provided.
     * Parameters for view template file are optional.
     *
     * @param mixed $filename
     * @param array $params
     */
    public function fetch($filename, array $params = []);

    /**
     * Fetch the targeted view template file for the page layout.
     * Parameters for view template file are optional.
     *
     * @param mixed $filename
     * @param array $params
     */
    public function layout($filename, array $params = []);

    /**
     * Fetch the targeted view template file for the partial.
     * Parameters for view template file are optional.
     *
     * @param mixed $filename
     * @param array $params
     */
    public function partial($filename, array $params = []);

    /**
     * Register helper by providing a helper name and a callback for the engine.
     *
     * @param mixed $name
     * @param \Closure $callback
     */
    public function register($name, \Closure $callback);
}
