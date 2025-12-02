import React from "react";
import AppLayout from "@/layouts/app-layout";
import { Head, useForm, Link } from "@inertiajs/react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
    CardFooter,
} from "@/components/ui/card";
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";
import { Info, ArrowRight } from "lucide-react";
import { route } from "ziggy-js";

export default function UploadDocsPage({ bookId, bookTitle }) {
    const breadcrumbs = [
        { title: "Penghargaan", url: "#" },
        { title: "Buku", url: route("app.penghargaan.buku.index") },
        { title: "Upload Dokumen", url: "#" },
    ];

    // State untuk 5 link dokumen
    const { data, setData, post, processing, errors } = useForm({
        links: ["", "", "", "", ""],
    });

    // Daftar Label Dokumen yang spesifik
    const documentLabels = [
        "1. Berita Acara Serah Terima Buku ke Perpustakaan",
        "2. Hasil Scan Penertiban Buku",
        "3. Hasil Review Penertiban Buku",
        "4. Surat Pernyataan (Penertiban Tidak Didanai oleh Institusi + Bukti Biaya)",
        "5. Folder Google Drive Berisi Semua Dokumen Pendukung",
    ];

    // Helper untuk update link berdasarkan index
    function handleLinkChange(index, value) {
        const newLinks = [...data.links];
        newLinks[index] = value;
        setData("links", newLinks);
    }

    function handleSubmit(e) {
        e.preventDefault();
        post(route("app.penghargaan.buku.store-upload", { id: bookId }));
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Unggah Dokumen Pendukung" />

            <div className="max-w-2xl mx-auto w-full space-y-6">
                <Alert className="bg-blue-50 border-blue-200 text-blue-800">
                    <Info className="h-4 w-4" />
                    <AlertTitle>Langkah Terakhir!</AlertTitle>
                    <AlertDescription>
                        Anda sedang melengkapi dokumen untuk buku: <br />
                        <strong>"{bookTitle}"</strong>. <br />
                        Silakan lampirkan link Google Drive untuk setiap dokumen
                        persyaratan di bawah ini.
                    </AlertDescription>
                </Alert>

                <Card>
                    <CardHeader>
                        <CardTitle>Link Dokumen Digital</CardTitle>
                        <CardDescription>
                            Pastikan link Google Drive bersifat{" "}
                            <strong>Public (Anyone with the link)</strong> agar
                            dapat diakses oleh reviewer.
                        </CardDescription>
                    </CardHeader>
                    <form onSubmit={handleSubmit}>
                        <CardContent className="space-y-6">
                            {/* Loop untuk menampilkan 5 Input Link dengan Label Spesifik */}
                            {data.links.map((link, index) => (
                                <div key={index} className="space-y-2">
                                    <Label
                                        htmlFor={`link_${index}`}
                                        className="text-base font-medium"
                                    >
                                        {documentLabels[index]}{" "}
                                        <span className="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id={`link_${index}`}
                                        placeholder={`Tempel link Google Drive untuk dokumen ini...`}
                                        value={link}
                                        onChange={(e) =>
                                            handleLinkChange(
                                                index,
                                                e.target.value
                                            )
                                        }
                                    />
                                    {/* Menampilkan error spesifik per baris */}
                                    {errors[`links.${index}`] && (
                                        <p className="text-sm text-red-500">
                                            {errors[`links.${index}`]}
                                        </p>
                                    )}
                                </div>
                            ))}

                            {errors.links && (
                                <p className="text-sm text-red-500 font-medium mt-2 bg-red-50 p-2 rounded">
                                    ⚠️ {errors.links}
                                </p>
                            )}
                        </CardContent>
                        <CardFooter className="flex justify-end bg-muted/10 py-4 gap-2">
                            <Link href={route("app.penghargaan.buku.index")}>
                                <Button variant="ghost" type="button">
                                    Nanti Saja
                                </Button>
                            </Link>

                            <Button
                                type="submit"
                                disabled={processing}
                                className="bg-black text-white hover:bg-gray-800"
                            >
                                <ArrowRight className="mr-2 h-4 w-4" />
                                Simpan & Ajukan
                            </Button>
                        </CardFooter>
                    </form>
                </Card>
            </div>
        </AppLayout>
    );
}