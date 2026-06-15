    </div> <!-- Fecha main-content -->
    
    <script>
        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const body = document.body;

        if(themeToggle) {
            themeToggle.addEventListener('click', () => {
                const currentTheme = body.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                
                body.setAttribute('data-theme', newTheme);
                document.cookie = `admin_theme=${newTheme}; path=/`;
                
                themeIcon.className = newTheme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
            });
        }

        // User Dropdown
        const userDropdown = document.getElementById('userDropdown');
        const userMenu = document.getElementById('userMenu');

        if(userDropdown) {
            userDropdown.addEventListener('click', (e) => {
                e.stopPropagation();
                userMenu.classList.toggle('show');
            });

            document.addEventListener('click', () => {
                userMenu.classList.remove('show');
            });
        }

        // Notificação
        const notificationBtn = document.getElementById('notificationBtn');
        if(notificationBtn) {
            notificationBtn.addEventListener('click', () => {
                alert('Sistema de notificações em desenvolvimento');
            });
        }
    </script>
</body>
</html>