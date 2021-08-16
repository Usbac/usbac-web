<?php

namespace Controller\Admin;

use Wolff\Core\Container;
use Wolff\Core\Language;
use Wolff\Core\View;

class Category extends \Wolff\Core\Controller
{


    /**
     * Category list.
     */
    public function list()
    {
        View::render('admin/category_list', [
            'lang'       => Language::get('admin'),
            'categories' => Container::get('db')->query("SELECT c.*, COUNT(p.post_id) AS posts
                FROM category c
                LEFT JOIN post p ON c.category_id = p.category_id
                GROUP BY p.category_id
                ORDER BY category_id DESC")->get(),
        ]);
    }


    /**
     * Edit category.
     */
    public function edit($req)
    {
        $id = $req->query('id') ?? 0;
        $category = Container::get('db')->select('category', 'category_id = ?', $id)[0] ?? null;
        View::render('admin/category_form', [
            'lang'       => Language::get('admin'),
            'id'         => $id,
            'category'   => $category ?? [],
            'editing'    => isset($category),
        ]);
    }


    /**
     * Save category.
     */
    public function save($req)
    {
        $id = $req->query('id') ?? 0;
        $db = Container::get('db');

        if ($db->count('category', 'category_id = ?', $id) > 0) {
            $db->query('UPDATE category SET name = ?
                WHERE category_id = ?', $req->body('name'), $id);
        } else {
            $db->insert('category', [
                'name' => $req->body('name'),
            ]);
        }

        redirect(url('admin/category/list'));
    }


    /**
     * Delete category.
     */
    public function delete($req)
    {
        Container::get('db')->query('DELETE FROM category
            WHERE category_id = ?', $req->query('id') ?? 0);
        redirect(url('admin/category/list'));
    }
}
