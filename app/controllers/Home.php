<?php

namespace Controller;

use Wolff\Core\Container;
use Wolff\Core\Language;
use Wolff\Core\Session;
use Wolff\Core\View;

class Home extends \Wolff\Core\Controller
{

    const WPM = 210;
    const EMAIL = 'contacto@usbac.com.ve';
    const PER_PAGE = 6;
    const SIDE_PAGES = 2;
    const PROJECTS = [
        [
            'title'       => 'Borealis',
            'img'         => 'borealis.png',
            'url'         => 'https://github.com/usbac/borealis',
            'language'    => 'ANSI C',
            'description' => 'Lenguaje de programación elegante y consistente.',
            'version'     => '0.1.0',
        ],
        [
            'title'       => 'Wolff',
            'img'         => 'wolff.png',
            'url'         => 'https://github.com/usbac/wolff',
            'language'    => 'PHP',
            'description' => 'Framework ligero y rapido para construir paginas web.',
            'version'     => '3.2.2',
        ],
        [
            'title'       => 'Tundra',
            'img'         => 'tundra.png',
            'url'         => 'https://github.com/usbac/tundra',
            'language'    => 'Javascript',
            'description' => 'Motor de plantillas personalizable para NodeJS.',
            'version'     => '2.0.1',
        ],
        [
            'title'       => 'Quich',
            'img'         => 'quich.png',
            'url'         => 'https://github.com/usbac/quich',
            'language'    => 'ANSI C',
            'description' => 'Calculadora con opciones avanzadas para el terminal.',
            'version'     => '3.0.0',
        ],
    ];


    /**
     * Home page.
     */
    public function index($req, $res)
    {
        View::render('main/home', [
            'lang'     => Language::get('main'),
            'page'     => 'home',
            'projects' => self::PROJECTS,
            'posts'    => $this->getPosts('', 0),
        ]);
    }


    /**
     * Blog page.
     */
    public function blog($req, $res)
    {
        $search = $req->query('search') ?? '';
        $pagination = new \Wolff\Utils\Pagination(
            $this->getTotalPosts($search),
            self::PER_PAGE,
            $req->query('page') ?? 1,
            self::SIDE_PAGES,
            url("blog?page={page}&search=$search")
        );

        View::render('main/blog', [
            'lang'       => Language::get('main'),
            'page'       => 'blog',
            'pagination' => $pagination->get(),
            'posts'      => $this->getPosts($search, $req->query('page')),
            'search'     => $search,
        ]);
    }


    /**
     * Post page.
     */
    public function post($req, $res)
    {
        $post = Container::get('db')->query("SELECT p.*, c.name as category FROM post p
            INNER JOIN category c ON c.category_id = p.category_id
            WHERE p.status = 1 AND p.post_id = ?", $req->query('id') ?? 0)->first();

        if (empty($post)) {
            return $res->setHeader('Location', url());
        }

        // Remove current post from other posts list
        $others = $this->getPosts(null, 1);
        foreach ($others as $key => $val) {
            if ($val['post_id'] == $req->query('id')) {
                unset($others[$key]);
            }
        }

        View::render('main/post', [
            'lang'        => Language::get('main'),
            'post_id'     => $post['post_id'],
            'title'       => $post['title'],
            'description' => $post['description'],
            'category'    => $post['category'],
            'image'       => $post['image'],
            'date'        => $this->getDate($post['date']),
            'html'        => (new \cebe\markdown\GithubMarkdown())->parse($post['content']),
            'time'        => $this->getWPM($post['content']),
            'others'      => array_slice($others, 0, 3),
        ]);
    }


    /**
     * Contact page.
     */
    public function contact()
    {
        Session::start();

        $msg = '';
        if (Session::has('contact_msg')) {
            $msg = Session::get('contact_msg');
            Session::unset('contact_msg');
        }

        View::render('main/contact', [
            'lang'  => Language::get('main'),
            'page'  => 'contact',
            'email' => self::EMAIL,
            'msg'   => $msg,
        ]);
    }


    /**
     * Send email.
     */
    public function email($req, $res)
    {
        $result = $req->has('title') && $req->has('email') && $req->has('content') ?
            mail(self::EMAIL, $req->body('title'), $req->body('email') . "\n" . $req->body('content')) :
            false;

        Session::start();
        Session::set('contact_msg', Language::get('main')[ $result ? 'contact_success' : 'contact_error' ]);
        redirect(url('contact'));
    }


    private function getPosts($search = '', $page = 1)
    {
        $search = '%' . $search . '%';
        $posts = Container::get('db')->query("SELECT p.*, c.name as category FROM post p
            INNER JOIN category c ON p.category_id = c.category_id
            WHERE p.status = 1 AND (p.title LIKE ? OR p.description LIKE ?)
            ORDER BY date DESC
            LIMIT " . ($page - 1) * self::PER_PAGE . ", " . self::PER_PAGE, $search, $search)->get();

        // Format posts
        foreach ($posts as $key => $val) {
            $posts[$key]['date'] = $this->getDate($val['date']);
            $posts[$key]['time'] = $this->getWPM($val['content']);
        }

        return $posts;
    }


    private function getTotalPosts($search = '')
    {
        $search = "%$search%";

        return Container::get('db')->query("SELECT * FROM post p
            INNER JOIN category c ON p.category_id = c.category_id
            WHERE p.status = 1 AND (p.title LIKE ? OR p.description LIKE ?)", $search, $search)->count();
    }


    private function getWPM($str)
    {
        return floor(str_word_count($str) / self::WPM);
    }


    private function getDate($date)
    {
        return strtr(strftime('%e %B %Y', strtotime($date)), [
            'January'   => 'Ene',
            'February'  => 'Feb',
            'March'     => 'Mar',
            'April'     => 'Abr',
            'May'       => 'May',
            'June'      => 'Jun',
            'July'      => 'Jul',
            'August'    => 'Ago',
            'September' => 'Sep',
            'October'   => 'Oct',
            'November'  => 'Nov',
            'December'  => 'Dic',
        ]);
    }
}
