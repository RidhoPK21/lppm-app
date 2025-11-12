const bar = document.getElementById("loading-bar");
let progress = 0;
let interval = null;
let loadingActive = false;

// Mapping tema dengan warna yang sesuai
const themeColors = {
    blue: {
        light: ["#93c5fd", "#60a5fa", "#3b82f6"],
        dark: ["#60a5fa", "#3b82f6", "#2563eb"],
    },
    green: {
        light: ["#6ee7b7", "#34d399", "#10b981"],
        dark: ["#34d399", "#10b981", "#059669"],
    },
    orange: {
        light: ["#fdba74", "#fb923c", "#f97316"],
        dark: ["#fb923c", "#f97316", "#ea580c"],
    },
    red: {
        light: ["#fca5a5", "#f87171", "#ef4444"],
        dark: ["#f87171", "#ef4444", "#dc2626"],
    },
    pink: {
        light: ["#f9a8d4", "#f472b6", "#ec4899"],
        dark: ["#f472b6", "#ec4899", "#db2777"],
    },
    violet: {
        light: ["#c4b5fd", "#a78bfa", "#8b5cf6"],
        dark: ["#a78bfa", "#8b5cf6", "#7c3aed"],
    },
    yellow: {
        light: ["#fde047", "#facc15", "#eab308"],
        dark: ["#fbbf24", "#f59e0b", "#d97706"],
    },
};

// Fungsi untuk mendeteksi tema aktif dari class html
function getActiveTheme() {
    const html = document.documentElement;

    // Cari class theme-* yang aktif
    for (let className of html.classList) {
        if (className.startsWith("theme-")) {
            const themeName = className.replace("theme-", "");
            if (themeColors[themeName]) {
                return themeName;
            }
        }
    }

    return "blue"; // default theme
}

// Fungsi untuk mendapatkan warna berdasarkan tema aktif
function getThemeColors() {
    const activeTheme = getActiveTheme();
    const isDark = document.documentElement.classList.contains("dark");
    const mode = isDark ? "dark" : "light";

    const colors = themeColors[activeTheme]?.[mode] || themeColors.blue[mode];

    return {
        start: colors[0],
        middle: colors[1],
        end: colors[2],
    };
}

// Fungsi untuk mengupdate warna loading bar
function updateLoadingBarColors() {
    if (!bar) return;

    const colors = getThemeColors();
    bar.style.background = `linear-gradient(90deg, ${colors.start}, ${colors.middle}, ${colors.end})`;
}

// Mulai animasi loading
function startLoading() {
    if (!bar) return;

    // Update warna berdasarkan tema sebelum memulai
    updateLoadingBarColors();

    clearInterval(interval);
    progress = 0;
    bar.style.opacity = "1";
    bar.style.width = "0%";
    bar.classList.remove("hidden");

    interval = setInterval(() => {
        if (progress < 90) {
            progress += Math.random() * 4;
            bar.style.width = progress + "%";
        }
    }, 150);

    loadingActive = true;
}

// Selesaikan animasi loading
function finishLoading() {
    if (!bar) return;

    clearInterval(interval);
    bar.style.width = "100%";

    setTimeout(() => {
        bar.style.opacity = "0";
    }, 400);

    setTimeout(() => {
        bar.style.width = "0%";
        bar.classList.add("hidden");
        loadingActive = false;
    }, 800);
}

// Inisialisasi sistem loading
function initializeLoadingSystem() {
    // Update warna loading bar saat pertama kali load
    updateLoadingBarColors();

    // Setup observer untuk perubahan tema
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.attributeName === "class") {
                setTimeout(() => {
                    updateLoadingBarColors();
                }, 100);
            }
        });
    });

    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ["class"],
    });

    // Listen untuk event perubahan tema custom
    document.addEventListener("basecoat:theme", () => {
        setTimeout(() => {
            updateLoadingBarColors();
        }, 100);
    });

    // Inisialisasi Livewire hooks
    if (window.Livewire) {
        setupLivewireHooks();
    } else {
        // Fallback jika Livewire belum loaded
        document.addEventListener("livewire:init", setupLivewireHooks);
    }

    // Fallback untuk initial page load
    startLoading();
    window.addEventListener("load", () => {
        if (loadingActive) {
            finishLoading();
        }
    });
}

// Setup Livewire hooks
function setupLivewireHooks() {
    // Hook untuk memantau commit (request ke server)
    Livewire.hook("commit", ({ component, commit, succeed, fail }) => {
        // Cek apakah ada method calls dalam commit
        if (commit.calls && commit.calls.length > 0) {
            const excludedMethods = ["render", "mount", "hydrate", "dehydrate"];
            const hasValidMethod = commit.calls.some(
                (call) => !excludedMethods.includes(call.method)
            );

            if (hasValidMethod && !loadingActive) {
                startLoading();
            }
        }
    });

    // Hook ketika request berhasil
    Livewire.hook("commit", ({ succeed }) => {
        succeed(({ snapshot, effects }) => {
            if (loadingActive) {
                finishLoading();
            }
        });
    });

    // Hook ketika request gagal
    Livewire.hook("commit", ({ fail }) => {
        fail(() => {
            if (loadingActive) {
                finishLoading();
            }
        });
    });

    // Alternatif menggunakan request hook untuk coverage lebih luas
    Livewire.hook("request", ({ succeed, fail }) => {
        succeed(({ status, json }) => {
            if (loadingActive) {
                finishLoading();
            }
        });

        fail(({ status, content, preventDefault }) => {
            if (loadingActive) {
                finishLoading();
            }
        });
    });
}

// Jalankan ketika DOM siap
document.addEventListener("DOMContentLoaded", initializeLoadingSystem);

// Juga jalankan jika script loaded setelah DOMContentLoaded
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initializeLoadingSystem);
} else {
    initializeLoadingSystem();
}
