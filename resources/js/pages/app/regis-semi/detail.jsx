import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import AppLayout from "@/layouts/app-layout";
import { Head, Link } from "@inertiajs/react";
import {
    ArrowLeft,
    Calendar,
    CheckCircle,
    Clock,
    MapPin,
    Trophy,
    User,
} from "lucide-react";
import { route } from "ziggy-js";

export default function DetailPenghargaan({ id }) {
    return (
        <AppLayout>
            <Head title="Detail Penghargaan" />

            <div className="max-w-4xl mx-auto p-6 space-y-6">
                {/* Tombol Kembali */}
                <Link
                    href={route("regis-semi.index")}
                    className="flex items-center text-muted-foreground hover:text-primary transition-colors w-fit"
                >
                    <ArrowLeft className="h-4 w-4 mr-2" />
                    Kembali ke Daftar
                </Link>

                {/* Header Status */}
                <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b pb-6">
                    <div>
                        <div className="flex items-center gap-2 text-primary mb-2">
                            <Trophy className="h-5 w-5" />
                            <span className="font-semibold tracking-wide uppercase text-xs">
                                Penghargaan Masuk
                            </span>
                        </div>
                        <h1 className="text-3xl font-bold text-foreground">
                            Juara 1 Inovasi Teknologi Pendidikan
                        </h1>
                    </div>
                    <div className="flex items-center gap-2 bg-green-100 text-green-700 px-4 py-2 rounded-full border border-green-200">
                        <CheckCircle className="h-4 w-4" />
                        <span className="font-medium text-sm">
                            Terverifikasi
                        </span>
                    </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {/* Kolom Kiri: Informasi Utama */}
                    <div className="md:col-span-2 space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Deskripsi Penghargaan</CardTitle>
                            </CardHeader>
                            <CardContent className="text-muted-foreground leading-relaxed">
                                <p>
                                    Penghargaan ini diberikan atas kontribusi
                                    luar biasa dalam mengembangkan metode
                                    pembelajaran berbasis Artificial
                                    Intelligence yang diterapkan di lingkungan
                                    perguruan tinggi. Kompetisi ini diikuti oleh
                                    50 peserta dari berbagai universitas
                                    nasional.
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Informasi Acara</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="flex items-center gap-3">
                                    <div className="h-10 w-10 rounded-lg bg-muted flex items-center justify-center">
                                        <User className="h-5 w-5 text-muted-foreground" />
                                    </div>
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">
                                            Penyelenggara
                                        </p>
                                        <p className="font-medium">
                                            Kementerian Pendidikan & Kebudayaan
                                        </p>
                                    </div>
                                </div>
                                <div className="flex items-center gap-3">
                                    <div className="h-10 w-10 rounded-lg bg-muted flex items-center justify-center">
                                        <MapPin className="h-5 w-5 text-muted-foreground" />
                                    </div>
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">
                                            Lokasi Penyerahan
                                        </p>
                                        <p className="font-medium">
                                            Gedung A, Kemendikbud Jakarta
                                        </p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Kolom Kanan: Jadwal & Aksi */}
                    <div className="space-y-6">
                        <Card className="bg-muted/30">
                            <CardHeader>
                                <CardTitle className="text-base">
                                    Waktu Pelaksanaan
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="flex items-center gap-3">
                                    <Calendar className="h-4 w-4 text-primary" />
                                    <span className="text-sm font-medium">
                                        12 Desember 2025
                                    </span>
                                </div>
                                <div className="flex items-center gap-3">
                                    <Clock className="h-4 w-4 text-primary" />
                                    <span className="text-sm font-medium">
                                        09:00 - 12:00 WIB
                                    </span>
                                </div>
                            </CardContent>
                        </Card>

                        <div className="flex flex-col gap-3">
                            <Button className="w-full" asChild>
                                <Link href={route("regis-semi.invite", id)}>
                                    Lihat Undangan
                                </Link>
                            </Button>
                            <Button
                                variant="outline"
                                className="w-full"
                                asChild
                            >
                                <Link href={route("regis-semi.result", id)}>
                                    Lihat Sertifikat
                                </Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
