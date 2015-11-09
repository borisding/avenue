<?php
namespace App\Controllers;

use Avenue\Controller;

class DefaultController extends Controller
{
    protected $logger;
    
    public function beforeAction()
    {
        parent::beforeAction();
        // create logger instance
        $this->logger = $this->app->singleton('logger');
    }
    
    public function indexAction()
    {
        // template variable through view object
        $this->view->title = 'Avenue | PHP Framework';
        
        // template variable as second parameter
        $page = $this->view->fetch('layout', [
           'content' => '<h3>Hello! Welcome to Avenue.</h3>'
        ]);
        
        $this->response->write($page);
        
        // log info example
        $this->logger->info('page rendered!');
    }
    
    public function afterAction()
    {
        parent::afterAction();
    }
}