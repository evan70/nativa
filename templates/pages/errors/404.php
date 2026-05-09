<?php
// Self-contained 404 page — no PhpView layout dependency
// Used by Router::renderNotFoundResponse() which renders via plain include
use App\View;
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Page not found — Nativa CMS" />
    <title>404 — Page Not Found</title>
    <link rel="icon" type="image/svg+xml" href="/dist/favicon.svg" />
    <?= View::viteCss('core') ?>
    <?= View::viteCss('page-errors') ?>
</head>
<body class="page-errors">
    <main>
        <section class="section">
            <div class="container container--narrow">
                <header class="page-header">
                    <p class="page-header__subtitle">404</p>
                    <h1 class="page-header__title"><?= htmlspecialchars($heading ?? 'Page not found', ENT_QUOTES, 'UTF-8') ?></h1>
                    <p class="page-header__subtitle"><?= htmlspecialchars($description ?? 'The page you are looking for does not exist.', ENT_QUOTES, 'UTF-8') ?></p>
                </header>

                <article class="card card--large">
                    <div class="card__body">
                        <p class="card__subtitle">You can try one of these pages instead.</p>
                    </div>
                    <footer class="card__footer">
                        <a href="/" class="btn btn--secondary">Home</a>
                        <a href="/portfolio" class="btn btn--secondary">Portfolio</a>
                        <a href="/articles" class="btn btn--secondary">Blog</a>
                    </footer>
                </article>
            </div>
        </section>
    </main>
    <?= View::viteJs('init') ?>
    <?= View::viteJs('core') ?>
    <?= View::viteJs('page-errors') ?>
</body>
</html>
