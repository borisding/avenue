<?php
namespace App\Controllers;

class FooController extends \Avenue\Controller
{
    public function indexAction()
    {
        return true;
    }

    public function beforeAction()
    {
        return true;
    }

    public function testAction()
    {
        return true;
    }

    public function afterAction()
    {
        return true;
    }
}