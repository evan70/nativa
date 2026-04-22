<?php

declare(strict_types=1);

namespace App\Blog\Controller;

use App\Blog\Repository\ArticleRepository;
use Marko\Routing\Attributes\Get;
use Marko\Routing\Attributes\Post;
use Marko\Routing\Http\Response;

class ArticleController
{
    public function __construct(
        private readonly ArticleRepository $repository,
    ) {}

    #[Get('/blog')]
    public function index(): string
    {
        $articles = $this->repository->findAll();
        
        $html = "<h1>Blog</h1>\n";
        $html .= "<a href='/articles/new'>New Article</a>\n<ul>\n";
        
        foreach ($articles as $article) {
            $html .= "  <li><a href='/blog/{$article->id}'>{$article->title}</a></li>\n";
        }
        
        $html .= "</ul>";
        
        return $html;
    }

    #[Get('/blog/{id}')]
    public function show(int $id): string
    {
        $article = $this->repository->find($id);
        
        if ($article === null) {
            return "Article not found";
        }
        
        return "<h1>{$article->title}</h1>
<p>{$article->content}</p>
<p><small>Created: {$article->createdAt}</small></p>
<a href='/blog'>Back</a>";
    }

    #[Get('/articles/new')]
    public function create(): string
    {
        return "<h1>New Article</h1>
<form action='/blog' method='POST'>
  <p><label>Title: <input type='text' name='title' required></label></p>
  <p><label>Content: <textarea name='content' required></textarea></label></p>
  <p><button type='submit'>Create</button></p>
</form>
<a href='/blog'>Back</a>";
    }

    #[Post('/blog')]
    public function store(): Response
    {
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        
        $article = new \App\Blog\Entity\Article();
        $article->title = $title;
        $article->content = $content;
        
        $this->repository->save($article);
        
        return new Response('', 302, ['Location' => '/blog']);
    }
}