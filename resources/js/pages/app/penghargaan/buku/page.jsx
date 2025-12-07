import React, { useEffect, useState } from "react";
import AppLayout from "@/layouts/app-layout";
import { Head, Link, usePage } from "@inertiajs/react";
import { Button } from "@/components/ui/button";
import { Plus, Search } from "lucide-react";
import { Input } from "@/components/ui/input";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import { route } from "ziggy-js";
import Swal from "sweetalert2";
// Import Icon dari Tabler untuk menyamakan gaya dengan RegisSemi
import * as Icon from "@tabler/icons-react";

// --- Komponen Item Buku (Gaya List seperti RegisSemi) ---
const BukuItem = ({ href, judul, penulis, status, tahun, kategori }) => {
    // Logika warna teks status agar sesuai dengan standar
    let statusColorClass = "text-gray-500"; // Default (abu-abu)
    const statusLower = status.toLowerCase();

    if (
        statusLower.includes("disetujui") ||
        statusLower.includes("cair") ||
        statusLower.includes("paid")
    ) {
        statusColorClass = "text-green-600";
    } else if (
        statusLower.includes("ditolak") ||
        statusLower.includes("revisi")
    ) {
        statusColorClass = "text-red-600";
    } else if (
        statusLower.includes("menunggu") ||
        statusLower.includes("submitted") ||
        statusLower.includes("verified")
    ) {
        statusColorClass = "text-yellow-600"; // Kuning kecoklatan untuk status proses
    }

    return (
        <Link href={href} className="block w-full group">
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 mb-3 cursor-pointer hover:shadow-md transition-shadow">
                <div className="flex items-stretch p-4">
                    {/* Ikon Segitiga Putih dalam Lingkaran Hitam (Ciri Khas RegisSemi) */}
                    <div className="mr-4 flex items-center justify-center w-8 h-8 rounded-full bg-black flex-shrink-0 group-hover:scale-105 transition-transform">
                        <Icon.IconTriangle size={16} fill="white" />
                    </div>

                    {/* Detail Buku (Judul, Penulis, Kategori) */}
                    <div className="flex-1 min-w-0 flex flex-col justify-center">
                        <div className="font-medium text-base truncate text-gray-900">
                            {judul}
                        </div>
                        <div className="text-sm text-gray-500 truncate mt-0.5">
                            {penulis} <span className="mx-1">•</span> {kategori}
                        </div>
                    </div>

                    {/* Status dan Tahun */}
                    <div className="text-right ml-4 flex flex-col justify-between items-end">
                        <div className="text-gray-500 text-sm whitespace-nowrap">
                            Status :{" "}
                            <span
                                className={`capitalize font-normal ${statusColorClass}`}
                            >
                                {status}
                            </span>
                        </div>
                        <div className="text-gray-500 text-xs mt-2">
                            Tahun: {tahun}
                        </div>
                    </div>
                </div>
            </div>
        </Link>
    );
};

export default function BukuPage({ buku }) {
    const { flash } = usePage().props;
    const [searchTerm, setSearchTerm] = useState("");
    const [sortBy, setSortBy] = useState("newest");

    // Efek untuk notifikasi sukses (SweetAlert)
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

    // Filter dan Sorting Data
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
                {/* Header & Tombol Aksi */}
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
                    {/* Kolom Pencarian */}
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

                    {/* Kolom Sorting */}
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

                {/* Konten Daftar Buku (List View) */}
                <div className="mt-6">
                    {filteredBooks.length === 0 ? (
                        <div className="flex flex-col items-center justify-center py-20 text-center space-y-4 border-2 border-dashed rounded-lg bg-muted/10">
                            <div className="bg-muted/50 p-4 rounded-full">
                                <Icon.IconBook className="h-10 w-10 text-muted-foreground" />
                            </div>
                            <div className="space-y-1">
                                <p className="text-lg font-medium">
                                    Tidak ada buku ditemukan
                                </p>
                                <p className="text-sm text-muted-foreground">
                                    Coba ubah kata kunci pencarian atau ajukan
                                    buku baru.
                                </p>
                            </div>
                        </div>
                    ) : (
                        <div className="space-y-3">
                            {filteredBooks.map((item) => (
                                <BukuItem
                                    key={item.id}
                                    href={route("app.penghargaan.buku.detail", {
                                        id: item.id,
                                    })}
                                    judul={item.judul}
                                    penulis={item.penulis}
                                    kategori={item.kategori}
                                    status={item.status}
                                    tahun={item.tahun}
                                />
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
