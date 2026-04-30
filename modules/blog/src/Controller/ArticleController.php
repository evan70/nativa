<?php

declare(strict_types=1);

namespace App\Blog\Controller;

use App\Blog\Repository\ArticleRepository;
use App\Blog\Entity\Article;
use Marko\Routing\Attributes\Get;
use Marko\Routing\Http\Response;

use Marko\View\ViewInterface;

class ArticleController
{
    public function __construct(
        private readonly ArticleRepository $repository,
        private readonly ViewInterface $view,
    ) {}

    #[Get('/articles')]
    public function index(): Response
    {
        $articles = $this->repository->findAll();

        return $this->view->render('blog::article/index', [
            'title' => 'Articles',
            'message' => 'Read existing articles.',
            'articles' => $articles,
        ]);
    }

    #[Get('/articles/{id}')]
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


}
