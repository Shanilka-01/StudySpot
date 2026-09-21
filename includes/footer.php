</main>

<footer class="site-footer">
    <div class="site-footer__inner">
        <div>
            <p class="logo__text logo__text--footer">Study<span>Spot</span></p>
            <p class="muted">Find quiet places to study across Sri Lanka.</p>
        </div>
        <div>
            <h4>Explore</h4>
            <a href="<?= url('pages/explore.php') ?>">All study spaces</a>
            <a href="<?= url('pages/map.php') ?>">Map view</a>
            <a href="<?= url('pages/my-bookings.php') ?>">My bookings</a>
        </div>
        <div>
            <h4>Support</h4>
            <a href="<?= url('pages/help.php') ?>">Help &amp; FAQ</a>
            <a href="<?= url('pages/about.php') ?>">About us</a>
            <a href="mailto:support@studyspot.lk">support@studyspot.lk</a>
        </div>
    </div>
    <p class="site-footer__bottom">&copy; <?= date('Y') ?> StudySpot &middot; Web Architecture Group Project</p>
</footer>

<script src="<?= asset('js/main.js') ?>"></script>
</body>
</html>
