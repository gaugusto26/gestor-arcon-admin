</main><!-- /main -->
</div><!-- /dash -->

<script>
/* ════════════════════════════════════════════════════════════════════
   SHARED JS — sidebar, dropdown, dark/light theme
════════════════════════════════════════════════════════════════════ */
(function () {
    const html    = document.documentElement;
    const sidebar = document.getElementById('sidebar');
    const sbTog   = document.getElementById('sbTog');
    const sbBtn   = document.getElementById('sbTogBtn');
    const main    = document.querySelector('.main');
    const overlay = document.getElementById('overlay');
    const isMob   = () => window.innerWidth <= 768;

    // ── Sidebar ───────────────────────────────────────────────────────
    function openMob() {
        sidebar.classList.add('mob-open');
        overlay.classList.add('show');
        // shift toggle pill next to sidebar edge
        sbTog.style.left = 'var(--sb-w)';
    }

    function closeMob() {
        sidebar.classList.remove('mob-open');
        overlay.classList.remove('show');
        sbTog.style.left = '0';
    }

    function toggleDesktop() {
        const col = sidebar.classList.toggle('collapsed');
        if (main) main.classList.toggle('expanded', col);
        sbTog.classList.toggle('collapsed', col);
    }

    sbBtn.addEventListener('click', () => {
        if (isMob()) {
            sidebar.classList.contains('mob-open') ? closeMob() : openMob();
        } else {
            toggleDesktop();
        }
    });

    overlay.addEventListener('click', closeMob);

    window.addEventListener('resize', () => {
        if (!isMob()) closeMob();
    });

    // ── User dropdown ─────────────────────────────────────────────────
    const userBtn  = document.getElementById('userBtn');
    const userDrop = document.getElementById('userDrop');

    if (userBtn && userDrop) {
        userBtn.addEventListener('click', e => {
            e.stopPropagation();
            const open = userDrop.classList.toggle('open');
            userBtn.classList.toggle('open', open);
        });
        document.addEventListener('click', () => {
            userDrop.classList.remove('open');
            userBtn.classList.remove('open');
        });
    }

    // ── Notificação ──────────────────────────────────────────────────
    const notifBtn = document.getElementById('notifBtn');
    if (notifBtn) {
        notifBtn.addEventListener('click', () => {
            window.location.href = '?mod=faturas';
        });
    }

    // ── Theme toggle (salva no banco via AJAX) ────────────────────────
    const themeTog = document.getElementById('themeTog');
    if (themeTog) {
        themeTog.addEventListener('click', () => {
            const isDark = html.getAttribute('data-theme') === 'dark';
            const novo   = isDark ? 'light' : 'dark';
            html.setAttribute('data-theme', novo);

            // Rebuild charts com novas cores
            if (typeof rebuildCharts === 'function') rebuildCharts(novo);

            // Persiste no banco
            const fd = new FormData();
            fd.append('action', 'toggle_tema');
            fd.append('tema', novo);
            fetch(window.location.pathname, { method: 'POST', body: fd })
                .catch(() => {/* silencioso */});
        });
    }
})();
</script>
</body>
</html>