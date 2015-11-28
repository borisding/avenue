<?php
namespace App\Controllers;

use App\Controllers\PageController;
use App\Models\User;

class DefaultController extends PageController
{
    public function beforeAction()
    {
        parent::beforeAction();
    }
    
    public function indexAction()
    {
        // assign index title and content
        $this->title .= 'Default';
        $this->content = '<h3>Hello! Welcome to Avenue.</h3>';
        
        $user = new User();
    }
    
    public function afterAction()
    {
        parent::afterAction();
    }
}