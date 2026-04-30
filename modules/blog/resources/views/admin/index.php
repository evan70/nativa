<?php /** @var array $articles */
?>
<h1>Articles Administration</h1>
<a href="/mark/articles/new">Create New Article</a>
<ul>
<?php foreach ($articles as $article): ?>
    <li><?php echo htmlspecialchars($article->title); ?> - <a href="/mark/articles/edit/<?php echo $article->id; ?>">edit</a></li>
<?php endforeach; ?>
</ul>
