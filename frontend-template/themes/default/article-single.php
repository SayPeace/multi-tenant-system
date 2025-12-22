<?php
$pageTitle = ($article['title'] ?? '') . ' - ' . $tenant['name'];
$metaDescription = $article['meta_description'] ?? substr(strip_tags($article['abstract'] ?? ''), 0, 160);
include __DIR__ . '/header.php';
?>

<article>
    <div class="card">
        <h1 style="margin-bottom: 20px; line-height: 1.4;"><?= htmlspecialchars($article['title'] ?? '') ?></h1>

        <!-- Authors -->
        <?php if (!empty($authors)): ?>
        <div style="margin-bottom: 20px;">
            <strong>Authors:</strong>
            <?php
            $authorNames = array_map(function($a) {
                $name = htmlspecialchars($a['author_name'] ?? '');
                if ($a['is_corresponding'] ?? false) {
                    $name .= '*';
                }
                return $name;
            }, $authors);
            echo implode(', ', $authorNames);
            ?>
        </div>

        <!-- Author Affiliations -->
        <div style="margin-bottom: 20px; font-size: 0.9rem; color: #666;">
            <?php foreach ($authors as $author): ?>
            <?php if (!empty($author['author_affiliation'])): ?>
            <div>
                <?= htmlspecialchars($author['author_name'] ?? '') ?>:
                <?= htmlspecialchars($author['author_affiliation']) ?>
                <?php if ($author['is_corresponding'] ?? false): ?>
                <em>(Corresponding author)</em>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Article Meta -->
        <div class="article-meta" style="padding: 15px; background: #f9f9f9; border-radius: 4px; margin-bottom: 25px;">
            <?php if (!empty($article['published_at'])): ?>
            <div><strong>Published:</strong> <?= date('F j, Y', strtotime($article['published_at'])) ?></div>
            <?php endif; ?>
            <?php if (!empty($article['doi'])): ?>
            <div><strong>DOI:</strong> <a href="https://doi.org/<?= htmlspecialchars($article['doi']) ?>" target="_blank"><?= htmlspecialchars($article['doi']) ?></a></div>
            <?php endif; ?>
            <?php if (!empty($article['pages'])): ?>
            <div><strong>Pages:</strong> <?= htmlspecialchars($article['pages']) ?></div>
            <?php endif; ?>
            <div><strong>Views:</strong> <?= number_format($article['view_count'] ?? 0) ?></div>
            <div><strong>Downloads:</strong> <?= number_format($article['download_count'] ?? 0) ?></div>
        </div>

        <!-- PDF Download -->
        <?php if (!empty($article['pdf_url'])): ?>
        <div style="margin-bottom: 25px;">
            <a href="<?= htmlspecialchars($article['pdf_url']) ?>" class="btn btn-primary" target="_blank" download>
                Download PDF
            </a>
        </div>
        <?php endif; ?>

        <!-- Abstract -->
        <?php if (!empty($article['abstract'])): ?>
        <div style="margin-bottom: 25px;">
            <h2 style="font-size: 1.2rem; margin-bottom: 10px;">Abstract</h2>
            <div style="text-align: justify; line-height: 1.8;">
                <?= nl2br(htmlspecialchars($article['abstract'])) ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Keywords -->
        <?php if (!empty($article['keywords'])): ?>
        <div style="margin-bottom: 25px;">
            <h2 style="font-size: 1.2rem; margin-bottom: 10px;">Keywords</h2>
            <p><?= htmlspecialchars($article['keywords']) ?></p>
        </div>
        <?php endif; ?>

        <!-- Full Content (if available) -->
        <?php if (!empty($article['content'])): ?>
        <div style="margin-bottom: 25px;">
            <h2 style="font-size: 1.2rem; margin-bottom: 10px;">Full Text</h2>
            <div class="page-content">
                <?= $article['content'] ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Citation -->
        <div style="margin-top: 30px; padding: 15px; background: #f0f7ff; border-radius: 4px;">
            <h3 style="font-size: 1rem; margin-bottom: 10px;">How to Cite</h3>
            <p style="font-size: 0.9rem;">
                <?php
                $authorList = array_map(fn($a) => $a['author_name'] ?? '', $authors);
                $authorStr = count($authorList) > 2
                    ? $authorList[0] . ' et al.'
                    : implode(' & ', $authorList);
                $year = date('Y', strtotime($article['published_at'] ?? 'now'));
                ?>
                <?= htmlspecialchars($authorStr) ?> (<?= $year ?>).
                <?= htmlspecialchars($article['title'] ?? '') ?>.
                <em><?= htmlspecialchars($tenant['name']) ?></em>.
                <?php if (!empty($article['doi'])): ?>
                https://doi.org/<?= htmlspecialchars($article['doi']) ?>
                <?php endif; ?>
            </p>
        </div>
    </div>

    <!-- Back Link -->
    <div style="margin-top: 20px;">
        <a href="<?= $baseUrl ?>/articles">&larr; Back to Articles</a>
    </div>
</article>

<!-- Structured Data (JSON-LD) -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "ScholarlyArticle",
    "headline": <?= json_encode($article['title'] ?? '') ?>,
    "abstract": <?= json_encode($article['abstract'] ?? '') ?>,
    "datePublished": <?= json_encode($article['published_at'] ?? '') ?>,
    "author": [
        <?php
        $jsonAuthors = [];
        foreach ($authors as $author) {
            $jsonAuthors[] = json_encode([
                "@type" => "Person",
                "name" => $author['author_name'] ?? '',
                "affiliation" => $author['author_affiliation'] ?? null
            ]);
        }
        echo implode(",\n        ", $jsonAuthors);
        ?>
    ],
    "publisher": {
        "@type": "Organization",
        "name": <?= json_encode($tenant['name']) ?>
    }
    <?php if (!empty($article['doi'])): ?>
    ,"identifier": {
        "@type": "PropertyValue",
        "propertyID": "DOI",
        "value": <?= json_encode($article['doi']) ?>
    }
    <?php endif; ?>
}
</script>

<?php include __DIR__ . '/footer.php'; ?>
