import React, { useState } from "react";
import AppLayout from "@/layouts/app-layout";
import { Head, router } from "@inertiajs/react";
import { route } from "ziggy-js";

// Import Komponen UI
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Card, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Label } from "@/components/ui/label";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";

// Import Ikon
import {
    ArrowLeft,
    FileText,
    CheckCircle,
    XCircle,
    Users,
    ClipboardList,
    ExternalLink,
    Loader2,
    FileType,
    AlertCircle,
    MessageSquare,
} from "lucide-react";

// Helper Component untuk Form Read-Only
const SideBySideFormField = ({ label, children }) => (
    <div className="flex flex-col md:flex-row md:items-center space-y-1 md:space-y-0 space-x-0 md:space-x-8">
        <label className="text-sm font-medium text-gray-700 md:w-1/4 min-w-[200px] text-left">
            {label}:
        </label>
        <div className="flex-1 w-full">{children}</div>
    </div>
);

const StackedFormField = ({ label, children }) => (
    <div className="flex flex-col space-y-1 mt-4">
        <label className="text-sm font-medium text-gray-700 text-left">
            {label}:
        </label>
        <div className="w-full">{children}</div>
    </div>
);

export default function DetailRegisSemi({ book }) {
    // --- STATE UNTUK POPUP ---
    const [isApproveOpen, setIsApproveOpen] = useState(false);
    const [isRejectOpen, setIsRejectOpen] = useState(false);
    
    const [amount, setAmount] = useState("");
    const [rejectNote, setRejectNote] = useState("");
    const [isSubmitting, setIsSubmitting] = useState(false);

    // Safety check data
    if (!book) return <div>Loading data...</div>;
    
    // Handle drive_links
    const links = Array.isArray(book.drive_link) ? book.drive_link : [];
    
    // Tautan pertama (dianggap sebagai tautan folder utama)
    const documentLink = links.length > 0 ? links[0] : null;

    // Cek apakah ada PDF
    const hasPdfFile = book.pdf_path ? true : false;

    // --- LOGIKA TOMBOL NAVIGASI ---
    const handleAction = (action) => {
        if (action === "back") {
            router.visit(route("regis-semi.index"));
        } else if (action === "invite") {
            router.visit(route("regis-semi.invite", book.id));
        } else if (action === "result") {
            router.visit(route("regis-semi.result", book.id));
        } else if (action === "open-document" && documentLink) {
            window.open(documentLink, "_blank");
        }
    };

    // --- 1. SUBMIT PERSETUJUAN (DENGAN HARGA) ---
    const submitApprove = (e) => {
        e.preventDefault();
        setIsSubmitting(true);

        router.post(
            route("regis-semi.approve", book.id),
            {
                amount: amount,
            },
            {
                onSuccess: () => {
                    setIsApproveOpen(false);
                    setIsSubmitting(false);
                    setAmount("");
                },
                onError: () => setIsSubmitting(false),
            }
        );
    };

    // --- 2. SUBMIT PENOLAKAN (DENGAN CATATAN) ---
    const submitReject = (e) => {
        e.preventDefault();
        setIsSubmitting(true);

        router.post(
            route("regis-semi.reject", book.id),
            {
                note: rejectNote,
            },
            {
                onSuccess: () => {
                    setIsRejectOpen(false);
                    setIsSubmitting(false);
                    setRejectNote("");
                },
                onError: () => setIsSubmitting(false),
            }
        );
    };

    return (
        <AppLayout>
            <Head title={`Verifikasi - ${book.title}`} />

            <div className="max-w-7xl mx-auto p-4 md:px-8 space-y-6 pb-20">
                {/* HEADER */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Button
                            variant="outline"
                            size="icon"
                            onClick={() => handleAction("back")}
                        >
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                        <div>
                            <h1 className="text-2xl font-bold">
                                Verifikasi Buku
                            </h1>
                            <p className="text-sm text-gray-500">
                                Pengusul: {book.dosen}
                            </p>
                        </div>
                    </div>
                    <Badge
                        variant={
                            book.status === "APPROVED_CHIEF"
                                ? "success"
                                : book.status === "REJECTED"
                                ? "destructive"
                                : "outline"
                        }
                    >
                        {book.status_label}
                    </Badge>
                </div>

                {/* DETAIL BUKU */}
                <Card>
                    <CardContent className="p-6 space-y-4">
                        <SideBySideFormField label="Judul Buku">
                            <Input
                                value={book.title || ""}
                                readOnly
                                className="bg-gray-50"
                            />
                        </SideBySideFormField>
                        <SideBySideFormField label="ISBN">
                            <Input
                                value={book.isbn || ""}
                                readOnly
                                className="bg-gray-50 font-mono"
                            />
                        </SideBySideFormField>
                        <SideBySideFormField label="Penerbit">
                            <Input
                                value={book.publisher || ""}
                                readOnly
                                className="bg-gray-50"
                            />
                        </SideBySideFormField>

                        {/* PDF SECTION (Sederhana) */}
                        {hasPdfFile && (
                            <div className="mt-6 pt-4 border-t">
                                <StackedFormField label="File PDF Buku">
                                    <div className="space-y-2">
                                        <div className="flex items-center gap-2">
                                            <FileType className="h-5 w-5 text-green-600" />
                                            <span className="text-sm font-medium text-gray-700">
                                                PDF Surat Permohonan Tersedia
                                            </span>
                                        </div>
                                       
                                    </div>
                                </StackedFormField>
                            </div>
                        )}

                        {/* LIST DOKUMEN */}
                        <div className="mt-8 pt-4 border-t">
                            <h3 className="font-semibold mb-4 text-gray-900">
                                Dokumen Pendukung
                            </h3>
                            {links.length > 0 ? (
                                <div className="space-y-3">
                                    {links.map((link, idx) => {
                                        const isPdf = link && (
                                            link.toLowerCase().includes('.pdf') ||
                                            link.includes('drive.google.com/file')
                                        );
                                        
                                        return (
                                            <StackedFormField
                                                key={idx}
                                                label={`Dokumen ${idx + 1}${isPdf ? ' (PDF)' : ''}`}
                                            >
                                                <div className="flex gap-2">
                                                    <Input
                                                        value={link}
                                                        readOnly
                                                        className={`bg-gray-50 ${isPdf ? 'text-purple-600' : 'text-blue-600'}`}
                                                    />
                                                    <a
                                                        href={link}
                                                        target="_blank"
                                                        rel="noreferrer"
                                                    >
                                                        <Button
                                                            variant="outline"
                                                            size="icon"
                                                            title={isPdf ? "Buka PDF" : "Buka Link"}
                                                        >
                                                            {isPdf ? (
                                                                <FileType className="h-4 w-4" />
                                                            ) : (
                                                                <ExternalLink className="h-4 w-4" />
                                                            )}
                                                        </Button>
                                                    </a>
                                                </div>
                                            </StackedFormField>
                                        );
                                    })}
                                </div>
                            ) : (
                                <div className="flex items-center gap-2 text-amber-600 bg-amber-50 p-3 rounded-md">
                                    <AlertCircle className="h-4 w-4" />
                                    <p className="text-sm">
                                        Tidak ada link dokumen pendukung.
                                    </p>
                                </div>
                            )}
                        </div>
                    </CardContent>
                </Card>

                {/* --- TOMBOL UTAMA --- */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    {/* 1. Buka Dokumen */}
                    <Button
                        variant="secondary"
                        className="h-12 border border-gray-200"
                        onClick={() => handleAction("open-document")}
                        disabled={!documentLink}
                    >
                        <FileText className="mr-2 h-5 w-5" /> Buka Folder
                        Dokumen
                    </Button>

                    {/* 2. PDF Download (jika ada) */}
                    {hasPdfFile ? (
                        <Button
                            variant="outline"
                            className="h-12 border-blue-600 text-blue-600 hover:bg-blue-50"
                            onClick={() => window.open(route("regis-semi.download-pdf", book.id), "_blank")}
                        >
                            <FileType className="mr-2 h-5 w-5" />
                            Download PDF
                        </Button>
                    ) : (
                        <Button
                            variant="outline"
                            className="h-12 border-gray-300 text-gray-400 hover:bg-gray-50"
                            disabled
                        >
                            <FileType className="mr-2 h-5 w-5" />
                            Tidak Ada PDF
                        </Button>
                    )}

                    {/* 3. SETUJUI */}
                    <Button
                        onClick={() => setIsApproveOpen(true)}
                        className="bg-green-600 hover:bg-green-700 h-12 text-white"
                        disabled={
                            book.status === "APPROVED_CHIEF" ||
                            book.status === "REJECTED" ||
                            book.status === "PAID"
                        }
                    >
                        <CheckCircle className="mr-2 h-5 w-5" /> Setujui
                    </Button>

                    {/* 4. TOLAK */}
                    <Button
                        onClick={() => setIsRejectOpen(true)}
                        className="bg-red-600 hover:bg-red-700 h-12 text-white"
                        disabled={
                            book.status === "APPROVED_CHIEF" ||
                            book.status === "REJECTED" ||
                            book.status === "PAID"
                        }
                    >
                        <XCircle className="mr-2 h-5 w-5" /> Tolak
                    </Button>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <Button
                        variant="outline"
                        onClick={() => handleAction("invite")}
                        className="h-12 border-gray-400"
                        disabled={book.status === "REJECTED"}
                    >
                        <Users className="mr-2 h-5 w-5" /> Minta Penilaian
                    </Button>
                   <Button
    onClick={() => router.visit(route('regis-semi.review-results', book.id))}
    variant="outline"
    className="border-2 border-black hover:bg-black hover:text-white"
>
    <MessageSquare className="h-4 w-4 mr-2" />
    Lihat Hasil 
</Button>
                </div>
            </div>

            {/* --- POPUP 1: SETUJUI (INPUT HARGA) --- */}
            <Dialog open={isApproveOpen} onOpenChange={setIsApproveOpen}>
                <DialogContent className="sm:max-w-[425px]">
                    <DialogHeader>
                        <DialogTitle className="text-green-700">
                            Setujui Pengajuan
                        </DialogTitle>
                        <DialogDescription>
                            Tentukan nominal penghargaan yang akan diterima
                            dosen.
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={submitApprove} className="space-y-4 py-4">
                        <div className="grid gap-2">
                            <Label htmlFor="amount">Nominal (Rupiah)</Label>
                            <div className="relative">
                                <span className="absolute left-3 top-2.5 text-gray-500 font-semibold">
                                    Rp
                                </span>
                                <Input
                                    id="amount"
                                    type="number"
                                    min="0"
                                    placeholder="0"
                                    className="pl-10"
                                    value={amount}
                                    onChange={(e) => setAmount(e.target.value)}
                                    required
                                    autoFocus
                                />
                            </div>
                        </div>
                        <DialogFooter>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => setIsApproveOpen(false)}
                            >
                                Batal
                            </Button>
                            <Button
                                type="submit"
                                className="bg-green-600 hover:bg-green-700"
                                disabled={isSubmitting || !amount}
                            >
                                {isSubmitting ? (
                                    <>
                                        <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                        Menyimpan...
                                    </>
                                ) : (
                                    "Setujui"
                                )}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* --- POPUP 2: TOLAK (INPUT KOMENTAR) --- */}
            <Dialog open={isRejectOpen} onOpenChange={setIsRejectOpen}>
                <DialogContent className="sm:max-w-[500px]">
                    <DialogHeader>
                        <DialogTitle className="text-red-600">
                            Tolak Pengajuan
                        </DialogTitle>
                        <DialogDescription>
                            Berikan alasan penolakan agar dosen dapat
                            memperbaikinya.
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={submitReject} className="space-y-4 py-4">
                        <div className="grid gap-2">
                            <Label htmlFor="note">
                                Alasan Penolakan / Revisi
                            </Label>
                            <Textarea
                                id="note"
                                placeholder="Contoh: Dokumen scan tidak terbaca, mohon upload ulang..."
                                className="min-h-[120px]"
                                value={rejectNote}
                                onChange={(e) => setRejectNote(e.target.value)}
                                required
                                autoFocus
                            />
                        </div>
                        <DialogFooter>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => setIsRejectOpen(false)}
                            >
                                Batal
                            </Button>
                            <Button
                                type="submit"
                                variant="destructive"
                                disabled={isSubmitting || !rejectNote}
                            >
                                {isSubmitting ? (
                                    <>
                                        <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                        Mengirim...
                                    </>
                                ) : (
                                    "Kirim Penolakan"
                                )}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}