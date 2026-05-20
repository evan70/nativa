<?php

declare(strict_types=1);

namespace App\Blog\Tests;

use PHPUnit\Framework\TestCase;
use App\Blog\Service\ArticleService;
use App\Blog\DTO\CreateArticleRequest;
use App\Blog\DTO\UpdateArticleRequest;
use App\Blog\Entity\Article;
use App\Blog\Repository\ArticleRepository;
use Marko\Database\Entity\EntityCollection;

class ArticleServiceTest extends TestCase
{
    private ArticleService $service;
    private ArticleRepository $repository;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        parent::setUp();

        // Create mock repository
        $this->repository = $this->createMock(ArticleRepository::class);

        // Create service without logger
        $this->service = new ArticleService($this->repository);
    }

    public function testCreateArticle(): void
    {
        // Arrange
        $request = new CreateArticleRequest(
            title: 'Test Article',
            content: 'Test content',
            slug: 'test-article',
            excerpt: '',
            image: '',
            published: true,
            categoryId: null,
        );

        $article = new Article();
        $article->id = 1;
        $article->title = 'Test Article';
        $article->content = 'Test content';
        $article->slug = 'test-article';

        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with(['slug' => 'test-article'])
            ->willReturn(null);

        $this->repository->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($a) use ($article) {
                return $a instanceof Article;
            }));

        // Act
        $result = $this->service->createArticle($request);

        // Assert
        $this->assertEquals('Test Article', $result->title);
        $this->assertEquals('test-article', $result->slug);
    }

    public function testCreateArticleThrowsOnDuplicateSlug(): void
    {
        // Arrange
        $request = new CreateArticleRequest(
            title: 'Test Article',
            content: 'Test content',
            slug: 'existing-slug',
            excerpt: '',
            image: '',
            published: true,
            categoryId: null,
        );

        $existingArticle = new Article();
        $existingArticle->id = 1;
        $existingArticle->slug = 'existing-slug';

        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with(['slug' => 'existing-slug'])
            ->willReturn($existingArticle);

        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('already exists');

        // Act
        $this->service->createArticle($request);
    }

    public function testUpdateArticle(): void
    {
        // Arrange
        $request = new UpdateArticleRequest(
            title: 'Updated Title',
            content: null,
            slug: null,
            excerpt: null,
            image: null,
            published: null,
            categoryId: null,
        );

        $article = new Article();
        $article->id = 1;
        $article->title = 'Original Title';
        $article->content = 'Original content';
        $article->slug = 'original-slug';
        $article->published = false;

        $this->repository->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($article);

        $this->repository->expects($this->once())
            ->method('save');

        // Act
        $result = $this->service->updateArticle(1, $request);

        // Assert
        $this->assertEquals('Updated Title', $result->title);
    }

    public function testUpdateArticleThrowsOnNotFound(): void
    {
        // Arrange
        $request = new UpdateArticleRequest(
            title: 'New Title',
            content: null,
            slug: null,
            excerpt: null,
            image: null,
            published: null,
            categoryId: null,
        );

        $this->repository->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);

        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('not found');

        // Act
        $this->service->updateArticle(999, $request);
    }

    public function testDeleteArticle(): void
    {
        // Arrange
        $article = new Article();
        $article->id = 1;
        $article->title = 'To Delete';

        $this->repository->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($article);

        $this->repository->expects($this->once())
            ->method('delete')
            ->with($article);

        // Act
        $result = $this->service->deleteArticle(1);

        // Assert
        $this->assertTrue($result);
    }

    public function testDeleteArticleReturnsFalseOnNotFound(): void
    {
        // Arrange
        $this->repository->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);

        // Act
        $result = $this->service->deleteArticle(999);

        // Assert
        $this->assertFalse($result);
    }

    public function testFindBySlug(): void
    {
        // Arrange
        $article = new Article();
        $article->id = 1;
        $article->title = 'Test Article';
        $article->slug = 'test-article';

        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with(['slug' => 'test-article'])
            ->willReturn($article);

        // Act
        $result = $this->service->findBySlug('test-article');

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals('test-article', $result->slug);
    }

    public function testFindBySlugReturnsNullOnNotFound(): void
    {
        // Arrange
        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with(['slug' => 'non-existent-slug'])
            ->willReturn(null);

        // Act
        $result = $this->service->findBySlug('non-existent-slug');

        // Assert
        $this->assertNull($result);
    }

    public function testFindPublished(): void
    {
        // Arrange
        $article1 = new Article();
        $article1->id = 1;
        $article1->title = 'Article 1';
        $article1->published = true;

        $article2 = new Article();
        $article2->id = 2;
        $article2->title = 'Article 2';
        $article2->published = true;

        $this->repository->expects($this->once())
            ->method('findAll')
            ->willReturn(new EntityCollection([$article1, $article2]));

        // Act
        $results = $this->service->findPublished();

        // Assert
        $this->assertCount(2, $results);
    }

    public function testFindByCategory(): void
    {
        // Arrange
        $article = new Article();
        $article->id = 1;
        $article->title = 'Category Article';
        $article->categoryId = 5;
        $article->published = true;

        $this->repository->expects($this->once())
            ->method('findAll')
            ->willReturn(new EntityCollection([$article]));

        // Act
        $results = $this->service->findByCategory(5);

        // Assert
        $this->assertCount(1, $results);
    }
}