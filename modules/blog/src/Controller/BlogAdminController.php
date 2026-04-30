<?php

declare(strict_types=1);

namespace App\Blog\Controller;

use App\Blog\Entity\Article;
use App\Blog\Repository\ArticleRepository;
use Marko\Routing\Attributes\Get;
use Marko\Routing\Attributes\Post;
use Marko\Routing\Http\Request;
use Marko\Routing\Http\Response;
use Marko\View\ViewInterface;

class BlogAdminController
{
    public function __construct(
        private readonly ArticleRepository $articleRepository,
        private readonly ViewInterface $view,
    ) {}

    /**
     * Admin list of articles.
     */
    #[Get(path: '/mark/articles')]
    public function index(): Response
    {
        $articles = $this->articleRepository->findAll();
        return $this->view->render('blog::admin/index', [
            'title' => 'Articles Administration',
            'articles' => $articles,
        ]);
    }

    /**
     * Form to create a new article.
     */
    #[Get(path: '/mark/articles/new')]
    public function create(): Response
    {
        return $this->view->render('blog::admin/create', [
            'title' => 'Create New Article',
        ]);
    }

    /**
     * Store a new article (POST).
     */
    #[Post(path: '/mark/articles')]
    public function store(Request $request): Response
    {
        $title = $request->post('title', '');
        $content = $request->post('content', '');

        $article = new Article();
        $article->title = $title;
        $article->content = $content;

        $this->articleRepository->save($article);

        return new Response('', 302, ['Location' => '/mark/articles']);
    }
}
