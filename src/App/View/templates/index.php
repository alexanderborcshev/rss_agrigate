<?php
use App\Util;

/** @var array $categories */
/** @var string $catSlug */
/** @var string $dateFrom */
/** @var string $dateTo */
/** @var callable $urlWith */
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Новости (RSS агрегатор)</title>
    <style>
        body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;margin:0;background:#f6f7f9;color:#111}
        header{background:#1a73e8;color:#fff;padding:16px 20px}
        main{max-width:1000px;margin:0 auto;padding:20px}
        .filters{background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:12px;margin-bottom:16px;display:flex;gap:12px;flex-wrap:wrap}
        .filters label{font-size:12px;color:#555;display:block}
        .filters input,.filters select{padding:8px;border:1px solid #d1d5db;border-radius:6px}
        .news-item{background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:16px;margin-bottom:12px;display:flex;gap:12px}
        .news-thumb{width:140px;flex:0 0 140px}
        .news-thumb img{width:140px;height:90px;object-fit:cover;border-radius:6px}
        .news-content h3{margin:0 0 8px;font-size:18px}
        .news-meta{color:#666;font-size:12px;margin-bottom:8px}
        .pagination{display:flex;gap:8px;margin-top:16px}
        .pagination a,.pagination span{padding:6px 10px;border:1px solid #d1d5db;border-radius:6px;background:#fff;text-decoration:none;color:#1a73e8}
        .pagination .active{background:#1a73e8;color:#fff;border-color:#1a73e8}
        .actions a{color:#1a73e8;text-decoration:none}
        form .row{display:flex;gap:12px;align-items:flex-end}
        .btn{padding:8px 12px;border:0;border-radius:6px;background:#1a73e8;color:#fff;cursor:pointer}
        .btn-secondary{background:#f3f4f6;color:#111}
    </style>
    </head>
<body>
<header>
    <h1>Лента новостей</h1>
    </header>
<main>
    <div class="filters">
        <form method="get" action="">
            <div class="row">
                <div>
                    <label>Категория</label>
                    <label>
                        <select name="category">
                            <option value="">Все</option>
                            <?php foreach ($categories as $c): $sel = ($catSlug === $c['slug']) ? 'selected' : ''; ?>
                                <option value="<?= Util::h($c['slug']) ?>" <?= $sel ?>><?= Util::h($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <div>
                    <label>С даты</label>
                    <label>
                        <input type="date" name="date_from" value="<?= Util::h($dateFrom) ?>">
                    </label>
                </div>
                <div>
                    <label>По дату</label>
                    <label>
                        <input type="date" name="date_to" value="<?= Util::h($dateTo) ?>">
                    </label>
                </div>
                <div>
                    <button class="btn" type="submit">Фильтровать</button>
                    <a class="btn btn-secondary" href="/">Сброс</a>
                </div>
            </div>
        </form>
    </div>

    <?php if (empty($result['items'])): ?>
        <p>Нет новостей по заданным фильтрам.</p>
    <?php else: ?>
        <?php foreach ($result['items'] as $n): ?>
            <article class="news-item">
                <div class="news-thumb">
                    <?php if (!empty($n['image_url'])): ?>
                        <img src="<?= Util::h($n['image_url']) ?>" alt="thumb" loading="lazy">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/140x90?text=No+Image" alt="no image">
                    <?php endif; ?>
                </div>
                <div class="news-content">
                    <h3><a href="<?= Util::h('article.php?id=' . (int)$n['id']) ?>"><?= Util::h($n['title']) ?></a></h3>
                    <div class="news-meta">Опубликовано: <?= Util::h(date('d.m.Y H:i', strtotime($n['pub_date']))) ?></div>
                    <div class="excerpt">
                        <?= Util::h(strip_tags(mb_strimwidth((string)($n['description'] ?: $n['content']), 0, 200, '...'))) ?>
                    </div>
                    <div style="margin-top:8px;">
                        <a href="<?= Util::h($n['link']) ?>" target="_blank">Оригинал</a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>

        <?php if ($result['pages'] > 1): ?>
            <div class="pagination">
                <?php for ($p = 1; $p <= $result['pages']; $p++): ?>
                    <?php if ($p === $result['page']): ?>
                        <span class="active"><?= $p ?></span>
                    <?php else: ?>
                        <a href="<?= Util::h($urlWith(['page' => $p])) ?>"><?= $p ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    </main>
</body>
</html>
