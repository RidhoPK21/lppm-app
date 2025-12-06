import React, { useEffect, useState } from "react";
import AppLayout from "@/layouts/app-layout";
import { Head, Link, usePage } from "@inertiajs/react"; // Hapus router, gunakan Link
import { Button } from "@/components/ui/button";
import { Plus, Search, BookOpen, User, FileText } from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { Input } from "@/components/ui/input";
import { Card, CardContent } from "@/components/ui/card";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import { route } from "ziggy-js";
import Swal from "sweetalert2";

export default function BukuPage({ buku }) {
    const { flash } = usePage().props;
    const [searchTerm, setSearchTerm] = useState("");
    const [sortBy, setSortBy] = useState("newest");

    useEffect(() => {
        if (flash.success) {
            Swal.fire({
                title: "Berhasil!",
                text: flash.success,
                icon: "success",
                confirmButtonText: "OK",
                confirmButtonColor: "#000000",
                timer: 3000,
                timerProgressBar: true,
            });
        }
    }, [flash]);

    const breadcrumbs = [
        { title: "Penghargaan", url: "#" },
        { title: "Buku", url: "#" },
    ];

    const getStatusColor = (status) => {
        if (status.includes("Draft")) return "secondary";
        if (status.includes("Menunggu")) return "outline";
        if (status.includes("Disetujui")) return "default";
        if (status.includes("Ditolak")) return "destructive";
        return "outline";
    };

    const filteredBooks = buku
        .filter(
            (item) =>
                item.judul.toLowerCase().includes(searchTerm.toLowerCase()) ||
                item.penulis.toLowerCase().includes(searchTerm.toLowerCase()) ||
                item.isbn.includes(searchTerm)
        )
        .sort((a, b) => {
            if (sortBy === "newest") return b.id - a.id;
            if (sortBy === "oldest") return a.id - b.id;
            if (sortBy === "title_asc") return a.judul.localeCompare(b.judul);
            if (sortBy === "title_desc") return b.judul.localeCompare(a.judul);
            return 0;
        });

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Penghargaan Buku" />

            <div className="flex flex-col space-y-6">
                {/* Header & Actions */}
                <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">
                            Penghargaan Buku
                        </h1>
                        <p className="text-muted-foreground">
                            Kelola dan pantau status pengajuan buku Anda.
                        </p>
                    </div>
                    <Link href={route("app.penghargaan.buku.create")}>
                        <Button className="bg-black text-white hover:bg-black/80 w-full md:w-auto">
                            <Plus className="mr-2 h-4 w-4" />
                            Ajukan Buku Baru
                        </Button>
                    </Link>
                </div>

                {/* Toolbar: Search & Sort */}
                <div className="flex flex-col md:flex-row gap-4 items-center justify-between bg-white p-4 rounded-lg border shadow-sm">
                    <div className="relative w-full md:w-96">
                        <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input
                            type="search"
                            placeholder="Cari judul, penulis, atau ISBN..."
                            className="pl-9"
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                        />
                    </div>
                    <div className="flex items-center gap-3 w-full md:w-auto">
                        <span className="text-sm font-medium text-muted-foreground whitespace-nowrap">
                            Urutkan:
                        </span>
                        <Select value={sortBy} onValueChange={setSortBy}>
                            <SelectTrigger className="w-[180px]">
                                <SelectValue placeholder="Urutan" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="newest">Terbaru</SelectItem>
                                <SelectItem value="oldest">Terlama</SelectItem>
                                <SelectItem value="title_asc">
                                    Judul (A-Z)
                                </SelectItem>
                                <SelectItem value="title_desc">
                                    Judul (Z-A)
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                {/* List Content (Stacked Cards) */}
                {filteredBooks.length === 0 ? (
                    <div className="flex flex-col items-center justify-center py-20 text-center space-y-4 border-2 border-dashed rounded-lg bg-muted/10">
                        <div className="bg-muted/50 p-4 rounded-full">
                            <BookOpen className="h-10 w-10 text-muted-foreground" />
                        </div>
                        <div className="space-y-1">
                            <p className="text-lg font-medium">
                                Tidak ada buku ditemukan
                            </p>
                            <p className="text-sm text-muted-foreground">
                                Coba ubah kata kunci pencarian atau ajukan buku
                                baru.
                            </p>
                        </div>
                    </div>
                ) : (
                    <div className="flex flex-col space-y-4">
                        {filteredBooks.map((item) => (
                            // [SOLUSI UTAMA] Bungkus Card dengan Link Inertia
                            // block w-full: Agar Link mengisi lebar container
                            // hover:opacity-75: Feedback visual saat di-hover
                            <Link
                                key={item.id}
                                href={route("app.penghargaan.buku.detail", {
                                    id: item.id,
                                })}
                                className="block w-full transition-opacity hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black rounded-lg"
                            >
                                <Card className="border-l-4 border-l-transparent hover:border-l-black hover:shadow-md transition-all relative overflow-hidden bg-white cursor-pointer">
                                    <CardContent className="p-6">
                                        <div className="flex flex-col gap-4">
                                            {/* Header Card */}
                                            <div className="flex justify-between items-start">
                                                <div className="flex items-center gap-2">
                                                    <Badge
                                                        variant="outline"
                                                        className="text-xs font-normal bg-gray-50"
                                                    >
                                                        {item.kategori}
                                                    </Badge>
                                                    <span className="text-xs text-muted-foreground">
                                                        • {item.tahun}
                                                    </span>
                                                </div>
                                                <Badge
                                                    variant={getStatusColor(
                                                        item.status
                                                    )}
                                                    className="shrink-0 ml-4"
                                                >
                                                    {item.status}
                                                </Badge>
                                            </div>

                                            {/* Body Card */}
                                            <div>
                                                <h3 className="text-xl font-bold text-gray-900 leading-tight">
                                                    {item.judul}
                                                </h3>
                                                <p className="text-sm text-muted-foreground mt-1">
                                                    Penerbit: {item.penerbit}
                                                </p>
                                            </div>

                                            {/* Footer Card */}
                                            <div className="flex flex-wrap gap-3 text-sm text-gray-600 pt-2 border-t border-gray-100 mt-2">
                                                <div className="flex items-center gap-1.5">
                                                    <User className="h-3.5 w-3.5 text-muted-foreground" />
                                                    <span className="truncate max-w-[200px]">
                                                        {item.penulis}
                                                    </span>
                                                </div>
                                                <div className="flex items-center gap-1.5">
                                                    <FileText className="h-3.5 w-3.5 text-muted-foreground" />
                                                    <span>
                                                        ISBN: {item.isbn}
                                                    </span>
                                                </div>
                                                <div className="flex items-center gap-1.5">
                                                    <BookOpen className="h-3.5 w-3.5 text-muted-foreground" />
                                                    <span>
                                                        {item.jumlah_halaman}{" "}
                                                        Hal
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>
                            </Link>
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}