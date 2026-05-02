<?php $this->layout('app.layouts.app') ?>

<?php $this->section('content') ?>
    <section class="hero-section hero-section--fw" data-section="hero">
        <div class="hero-section__content">
            <h1 class="hero-section__title"><?= $this->e($title) ?></h1>
            <p class="hero-section__description"><?= $this->e($message) ?></p>
        </div>
    </section>

    <section class="features-section">
        <div class="container">
            <div class="card" style="max-width: 600px; margin: 0 auto;">
                <div class="card__body">
                    <form action="/articles/" method="post" style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <div class="field" style="display: flex; flex-direction: column; gap: 0.5rem;">
                            <label for="title" style="font-weight: bold; color: var(--color-text);">Title</label>
                            <input type="text" id="title" name="title" required style="padding: 0.75rem; border-radius: var(--radius-md); border: 1px solid var(--color-border); background: var(--color-bg); color: var(--color-text);">
                        </div>

                        <div class="field" style="display: flex; flex-direction: column; gap: 0.5rem;">
                            <label for="content" style="font-weight: bold; color: var(--color-text);">Content</label>
                            <textarea id="content" name="content" required rows="10" style="padding: 0.75rem; border-radius: var(--radius-md); border: 1px solid var(--color-border); background: var(--color-bg); color: var(--color-text); resize: vertical;"></textarea>
                        </div>

                        <div class="actions" style="display: flex; gap: 1rem; margin-top: 1rem;">
                            <button type="submit" class="btn">Publish Article</button>
                            <a href="/articles/" class="btn btn--secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
<?php $this->endSection() ?>
