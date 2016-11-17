<?php
/**
 * Example of PageController.
 */
namespace App\Controllers;

use Avenue\Controller;

class PageController extends Controller
{
    /**
     * View layout template.
     *
     * @var mixed
     */
    protected $template;

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
     * Page controller before action.
     * Can add some settings here for child class usage.
     *
     * @see \Avenue\Controller::beforeAction()
     */
    public function beforeAction()
    {
        parent::beforeAction();

        // default values
        $this->template = 'page';
        $this->title = 'Avenue Framework | ';
        $this->content= '';
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
        $page = $this->view->layout($this->template, [
            'title' => $this->title,
            'content' => $this->content
        ]);

        // write to body
        $this->response->write($page);
    }
}
