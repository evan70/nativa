<?php

declare(strict_types=1);

namespace App\Blog\Controller;

use App\Blog\Repository\ArticleRepository;
use App\Blog\Entity\Article;
use Marko\Routing\Attributes\Get;
use Marko\Routing\Attributes\Post;
use Marko\Routing\Http\Response;

use Marko\View\ViewInterface;

class ArticleController
{
    public function __construct(
        private readonly ArticleRepository $repository,
        private readonly ViewInterface $view,
    ) {}

    #[Get('/blog')]
    public function index(): Response
    {
        $articles = $this->repository->findAll();

        return $this->view->render('blog::article/index', [
            'title' => 'Blog',
            'message' => 'Read existing articles or publish a new one.',
            'articles' => $articles,
        ]);
    }

    #[Get('/blog/{id}')]
    public function show(int $id): Response
    {
        $article = $this->repository->find($id);

        if ($article === null) {
            return $this->view->render('blog::article/not-found', [
                'title' => 'Article Not Found',
                'message' => 'The requested article does not exist.',
            ])->withStatus(404);
        }

        return $this->view->render('blog::article/show', [
            'title' => $article->title,
            'article' => $article,
        ]);
    }

    #[Get('/articles/new')]
    public function create(): Response
    {
        return $this->view->render('blog::article/create', [
            'title' => 'New Article',
            'message' => 'Draft something worth keeping.',
        ]);
    }

    #[Post('/blog')]
    public function store(): Response
    {
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';

        $article = new Article();
        $article->title = $title;
        $article->content = $content;

        $this->repository->save($article);

        return new Response('', 302, ['Location' => '/blog']);
    }
}
