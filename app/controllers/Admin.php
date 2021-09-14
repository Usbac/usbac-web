<?php

namespace Controller;

use Wolff\Core\Language;
use Wolff\Core\View;
use Wolff\Core\Session;

class Admin extends \Wolff\Core\Controller
{


    /**
     * Login.
     */
    public function login($req, $res)
    {
        Session::start();
        Session::unset('login');
        View::render('admin/login', [
            'lang' => Language::get('admin'),
        ]);
    }


    /**
     * Enter admin.
     */
    public function start($req)
    {
        Session::set('login', $req->body() == config('admin'));
        redirect(url('admin/post/list'));
    }
}
