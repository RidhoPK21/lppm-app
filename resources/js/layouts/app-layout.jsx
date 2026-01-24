import { AppSidebar } from "@/components/app-sidebar";
import { Button } from "@/components/ui/button";
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import { Separator } from "@/components/ui/separator";
import {
    SidebarInset,
    SidebarProvider,
    SidebarTrigger,
} from "@/components/ui/sidebar";
import { useTheme } from "@/providers/theme-provider";
import { NavUser } from "@/components/nav-user"; // Pastikan ini diimport

import { usePage, Link } from "@inertiajs/react";

import * as Icon from "@tabler/icons-react";
import { Moon, Sun, Bell } from "lucide-react";
import { Toaster } from "sonner";
import { route } from "ziggy-js";

export default function AppLayout({ children }) {
    const { auth, appName, pageName } = usePage().props;
    const { theme, colorTheme, toggleTheme, setColorTheme } = useTheme();

    const colorThemes = [
        "blue",
        "green",
        "default",
        "orange",
        "red",
        "rose",
        "violet",
        "yellow",
    ];

    // =========================================================
    // 1. LOGIKA CEK ROLE
    // =========================================================
    const rolesUser = Array.isArray(auth?.roles) ? auth.roles : [];

    const hasRole = (roleCheck) => {
        if (Array.isArray(roleCheck)) {
            return roleCheck.some((r) => rolesUser.includes(r));
        }
        return rolesUser.includes(roleCheck);
    };

    // =========================================================
    // 2. DEFINISI MENU (GABUNGAN FITUR SEMINAR & BUKU)
    // =========================================================

    const navDataUtility = {
        title: "Utilitas",
        items: [
            { title: "Todo List", url: "#", icon: Icon.IconChecklist }, // Placeholder jika route todo belum ada
        ],
    };

    const navDataDefault = [
        {
            title: "Main",
            items: [
                {
                    title: "Beranda Umum",
                    url: route("home"),
                    icon: Icon.IconHome,
                },
            ],
        },
    ];

    // STAFF LPPM
    const navDataAnggota = [
        {
            title: "Main",
            items: [
                {
                    title: "Beranda Staff",
                    url: route("home"),
                    icon: Icon.IconHome,
                },
            ],
        },
        {
            title: "Staff LPPM",
            items: [
                // Fitur Seminar (Baru)
                {
                    title: "Pengajuan Dana",
                    url: route("lppm.ketua.pengajuan-dana.index"),
                    icon: Icon.IconCurrencyDollar,
                },
                // Fitur Regis Semi (Lama)
                {
                    title: "Arsip Seminar",
                    url: route("regis-semi.index"),
                    icon: Icon.IconArchive,
                },
            ],
        },
    ];

    // KEUANGAN
    const navDataKeuangan = [
        {
            title: "Main",
            items: [
                {
                    title: "Beranda Keuangan",
                    url: route("keuangan.home"),
                    icon: Icon.IconHome,
                },
            ],
        },
        {
            title: "Keuangan",
            items: [
                {
                    title: "Daftar Pembayaran",
                    url: route("keuangan.pembayaran.index"),
                    icon: Icon.IconCash,
                },
            ],
        },
    ];

    // REVIEWER
    const navDataReviewer = [
        {
            title: "Main",
            items: [
                {
                    title: "Beranda Reviewer",
                    url: route("reviewer.home"),
                    icon: Icon.IconHome,
                },
            ],
        },
        {
            title: "Tugas Review",
            collapsible: true,
            groupIcon: Icon.IconNotebook,
            items: [
                {
                    title: "Seminar Masuk",
                    url: route("reviewer.seminar.masuk"),
                    icon: Icon.IconInbox,
                },
                {
                    title: "Riwayat Review",
                    url: route("reviewer.seminar.disetujui"),
                    icon: Icon.IconCircleCheck,
                },
            ],
        },
    ];

    // KETUA LPPM
    const navDataKetua = [
        {
            title: "Main",
            items: [
                {
                    title: "Beranda LPPM",
                    url: route("lppm.ketua.home"),
                    icon: Icon.IconHome,
                },
            ],
        },
        {
            title: "Persetujuan",
            items: [
                {
                    title: "Pengajuan Dana",
                    url: route("lppm.ketua.pengajuan-dana.index"),
                    icon: Icon.IconCurrencyDollar,
                },
            ],
        },
        {
            title: "Admin Penghargaan",
            items: [
                // Menu Admin Buku (Fitur Ridho)
                {
                    title: "Data Buku Masuk",
                    url: route("app.admin.penghargaan.buku.index"),
                    icon: Icon.IconBook2,
                },
            ],
        },
    ];

    // DOSEN (Menu Gabungan)
    const navDataDosen = [
        {
            title: "Main",
            items: [
                {
                    title: "Beranda Dosen",
                    url: route("dosen.home"),
                    icon: Icon.IconDashboard,
                },
            ],
        },
        {
            title: "Kegiatan Akademik",
            collapsible: true,
            groupIcon: Icon.IconSchool,
            items: [
                // 1. Fitur Seminar (Porman)
                {
                    title: "Seminar & Luaran",
                    url: route("dosen.seminar.index"),
                    icon: Icon.IconPresentation,
                },

                // 2. Fitur Buku (Ridho) - DIAKTIFKAN KEMBALI
                {
                    title: "Insentif Buku",
                    url: route("app.penghargaan.buku.index"),
                    icon: Icon.IconBook,
                },
            ],
        },
    ];

    // KPRODI
    const navDataKprodi = [
        {
            title: "Main",
            items: [
                {
                    title: "Beranda Kprodi",
                    url: route("kprodi.home"),
                    icon: Icon.IconHome,
                },
            ],
        },
        {
            title: "Kprodi",
            items: [
                {
                    title: "Verifikasi Seminar",
                    url: route("kprodi.verifikasi.index"),
                    icon: Icon.IconCertificate,
                },
            ],
        },
    ];

    // HRD
    const navDataHRD = [
        {
            title: "Main",
            items: [
                {
                    title: "Beranda HRD",
                    url: route("hrd.home"),
                    icon: Icon.IconHome,
                },
            ],
        },
        {
            title: "HRD",
            items: [
                {
                    title: "Surat Tugas",
                    url: route("hrd.seminar.index"),
                    icon: Icon.IconMail,
                },
            ],
        },
    ];

    // ADMIN
    const navDataAdmin = [
        {
            title: "Admin System",
            items: [
                {
                    title: "Hak Akses",
                    url: route("hak-akses"),
                    icon: Icon.IconLock,
                },
                // Admin Buku juga bisa diakses oleh Admin
                {
                    title: "Admin Buku",
                    url: route("app.admin.penghargaan.buku.index"),
                    icon: Icon.IconBook2,
                },
            ],
        },
    ];

    // =========================================================
    // 3. LOGIKA MERGE MENU & FILTER PRIORITAS
    // =========================================================

    // TENTUKAN PRIMARY ROLE
    let primaryRole = "Guest";

    if (hasRole("Dosen")) primaryRole = "Dosen";
    else if (hasRole("Admin")) primaryRole = "Admin";
    else if (hasRole(["LppmKetua", "Ketua LPPM"])) primaryRole = "LppmKetua";
    else if (hasRole("Kprodi")) primaryRole = "Kprodi";
    else if (hasRole("Reviewer")) primaryRole = "Reviewer";
    else if (hasRole("Keuangan")) primaryRole = "Keuangan";
    else if (hasRole(["Hrd", "HRD"])) primaryRole = "Hrd";
    else if (hasRole(["LppmAnggota", "Lppm Staff"]))
        primaryRole = "LppmAnggota";

    let finalNavData = [];

    // FUNGSI PENGGABUNG MENU DENGAN FILTER
    const mergeMenu = (roleName, roleNavData, aliasRoles = []) => {
        if (hasRole(roleName) || aliasRoles.some((r) => hasRole(r))) {
            const filteredMenu = roleNavData.filter((group) => {
                // Filter menu "Main" agar tidak duplikat, hanya ambil dari Primary Role
                if (group.title === "Main") {
                    return primaryRole === roleName;
                }
                return true;
            });
            finalNavData.push(...filteredMenu);
        }
    };

    // URUTAN EKSEKUSI (Menentukan urutan tampilan di sidebar)
    mergeMenu("Dosen", navDataDosen); // Dosen paling atas jika dia Dosen
    mergeMenu("LppmKetua", navDataKetua, ["Ketua LPPM"]);
    mergeMenu("LppmAnggota", navDataAnggota, ["Lppm Staff", "Anggota LPPM"]);
    mergeMenu("Reviewer", navDataReviewer);
    mergeMenu("Kprodi", navDataKprodi);
    mergeMenu("Keuangan", navDataKeuangan);
    mergeMenu("Hrd", navDataHRD, ["HRD"]);
    mergeMenu("Admin", navDataAdmin);

    // FINALISASI
    if (finalNavData.length === 0) {
        finalNavData = [...navDataDefault];
    } else {
        // finalNavData.push(navDataUtility); // Opsional: Tambahkan utilitas di bawah
    }

    // =========================================================
    // 4. USER SAFEGUARD
    // =========================================================
    const userAccount = auth?.user || {
        name: "Tamu",
        username: "Guest",
        photo: "https://ui-avatars.com/api/?name=Guest&background=random",
    };

    return (
        <>
            <SidebarProvider
                style={{
                    "--sidebar-width": "calc(var(--spacing) * 72)",
                    "--header-height": "calc(var(--spacing) * 12)",
                }}
            >
                <AppSidebar
                    active={pageName}
                    user={userAccount}
                    navData={finalNavData}
                    appName={appName}
                    variant="inset"
                />
                <SidebarInset>
                    <header className="flex h-(--header-height) shrink-0 items-center gap-2 border-b transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-(--header-height) sticky top-0 z-50 bg-background/95 backdrop-blur-sm">
                        <div className="flex w-full items-center gap-1 px-4 lg:gap-2 lg:px-6">
                            <SidebarTrigger className="-ml-1" />
                            <Separator
                                orientation="vertical"
                                className="mx-2 data-[orientation=vertical]:h-4"
                            />
                            <h1 className="text-base font-medium">
                                {pageName}
                            </h1>
                            <div className="ml-auto flex items-center gap-2">
                                <Link href={route("notifications.index")}>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        className="relative"
                                    >
                                        <Bell className="h-4 w-4" />
                                        <span className="absolute top-2 right-2 h-2 w-2 rounded-full bg-red-500 ring-2 ring-white" />
                                    </Button>
                                </Link>

                                <Select
                                    className="capitalize"
                                    value={colorTheme}
                                    onValueChange={setColorTheme}
                                >
                                    <SelectTrigger className="w-[130px] hidden sm:flex">
                                        <SelectValue placeholder="Pilih Tema" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectLabel>Tema</SelectLabel>
                                            {colorThemes.map((item) => (
                                                <SelectItem
                                                    key={`theme-${item}`}
                                                    value={item}
                                                >
                                                    {item}
                                                </SelectItem>
                                            ))}
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>

                                <Button
                                    variant="ghost"
                                    size="icon"
                                    onClick={toggleTheme}
                                >
                                    {theme === "light" ? (
                                        <Sun className="h-4 w-4" />
                                    ) : (
                                        <Moon className="h-4 w-4" />
                                    )}
                                </Button>

                                {/* USER MENU DI HEADER (Opsional, karena sudah ada di sidebar footer) */}
                                <div className="hidden md:block">
                                    <NavUser user={userAccount} />
                                </div>
                            </div>
                        </div>
                    </header>
                    <div className="flex flex-1 flex-col">
                        <div className="@container/main flex flex-1 flex-col gap-2">
                            <div className="flex flex-col gap-4 py-4 md:gap-6 md:py-6 px-4 md:px-6">
                                {children}
                            </div>
                        </div>
                    </div>
                </SidebarInset>
            </SidebarProvider>
            <Toaster richColors position="top-center" />
        </>
    );
}
