import React from "react";
import AppLayout from "@/layouts/app-layout";
import { Head, Link } from "@inertiajs/react";
import { Button } from "@/components/ui/button";
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import {
    ArrowLeft,
    FileText,
    CheckCircle,
    Send,
    MessageSquare,
    File,
    Clock,
} from "lucide-react";
import { route } from "ziggy-js";

export default function DetailBukuPage({ book }) {
    const breadcrumbs = [
        { title: "Penghargaan", url: "#" },
        { title: "Buku", url: route("app.penghargaan.buku.index") },
        { title: "Detail", url: "#" },
    ];

    // Helper warna status
    const getStatusColor = (status) => {
        if (!status) return "outline";
        const s = status.toLowerCase();
        if (s.includes("draft")) return "secondary";
        if (s.includes("menunggu") || s.includes("submitted")) return "warning";
        if (s.includes("disetujui") || s.includes("approved")) return "default";
        if (s.includes("ditolak") || s.includes("rejected"))
            return "destructive";
        return "outline";
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Detail - ${book.title}`} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link href={route("app.penghargaan.buku.index")}>
                            <Button
                                variant="outline"
                                size="icon"
                                className="h-9 w-9"
                            >
                                <ArrowLeft className="h-4 w-4" />
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-bold tracking-tight">
                                Detail Pengajuan Buku
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                ID Pengajuan: #{book.id}
                            </p>
                        </div>
                    </div>
                    <Badge
                        variant={getStatusColor(book.status)}
                        className="text-sm px-3 py-1"
                    >
                        {book.status}
                    </Badge>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Kolom Kiri: Informasi Utama */}
                    <div className="lg:col-span-2 space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Informasi Buku</CardTitle>
                                <CardDescription>
                                    Detail buku yang diajukan.
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-6">
                                <div className="grid gap-4">
                                    <div className="space-y-1">
                                        <label className="text-sm font-medium text-muted-foreground">
                                            Judul Buku
                                        </label>
                                        <p className="text-lg font-semibold">
                                            {book.title}
                                        </p>
                                    </div>
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div className="space-y-1">
                                            <label className="text-sm font-medium text-muted-foreground">
                                                Penulis
                                            </label>
                                            <p>
                                                {book.authors &&
                                                book.authors.length > 0
                                                    ? book.authors
                                                          .map((a) => a.name)
                                                          .join(", ")
                                                    : "-"}
                                            </p>
                                        </div>
                                        <div className="space-y-1">
                                            <label className="text-sm font-medium text-muted-foreground">
                                                ISBN
                                            </label>
                                            <p className="font-mono bg-muted px-2 py-1 rounded w-fit text-sm">
                                                {book.isbn}
                                            </p>
                                        </div>
                                    </div>
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div className="space-y-1">
                                            <label className="text-sm font-medium text-muted-foreground">
                                                Penerbit
                                            </label>
                                            <p>
                                                {book.publisher} (
                                                {book.publication_year})
                                            </p>
                                            <Badge
                                                variant="secondary"
                                                className="mt-1"
                                            >
                                                {book.publisher_level}
                                            </Badge>
                                        </div>
                                        <div className="space-y-1">
                                            <label className="text-sm font-medium text-muted-foreground">
                                                Kategori
                                            </label>
                                            <div className="flex items-center gap-2">
                                                <Badge variant="outline">
                                                    {book.book_type}
                                                </Badge>
                                                <span className="text-sm text-muted-foreground">
                                                    • {book.total_pages} Hal
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Dokumen Pendukung */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Dokumen Pendukung</CardTitle>
                                <CardDescription>
                                    Berkas yang telah diunggah.
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                {book.drive_link ? (
                                    <div className="space-y-2">
                                        {(() => {
                                            try {
                                                const links = JSON.parse(
                                                    book.drive_link
                                                );
                                                return Array.isArray(links) ? (
                                                    links.map((link, idx) => (
                                                        <a
                                                            key={idx}
                                                            href={link}
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            className="flex items-center p-3 rounded-md border hover:bg-muted transition-colors group"
                                                        >
                                                            <File className="h-5 w-5 text-blue-500 mr-3" />
                                                            <div className="flex-1 truncate">
                                                                <p className="text-sm font-medium text-blue-600 group-hover:underline">
                                                                    Dokumen{" "}
                                                                    {idx + 1}
                                                                </p>
                                                                <p className="text-xs text-muted-foreground truncate">
                                                                    {link}
                                                                </p>
                                                            </div>
                                                        </a>
                                                    ))
                                                ) : (
                                                    <a
                                                        href={book.drive_link}
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        className="text-blue-600 hover:underline"
                                                    >
                                                        Buka Link Drive
                                                    </a>
                                                );
                                            } catch (e) {
                                                return (
                                                    <a
                                                        href={book.drive_link}
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        className="text-blue-600 hover:underline"
                                                    >
                                                        Buka Link Drive
                                                    </a>
                                                );
                                            }
                                        })()}
                                    </div>
                                ) : (
                                    <div className="text-center py-8 border-2 border-dashed rounded-lg bg-muted/5">
                                        <p className="text-muted-foreground text-sm italic">
                                            Belum ada dokumen diunggah.
                                        </p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>

                    {/* Kolom Kanan: Status & Aksi */}
                    <div className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Status Terkini</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-6">
                                    <div className="flex gap-3">
                                        <div className="mt-0.5">
                                            <CheckCircle className="h-5 w-5 text-green-500" />
                                        </div>
                                        <div>
                                            <p className="font-medium text-sm">
                                                Pengajuan Dibuat
                                            </p>
                                            <p className="text-xs text-muted-foreground">
                                                {new Date(
                                                    book.created_at
                                                ).toLocaleString("id-ID", {
                                                    dateStyle: "long",
                                                    timeStyle: "short",
                                                })}
                                            </p>
                                        </div>
                                    </div>

                                    {/* Tampilkan status saat ini */}
                                    <div className="flex gap-3">
                                        <div className="mt-0.5">
                                            <Clock className="h-5 w-5 text-yellow-500" />
                                        </div>
                                        <div>
                                            <p className="font-medium text-sm text-foreground">
                                                Status: {book.status}
                                            </p>
                                            <p className="text-xs text-muted-foreground">
                                                Proses sedang berlangsung.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>

                            <CardFooter className="flex flex-col gap-3 border-t pt-4">
                                {/* Tombol 1: Kirim (Lanjut Upload) - Selalu Muncul */}
                                <Link
                                    href={route("app.penghargaan.buku.upload", {
                                        id: book.id,
                                    })}
                                    className="w-full"
                                >
                                    <Button className="w-full bg-blue-600 hover:bg-blue-700 text-white">
                                        <Send className="mr-2 h-4 w-4" />
                                        Kirim (Lanjut Upload)
                                    </Button>
                                </Link>

                                {/* Tombol 2: Review Pengajuan - Selalu Muncul */}
                                <Button
                                    variant="outline"
                                    className="w-full border-dashed border-2 hover:bg-gray-50"
                                >
                                    <MessageSquare className="mr-2 h-4 w-4 text-muted-foreground" />
                                    Review Pengajuan
                                </Button>
                            </CardFooter>
                        </Card>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
