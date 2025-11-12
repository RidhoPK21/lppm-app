<header class="bg-background sticky inset-x-0 top-0 isolate flex shrink-0 items-center gap-2 border-b m-0"
    style="z-index: 1000;">
    <div class="flex h-14 w-full items-center gap-2 px-4">
        <button type="button" onclick="document.dispatchEvent(new CustomEvent('basecoat:sidebar'))"
            aria-label="Toggle sidebar" data-tooltip="Toggle sidebar" data-side="bottom" data-align="start"
            class="btn-sm-icon-ghost mr-auto size-7 -ml-1.5">
            <i class="ti ti-layout-sidebar" style="font-size: 1.25rem;"></i>
        </button>
        <select class="select h-8 leading-none" id="theme-select">
            <option value="">Default</option>
            <option value="blue">Blue</option>
            <option value="green">Green</option>
            <option value="orange">Orange</option>
            <option value="red">Red</option>
            <option value="pink">Pink</option>
            <option value="violet">Violet</option>
            <option value="yellow">Yellow</option>
        </select>
        <script>
            (() => {
                const themeSelect = document.getElementById('theme-select');
                const storedTheme = localStorage.getItem('themeVariant');
                if (themeSelect && storedTheme) themeSelect.value = storedTheme;
                themeSelect.addEventListener('change', () => {
                    const newTheme = themeSelect.value;
                    document.documentElement.classList.forEach(c => {
                        if (c.startsWith('theme-')) document.documentElement.classList.remove(c);
                    });
                    if (newTheme) {
                        document.documentElement.classList.add(`theme-${newTheme}`);
                        localStorage.setItem('themeVariant', newTheme);
                    } else {
                        localStorage.removeItem('themeVariant');
                    }
                });
            })();
        </script>

        <button type="button" aria-label="Toggle dark mode" data-tooltip="Toggle dark mode" data-side="bottom"
            onclick="document.dispatchEvent(new CustomEvent('basecoat:theme'))" class="btn-icon-outline size-8">
            <i class="ti ti-sun block dark:hidden" style="font-size: 18px;"></i>
            <i class="ti ti-moon hidden dark:block" style="font-size: 18px;"></i>
        </button>

    </div>
</header>
