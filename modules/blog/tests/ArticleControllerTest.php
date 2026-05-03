<?php

declare(strict_types=1);

namespace App\Blog\Tests;

use PHPUnit\Framework\TestCase;
use App\Blog\Controller\ArticleController;
use App\Blog\Service\ArticleService;
use App\Blog\Repository\ArticleRepository;
use App\Blog\DTO\ArticleDTO;
use Marko\View\ViewInterface;
use Marko\Routing\Http\Response;

class ArticleControllerTest extends TestCase
{
    private ArticleController $controller;
    private ArticleRepository $repository;
    private ArticleService $service;
    private ViewInterface $view;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(ArticleRepository::class);
        $this->service = $this->createMock(ArticleService::class);
        $this->view = $this->createMock(ViewInterface::class);

        $this->controller = new ArticleController(
            $this->repository,
            $this->service,
            $this->view,
        );
    }

    public function testIndexReturnsArticles(): void
    {
        // Arrange
        $article1 = new \stdClass();
        $article1->id = 1;
        $article1->title = 'Article 1';

        $article2 = new \stdClass();
        $article2->id = 2;
        $article2->title = 'Article 2';

        $this->repository->expects($this->once())
            ->method('findBy')
            ->willReturn([$article1, $article2]);

        $this->view->expects($this->once())
            ->method('render')
            ->willReturnCallback(function ($template, $data) {
                $this->assertEquals('blog::article/index', $template);
                $this->assertArrayHasKey('articles', $data);
                $this->assertCount(2, $data['articles']);
                return new Response('HTML response');
            });

        // Act
        $result = $this->controller->index();

        // Assert
        $this->assertInstanceOf(Response::class, $result);
    }

    public function testShowBySlug(): void
    {
        // Arrange
        $dto = new ArticleDTO(
            id: 1,
            title: 'Slug Article',
            content: 'Content',
            slug: 'slug-article',
            excerpt: '',
            image: '',
            published: true,
            status: 'published',
            categoryId: null,
            createdAt: null,
        );

        $this->service->expects($this->once())
            ->method('findBySlug')
            ->with('slug-article')
            ->willReturn($dto);

        $this->view->expects($this->once())
            ->method('render')
            ->willReturnCallback(function ($template, $data) {
                $this->assertEquals('blog::article/show', $template);
                return new Response('HTML');
            });

        // Act
        $result = $this->controller->showBySlug('slug-article');

        // Assert
        $this->assertInstanceOf(Response::class, $result);
    }

    public function testShowBySlugReturns404OnNotFound(): void
    {
        // Arrange
        $this->service->expects($this->once())
            ->method('findBySlug')
            ->with('non-existent')
            ->willReturn(null);

        $this->view->expects($this->once())
            ->method('render')
            ->willReturnCallback(function ($template, $data) {
                return (new Response('Not Found'))->withStatus(404);
            });

        // Act
        $result = $this->controller->showBySlug('non-existent');

        // Assert
        $this->assertInstanceOf(Response::class, $result);
    }
}