<script>
    (() => {
        const storageKey = 'theme';
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

        const isValidPreference = (value) => ['dark', 'light', 'system'].includes(value);

        const getPreference = () => {
            const stored = localStorage.getItem(storageKey);

            return isValidPreference(stored) ? stored : 'system';
        };

        const resolveTheme = (preference) => {
            if (preference === 'system') {
                return mediaQuery.matches ? 'dark' : 'light';
            }

            return preference === 'dark' ? 'dark' : 'light';
        };

        const applyTheme = (preference = getPreference()) => {
            const normalizedPreference = isValidPreference(preference) ? preference : 'system';
            const mode = resolveTheme(normalizedPreference);

            document.documentElement.classList.toggle('dark', mode === 'dark');
            document.documentElement.setAttribute('data-theme-preference', normalizedPreference);
            document.documentElement.setAttribute('data-theme-mode', mode);

            window.dispatchEvent(new CustomEvent('modulify-theme-changed', {
                detail: { preference: normalizedPreference, mode },
            }));

            return mode;
        };

        const setTheme = (preference) => {
            const normalizedPreference = isValidPreference(preference) ? preference : 'system';

            localStorage.setItem(storageKey, normalizedPreference);

            return applyTheme(normalizedPreference);
        };

        const toggleTheme = () => {
            const currentMode = resolveTheme(getPreference());
            const nextPreference = currentMode === 'dark' ? 'light' : 'dark';

            return setTheme(nextPreference);
        };

        window.modulifyTheme = {
            storageKey,
            getPreference,
            resolveTheme,
            applyTheme,
            setTheme,
            toggleTheme,
        };

        window.modulifyThemeState = () => ({
            themePreference: 'system',
            themeMode: 'light',
            initTheme() {
                const syncThemeState = () => {
                    const manager = window.modulifyTheme;

                    if (! manager) {
                        return;
                    }

                    this.themePreference = manager.getPreference();
                    this.themeMode = manager.resolveTheme(this.themePreference);
                };

                syncThemeState();
                window.addEventListener('modulify-theme-changed', syncThemeState);
            },
            setTheme(preference) {
                const manager = window.modulifyTheme;

                if (! manager) {
                    return;
                }

                this.themeMode = manager.setTheme(preference);
                this.themePreference = manager.getPreference();
            },
            toggleTheme() {
                const manager = window.modulifyTheme;

                if (! manager) {
                    return;
                }

                this.themeMode = manager.toggleTheme();
                this.themePreference = manager.getPreference();
            },
        });

        applyTheme();

        if (typeof mediaQuery.addEventListener === 'function') {
            mediaQuery.addEventListener('change', () => {
                if (getPreference() === 'system') {
                    applyTheme('system');
                }
            });
        } else if (typeof mediaQuery.addListener === 'function') {
            mediaQuery.addListener(() => {
                if (getPreference() === 'system') {
                    applyTheme('system');
                }
            });
        }
    })();
</script>
