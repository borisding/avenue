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
        $this->response->write('Example of Admin DefaultController.');
    }

    public function afterAction()
    {
        parent::afterAction();
    }
}
