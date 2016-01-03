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
     * @var mixed
     */
    protected $title;
    
    /**
     * Page content.
     * 
     * @var mixed
     */
    protected $content;
    
    /**
     * List of css files.
     * 
     * @var array
     */
    protected $css = [];
    
    /**
     * List of script files.
     * 
     * @var array
     */
    protected $scripts = [];
    
    /**
     * Page controller before action.
     * Can add some settings here for child class usage.
     * 
     * @see \Avenue\Controller::beforeAction()
     */
    public function beforeAction()
    {
        parent::beforeAction();
        
        // default values
        $this->layout = 'layouts/page';
        $this->title = 'Avenue Framework | ';
        $this->content= '';
        
        // css and scripts assignments
        $this->css = ['bootstrap', 'style'];
        $this->scripts = ['jquery-2.1.4.min', 'bootstrap.min'];
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
        
        // fetching page view tempate by passing parameters
        $page = $this->view->fetch($this->layout, [
            'css' => $this->css,
            'scripts' => $this->scripts,
            'title' => $this->title,
            'content' => $this->content
        ]);
        
        // write to body
        $this->response->write($page);
    }
}