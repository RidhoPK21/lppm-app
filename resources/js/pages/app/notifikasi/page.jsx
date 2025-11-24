import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
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
import { Bell } from "lucide-react";

export default function NotificationPage() {
    // Data Dummy Notifikasi (disesuaikan strukturnya)
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

    // Helper untuk warna teks tipe notifikasi (mirip status di buku)
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

            <div className="flex flex-col gap-6 w-full max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                {/* Header Judul */}
                <div>
                    <h1 className="text-3xl font-bold tracking-tight">
                        Notifikasi
                    </h1>
                    <p className="text-muted-foreground mt-1">
                        Pantau semua aktivitas dan pemberitahuan terbaru Anda di
                        sini.
                    </p>
                </div>

                {/* Baris Filter & Pencarian (Persis seperti Penghargaan Buku) */}
                <div className="flex flex-col md:flex-row gap-3 w-full">
                    <div className="flex-1 flex gap-2">
                        <Input
                            placeholder="Cari notifikasi..."
                            className="bg-white dark:bg-sidebar"
                        />
                        <Button
                            variant="secondary"
                            className="bg-gray-100 dark:bg-muted hover:bg-gray-200"
                        >
                            Cari
                        </Button>
                    </div>
                    <div className="flex gap-2">
                        <Select>
                            <SelectTrigger className="w-[140px] bg-white dark:bg-sidebar">
                                <SelectValue placeholder="Filter" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="semua">Semua</SelectItem>
                                <SelectItem value="belum_dibaca">
                                    Belum Dibaca
                                </SelectItem>
                                <SelectItem value="info">Info</SelectItem>
                            </SelectContent>
                        </Select>
                        <Select>
                            <SelectTrigger className="w-[140px] bg-white dark:bg-sidebar">
                                <SelectValue placeholder="Urutkan" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="terbaru">Terbaru</SelectItem>
                                <SelectItem value="terlama">Terlama</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                {/* List Notifikasi (Layout Card seperti Buku) */}
                <div className="flex flex-col gap-3 w-full">
                    {notifications.map((item) => (
                        <Card
                            key={item.id}
                            className={`w-full p-4 flex flex-row items-center justify-between gap-4 hover:bg-accent/5 transition-colors cursor-pointer ${
                                !item.isRead
                                    ? "bg-muted/30 border-l-4 border-l-primary"
                                    : ""
                            }`}
                        >
                            {/* BAGIAN KIRI: Icon + Judul + Pesan */}
                            <div className="flex items-center gap-4 min-w-0 flex-1 text-left">
                                {/* Icon Bulat Hitam (Ganti Arrow jadi Bell) */}
                                <div className="shrink-0">
                                    <div className="h-10 w-10 rounded-full bg-black flex items-center justify-center text-white dark:bg-white dark:text-black">
                                        <Bell className="h-5 w-5" />
                                    </div>
                                </div>

                                {/* Judul & Pesan */}
                                <div className="flex flex-col min-w-0">
                                    <h3 className="font-semibold text-base truncate">
                                        {item.title}
                                    </h3>
                                    <p className="text-sm text-muted-foreground truncate">
                                        {item.message}
                                    </p>
                                </div>
                            </div>

                            {/* BAGIAN KANAN: Tipe (Status) + Tanggal */}
                            <div className="text-right shrink-0">
                                <p
                                    className={`text-xs font-medium ${getTypeColor(
                                        item.type
                                    )}`}
                                >
                                    {item.type}
                                </p>
                                <p className="text-xs text-muted-foreground mt-1">
                                    {item.date}
                                </p>
                            </div>
                        </Card>
                    ))}

                    {notifications.length === 0 && (
                        <div className="text-center py-10 text-muted-foreground bg-muted/10 rounded-lg border border-dashed w-full">
                            Tidak ada notifikasi baru.
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}