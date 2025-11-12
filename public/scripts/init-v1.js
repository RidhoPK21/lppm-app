// Inisialisasi mode tema berdasarkan preferensi pengguna atau penyimpanan lokal
(() => {
    try {
        const stored = localStorage.getItem("themeMode");
        if (
            stored
                ? stored === "dark"
                : matchMedia("(prefers-color-scheme: dark)").matches
        ) {
            document.documentElement.classList.add("dark");
        }
    } catch (_) {}

    const apply = (dark) => {
        document.documentElement.classList.toggle("dark", dark);
        try {
            localStorage.setItem("themeMode", dark ? "dark" : "light");
        } catch (_) {}
    };

    document.addEventListener("basecoat:theme", (event) => {
        const mode = event.detail?.mode;
        apply(
            mode === "dark"
                ? true
                : mode === "light"
                ? false
                : !document.documentElement.classList.contains("dark")
        );
    });
})();

// Inisialisasi varian tema berdasarkan penyimpanan lokal
(function () {
    try {
        const storedTheme = localStorage.getItem("themeVariant");
        if (storedTheme)
            document.documentElement.classList.add(`theme-${storedTheme}`);
    } catch (event) {
        console.error("Could not apply theme variant from localStorage", event);
    }
})();

// Pastikan posisi horizontal selalu di 0
window.addEventListener("scroll", () => {
    if (window.scrollX !== 0) window.scrollTo(0, window.scrollY);
});
