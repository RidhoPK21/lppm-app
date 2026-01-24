import React, { useState, useEffect } from "react";
import { Head, Link, usePage, router } from "@inertiajs/react";
import AppLayout from "@/layouts/app-layout";
import { Card, CardContent } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge"; 
import { Separator } from "@/components/ui/separator"; 
import { Input } from "@/components/ui/input";
import {
    CircleArrowUp,
    Plus,
    FileText,
    Trash2,
    Filter,
    Calendar,
    ArrowRight,
    CheckCircle2
} from "lucide-react";
import { route } from "ziggy-js";

// ==============================================================================
// 1. HELPER: MAPPING STATUS KE STEPPER (FULL UPDATE)
// ==============================================================================
const getStepperInfo = (status) => {
    switch (status) {
        // Tahap 1: Submit Data (16%)
        case 'draft':
            return { label: 'Tahap 1: Submit Data', percent: 16, color: 'bg-slate-500', badgeVariant: 'secondary' };

        // Tahap 2: Submit Paper (33%)
        case 'menunggu_reviewer':
            return { label: 'Tahap 2: Submit Paper', percent: 33, color: 'bg-blue-500', badgeVariant: 'default' };

        // Tahap 3: Finalisasi Paper / Review (50%)
        case 'sedang_direview':
        case 'menunggu_perbaikan':
        case 'revisi_masuk':
        case 'disetujui':
        case 'lolos_review': 
            return { label: 'Tahap 3: Lolos Review (Lanjut Artefak)', percent: 50, color: 'bg-green-600', badgeVariant: 'success' };

        // Tahap 4: Submit Artefak (66%)
        case 'menunggu_persetujuan_kprodi':
        case 'menunggu_konfirmasi_lppm':
            return { label: 'Tahap 4: Verifikasi Dokumen', percent: 66, color: 'bg-orange-500', badgeVariant: 'warning' };

        // Tahap 5: Pencairan Dana (83%)
        case 'menunggu_pembayaran':
        case 'dana_dicairkan': // 🔥 SUDAH CAIR
            return { label: 'Tahap 5: Pencairan Dana', percent: 83, color: 'bg-emerald-500', badgeVariant: 'success' };

        // Tahap 6: Menunggu Surat Tugas (90%)
        case 'menunggu_surat_tugas':
             return { label: 'Tahap 6: Menunggu Surat Tugas (HRD)', percent: 90, color: 'bg-purple-500', badgeVariant: 'default' };

        // Tahap 6: Selesai (100%)
        case 'selesai': // 🔥 STATUS FINAL
            return { label: 'Tahap 6: Selesai', percent: 100, color: 'bg-green-700', badgeVariant: 'success' };

        default:
            return { label: 'Status Tidak Diketahui', percent: 0, color: 'bg-gray-300', badgeVariant: 'outline' };
    }
};

export default function SeminarPage({ seminars, filters }) {
    const { auth } = usePage().props;
    const userName = auth?.user?.name || "Dosen";

    // STATE FILTER
    const [search, setSearch] = useState(filters.search || "");
    const [statusFilter, setStatusFilter] = useState(filters.status || "");
    const [isFilterOpen, setIsFilterOpen] = useState(false);

    // DEBOUNCE SEARCH
    useEffect(() => {
        const delayDebounceFn = setTimeout(() => {
            applyFilter();
        }, 300);
        return () => clearTimeout(delayDebounceFn);
    }, [search]);

    const handleDelete = (seminarId, title) => {
        if (!confirm(`Hapus pengajuan "${title}"? Data tidak dapat dikembalikan.`)) return;
        router.delete(route("dosen.seminar.destroy", seminarId), { preserveScroll: true });
    };

    // 2. LOGIKA ROUTING PINTAR (FULL UPDATE)
    const getDetailRoute = (item) => {
        const status = item.status_progress;

        // Step 1
        if (status === 'draft') return route('dosen.seminar.step1', item.id);
        
        // Step 2 & 3
        if (['menunggu_reviewer', 'sedang_direview', 'menunggu_perbaikan', 'revisi_masuk'].includes(status)) {
            return route('dosen.seminar.step3', item.id);
        }

        // Step 4
        if (['disetujui', 'lolos_review', 'menunggu_persetujuan_kprodi', 'menunggu_konfirmasi_lppm'].includes(status)) {
            return route('dosen.seminar.step4', item.id); 
        }

        // Step 5
        if (['menunggu_pembayaran', 'dana_dicairkan'].includes(status)) {
            return route('dosen.seminar.step5', item.id);
        }

        // Step 6 (Mode / Surat Tugas / Selesai)
        if (['menunggu_surat_tugas', 'selesai'].includes(status)) {
            // Controller akan otomatis redirect ke Finish Page jika status == 'selesai'
            return route('dosen.seminar.step6', item.id);
        }
        
        // Default
        return route('dosen.seminar.step1', item.id);
    };

    const possibleStatuses = [
        { key: "", label: "Semua Status" },
        { key: "draft", label: "Draft" },
        { key: "menunggu_reviewer", label: "Menunggu Reviewer" },
        { key: "sedang_direview", label: "Sedang Direview" },
        { key: "menunggu_perbaikan", label: "Perlu Revisi" },
        { key: "lolos_review", label: "Lolos Review" },
        { key: "dana_dicairkan", label: "Dana Cair" },
        { key: "selesai", label: "Selesai" },
    ];

    return (
        <AppLayout pageName="Seminar">
            <Head title="Daftar Seminar" />

            <div className="w-full max-w-6xl mx-auto px-6 py-10 space-y-8 min-h-screen">
                
                {/* Header */}
                <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-gray-900">Daftar Seminar Saya</h1>
                        <p className="text-sm text-gray-500 mt-1">
                            Halo {userName}, pantau progres pengajuan dana seminar Anda di sini.
                        </p>
                    </div>
                </div>

                <Separator />

                {/* Search & Filter */}
                <div className="flex flex-col md:flex-row gap-3">
                    <div className="relative w-full md:w-1/3">
                        <Input
                            placeholder="Cari judul seminar..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            className="bg-white pl-10"
                        />
                        <FileText className="absolute left-3 top-2.5 h-4 w-4 text-gray-400" />
                    </div>

                    <div className="relative">
                        <Button
                            onClick={() => setIsFilterOpen(!isFilterOpen)}
                            variant="outline"
                            className="w-full md:w-auto gap-2 border-gray-300"
                        >
                            <Filter className="w-4 h-4" />
                            {possibleStatuses.find((s) => s.key === statusFilter)?.label || "Filter Status"}
                        </Button>

                        {isFilterOpen && (
                            <div className="absolute left-0 mt-2 w-56 bg-white border rounded-md shadow-lg z-20 p-1">
                                {possibleStatuses.map((status) => (
                                    <div
                                        key={status.key}
                                        className={`px-3 py-2 text-sm rounded cursor-pointer hover:bg-gray-100 ${
                                            statusFilter === status.key ? "bg-gray-100 font-medium" : ""
                                        }`}
                                        onClick={() => {
                                            setStatusFilter(status.key);
                                            setIsFilterOpen(false);
                                            router.get(
                                                route("dosen.seminar.index"),
                                                { search: search, status: status.key },
                                                { preserveState: true, replace: true }
                                            );
                                        }}
                                    >
                                        {status.label}
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                </div>

                {/* List Cards */}
                <div className="grid gap-6 pb-24">
                    {seminars && seminars.length > 0 ? (
                        seminars.map((item) => {
                            const stepper = getStepperInfo(item.status_progress);
                            const isFinished = item.status_progress === 'selesai';

                            return (
                                <Card key={item.id} className={`shadow-sm hover:shadow-md transition-all border-l-4 ${isFinished ? 'border-l-green-600' : 'border-l-gray-200'} overflow-hidden`}>
                                    <CardContent className="p-0 flex flex-col md:flex-row">
                                        
                                        {/* Kiri */}
                                        <div className="flex-1 p-6 space-y-4">
                                            <div className="flex items-start gap-4">
                                                <div className={`shrink-0 h-12 w-12 rounded-full flex items-center justify-center text-white ${isFinished ? 'bg-green-600' : 'bg-black'}`}>
                                                    {isFinished ? <CheckCircle2 className="h-6 w-6" /> : <CircleArrowUp className="h-6 w-6" />}
                                                </div>
                                                <div className="min-w-0 flex-1">
                                                    <h3 className="font-semibold text-lg leading-tight line-clamp-2" title={item.judul_makalah}>
                                                        {item.judul_makalah}
                                                    </h3>
                                                    <p className="text-sm text-gray-500 mt-1 flex items-center gap-2">
                                                        <FileText className="h-3 w-3" />
                                                        {item.nama_forum || "Nama Forum Belum Diisi"}
                                                    </p>
                                                </div>
                                            </div>

                                            <div className="flex items-center gap-3 text-sm text-gray-500 pl-16">
                                                <Badge variant="outline" className={`capitalize px-2 py-0.5 ${stepper.color} text-white border-none`}>
                                                    {item.status_progress.replace(/_/g, ' ')}
                                                </Badge>
                                                <span className="flex items-center gap-1">
                                                    <Calendar className="h-3 w-3" />
                                                    {new Date(item.created_at).toLocaleDateString('id-ID')}
                                                </span>
                                            </div>
                                        </div>

                                        {/* Kanan */}
                                        <div className="w-full md:w-5/12 bg-slate-50 border-t md:border-t-0 md:border-l p-6 flex flex-col justify-center gap-4">
                                            
                                            {/* Bar */}
                                            <div>
                                                <div className="flex justify-between items-center text-xs font-semibold text-slate-700 mb-2">
                                                    <span>{stepper.label}</span>
                                                    <span>{stepper.percent}%</span>
                                                </div>
                                                <div className="w-full bg-gray-200 rounded-full h-2.5">
                                                    <div 
                                                        className={`h-2.5 rounded-full transition-all duration-700 ${stepper.color}`} 
                                                        style={{ width: `${stepper.percent}%` }}
                                                    ></div>
                                                </div>
                                            </div>

                                            {/* Actions */}
                                            <div className="flex items-center gap-2 mt-2">
                                                {item.status_progress === "draft" && (
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        className="text-red-500 hover:bg-red-50 hover:text-red-700"
                                                        onClick={() => handleDelete(item.id, item.judul_makalah)}
                                                    >
                                                        <Trash2 className="h-4 w-4" />
                                                    </Button>
                                                )}

                                                <Link href={getDetailRoute(item)} className="w-full">
                                                    <Button variant="outline" className="w-full gap-2 border-blue-200 text-blue-700 hover:bg-blue-50 hover:text-blue-800 transition-all">
                                                        {item.status_progress === 'draft' ? 'Lengkapi Data' : 'Lihat Detail'}
                                                        <ArrowRight className="h-4 w-4" />
                                                    </Button>
                                                </Link>
                                            </div>
                                        </div>

                                    </CardContent>
                                </Card>
                            );
                        })
                    ) : (
                        <div className="text-center py-16 bg-gray-50 rounded-xl border-2 border-dashed border-gray-200 flex flex-col items-center justify-center gap-4">
                            <div className="h-16 w-16 bg-gray-100 rounded-full flex items-center justify-center">
                                <FileText className="h-8 w-8 text-gray-400" />
                            </div>
                            <div>
                                <h3 className="text-lg font-medium text-gray-900">Tidak ada hasil ditemukan</h3>
                                <p className="text-sm text-gray-500">Buat pengajuan baru untuk memulai.</p>
                            </div>
                        </div>
                    )}
                </div>

                {/* FAB */}
                <div className="fixed bottom-10 right-10 z-50">
                    <Button
                        asChild
                        size="icon"
                        className="h-14 w-14 rounded-full shadow-xl bg-black text-white hover:bg-gray-800 hover:scale-105 transition-transform flex items-center justify-center"
                    >
                        <Link href={route("dosen.seminar.create")}>
                            <Plus className="h-7 w-7" strokeWidth={2.5} />
                        </Link>
                    </Button>
                </div>
            </div>
        </AppLayout>
    );
}