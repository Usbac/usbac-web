<?php

namespace Controller;

use Wolff\Core\Language;
use Wolff\Core\View;
use Wolff\Core\Session;

class Admin extends \Wolff\Core\Controller
{


    /**
     * Options.
     */
    public function options()
    {
        $msg = '';
        if (Session::has('msg')) {
            $msg = Session::get('msg');
            Session::unset('msg');
        }

        View::render('admin/options', [
            'lang' => Language::get('admin'),
            'msg'  => $msg,
        ]);
    }


    /**
     * Login.
     */
    public function login($req, $res)
    {
        Session::start();
        Session::unset('login');
        View::render('admin/login');
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
