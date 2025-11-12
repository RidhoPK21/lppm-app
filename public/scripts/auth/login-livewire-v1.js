function parseUserAgent(userAgent) {
    // Parsing OS
    let osName = "Unknown OS";
    let osVersion = "Unknown";
    let architecture = "Unknown";

    if (userAgent.includes("Win64") || userAgent.includes("x64")) {
        architecture = "64bit";
    } else if (userAgent.includes("Win32") || userAgent.includes("x86")) {
        architecture = "32bit";
    }

    if (userAgent.includes("Windows NT 10.0")) osName = "Windows 10";
    else if (userAgent.includes("Windows NT 6.1")) osName = "Windows 7";
    else if (userAgent.includes("Mac OS X")) {
        osName = "Mac OS";
        osVersion =
            userAgent.match(/Mac OS X (\d+_\d+)/)?.[1]?.replace("_", ".") ||
            "Unknown";
    } else if (userAgent.includes("Android")) {
        osName = "Android";
        osVersion = userAgent.match(/Android (\d+(\.\d+)?)/)?.[1] || "Unknown";
    } else if (userAgent.includes("Linux")) osName = "Linux";

    // Parsing Browser
    let browserName = "Unknown Browser";
    let browserVersion = "Unknown";

    if (userAgent.includes("Chrome") && !userAgent.includes("Edg")) {
        browserName = "Chrome";
        browserVersion =
            userAgent.match(/Chrome\/(\d+\.\d+\.\d+\.\d+)/)?.[1] || "Unknown";
    } else if (userAgent.includes("Firefox")) {
        browserName = "Firefox";
        browserVersion =
            userAgent.match(/Firefox\/(\d+\.\d+)/)?.[1] || "Unknown";
    } else if (userAgent.includes("Edg")) {
        browserName = "Edge";
        browserVersion = userAgent.match(/Edg\/(\d+\.\d+)/)?.[1] || "Unknown";
    } else if (userAgent.includes("Safari") && !userAgent.includes("Chrome")) {
        browserName = "Safari";
        browserVersion =
            userAgent.match(/Version\/(\d+\.\d+)/)?.[1] || "Unknown";
    }
    return `${browserName} (${browserVersion}), ${osName} (${osVersion}) ${architecture}`;
}

document.addEventListener("livewire:init", () => {
    Livewire.on("saveAuthToken", (data) => {
        const authToken = data.authToken;

        // Simpan authToken ke dalam localStorage
        localStorage.setItem("authToken", authToken);

        // redirect ke halaman totp
        window.location.href = "/auth/totp";
    });

    Livewire.on("invalidAuthToken", () => {
        localStorage.removeItem("authToken");
        hidePreloader();
    });
});

// Livewire siap digunakan
document.addEventListener("livewire:initialized", () => {
    const userAgent = navigator.userAgent;
    const language = navigator.language || navigator.userLanguage;

    const deviceInfo = `[${language}] ` + parseUserAgent(userAgent);
    const deviceId = CryptoJS.MD5(deviceInfo).toString();

    let components = Livewire.all();
    if (components.length === 0) {
        window.alert("No Livewire components found.");
        return;
    }
    let livewire = components[0].$wire;

    livewire.$set("systemId", deviceId);
    livewire.$set("info", deviceInfo);

    const authToken = localStorage.getItem("authToken");
    if (authToken) {
        livewire.setup(authToken);
    }
});
