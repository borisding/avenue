<?php
namespace Avenue\Interfaces;

interface ControllerInterface
{
    /**
     * The action that is called BEFORE the targerted controller action.
     */
    public function beforeAction();

    /**
     * The action that is called AFTER the targeted controller action.
     */
    public function afterAction();

    /**
     * The targeted controller action to be invoked.
     */
    public function controllerAction();

    /**
     * The default index action that must be provided for a controller.
     * This action will be invoked when there is no action provided in the route.
     */
    public function indexAction();
}