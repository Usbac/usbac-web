<?php

namespace Controller\Admin;

use Wolff\Core\Language;
use Wolff\Core\View;

class Image extends \Wolff\Core\Controller
{


    /**
     * List images.
     */
    public function list()
    {
        $images = [];

        foreach (scandir(path(config('image_options.dir'))) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $file_path = config('image_options.dir') . "/$file";
            $file_size = getimagesize(path($file_path));

            $images[] = [
                'name'       => $file,
                'size'       => round(filesize(path($file_path)) / 1024, 2),
                'time'       => $time = filemtime(path($file_path)),
                'date'       => date('d/m/Y', $time),
                'resolution' => $file_size[0] . 'x' . $file_size[1] . ' ' . $file_size['mime'],
                'url'        => url($file_path),
            ];
        }

        usort($images, function ($a, $b) {
            return $a['time'] < $b['time'];
        });

        View::render('admin/image_list', [
            'lang'   => Language::get('admin'),
            'images' => $images,
        ]);
    }


    /**
     * Add image.
     */
    public function save($req)
    {
        $req->fileOptions(config('image_options'));
        $req->file('image')->upload();
        redirect(url('admin/image/list'));
    }


    /**
     * Delete image.
     */
    public function delete($req)
    {
        unlink(path(config('image_options.dir') . '/' . $req->query('name')));
        redirect(url('admin/image/list'));
    }
}
