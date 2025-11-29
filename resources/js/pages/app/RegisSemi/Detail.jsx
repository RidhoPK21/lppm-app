import React, { useState } from "react";
import AppLayout from "@/layouts/app-layout";
import { Head, router } from "@inertiajs/react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Card, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import {
    ArrowLeft,
    FileText,
    CheckCircle,
    XCircle,
    Users,
    ClipboardList,
    ExternalLink,
} from "lucide-react";
import { route } from "ziggy-js";

// --- KOMPONEN HELPER UI ---
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

// --- MODAL: TOLAK PENGAJUAN ---
const CommentModal = ({ isOpen, onClose, onSubmit, isSubmitting }) => {
    const [comment, setComment] = useState("");
    if (!isOpen) return null;

    return (
        <div
            className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
            onClick={onClose}
        >
            <div
                className="bg-white rounded-lg shadow-xl w-full max-w-md"
                onClick={(e) => e.stopPropagation()}
            >
                <div className="p-4 border-b">
                    <h3 className="text-lg font-semibold text-red-600">
                        Tolak Pengajuan
                    </h3>
                </div>
                <div className="p-4 space-y-3">
                    <p className="text-sm text-gray-600">
                        Berikan alasan penolakan atau revisi:
                    </p>
                    <Textarea
                        value={comment}
                        onChange={(e) => setComment(e.target.value)}
                        placeholder="Contoh: Dokumen Scan tidak terbaca..."
                        className="min-h-[100px]"
                    />
                </div>
                <div className="flex justify-end gap-3 p-4 border-t bg-gray-50 rounded-b-lg">
                    <Button
                        variant="outline"
                        onClick={onClose}
                        disabled={isSubmitting}
                    >
                        Batal
                    </Button>
                    <Button
                        className="bg-red-600 hover:bg-red-700 text-white"
                        onClick={() => onSubmit(comment)}
                        disabled={!comment.trim() || isSubmitting}
                    >
                        {isSubmitting ? "Memproses..." : "Kirim Penolakan"}
                    </Button>
                </div>
            </div>
        </div>
    );
};

// --- MODAL: SETUJUI PENGAJUAN ---
const ApproveModal = ({ isOpen, onClose, onSubmit, isSubmitting }) => {
    const [amount, setAmount] = useState("");
    if (!isOpen) return null;

    return (
        <div
            className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
            onClick={onClose}
        >
            <div
                className="bg-white rounded-lg shadow-xl w-full max-w-md"
                onClick={(e) => e.stopPropagation()}
            >
                <div className="p-4 border-b">
                    <h3 className="text-lg font-semibold text-green-700">
                        Setujui Pengajuan
                    </h3>
                </div>
                <div className="p-4 space-y-3">
                    <p className="text-sm text-gray-600">
                        Masukkan nominal penghargaan yang disetujui (Rp):
                    </p>
                    <Input
                        type="number"
                        value={amount}
                        onChange={(e) => setAmount(e.target.value)}
                        placeholder="Contoh: 5000000"
                        className="font-mono"
                    />
                </div>
                <div className="flex justify-end gap-3 p-4 border-t bg-gray-50 rounded-b-lg">
                    <Button
                        variant="outline"
                        onClick={onClose}
                        disabled={isSubmitting}
                    >
                        Batal
                    </Button>
                    <Button
                        className="bg-green-600 hover:bg-green-700 text-white"
                        onClick={() => onSubmit(amount)}
                        disabled={!amount || isSubmitting}
                    >
                        {isSubmitting ? "Memproses..." : "Setujui & Kirim"}
                    </Button>
                </div>
            </div>
        </div>
    );
};

// --- HALAMAN UTAMA ---
export default function DetailRegisSemi({ book }) {
    const [isCommentOpen, setIsCommentOpen] = useState(false);
    const [isApproveOpen, setIsApproveOpen] = useState(false);
    const [isSubmitting, setIsSubmitting] = useState(false);

    // Ambil data link dokumen (aman dari error)
    const links = Array.isArray(book.drive_link) ? book.drive_link : [];

    // Status visual
    const statusColor =
        {
            SUBMITTED: "bg-yellow-100 text-yellow-800",
            VERIFIED_STAFF: "bg-blue-100 text-blue-800",
            APPROVED_CHIEF: "bg-green-100 text-green-800",
            REJECTED: "bg-red-100 text-red-800",
            PAID: "bg-purple-100 text-purple-800",
        }[book.status] || "bg-gray-100 text-gray-800";

    // --- HANDLER AKSI ---
    const handleAction = (action) => {
        switch (action) {
            case "open_docs":
                if (links.length > 0 && links[0])
                    window.open(links[0], "_blank");
                else alert("Link dokumen tidak ditemukan.");
                break;
            case "approve":
                setIsApproveOpen(true);
                break;
            case "reject":
                setIsCommentOpen(true);
                break;
            case "invite":
                router.visit(route("app.regis-semi.invite", book.id));
                break;
            case "result":
                router.visit(route("app.regis-semi.result", book.id));
                break;
            default:
                break;
        }
    };

    const submitApprove = (amount) => {
        setIsSubmitting(true);
        router.post(
            route("app.regis-semi.approve", book.id),
            { amount },
            {
                onFinish: () => {
                    setIsSubmitting(false);
                    setIsApproveOpen(false);
                },
            }
        );
    };

    const submitReject = (note) => {
        setIsSubmitting(true);
        router.post(
            route("app.regis-semi.reject", book.id),
            { note },
            {
                onFinish: () => {
                    setIsSubmitting(false);
                    setIsCommentOpen(false);
                },
            }
        );
    };

    return (
        <AppLayout>
            <Head title={`Verifikasi - ${book.title}`} />

            <div className="max-w-7xl mx-auto p-4 md:px-8 space-y-6">
                {/* HEADER */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Button
                            variant="outline"
                            size="icon"
                            onClick={() =>
                                router.visit(route("app.regis-semi.index"))
                            }
                        >
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                        <div>
                            <h1 className="text-2xl font-bold">
                                Verifikasi Buku
                            </h1>
                            <p className="text-sm text-gray-500">
                                ID: #{book.id} • Dosen: {book.dosen}
                            </p>
                        </div>
                    </div>
                    <Badge className={`${statusColor} hover:${statusColor}`}>
                        {book.status_label || book.status}
                    </Badge>
                </div>

                {/* KONTEN DETAIL */}
                <Card className="shadow-sm">
                    <CardContent className="p-6 space-y-4">
                        <SideBySideFormField label="Judul Buku">
                            <Input
                                value={book.title}
                                readOnly
                                className="bg-gray-50"
                            />
                        </SideBySideFormField>

                        <SideBySideFormField label="ISBN">
                            <Input
                                value={book.isbn}
                                readOnly
                                className="bg-gray-50 font-mono"
                            />
                        </SideBySideFormField>

                        <SideBySideFormField label="Penerbit">
                            <Input
                                value={`${book.publisher} (${book.publisher_level})`}
                                readOnly
                                className="bg-gray-50"
                            />
                        </SideBySideFormField>

                        <SideBySideFormField label="Tahun / Halaman">
                            <Input
                                value={`${book.year} / ${book.total_pages} Hal`}
                                readOnly
                                className="bg-gray-50"
                            />
                        </SideBySideFormField>

                        {/* LIST DOKUMEN (5 Item) */}
                        <div className="mt-8 pt-4 border-t">
                            <h3 className="font-semibold mb-4 text-gray-900">
                                Dokumen Pendukung
                            </h3>
                            {[
                                "Berita Acara Perpustakaan",
                                "Hasil Scan Buku",
                                "Review Penerbit",
                                "Surat Pernyataan",
                                "Dokumen Lainnya",
                            ].map((label, idx) => (
                                <StackedFormField
                                    key={idx}
                                    label={`${idx + 1}. ${label}`}
                                >
                                    <div className="flex gap-2">
                                        <Input
                                            value={links[idx] || "-"}
                                            readOnly
                                            className="bg-gray-50 text-blue-600 truncate"
                                        />
                                        {links[idx] && (
                                            <Button
                                                variant="outline"
                                                size="icon"
                                                onClick={() =>
                                                    window.open(
                                                        links[idx],
                                                        "_blank"
                                                    )
                                                }
                                                title="Buka Link"
                                            >
                                                <ExternalLink className="h-4 w-4" />
                                            </Button>
                                        )}
                                    </div>
                                </StackedFormField>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                {/* 5 TOMBOL AKSI UTAMA */}
                <div className="space-y-4 pt-4">
                    {/* Baris 1: Aksi Utama */}
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <Button
                            onClick={() => handleAction("open_docs")}
                            className="bg-blue-600 hover:bg-blue-700 text-white h-12"
                        >
                            <FileText className="mr-2 h-5 w-5" /> Buka Folder
                            Dokumen
                        </Button>

                        <Button
                            onClick={() => handleAction("approve")}
                            className="bg-green-600 hover:bg-green-700 text-white h-12"
                            disabled={
                                book.status === "APPROVED_CHIEF" ||
                                book.status === "PAID"
                            }
                        >
                            <CheckCircle className="mr-2 h-5 w-5" /> Setujui
                        </Button>

                        <Button
                            onClick={() => handleAction("reject")}
                            className="bg-red-600 hover:bg-red-700 text-white h-12"
                            disabled={book.status === "REJECTED"}
                        >
                            <XCircle className="mr-2 h-5 w-5" /> Tolak
                        </Button>
                    </div>

                    {/* Baris 2: Fitur Reviewer */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <Button
                            variant="outline"
                            onClick={() => handleAction("invite")}
                            className="h-12 border-gray-400"
                        >
                            <Users className="mr-2 h-5 w-5" /> Minta Penilaian
                            Dosen Lain
                        </Button>

                        <Button
                            variant="outline"
                            onClick={() => handleAction("result")}
                            className="h-12 border-gray-400"
                        >
                            <ClipboardList className="mr-2 h-5 w-5" /> Lihat
                            Hasil Penilaian
                        </Button>
                    </div>
                </div>
            </div>

            {/* MODALS */}
            <ApproveModal
                isOpen={isApproveOpen}
                onClose={() => setIsApproveOpen(false)}
                onSubmit={submitApprove}
                isSubmitting={isSubmitting}
            />
            <CommentModal
                isOpen={isCommentOpen}
                onClose={() => setIsCommentOpen(false)}
                onSubmit={submitReject}
                isSubmitting={isSubmitting}
            />
        </AppLayout>
    );
}
