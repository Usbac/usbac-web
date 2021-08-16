<?php

namespace Controller\Admin;

use Wolff\Core\Container;
use Wolff\Core\Language;
use Wolff\Core\View;

class Post extends \Wolff\Core\Controller
{


    /**
     * Posts list.
     */
    public function list()
    {
        View::render('admin/post_list', [
            'lang'  => Language::get('admin'),
            'posts' => Container::get('db')->query("SELECT p.*, c.name as category
                FROM post p
                INNER JOIN category c ON p.category_id = c.category_id
                ORDER BY post_id DESC")->get(),
        ]);
    }


    /**
     * Edit post.
     */
    public function edit($req)
    {
        $id = $req->query('id') ?? 0;
        $post = Container::get('db')->select('post', 'post_id = ?', $id)[0] ?? null;
        View::render('admin/post_form', [
            'lang'       => Language::get('admin'),
            'id'         => $id,
            'post'       => $post ?? [],
            'editing'    => isset($post),
            'categories' => Container::get('db')->select('category'),
        ]);
    }


    /**
     * Save post.
     */
    public function save($req)
    {
        $id = $req->query('id') ?? 0;
        $db = Container::get('db');

        if ($req->hasFile('file_image')) {
            $req->fileOptions(config('image_options'));
            $req->file('file_image')->upload();
        }

        if ($db->count('post', 'post_id = ?', $id) > 0) {
            $db->query('UPDATE post SET
                    title = ?,
                    description = ?,
                    category_id = ?,
                    content = ?,
                    image = ?,
                    status = ?,
                    date = ?
                WHERE post_id = ?',
                $req->body('title'),
                $req->body('description'),
                $req->body('category_id'),
                $req->body('content'),
                $req->body('image'),
                $req->body('status'),
                $req->body('date'),
                $id
            );
        } else {
            $db->insert('post', [
                'title'       => $req->body('title'),
                'description' => $req->body('description'),
                'category_id' => $req->body('category_id'),
                'content'     => $req->body('content'),
                'image'       => $req->body('image'),
                'status'      => $req->body('status'),
                'date'        => $req->body('date'),
            ]);
        }

        redirect(url('admin/post/list'));
    }


    /**
     * Delete post.
     */
    public function delete($req)
    {
        Container::get('db')->query('DELETE FROM post
            WHERE post_id = ?', $req->query('id') ?? 0);
        redirect(url('admin/post/list'));
    }

}
