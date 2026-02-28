<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <h3 class="footer-logo">CalcOnlineHub</h3>
                <p>Самый точный агрегатор калькуляторов для профессионалов и студентов.</p>
            </div>
            <div class="footer-col">
                <h4>Навигация</h4>
                <ul><li><a href="/">Все калькуляторы</a></li></ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> CalculatorOnlineHub. Все права защищены.</p>
        </div>
    </div>
</footer>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('mobileMenuBtn');
    const nav = document.getElementById('mainNav');
    if(btn && nav) btn.addEventListener('click', () => { nav.classList.toggle('active'); btn.classList.toggle('active'); });
});
</script>
<?php wp_footer(); ?>
</body>
</html>