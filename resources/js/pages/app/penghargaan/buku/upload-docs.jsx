import React from "react";
import AppLayout from "@/layouts/app-layout";
import { Head, useForm, Link, router } from "@inertiajs/react"; 
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardContent, CardFooter } from "@/components/ui/card";
import { ArrowLeft } from "lucide-react";
import { route } from "ziggy-js";
import Swal from "sweetalert2"; 

export default function UploadDocsPage({ bookId }) {
    const breadcrumbs = [
        { title: "Penghargaan", url: "#" },
        { title: "Buku", url: route("app.penghargaan.buku.index") },
        { title: "Unggah Dokumen", url: "#" },
    ];

    // Setup form state
    const { data, setData, post, processing, errors } = useForm({
        book_id: bookId,
        berita_acara: "",
        hasil_scan: "",
        hasil_review: "",
        surat_pernyataan: "",
        link_drive: "",
    });

    function handleSubmit(e) {
        e.preventDefault();

        post(route("app.penghargaan.buku.store_upload"), {
            onSuccess: () => {
                // Tampilkan SweetAlert ketika sukses
                Swal.fire({
                    title: "Data Berhasil Disimpan",
                    text: "Pengajuan penghargaan buku Anda telah berhasil dikirim.",
                    icon: "success",
                    showConfirmButton: false,
                    timer: 1500, // Alert hilang otomatis setelah 1.5 detik
                    timerProgressBar: true,
                }).then(() => {
                    // Setelah alert hilang, pindah ke halaman Index
                    router.visit(route("app.penghargaan.buku.index"));
                });
            },
            onError: () => {
                // Optional: Tampilkan error jika ada
                Swal.fire({
                    title: "Gagal Menyimpan",
                    text: "Silakan periksa kembali kelengkapan data Anda.",
                    icon: "error",
                });
            },
        });
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Unggah Dokumen Pendukung" />

            <div className="max-w-4xl mx-auto w-full space-y-6">
                {/* Header Navigation */}
                <div className="flex items-center gap-4">
                    <Link href={route("app.penghargaan.buku.create")}>
                        <Button
                            variant="secondary"
                            size="sm"
                            className="gap-2 bg-black text-white hover:bg-black/80 dark:bg-white dark:text-black"
                        >
                            <ArrowLeft className="h-4 w-4" /> Kembali
                        </Button>
                    </Link>
                    <h1 className="text-lg font-semibold">Link Google Drive</h1>
                </div>

                {/* Form Card */}
                <form onSubmit={handleSubmit}>
                    <Card className="pt-6">
                        <CardContent className="space-y-6">
                            {/* Berita Acara */}
                            <div className="space-y-2">
                                <Label htmlFor="berita_acara">
                                    Berita Acara Serah Terima Buku ke
                                    Perpustakaan :
                                </Label>
                                <Input
                                    id="berita_acara"
                                    placeholder="Value"
                                    value={data.berita_acara}
                                    onChange={(e) =>
                                        setData("berita_acara", e.target.value)
                                    }
                                    className="bg-muted/20"
                                />
                            </div>

                            {/* Hasil Scan */}
                            <div className="space-y-2">
                                <Label htmlFor="hasil_scan">
                                    Hasil Scan Penerbitan Buku :
                                </Label>
                                <Input
                                    id="hasil_scan"
                                    placeholder="Value"
                                    value={data.hasil_scan}
                                    onChange={(e) =>
                                        setData("hasil_scan", e.target.value)
                                    }
                                    className="bg-muted/20"
                                />
                            </div>

                            {/* Hasil Review */}
                            <div className="space-y-2">
                                <Label htmlFor="hasil_review">
                                    Hasil Review Penertiban Buku :
                                </Label>
                                <Input
                                    id="hasil_review"
                                    placeholder="Value"
                                    value={data.hasil_review}
                                    onChange={(e) =>
                                        setData("hasil_review", e.target.value)
                                    }
                                    className="bg-muted/20"
                                />
                            </div>

                            {/* Surat Pernyataan */}
                            <div className="space-y-2">
                                <Label htmlFor="surat_pernyataan">
                                    Surat Pernyataan ( Penertiban Tidak Didanai
                                    oleh Institusi + Bukti Biaya Penertiban ) :
                                </Label>
                                <Input
                                    id="surat_pernyataan"
                                    placeholder="Value"
                                    value={data.surat_pernyataan}
                                    onChange={(e) =>
                                        setData(
                                            "surat_pernyataan",
                                            e.target.value
                                        )
                                    }
                                    className="bg-muted/20"
                                />
                            </div>

                            {/* Google Drive Folder */}
                            <div className="space-y-2">
                                <Label htmlFor="link_drive">
                                    Folder Google Drive Berisi Semua Dokumen
                                    Pendukung :
                                </Label>
                                <Input
                                    id="link_drive"
                                    placeholder="Value"
                                    value={data.link_drive}
                                    onChange={(e) =>
                                        setData("link_drive", e.target.value)
                                    }
                                    className="bg-muted/20"
                                />
                                {errors.link_drive && (
                                    <p className="text-sm text-red-500">
                                        {errors.link_drive}
                                    </p>
                                )}
                            </div>
                        </CardContent>

                        {/* Tombol Aksi */}
                        <CardFooter className="flex justify-end py-6">
                            <Button
                                type="submit"
                                disabled={processing}
                                size="lg"
                                className="bg-black text-white hover:bg-black/80 dark:bg-white dark:text-black font-semibold"
                            >
                                {processing
                                    ? "Menyimpan..."
                                    : "Simpan Data & Lanjutkan"}
                            </Button>
                        </CardFooter>
                    </Card>
                </form>
            </div>
        </AppLayout>
    );
}