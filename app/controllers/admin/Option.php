<?php

namespace Controller\Admin;

use Wolff\Core\Cache;
use Wolff\Core\Language;
use Wolff\Core\Session;
use Wolff\Core\View;

class Option extends \Wolff\Core\Controller
{


    /**
     * Options list.
     */
    public function list()
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
     * Clear view cache.
     */
    public function clearCache()
    {
        Cache::clear();
        Session::start();
        Session::set('msg', Language::get('admin.cache_success'));
        redirect(url('admin/option/list'));
    }


    /**
     * Download database file.
     */
    public function dbDownload($req, $res)
    {
        $file_path = path(config('db_file'));
        $res->setHeader('Content-Description', 'File Transfer');
        $res->setHeader('Content-Type', 'application/octet-stream');
        $res->setHeader('Content-Disposition', 'attachment; filename=' . basename($file_path));
        $res->setHeader('Content-Length', filesize($file_path));
        $res->send();
        readfile($file_path);
    }


    /**
     * Upload database file.
     */
    public function dbUpload($req)
    {
        $req->fileOptions([
            'override' => true,
        ]);

        if ($req->file('db')->upload(config('db_file'))) {
            Session::set('msg', Language::get('admin.db_success'));
        }

        redirect(url('admin/option/list'));
    }


    /**
     * Show PHP information.
     */
    public function phpInfo()
    {
        phpinfo();
    }
}
