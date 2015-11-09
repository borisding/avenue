<?php
namespace App\Controllers\Admin;

use Avenue\Controller;

class DefaultController extends Controller
{
    public function beforeAction()
    {
        parent::beforeAction();
    }
    
    public function indexAction()
    {
        // template variable through view object
        $this->view->title = 'Avenue | PHP Framework';
        
        // template variable as second parameter
        $page = $this->view->fetch('layout', [
           'content' => '<h3>Hello from Admin page.</h3>'
        ]);
        
        $this->response->write($page);
    }
    
    public function afterAction()
    {
        parent::afterAction();
    }
}