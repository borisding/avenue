<?php
namespace App\Controllers;

use App\Controllers\PageController;

class DefaultController extends PageController
{
    public function beforeAction()
    {
        parent::beforeAction();
    }

    public function indexAction()
    {
        // assign index title and content
        $this->title .= 'Demo Page';
        $this->content = $this->view->partial('home');
    }

    public function afterAction()
    {
        parent::afterAction();
    }
}
