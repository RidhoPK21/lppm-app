function onLogout() {
    // Kosongkan authToken di localStorage
    localStorage.setItem("authToken", "");

    // redirect ke halaman login
    window.location.href = "/auth/logout";
}

// Handle Livewire modals
document.addEventListener("livewire:initialized", () => {
    Livewire.on("closeModal", (data) => {
        const modal = bootstrap.Modal.getInstance(
            document.getElementById(data.id)
        );
        if (modal) {
            modal.hide();
        }
    });

    Livewire.on("showModal", (data) => {
        const modal = bootstrap.Modal.getOrCreateInstance(
            document.getElementById(data.id)
        );
        if (modal) {
            modal.show();
        }
    });

    Livewire.on("showDialog", (data) => {
        const modal = document.getElementById(data.id);
        if (modal) {
            modal.showModal();
        }
    });

    Livewire.on("closeDialog", (data) => {
        const modal = document.getElementById(data.id);
        if (modal) {
            modal.close();
        }
    });

    Livewire.on("showToastSuccess", (data) => {
        document.dispatchEvent(
            new CustomEvent("basecoat:toast", {
                detail: {
                    config: {
                        category: "success",
                        title: "Success",
                        duration: 8000,
                        description:
                            data.message || "Berhasil melakukan tindakan.",
                        cancel: {
                            label: "Dismiss",
                        },
                    },
                },
            })
        );
    });
});

// Handle logo
// {
//     document.addEventListener("DOMContentLoaded", () => {
//         const pathLogoDark = "/img/logo-dark.png";
//         const pathLogoLight = "/img/logo-light.png";
//         const allLogos = document.querySelectorAll(".target-logo");

//         const pathLogoDarkText = "/img/logo-dark-text.png";
//         const pathLogoLightText = "/img/logo-light-text.png";
//         const allTextLogos = document.querySelectorAll(".target-logo-text");

//         function updateLogos() {
//             const isDarkMode =
//                 document.documentElement.classList.contains("dark");
//             // console.log("Dark mode:", isDarkMode);
//             if (isDarkMode) {
//                 allLogos.forEach((el) => {
//                     el.src = pathLogoLight;
//                 });
//                 allTextLogos.forEach((el) => {
//                     el.src = pathLogoLightText;
//                 });
//             } else {
//                 allLogos.forEach((el) => {
//                     el.src = pathLogoDark;
//                 });
//                 allTextLogos.forEach((el) => {
//                     el.src = pathLogoDarkText;
//                 });
//             }
//         }
//         updateLogos();

//         // Observe changes to the class attribute of the html element
//         const observer = new MutationObserver(updateLogos);
//         observer.observe(document.documentElement, {
//             attributes: true,
//             attributeFilter: ["class"],
//         });
//     });
// }
