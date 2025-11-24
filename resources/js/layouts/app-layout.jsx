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

import { usePage, Link } from "@inertiajs/react"; // Update: Tambah Link

import * as Icon from "@tabler/icons-react";
import { Moon, Sun, HandCoins, Bell } from "lucide-react"; // Update: Tambah Bell
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

    const navData = [
        // 1. GROUP MAIN
        {
            title: "Main",
            items: [
                {
                    title: "Beranda",
                    url: route("home"),
                    icon: Icon.IconHome,
                },
                {
                    title: "Todo",
                    url: route("todo"),
                    icon: Icon.IconChecklist,
                },
            ],
        },

        // 2. GROUP REGISTRASI (Langsung di bawah Todo)
        {
            title: "Registrasi",
            collapsible: true,
            groupIcon: HandCoins,
            items: [
                {
                    title: "Registrasi Seminar",
                    url: route("registrasi.seminar"),
                    icon: Icon.IconNotebook,
                },
                {
                    title: "Registrasi Jurnal",
                    url: route("registrasi.jurnal"),
                    icon: Icon.IconBook,
                },
            ],
        },

        // 3. GROUP PENGHARGAAN
        {
            title: "Penghargaan",
            collapsible: true,
            groupIcon: Icon.IconAward,
            items: [
                {
                    title: "Penghargaan Buku",
                    // Dikembalikan ke sini dengan route yang benar
                    url: route("app.penghargaan.buku.index"),
                    icon: Icon.IconBook2,
                },
                {
                    title: "Penghargaan Jurnal",
                    url: route("penghargaan.mahasiswa"),
                    icon: Icon.IconFileCertificate,
                },
                {
                    title: "Penghargaan Seminar",
                    url: route("penghargaan.penelitian"),
                    icon: Icon.IconPresentation,
                },
            ],
        },

        // 4. GROUP ADMIN (Pindah ke Bawah)
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
                    user={auth}
                    navData={navData}
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
                                {/* --- TOMBOL NOTIFIKASI DITAMBAHKAN DI SINI --- */}
                                <Button variant="ghost" size="icon" asChild>
                                    <Link href={route("notifications.index")}>
                                        <Bell className="h-4 w-4" />
                                        <span className="sr-only">
                                            Notifikasi
                                        </span>
                                    </Link>
                                </Button>
                                {/* --------------------------------------------- */}

                                <Select
                                    className="capitalize"
                                    value={colorTheme}
                                    onValueChange={setColorTheme}
                                >
                                    <SelectTrigger>
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
