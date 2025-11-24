import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import AppLayout from "@/layouts/app-layout";
import { Head } from "@inertiajs/react";
import { Bell, ChevronDown } from "lucide-react";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";

// Komponen Dropdown Reusable (Sama seperti RegisSemi)
const SelectDropdown = ({ label, options, className = "", onChange }) => (
    <DropdownMenu>
        <DropdownMenuTrigger asChild>
            <div
                className={`flex items-center justify-between border border-gray-300 rounded-md bg-white text-sm px-3 h-10 cursor-pointer ${className}`}
            >
                {label}
                <ChevronDown className="h-4 w-4 ml-2 opacity-50" />
            </div>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" className="min-w-[120px]">
            {options.map((option) => (
                <DropdownMenuItem
                    key={option}
                    onSelect={() => onChange(option)}
                >
                    {option}
                </DropdownMenuItem>
            ))}
        </DropdownMenuContent>
    </DropdownMenu>
);

export default function NotificationPage() {
    // Data Dummy Notifikasi
    const notifications = [
        {
            id: 1,
            title: "Selamat Datang",
            message:
                "Selamat datang di aplikasi LPPM. Silakan lengkapi profil Anda.",
            date: "23 / 11 / 2025",
            type: "Info",
            isRead: false,
        },
        {
            id: 2,
            title: "Pengajuan Disetujui",
            message:
                "Pengajuan penghargaan buku Anda 'Dasar Pemrograman' telah disetujui.",
            date: "22 / 11 / 2025",
            type: "Sukses",
            isRead: true,
        },
        {
            id: 3,
            title: "Revisi Diperlukan",
            message:
                "Mohon perbaiki dokumen pendukung pada pengajuan Jurnal Anda.",
            date: "21 / 11 / 2025",
            type: "Peringatan",
            isRead: false,
        },
        {
            id: 4,
            title: "Maintenance Server",
            message:
                "Sistem akan mengalami pemeliharaan pada tanggal 25 November pukul 23:00 WIB.",
            date: "20 / 11 / 2025",
            type: "System",
            isRead: true,
        },
    ];

    const getTypeColor = (type) => {
        if (type === "Info") return "text-blue-500";
        if (type === "Sukses") return "text-green-500";
        if (type === "Peringatan") return "text-yellow-500";
        if (type === "Error") return "text-red-500";
        return "text-gray-500";
    };

    return (
        <AppLayout>
            <Head title="Notifikasi" />

            <Card className="h-full border-none shadow-none">
                <CardHeader className="p-0 space-y-4">
                    {/* Judul Halaman */}
                    <CardTitle className="text-2xl font-normal px-4">
                        Notifikasi
                    </CardTitle>

                    {/* Baris Search & Filter */}
                    <div className="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-2 items-center px-4">
                        {/* Search Input Gabungan */}
                        <div className="flex-1 flex border border-gray-300 rounded-md overflow-hidden h-10 w-full">
                            <input
                                type="text"
                                placeholder="Cari notifikasi..."
                                className="flex-1 p-2 focus:outline-none placeholder:text-gray-400 text-sm border-none"
                            />
                            <Button
                                variant="default"
                                className="h-full px-4 bg-gray-100 text-gray-800 hover:bg-gray-200 rounded-l-none border-l border-gray-300 shadow-none font-normal text-sm"
                            >
                                Cari
                            </Button>
                        </div>

                        {/* Dropdowns */}
                        <div className="w-full md:w-[150px]">
                            <SelectDropdown
                                label="Filter"
                                options={["Semua", "Belum Dibaca", "Info"]}
                                className="w-full h-10"
                                onChange={() => {}}
                            />
                        </div>
                        <div className="w-full md:w-[120px]">
                            <SelectDropdown
                                label="Urutkan"
                                options={["Terbaru", "Terlama"]}
                                className="w-full h-10"
                                onChange={() => {}}
                            />
                        </div>
                    </div>

                    {/* Garis Pemisah */}
                    <hr className="mt-4 mb-0" />
                </CardHeader>

                <CardContent className="p-0 px-4">
                    <div className="flex flex-col gap-2 mt-4">
                        {notifications.map((item) => (
                            <div
                                key={item.id}
                                className={`bg-white rounded-lg shadow-md cursor-pointer hover:shadow-lg transition-shadow border-l-4 ${
                                    !item.isRead
                                        ? "border-l-blue-500 bg-blue-50/30"
                                        : "border-l-transparent"
                                }`}
                            >
                                <div className="flex items-stretch p-4">
                                    {/* Icon */}
                                    <div className="mr-4 flex items-center justify-center w-10 h-10 rounded-full bg-black shrink-0">
                                        <Bell className="h-5 w-5 text-white" />
                                    </div>

                                    {/* Detail */}
                                    <div className="flex-1 min-w-0 flex flex-col justify-center">
                                        <div className="font-semibold text-lg truncate">
                                            {item.title}
                                        </div>
                                        <div className="text-sm text-gray-500 truncate">
                                            {item.message}
                                        </div>
                                    </div>

                                    {/* Kanan: Tipe & Tanggal */}
                                    <div className="text-right ml-4 flex flex-col justify-between h-full min-w-[80px]">
                                        <div
                                            className={`text-xs font-medium ${getTypeColor(
                                                item.type
                                            )}`}
                                        >
                                            {item.type}
                                        </div>
                                        <div className="text-gray-500 text-xs mt-2">
                                            {item.date}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ))}

                        {notifications.length === 0 && (
                            <div className="text-center py-10 text-muted-foreground bg-muted/10 rounded-lg border border-dashed w-full">
                                Tidak ada notifikasi baru.
                            </div>
                        )}
                    </div>
                </CardContent>
            </Card>
        </AppLayout>
    );
}
