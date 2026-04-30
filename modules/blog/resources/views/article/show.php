<?php /** @var \App\Blog\Entity\Article $article */
?>
<h1><?php echo htmlspecialchars($article->title); ?></h1>
<div><?php echo nl2br(htmlspecialchars($article->content)); ?></div>
<a href="/articles">Back to list</a>
