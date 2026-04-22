<?php

declare(strict_types=1);

namespace App\Blog\Controller;

use App\Controllers\View;
use App\Blog\Repository\ArticleRepository;
use App\Blog\Entity\Article;
use Marko\Routing\Attributes\Get;
use Marko\Routing\Attributes\Post;
use Marko\Routing\Http\Response;

class ArticleController
{
    public function __construct(
        private readonly ArticleRepository $repository,
    ) {}

    #[Get('/blog')]
    public function index(): Response
    {
        $articles = $this->repository->findAll();

        return Response::html(View::render('blog/index.phtml', [
            'title' => 'Blog',
            'message' => 'Read existing posts or publish a new one.',
            'articles' => $articles,
        ]));
    }

    #[Get('/blog/{id}')]
    public function show(int $id): Response
    {
        $article = $this->repository->find($id);

        if ($article === null) {
            return Response::html(View::render('blog/not-found.phtml', [
                'title' => 'Article Not Found',
                'message' => 'The requested article does not exist.',
            ]), 404);
        }

        return Response::html(View::render('blog/show.phtml', [
            'title' => $article->title,
            'article' => $article,
        ]));
    }

    #[Get('/articles/new')]
    public function create(): Response
    {
        return Response::html(View::render('blog/create.phtml', [
            'title' => 'New Article',
            'message' => 'Draft something worth keeping.',
        ]));
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
