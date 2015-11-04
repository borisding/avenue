<?php
namespace App\Controllers;

use Avenue\Controller;

class DefaultController extends Controller
{
    public function beforeAction()
    {
        parent::beforeAction();
    }
    
    public function indexAction()
    {
        $this->response->write('Hello! Welcome to Avenue.');
    }
    
    public function afterAction()
    {
        parent::afterAction();
    }
}