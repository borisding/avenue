<?php
namespace App\Controllers;

use Avenue\Controller;

class PageController extends Controller
{
    /**
     * View layout.
     * 
     * @var mixed
     */
    protected $layout;
    
    /**
     * Page title.
     * 
     * @var string
     */
    protected $title = 'Avenue Framework | ';
    
    /**
     * Page controller before action.
     * Can add some settings here for extended child class usage.
     * 
     * @see \Avenue\Controller::beforeAction()
     */
    public function beforeAction()
    {
        parent::beforeAction();
        
        $this->layout = 'layouts/page';
    }
    
    /**
     * Page controller index action.
     * 
     * @see \Avenue\Controller::indexAction()
     */
    public function indexAction()
    {
        // do nothing
    }
    
    /**
     * Page controller after action.
     * 
     * @see \Avenue\Controller::afterAction()
     */
    public function afterAction()
    {
        parent::afterAction();
    }
}