<?php /** @var array $articles */
?>
<h1>Articles</h1>
<ul>
<?php foreach ($articles as $article): ?>
    <li><a href="/articles/<?php echo htmlspecialchars($article->slug ?? $article->id); ?>"><?php echo htmlspecialchars($article->title); ?></a></li>
<?php endforeach; ?>
</ul>
