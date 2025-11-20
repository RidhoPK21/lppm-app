import React from "react";
import AppLayout from "@/layouts/app-layout";
import { Head, Link } from "@inertiajs/react";
import { Button } from "@/components/ui/button";
import { DataTable } from "@/components/data-table";
import { columns } from "./columns";
import { BookOpen, PlusCircle } from "lucide-react";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";
import { route } from "ziggy-js";

export default function PenghargaanBukuPage({ buku }) {
    const breadcrumbs = [
        { title: "Penghargaan", url: "#" },
        { title: "Buku", url: route("app.penghargaan.buku.index") },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Penghargaan Buku" />

            <div className="flex flex-1 flex-col gap-8">
                {/* Header Halaman */}
                <div className="flex items-center justify-between">
                    <h2 className="text-2xl font-bold tracking-tight">
                        Penghargaan Buku
                    </h2>
                </div>

                {/* BAGIAN ATAS: Card Informasi & Tombol Ajukan */}
                <Card className="w-full border-l-4 border-l-primary shadow-sm">
                    <div className="flex flex-col md:flex-row">
                        <div className="flex-1 p-6 pt-6">
                            <div className="flex flex-row items-start gap-4">
                                <div className="p-3 bg-primary/10 rounded-xl shrink-0">
                                    <BookOpen className="h-8 w-8 text-primary" />
                                </div>
                                <div className="space-y-2">
                                    <CardTitle className="text-xl">
                                        Penghargaan Publikasi Buku
                                    </CardTitle>
                                    <CardDescription className="text-base text-muted-foreground leading-relaxed max-w-3xl">
                                        Fasilitas ini diberikan kepada
                                        dosen/peneliti yang berhasil menerbitkan
                                        buku ajar, buku referensi, atau monograf
                                        yang memiliki ISBN dan diterbitkan oleh
                                        penerbit anggota IKAPI atau penerbit
                                        internasional bereputasi.
                                    </CardDescription>
                                </div>
                            </div>
                        </div>

                        {/* Tombol Ajukan di sebelah kanan (desktop) atau bawah (mobile) */}
                        <div className="p-6 flex items-center justify-end md:border-l bg-muted/10 md:w-64 shrink-0">
                            <Link
                                href={route("app.penghargaan.buku.create")}
                                className="w-full"
                            >
                                <Button
                                    size="lg"
                                    className="w-full font-semibold shadow-sm"
                                >
                                    <PlusCircle className="mr-2 h-5 w-5" />
                                    Ajukan Penghargaan Buku
                                </Button>
                            </Link>
                        </div>
                    </div>
                </Card>

                {/* BAGIAN BAWAH: Tabel Riwayat Pengajuan */}
                <div className="space-y-4">
                    <div className="flex items-center justify-between">
                        <h3 className="text-lg font-semibold tracking-tight">
                            Riwayat Pengajuan
                        </h3>
                    </div>

                    <Card>
                        <CardHeader className="pb-3">
                            <CardTitle className="text-base">
                                Data Buku yang Diajukan
                            </CardTitle>
                            <CardDescription>
                                Daftar buku yang telah Anda ajukan untuk proses
                                insentif.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            {/* Table Component memanggil kolom dari columns.jsx dan data dari Controller */}
                            <DataTable columns={columns} data={buku} />
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
