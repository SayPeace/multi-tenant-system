        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div>
                    <h3><?= htmlspecialchars($tenant['name']) ?></h3>
                    <?php if (!empty($tenant['description'])): ?>
                    <p><?= htmlspecialchars(substr($tenant['description'], 0, 200)) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <h3>Quick Links</h3>
                    <ul style="list-style: none;">
                        <li><a href="<?= $baseUrl ?>/articles">Articles</a></li>
                        <li><a href="<?= $baseUrl ?>/archives">Archives</a></li>
                        <li><a href="<?= $baseUrl ?>/editorial-board">Editorial Board</a></li>
                        <?php foreach ($menuPages as $menuPage): ?>
                        <li><a href="<?= $baseUrl ?>/<?= htmlspecialchars($menuPage['slug'] ?? '') ?>"><?= htmlspecialchars($menuPage['title'] ?? '') ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div>
                    <h3>Contact</h3>
                    <?php if (!empty($tenant['contact']['email'])): ?>
                    <p>Email: <a href="mailto:<?= htmlspecialchars($tenant['contact']['email']) ?>"><?= htmlspecialchars($tenant['contact']['email']) ?></a></p>
                    <?php endif; ?>
                    <?php if (!empty($tenant['contact']['phone'])): ?>
                    <p>Phone: <?= htmlspecialchars($tenant['contact']['phone']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($tenant['contact']['address'])): ?>
                    <p><?= nl2br(htmlspecialchars($tenant['contact']['address'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($tenant['name']) ?>. All rights reserved.</p>
                <p style="margin-top: 10px; font-size: 0.85rem;">
                    Powered by Multi-Tenant Journal System
                </p>
            </div>
        </div>
    </footer>
</body>
</html>
