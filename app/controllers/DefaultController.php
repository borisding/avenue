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
        // template variable through view object
        $this->view->title = $this->title .= 'Default';
        
        // template variable as second parameter
        $page = $this->view->fetch($this->layout, [
           'content' => '<h3>Hello! Welcome to Avenue.</h3>'
        ]);
        
        $this->response->write($page);
    }
    
    public function afterAction()
    {
        parent::afterAction();
    }
}