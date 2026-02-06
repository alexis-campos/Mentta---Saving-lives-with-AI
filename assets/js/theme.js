/**
 * MENTTA - Theme Manager
 * Handles dark/light mode switching and persistence
 */

const Theme = {
    /**
     * Initialize theme on page load
     */
    init() {
        // Check for saved theme or use default
        const savedTheme = localStorage.getItem('mentta-theme');
        if (savedTheme) {
            this.apply(savedTheme);
        } else {
            // Check system preference
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            this.apply(prefersDark ? 'dark' : 'light');
        }

        // Listen for system preference changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (!localStorage.getItem('mentta-theme')) {
                this.apply(e.matches ? 'dark' : 'light');
            }
        });
    },

    /**
     * Get current theme
     */
    get() {
        return document.documentElement.getAttribute('data-theme') || 'light';
    },

    /**
     * Apply theme to document
     */
    apply(theme) {
        document.documentElement.setAttribute('data-theme', theme);

        // Update meta theme-color for mobile browsers
        const metaThemeColor = document.querySelector('meta[name="theme-color"]');
        if (metaThemeColor) {
            metaThemeColor.content = theme === 'dark' ? '#1F2937' : '#6366F1';
        }

        // Update any toggle switches on the page
        const toggles = document.querySelectorAll('#theme-toggle, #profile-theme-toggle');
        toggles.forEach(toggle => {
            if (toggle) toggle.checked = theme === 'dark';
        });
    },

    /**
     * Toggle between light and dark
     */
    toggle() {
        const newTheme = this.get() === 'dark' ? 'light' : 'dark';
        this.apply(newTheme);
        localStorage.setItem('mentta-theme', newTheme);
        this.saveToServer(newTheme);
    },

    /**
     * Save theme preference to server
     */
    async saveToServer(theme) {
        try {
            const formData = new FormData();
            formData.append('theme', theme);

            await fetch('api/patient/update-theme.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
        } catch (error) {
            console.error('Error saving theme preference:', error);
        }
    },

    /**
     * Load theme from server (for logged-in users)
     */
    async loadFromServer() {
        try {
            const response = await fetch('api/patient/get-preferences.php', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();

            if (data.success && data.data.theme) {
                this.apply(data.data.theme);
                localStorage.setItem('mentta-theme', data.data.theme);
            }
        } catch (error) {
            console.error('Error loading theme preference:', error);
        }
    }
};

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', () => {
    Theme.init();
});

// Export for global access
window.Theme = Theme;
window.toggleTheme = () => Theme.toggle();
