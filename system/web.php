<?php

use Wolff\Core\Route;
use Wolff\Core\View;
use Wolff\Core\Container;
use Wolff\Core\DB;
use Wolff\Core\Language;
use Wolff\Core\Middleware;
use Wolff\Core\Session;

Route::get('/', [ Controller\Home::class, 'index' ]);
Route::get('blog', [ Controller\Home::class, 'blog' ]);
Route::get('post/{id}', [ Controller\Home::class, 'post' ]);
Route::get('contact', [ Controller\Home::class, 'contact' ]);
Route::post('contact/send', [ Controller\Home::class, 'email' ]);

// Admin

Route::get('admin', [ Controller\Admin::class, 'login' ]);
Route::post('admin/start', [ Controller\Admin::class, 'start' ]);

Route::get('admin/post/list', [ Controller\Admin\Post::class, 'list' ]);
Route::get('admin/post/edit/{id?}', [ Controller\Admin\Post::class, 'edit' ]);
Route::get('admin/post/delete', [ Controller\Admin\Post::class, 'delete' ]);
Route::post('admin/post/save', [ Controller\Admin\Post::class, 'save' ]);

Route::get('admin/image/list', [ Controller\Admin\Image::class, 'list' ]);
Route::get('admin/image/delete', [ Controller\Admin\Image::class, 'delete' ]);
Route::post('admin/image/add', [ Controller\Admin\Image::class, 'save' ]);
Route::get('json:admin/image/get', [ Controller\Admin\Image::class, 'getImages' ]);

Route::get('admin/category/list', [ Controller\Admin\Category::class, 'list' ]);
Route::get('admin/category/edit/{id?}', [ Controller\Admin\Category::class, 'edit' ]);
Route::get('admin/category/delete', [ Controller\Admin\Category::class, 'delete' ]);
Route::post('admin/category/save', [ Controller\Admin\Category::class, 'save' ]);

Route::get('admin/option/list', [ Controller\Admin\Option::class, 'list' ]);
Route::get('admin/option/clearCache', [ Controller\Admin\Option::class, 'clearCache' ]);
Route::get('admin/option/dbDownload', [ Controller\Admin\Option::class, 'dbDownload' ]);
Route::post('admin/option/dbUpload', [ Controller\Admin\Option::class, 'dbUpload' ]);
Route::get('admin/option/phpinfo', [ Controller\Admin\Option::class, 'phpInfo' ]);

// Extra

Route::code(404, function () {
    View::render('main/404', [
        'lang' => Language::get('main'),
    ]);
});

Container::singleton('db', function () {
    return new DB;
});

Middleware::before('admin/*', function () {
    Session::start();

    if (Session::get('login') !== true && getCurrentPage() != 'admin/start') {
        http_response_code(404);
        redirect(url('admin'));
    }
});

function getPostInfoLine($date, $category, $time) {
    return "$date · $category · $time " . Wolff\Core\Language::get('main')['minutes_suffix'];
}
