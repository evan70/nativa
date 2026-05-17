<?php

declare(strict_types=1);

use Marko\Database\Migration\Migration;
use Marko\Database\Connection\ConnectionInterface;

return new class extends Migration
{
    public function up(ConnectionInterface $connection): void
    {
        // Check if articles table exists
        try {
            $connection->query('SELECT 1 FROM articles LIMIT 1');
        } catch (\Exception $e) {
            // Table doesn't exist or other error - skip optimization
            return;
        }
        
        // Update article images with optimized Cloudinary URLs
        try {
            $articles = $connection->query('SELECT id, image FROM articles WHERE image LIKE "%/upload/%"');
            
            foreach ($articles as $article) {
                $oldImage = $article['image'];
                $newImage = $this->optimizeImageUrl($oldImage);
                
                if ($newImage !== $oldImage) {
                    $connection->execute(
                        'UPDATE articles SET image = ? WHERE id = ?',
                        [$newImage, $article['id']]
                    );
                }
            }
        } catch (\Exception $e) {
            // Skip if there are any issues with the query
            // This migration is not critical for functionality
        }
    }

    public function down(ConnectionInterface $connection): void
    {
        // This migration is not reversible as we don't store the original URLs
    }

    private function optimizeImageUrl(string $url): string
    {
        // Skip already optimized URLs
        if (str_contains($url, 'f_auto,q_auto:eco')) {
            return $url;
        }

        // Skip non-Cloudinary URLs
        if (!str_contains($url, 'res.cloudinary.com')) {
            return $url;
        }

        // Remove .webp extension if present
        $url = rtrim($url, '.webp');

        // Remove any existing transformations
        if (str_contains($url, '/upload/')) {
            $parts = explode('/upload/', $url, 2);
            if (isset($parts[1])) {
                $url = 'https://res.cloudinary.com/epithemic/image/upload/f_auto,q_auto:eco/' . $parts[1];
            }
        }

        return $url;
    }
};