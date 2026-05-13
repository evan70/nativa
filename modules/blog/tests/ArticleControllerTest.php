<?php

declare(strict_types=1);

namespace App\Blog\Tests;

use PHPUnit\Framework\TestCase;
use App\Blog\Controller\ArticleController;
use App\Blog\Service\ArticleService;
use App\Blog\Repository\ArticleRepository;
use App\Blog\DTO\ArticleDTO;
use App\Blog\Entity\Article;
use Marko\View\ViewInterface;
use Marko\Routing\Http\Request;
use Marko\Routing\Http\Response;

class ArticleControllerTest extends TestCase
{
    private ArticleRepository $repository;
    private ArticleService $service;
    private ViewInterface $view;

    protected function setUp(): void
    {
        parent::setUp();

        // Create mocks
        $this->repository = $this->createMock(ArticleRepository::class);
        $this->service = new ArticleService($this->repository);
        $this->view = $this->createMock(ViewInterface::class);
    }

    public function testIndexReturnsArticles(): void
    {
        // Arrange
        $article1 = new Article();
        $article1->id = 1;
        $article1->title = 'Article 1';
        $article1->content = 'Content 1';
        $article1->slug = 'article-1';
        $article1->excerpt = 'Excerpt 1';
        $article1->image = '';
        $article1->published = true;
        $article1->status = 'published';
        $article1->categoryId = null;
        $article1->createdAt = '2026-05-13 06:51:40';

        $article2 = new Article();
        $article2->id = 2;
        $article2->title = 'Article 2';
        $article2->content = 'Content 2';
        $article2->slug = 'article-2';
        $article2->excerpt = 'Excerpt 2';
        $article2->image = '';
        $article2->published = true;
        $article2->status = 'published';
        $article2->categoryId = null;
        $article2->createdAt = '2026-05-12 06:51:40';

        // The controller calls findPublished() which internally calls findAll()
        // and then filter/paginate, so we need to expect findAll() to be called
        $this->repository->expects($this->exactly(2)) // Called once in findPublished, once in countPublished
            ->method('findAll')
            ->willReturn([$article1, $article2]);

        $this->view->expects($this->once())
            ->method('render')
            ->willReturnCallback(function ($template, $data) {
                $this->assertEquals('pages/articles/index', $template);
                $this->assertArrayHasKey('articles', $data);
                $this->assertCount(2, $data['articles']);
                return new Response('HTML response');
            });

        $controller = new ArticleController(
            $this->repository,
            $this->service,
            $this->view,
        );

        // Act
        $_GET = ['page' => 1];
        $request = new Request();
        $result = $controller->index($request);

        // Assert
        $this->assertInstanceOf(Response::class, $result);
    }

    public function testShowBySlug(): void
    {
        // Arrange
        $article = new Article();
        $article->id = 1;
        $article->title = 'Slug Article';
        $article->content = 'Content';
        $article->slug = 'slug-article';
        $article->excerpt = '';
        $article->image = '';
        $article->published = true;
        $article->status = 'published';
        $article->categoryId = null;
        $article->createdAt = null;

        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with(['slug' => 'slug-article'])
            ->willReturn($article);

        $this->view->expects($this->once())
            ->method('render')
            ->willReturnCallback(function ($template, $data) {
                $this->assertEquals('pages/articles/show', $template);
                return new Response('HTML');
            });

        $controller = new ArticleController(
            $this->repository,
            $this->service,
            $this->view,
        );

        // Act
        $result = $controller->show('slug-article');

        // Assert
        $this->assertInstanceOf(Response::class, $result);
    }

    public function testShowBySlugReturns404OnNotFound(): void
    {
        // Arrange
        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with(['slug' => 'non-existent'])
            ->willReturn(null);

        $this->view->expects($this->once())
            ->method('render')
            ->willReturnCallback(function ($template, $data) {
                return (new Response('Not Found'))->withStatus(404);
            });

        $controller = new ArticleController(
            $this->repository,
            $this->service,
            $this->view,
        );

        // Act
        $result = $controller->show('non-existent');

        // Assert
        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals(404, $result->statusCode());
    }
}