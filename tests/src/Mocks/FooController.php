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
        parent::beforeAction();
        return true;
    }

    public function testAction()
    {
        return true;
    }

    public function getAction()
    {
        return true;
    }

    public function deleteAction()
    {
        return true;
    }

    public function putAction()
    {
        return true;
    }

    public function postAction()
    {
        return true;
    }

    public function afterAction()
    {
        parent::afterAction();
        return true;
    }
}
