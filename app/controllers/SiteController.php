<?php

namespace app\controllers;

use app\core\Controller;

class SiteController extends Controller
{
    public function home(): string
    {
        return $this->render('site/home');
    }

    public function contact(): string
    {
        $this->view->title = 'Contact';

        return $this->render('site/contact');
    }
}
