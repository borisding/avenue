<?php
namespace App\Controllers\Admin;

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
        $this->title .= 'Admin';
        $this->content = '<h3>Hello from Admin page.</h3>';
    }
    
    public function afterAction()
    {
        parent::afterAction();
    }
}