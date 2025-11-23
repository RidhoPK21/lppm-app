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
import { Head, Link } from "@inertiajs/react";
import { CircleArrowUp, Plus, Search } from "lucide-react";
import { route } from "ziggy-js";

export default function PenghargaanBukuPage({ buku }) {
    const breadcrumbs = [
        { title: "Penghargaan", url: "#" },
        { title: "Buku", url: route("app.penghargaan.buku.index") },
    ];

    // Helper warna status
    const getStatusColor = (status) => {
        const s = status.toLowerCase();
        if (
            s.includes("belum") ||
            s.includes("diajukan") ||
            s.includes("pending")
        )
            return "text-red-500";
        if (s.includes("setuju") || s.includes("disetujui"))
            return "text-green-500";
        if (s.includes("tolak")) return "text-gray-500";
        return "text-gray-500";
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Penghargaan Buku" />

            <div className="flex flex-col gap-6 w-full max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                {/* Header Judul */}
                <div>
                    <h1 className="text-3xl font-bold tracking-tight">Buku</h1>
                </div>

                {/* Tombol Ajukan Besar */}
                <Link
                    href={route("app.penghargaan.buku.create")}
                    className="w-full"
                >
                    <div className="bg-white dark:bg-sidebar border border-border rounded-lg p-4 shadow-sm hover:shadow-md transition-all cursor-pointer flex items-center text-muted-foreground hover:text-primary group w-full">
                        <span className="font-medium">
                            Ajukan Penghargaan Buku
                        </span>
                        <Plus className="ml-auto h-5 w-5 opacity-50 group-hover:opacity-100 transition-opacity" />
                    </div>
                </Link>

                {/* Baris Filter & Pencarian */}
                <div className="flex flex-col md:flex-row gap-3 w-full">
                    <div className="flex-1 flex gap-2">
                        <Input
                            placeholder="Type to search"
                            className="bg-white dark:bg-sidebar"
                        />
                        <Button
                            variant="secondary"
                            className="bg-gray-100 dark:bg-muted hover:bg-gray-200"
                        >
                            Search
                        </Button>
                    </div>
                    <div className="flex gap-2">
                        <Select>
                            <SelectTrigger className="w-[140px] bg-white dark:bg-sidebar">
                                <SelectValue placeholder="Search by" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="judul">Judul</SelectItem>
                                <SelectItem value="penulis">Penulis</SelectItem>
                            </SelectContent>
                        </Select>
                        <Select>
                            <SelectTrigger className="w-[140px] bg-white dark:bg-sidebar">
                                <SelectValue placeholder="Sort by" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="terbaru">Terbaru</SelectItem>
                                <SelectItem value="terlama">Terlama</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                {/* List Buku (Card Rows) */}
                <div className="flex flex-col gap-3 w-full">
                    {buku.map((item) => (
                        // PERBAIKAN DI SINI: Menambahkan 'flex-row'
                        <Card
                            key={item.id}
                            className="w-full p-4 flex flex-row items-center justify-between gap-4 hover:bg-accent/5 transition-colors"
                        >
                            {/* BAGIAN KIRI: Icon + Judul + Penulis */}
                            <div className="flex items-center gap-4 min-w-0 flex-1 text-left">
                                {/* Icon Bulat Hitam */}
                                <div className="shrink-0">
                                    <div className="h-10 w-10 rounded-full bg-black flex items-center justify-center text-white dark:bg-white dark:text-black">
                                        <CircleArrowUp className="h-6 w-6" />
                                    </div>
                                </div>

                                {/* Judul & Penulis */}
                                <div className="flex flex-col min-w-0">
                                    <h3 className="font-semibold text-base truncate">
                                        {item.judul}
                                    </h3>
                                    <p className="text-sm text-muted-foreground truncate">
                                        {item.penulis}
                                    </p>
                                </div>
                            </div>

                            {/* BAGIAN KANAN: Status + Tanggal */}
                            <div className="text-right shrink-0">
                                <p
                                    className={`text-xs font-medium ${getStatusColor(
                                        item.status
                                    )}`}
                                >
                                    Status : {item.status}
                                </p>
                                <p className="text-xs text-muted-foreground mt-1">
                                    {item.tahun
                                        ? `Tahun ${item.tahun}`
                                        : "23 / 11 / 2025"}
                                </p>
                            </div>
                        </Card>
                    ))}

                    {buku.length === 0 && (
                        <div className="text-center py-10 text-muted-foreground bg-muted/10 rounded-lg border border-dashed w-full">
                            Belum ada data buku yang diajukan.
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
