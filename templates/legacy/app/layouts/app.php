<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->e($title ?? 'Nativa') ?></title>
    <style>
        :root {
            color-scheme: light;
            --bg: #efe6d3;
            --bg-accent: #dcefe6;
            --panel: rgba(255, 251, 243, 0.9);
            --panel-strong: #fffaf1;
            --ink: #1f2a1f;
            --muted: #5f655b;
            --line: rgba(76, 70, 56, 0.16);
            --accent: #1f6f50;
            --accent-strong: #144c37;
            --shadow: 0 24px 80px rgba(44, 36, 22, 0.12);
        }

        * {
            box-sizing: border-box;
        }

        html {
            min-height: 100%;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Georgia, "Times New Roman", serif;
            color: var(--ink);
            background:
                radial-gradient(circle at top left, rgba(31, 111, 80, 0.14), transparent 30%),
                radial-gradient(circle at bottom right, rgba(169, 122, 52, 0.10), transparent 28%),
                linear-gradient(135deg, var(--bg), #f7f0e2);
        }

        a {
            color: var(--accent-strong);
        }

        .shell {
            width: min(1100px, calc(100% - 2rem));
            margin: 0 auto;
            padding: 1.5rem 0 4rem;
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            padding: 1rem 1.25rem;
            border: 1px solid var(--line);
            border-radius: 20px;
            background: rgba(255, 250, 241, 0.72);
            backdrop-filter: blur(12px);
            box-shadow: 0 12px 40px rgba(44, 36, 22, 0.08);
        }

        .brand {
            font-size: 1.2rem;
            font-weight: bold;
            text-decoration: none;
            letter-spacing: 0.04em;
        }

        .nav-links {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .nav-links a {
            padding: 0.55rem 0.85rem;
            border-radius: 999px;
            text-decoration: none;
            color: var(--muted);
        }

        .nav-links a:hover {
            background: var(--bg-accent);
            color: var(--accent-strong);
        }

        .page {
            padding: clamp(1.5rem, 4vw, 3rem);
            border: 1px solid var(--line);
            border-radius: 28px;
            background: var(--panel);
            box-shadow: var(--shadow);
        }

        .eyebrow {
            display: inline-block;
            margin: 0 0 1rem;
            padding: 0.35rem 0.7rem;
            border-radius: 999px;
            background: var(--bg-accent);
            color: var(--accent);
            font-size: 0.8rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        h1, h2, h3 {
            margin-top: 0;
            line-height: 1;
        }

        h1 {
            margin-bottom: 1rem;
            font-size: clamp(2.6rem, 6vw, 5.4rem);
        }

        p {
            color: var(--muted);
            line-height: 1.7;
        }

        .stack {
            display: grid;
            gap: 1.25rem;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .button,
        button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.8rem 1.1rem;
            border: 0;
            border-radius: 999px;
            background: var(--accent);
            color: #f7f6f1;
            font: inherit;
            text-decoration: none;
            cursor: pointer;
        }

        .button.secondary {
            background: transparent;
            color: var(--accent-strong);
            border: 1px solid var(--line);
        }

        .cards {
            display: grid;
            gap: 1rem;
        }

        .card {
            padding: 1.2rem 1.25rem;
            border: 1px solid var(--line);
            border-radius: 20px;
            background: var(--panel-strong);
        }

        .meta {
            font-size: 0.92rem;
            color: var(--muted);
        }

        .article-content {
            white-space: pre-wrap;
        }

        .field {
            display: grid;
            gap: 0.45rem;
        }

        label {
            font-size: 0.95rem;
            color: var(--muted);
        }

        input,
        textarea {
            width: 100%;
            padding: 0.9rem 1rem;
            border: 1px solid var(--line);
            border-radius: 18px;
            background: #fffef9;
            color: var(--ink);
            font: inherit;
        }

        textarea {
            min-height: 14rem;
            resize: vertical;
        }
    </style>
</head>
<body>
<div class="shell">
    <?= $this->include('controllers::partials/nav') ?>
    <main class="page">
        <?= $this->yield('content') ?>
    </main>
</div>
</body>
</html>
