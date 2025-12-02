import { AppSidebar } from "@/components/app-sidebar";
import { Button } from "@/components/ui/button";
import { Link } from "@inertiajs/react";
import { Bell } from "lucide-react";
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
import { usePage } from "@inertiajs/react";
import * as Icon from "@tabler/icons-react";
import { Moon, Sun, HandCoins } from "lucide-react";
import { Toaster } from "sonner";
import { route } from "ziggy-js";

export default function AppLayout({ children }) {
    const { auth, appName, pageName } = usePage().props || {};
    const { theme, colorTheme, toggleTheme, setColorTheme } = useTheme();

    const colorThemes = [
        "blue", "green", "default", "orange", "red", "rose", "violet", "yellow"
    ];

    const rolesUser = Array.isArray(auth?.roles) ? auth.roles : [];
    const aksesUser = Array.isArray(auth?.akses) ? auth.akses : [];

    // Jika roles/akses kosong, tampilkan semua menu
    const hasRole = (role) => {
        if (rolesUser.length === 0 && aksesUser.length === 0) return true;
        return rolesUser.includes(role) || aksesUser.includes(role);
    };

    // KHUSUS: Menu untuk Lppm Staff (dengan spasi)
    const navDataLppmStaff = [
        { 
            title: "Main", 
            items: [
                { title: "Beranda", url: route("home"), icon: Icon.IconHome },
                { title: "Todo", url: route("todo"), icon: Icon.IconChecklist },
            ] 
        },
        {
            title: "Registrasi Masuk",
            collapsible: true,
            items: [
                { title: "Registrasi Jurnal Masuk", url: route("regis-semi.index"), icon: Icon.IconFileCertificate },
                { title: "Registrasi Seminar Masuk", url: route("regis-semi.index"), icon: Icon.IconPresentation },
            ],
        },
        {
            title: "Penghargaan Masuk",
            collapsible: true,
            items: [
                // PERBAIKAN: Mengarahkan "Penghargaan Buku Masuk" ke route("regis-semi.index") sesuai permintaan
                { title: "Penghargaan Buku Masuk", url: route("regis-semi.index"), icon: Icon.IconBook2 }, 
                { title: "Penghargaan Jurnal Masuk", url: route("regis-semi.index"), icon: Icon.IconFileCertificate },
                { title: "Penghargaan Seminar Masuk", url: route("regis-semi.index"), icon: Icon.IconPresentation },
            ],
        },
        {
            title: "Admin",
            items: [
                { title: "Hak Akses", url: route("hak-akses"), icon: Icon.IconLock },
            ],
        },
    ];

    // Menu default untuk role lainnya
    const navDataDefault = [
        { 
            title: "Main", 
            items: [
                { title: "Beranda", url: route("home"), icon: Icon.IconHome },
                { title: "Todo", url: route("todo"), icon: Icon.IconChecklist },
            ] 
        },

        ...((hasRole("Dosen") || hasRole("Lppm Ketua") ) ? [{
            title: "Registrasi",
            collapsible: true,
            groupIcon: HandCoins,
            items: [
                { title: "Seminar", url: route("regis-semi.index"), icon: Icon.IconNotebook },
                { title: "Jurnal", url: route("regis-semi.index"), icon: Icon.IconBook },
            ],
        }] : []),

        ...((hasRole("Dosen") || hasRole("Lppm Ketua") ) ? [{
            title: "Penghargaan",
            collapsible: true,
            groupIcon: Icon.IconAward,
            items: [
                { title: "Penghargaan Buku", url: route("app.penghargaan.buku.index"), icon: Icon.IconBook2 },
                { title: "Penghargaan Jurnal", url: route("regis-semi.index"), icon: Icon.IconFileCertificate },
                { title: "Penghargaan Seminar", url: route("regis-semi.index"), icon: Icon.IconPresentation },
            ],
        }] : []),

        ...(hasRole("Lppm Ketua") ? [{
            title: "Registrasi Masuk",
            collapsible: true,
            items: [
                { title: "Registrasi Jurnal Masuk", url: route("regis-semi.index"), icon: Icon.IconFileCertificate },
                { title: "Registrasi Seminar Masuk", url: route("regis-semi.index"), icon: Icon.IconPresentation },
            ],
        }] : []),

        // Memastikan "Penghargaan Buku Masuk" di navDataDefault juga mengarah ke regis-semi.index, sesuai permintaan.
        ...(hasRole("Lppm Ketua") || hasRole("Lppm Staff") ? [{
            title: "Penghargaan Masuk",
            collapsible: true,
            items: [
                { title: "Penghargaan Buku Masuk", url: route("regis-semi.index"), icon: Icon.IconBook2 }, // Sudah benar
                { title: "Penghargaan Jurnal Masuk", url: route("regis-semi.index"), icon: Icon.IconFileCertificate },
                { title: "Penghargaan Seminar Masuk", url: route("regis-semi.index"), icon: Icon.IconPresentation },
            ],
        }] : []),

        // Tampilkan Hak Akses untuk SEMUA role
{
    title: "Admin",
    items: [
        { title: "Hak Akses", url: route("hak-akses"), icon: Icon.IconLock },
    ],
}

    ];

    // Tentukan navData berdasarkan role - PERBAIKAN: Gunakan "Lppm Staff" dengan spasi
    const navData = hasRole("Lppm Staff") ? navDataLppmStaff : navDataDefault;

    return (
        <>
            <SidebarProvider style={{ "--sidebar-width": "calc(var(--spacing) * 72)", "--header-height": "calc(var(--spacing) * 12)" }}>
                <AppSidebar active={pageName} user={auth} navData={navData} appName={appName} variant="inset" />
                <SidebarInset>
                    <header className="flex h-(--header-height) shrink-0 items-center gap-2 border-b sticky top-0 z-50 bg-background/95 backdrop-blur-sm">
                        <div className="flex w-full items-center gap-1 px-4 lg:gap-2 lg:px-6">
                            <SidebarTrigger className="-ml-1" />
                            <Separator orientation="vertical" className="mx-2 data-[orientation=vertical]:h-4" />
                            <h1 className="text-base font-medium">{pageName || "Halaman"}</h1>
                            <div className="ml-auto flex items-center gap-2">
                                <Button variant="ghost" size="icon" asChild>
                                    <Link href={route("notifications.index")}>
                                        <Bell className="h-4 w-4" />
                                        <span className="sr-only">Notifikasi</span>
                                    </Link>
                                </Button>
                                <Select className="capitalize" value={colorTheme} onValueChange={setColorTheme}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Pilih Tema" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectLabel>Tema</SelectLabel>
                                            {colorThemes.map((item) => (
                                                <SelectItem key={`theme-${item}`} value={item}>{item}</SelectItem>
                                            ))}
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                                <Button variant="ghost" size="icon" onClick={toggleTheme}>
                                    {theme === "light" ? <Sun className="h-4 w-4" /> : <Moon className="h-4 w-4" />}
                                </Button>
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