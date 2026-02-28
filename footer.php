<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <h3>CalcOnlineHub</h3>
            <p>Многофункциональная платформа калькуляторов с автоматизацией SEO и перелинковки.</p>
        </div>
        <div>
            <h4>SEO и сервисы</h4>
            <ul>
                <li><a href="<?php echo esc_url(home_url('/calculator-sitemap.xml')); ?>">XML карта калькуляторов</a></li>
                <li><a href="<?php echo esc_url(get_post_type_archive_link('calculator')); ?>">Все калькуляторы</a></li>
            </ul>
        </div>
    </div>
    <div class="container footer-bottom">© <?php echo esc_html(date('Y')); ?> CalcOnlineHub</div>
</footer>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('mobileMenuBtn');
    const nav = document.getElementById('mainNav');
    if (!btn || !nav) return;

    btn.addEventListener('click', function () {
        nav.classList.toggle('active');
        btn.classList.toggle('active');
    });
});
</script>
<?php wp_footer(); ?>
</body>
</html>
