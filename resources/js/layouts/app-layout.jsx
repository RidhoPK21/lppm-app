import { AppSidebar } from "@/components/app-sidebar";
import { NavUser } from "@/components/nav-user"; // Added from Code 2
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

import { usePage, Link } from "@inertiajs/react";

import * as Icon from "@tabler/icons-react";
import { Moon, Sun, Bell, HandCoins } from "lucide-react"; // Added HandCoins from Code 2
import { Toaster } from "sonner";
import { route } from "ziggy-js";

export default function AppLayout({ children }) {
    const { auth, appName, pageName } = usePage().props || {}; // Added safeguard from Code 2
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
    // 1. LOGIKA CEK ROLE (GABUNGAN KODE 1 & 2)
    // =========================================================
    const rolesUser = Array.isArray(auth?.roles) ? auth.roles : [];
    const aksesUser = Array.isArray(auth?.akses) ? auth.akses : []; // Added from Code 2

    // Helper fleksibel untuk cek role & akses
    const hasRole = (roleCheck) => {
        // Jika array, cek salah satu
        if (Array.isArray(roleCheck)) {
            return roleCheck.some(
                (r) => rolesUser.includes(r) || aksesUser.includes(r),
            );
        }
        // Single check
        return rolesUser.includes(roleCheck) || aksesUser.includes(roleCheck);
    };

    // =========================================================
    // 2. DEFINISI MENU (MERGE ITEM KODE 1 & KODE 2)
    // =========================================================

    const navDataUtility = {
        title: "Utilitas",
        items: [
            {
                title: "Todo List",
                url: route("todo"),
                icon: Icon.IconChecklist,
            },
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

    // STAFF LPPM / ANGGOTA (Merge Code 1 & 2)
    const navDataAnggota = [
        {
            title: "Main",
            items: [
                {
                    title: "Beranda Staff",
                    url: route("lppm.anggota.home"),
                    icon: Icon.IconHome,
                },
            ],
        },
        {
            title: "Staff LPPM",
            items: [
                {
                    title: "Pengajuan Dana",
                    url: route("lppm.anggota.pengajuan-dana"),
                    icon: Icon.IconCurrencyDollar,
                },
            ],
        },
        // Item baru dari Kode 2
        {
            title: "Registrasi Masuk",
            collapsible: true,
            items: [
                {
                    title: "Registrasi Jurnal Masuk",
                    url: route("regis-semi.index"),
                    icon: Icon.IconFileCertificate,
                },
                {
                    title: "Registrasi Seminar Masuk",
                    url: route("regis-semi.index"),
                    icon: Icon.IconPresentation,
                },
            ],
        },
        {
            title: "Penghargaan Masuk",
            collapsible: true,
            items: [
                {
                    title: "Penghargaan Buku Masuk",
                    url: route("regis-semi.indexx"),
                    icon: Icon.IconBook2,
                },
                {
                    title: "Penghargaan Jurnal Masuk",
                    url: route("regis-semi.index"),
                    icon: Icon.IconFileCertificate,
                },
                {
                    title: "Penghargaan Seminar Masuk",
                    url: route("regis-semi.index"),
                    icon: Icon.IconPresentation,
                },
            ],
        },
    ];

    // KEUANGAN (From Code 1)
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
                    url: route("keuangan.pembayaran"),
                    icon: Icon.IconCash,
                },
            ],
        },
    ];

    // REVIEWER (From Code 1)
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
                    title: "Jurnal",
                    collapsible: true,
                    icon: Icon.IconBook,
                    items: [
                        {
                            title: "Masuk",
                            url: route("reviewer.jurnal.masuk"),
                            icon: Icon.IconInbox,
                        },
                        {
                            title: "Disetujui",
                            url: route("reviewer.jurnal.disetujui"),
                            icon: Icon.IconCircleCheck,
                        },
                    ],
                },
                {
                    title: "Seminar",
                    collapsible: true,
                    icon: Icon.IconNotebook,
                    items: [
                        {
                            title: "Masuk",
                            url: route("reviewer.seminar.masuk"),
                            icon: Icon.IconInbox,
                        },
                        {
                            title: "Disetujui",
                            url: route("reviewer.seminar.disetujui"),
                            icon: Icon.IconCircleCheck,
                        },
                    ],
                },
            ],
        },
    ];

    // KETUA LPPM (Merge Code 1 & 2)
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
            title: "Ketua LPPM",
            items: [
                {
                    title: "Persetujuan Dana",
                    url: route("lppm.ketua.pengajuan-dana"),
                    icon: Icon.IconCurrencyDollar,
                },
            ],
        },
        // Item tambahan dari Kode 2 (jika Ketua juga butuh menu masuk)
        {
            title: "Registrasi Masuk",
            collapsible: true,
            items: [
                {
                    title: "Registrasi Jurnal Masuk",
                    url: route("regis-semi.index"),
                    icon: Icon.IconFileCertificate,
                },
                {
                    title: "Registrasi Seminar Masuk",
                    url: route("regis-semi.index"),
                    icon: Icon.IconPresentation,
                },
            ],
        },
        {
            title: "Penghargaan Masuk",
            collapsible: true,
            items: [
                {
                    title: "Penghargaan Buku Masuk",
                    url: route("regis-semi.index"),
                    icon: Icon.IconBook2,
                },
                {
                    title: "Penghargaan Jurnal Masuk",
                    url: route("regis-semi.index"),
                    icon: Icon.IconFileCertificate,
                },
                {
                    title: "Penghargaan Seminar Masuk",
                    url: route("regis-semi.index"),
                    icon: Icon.IconPresentation,
                },
            ],
        },
    ];

    // DOSEN (Updated with items from Code 2)
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
            title: "Dosen",
            items: [
                {
                    title: "Daftar Seminar",
                    url: route("dosen.seminar.index"),
                    icon: Icon.IconPresentation,
                },
                {
                    title: "Daftar Jurnal",
                    url: route("registrasi.jurnal"),
                    icon: Icon.IconBook,
                },
            ],
        },
        // Registrasi (From Code 2)
        {
            title: "Registrasi",
            collapsible: true,
            groupIcon: HandCoins,
            items: [
                {
                    title: "Seminar",
                    url: route("regis-semi.index"),
                    icon: Icon.IconNotebook,
                },
                {
                    title: "Jurnal",
                    url: route("regis-semi.index"),
                    icon: Icon.IconBook,
                },
            ],
        },
        // Penghargaan (From Code 2)
        {
            title: "Penghargaan",
            collapsible: true,
            groupIcon: Icon.IconAward,
            items: [
                {
                    title: "Penghargaan Buku",
                    url: route("app.penghargaan.buku.index"),
                    icon: Icon.IconBook2,
                },
                {
                    title: "Penghargaan Jurnal",
                    url: route("regis-semi.index"),
                    icon: Icon.IconFileCertificate,
                },
                {
                    title: "Penghargaan Seminar",
                    url: route("regis-semi.index"),
                    icon: Icon.IconPresentation,
                },
            ],
        },
    ];

    // KPRODI (From Code 1)
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

    // HRD (Merge Code 1 & 2)
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
                    url: route("hrd.surat-tugas.index"),
                    icon: Icon.IconMail,
                },
            ],
        },
        // Admin menu from Code 2 inside HRD (if needed specifically, otherwise handled by global Admin)
        {
            title: "Admin",
            items: [
                {
                    title: "Hak Akses",
                    url: route("hak-akses"),
                    icon: Icon.IconLock,
                },
            ],
        },
    ];

    // ADMIN
    const navDataAdmin = [
        {
            title: "Admin",
            items: [
                {
                    title: "Hak Akses",
                    url: route("hak-akses"),
                    icon: Icon.IconLock,
                },
            ],
        },
    ];

    // =========================================================
    // 3. LOGIKA MERGE MENU & FILTER PRIORITAS DOSEN
    // =========================================================

    // TENTUKAN PRIMARY ROLE
    // Jika User adalah Dosen, maka Dashboard Dosen yang menang.
    let primaryRole = "Guest";

    if (hasRole("Dosen")) primaryRole = "Dosen";
    else if (hasRole("Admin")) primaryRole = "Admin";
    else if (hasRole(["LppmKetua", "Ketua LPPM"])) primaryRole = "LppmKetua";
    else if (hasRole("Kprodi")) primaryRole = "Kprodi";
    else if (hasRole("Reviewer")) primaryRole = "Reviewer";
    else if (hasRole("Keuangan")) primaryRole = "Keuangan";
    else if (hasRole(["Hrd", "HRD"])) primaryRole = "Hrd";
    else if (hasRole(["LppmAnggota", "Lppm Staff", "Lppm Staff"]))
        primaryRole = "LppmAnggota";

    let finalNavData = [];

    // FUNGSI PENGGABUNG MENU DENGAN FILTER
    const mergeMenu = (roleName, roleNavData, aliasRoles = []) => {
        // Cek apakah user punya role ini
        if (hasRole(roleName) || aliasRoles.some((r) => hasRole(r))) {
            // Filter menu: Jika Grup Menu berjudul "Main", HANYA masukkan jika ini Primary Role user
            const filteredMenu = roleNavData.filter((group) => {
                if (group.title === "Main") {
                    return primaryRole === roleName;
                }
                return true;
            });

            finalNavData.push(...filteredMenu);
        }
    };

    // EKSEKUSI PENGGABUNGAN (Urutan menentukan posisi di sidebar)
    mergeMenu("Admin", navDataAdmin);
    mergeMenu("Dosen", navDataDosen);
    mergeMenu("Kprodi", navDataKprodi);
    mergeMenu("Reviewer", navDataReviewer);
    mergeMenu("LppmKetua", navDataKetua, ["Ketua LPPM"]);
    mergeMenu("LppmAnggota", navDataAnggota, ["Lppm Staff", "Anggota LPPM"]);
    mergeMenu("Keuangan", navDataKeuangan);
    mergeMenu("Hrd", navDataHRD, ["HRD"]);

    // FINALISASI
    if (finalNavData.length === 0) {
        // Jika tidak punya role sama sekali (Guest), pakai Default
        finalNavData = [...navDataDefault];
    } else {
        // Jika punya role, tambahkan Utility (Todo List) di paling bawah
        finalNavData.push(navDataUtility);
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
                    {/* Header Structure updated from Code 2 */}
                    <header className="flex h-(--header-height) shrink-0 items-center gap-2 border-b transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-(--header-height) sticky top-0 z-50 bg-background/95 backdrop-blur-sm">
                        <div className="flex w-full items-center gap-1 px-4 lg:gap-2 lg:px-6">
                            <SidebarTrigger className="-ml-1" />
                            <Separator
                                orientation="vertical"
                                className="mx-2 data-[orientation=vertical]:h-4"
                            />
                            {/* Breadcrumb / Title area */}
                            <h1 className="text-base font-medium hidden md:block">
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
                                    <SelectTrigger className="w-[120px]">
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

                                {/* Added NavUser from Code 2 for User Profile in Header */}
                                <NavUser user={userAccount} />
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
