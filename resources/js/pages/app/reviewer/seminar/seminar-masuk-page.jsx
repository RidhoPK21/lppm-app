import React, { useState } from 'react';
import AppLayout from "@/layouts/app-layout";
import { Head, Link, router } from "@inertiajs/react";
import Swal from "sweetalert2";
import { Download, CheckCircle2, XCircle, FileText, Clock } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { route } from "ziggy-js";

export default function SeminarMasukPage({ reviews = [], pageName }) {
    
    // --- 1. MAPPING DATA ---
    const [localPapers, setLocalPapers] = useState(reviews.map(item => {
        return {
            id: item.id,
            judul: item.seminar?.judul_makalah || 'Judul Tidak Tersedia',
            pengaju: item.seminar?.dosen?.user?.name || 'Dosen Tidak Diketahui',
            tanggal: new Date(item.created_at).toLocaleDateString('id-ID', {
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
            }),
            // LOGIKA STATUS PENTING DISINI
            // Asumsi status dari database: 'menunggu', 'proses', 'menunggu_perbaikan', 'selesai', 'ditolak'
            status_db: item.status, 
            
            // Hitungan Revisi (Jika belum ada kolom revisi_ke di DB, kita default 0)
            revisi_ke: item.revisi_ke || 0, 

            // File Paper (Ambil yang terbaru jika ada revisi)
            file_url: item.seminar?.file_url || '#'
        };
    }));

    // --- 2. ACTION: TERIMA PENUGASAN ---
    // Status berubah jadi 'proses' (Muncul 3 tombol)
    const handleAcceptAssignment = (id) => {
        Swal.fire({
            title: "Terima Penugasan?",
            text: "Anda akan mulai mereview paper ini.",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Ya, Terima",
            confirmButtonColor: "#000000"
        }).then((result) => {
            if (result.isConfirmed) {
                router.patch(route('reviewer.seminar.update_status', id), { status: 'proses' }, {
                    onSuccess: () => updateLocalStatus(id, 'proses')
                });
            }
        });
    };

    // --- 3. ACTION: TOLAK PENUGASAN ---
    // Status berubah jadi 'ditolak' (Hanya bisa download)
    const handleRejectAssignment = (id) => {
        Swal.fire({
            title: "Tolak Penugasan?",
            text: "Paper akan ditandai sebagai ditolak.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Tolak",
            confirmButtonColor: "#ef4444"
        }).then((result) => {
            if (result.isConfirmed) {
                router.patch(route('reviewer.seminar.update_status', id), { status: 'ditolak' }, {
                    onSuccess: () => updateLocalStatus(id, 'ditolak')
                });
            }
        });
    };

    // --- 4. ACTION: KONFIRMASI (FINAL ACCEPT) ---
    // Status berubah jadi 'selesai'
    const handleFinalConfirm = (id) => {
        Swal.fire({
            title: "Konfirmasi Paper?",
            text: "Paper dianggap sudah pas dan layak. Proses review selesai.",
            icon: "success",
            showCancelButton: true,
            confirmButtonText: "Ya, Konfirmasi",
            confirmButtonColor: "#16a34a"
        }).then((result) => {
            if (result.isConfirmed) {
                router.patch(route('reviewer.seminar.update_status', id), { status: 'selesai' }, {
                    onSuccess: () => {
                        updateLocalStatus(id, 'selesai');
                        Swal.fire("Selesai", "Paper berhasil dikonfirmasi.", "success");
                    }
                });
            }
        });
    };

    // Helper untuk update state lokal tanpa refresh
    const updateLocalStatus = (id, newStatus) => {
        setLocalPapers(prev => prev.map(p => p.id === id ? { ...p, status_db: newStatus } : p));
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'Beranda', href: '/reviewer/home' }, { title: 'Seminar Masuk', href: '#' }]}>
            <Head title={pageName} />

            <div className="w-full max-w-6xl mx-auto px-6 py-8 space-y-8">
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">{pageName}</h1>
                        <p className="text-muted-foreground mt-1">Daftar antrian review paper seminar.</p>
                    </div>
                </div>

                <div className="space-y-4">
                    {localPapers.map((item) => (
                        <Card key={item.id} className="border shadow-sm hover:shadow-md transition-shadow">
                            <CardContent className="p-5 flex flex-col md:flex-row items-center justify-between gap-4">
                                
                                {/* --- KIRI: INFO PAPER --- */}
                                <div className="flex items-center gap-4 w-full md:w-2/3">
                                    {/* Icon Status */}
                                    <div className={`h-12 w-12 rounded-full flex items-center justify-center text-white shrink-0 
                                        ${item.status_db === 'selesai' ? 'bg-green-600' : 
                                          item.status_db === 'ditolak' ? 'bg-red-500' : 
                                          item.status_db === 'menunggu' ? 'bg-gray-800' : 'bg-blue-600'}`}>
                                        {item.status_db === 'selesai' ? <CheckCircle2 /> : 
                                         item.status_db === 'ditolak' ? <XCircle /> : 
                                         item.status_db === 'menunggu' ? <Clock /> : <FileText />}
                                    </div>

                                    <div className="min-w-0">
                                        <h3 className="font-bold text-base text-gray-900 truncate">{item.judul}</h3>
                                        <p className="text-sm text-gray-500">Pengaju: {item.pengaju}</p>
                                        
                                        {/* Badge Status & Revisi */}
                                        <div className="flex gap-2 mt-1">
                                            {/* Badge Status Utama */}
                                            <span className={`text-xs font-medium px-2 py-0.5 rounded border 
                                                ${item.status_db === 'menunggu' ? 'bg-gray-100 border-gray-200 text-gray-600' :
                                                  item.status_db === 'proses' ? 'bg-blue-50 border-blue-200 text-blue-600' :
                                                  item.status_db === 'menunggu_perbaikan' ? 'bg-yellow-50 border-yellow-200 text-yellow-600' :
                                                  item.status_db === 'selesai' ? 'bg-green-50 border-green-200 text-green-600' :
                                                  'bg-red-50 border-red-200 text-red-600'}`}>
                                                {item.status_db === 'menunggu' ? 'Menunggu Konfirmasi Anda' :
                                                 item.status_db === 'proses' ? 'Sedang Direview' :
                                                 item.status_db === 'menunggu_perbaikan' ? 'Menunggu Perbaikan Dosen' :
                                                 item.status_db === 'revisi_masuk' ? 'Revisi Baru Masuk' : // Opsional jika ada
                                                 item.status_db === 'selesai' ? 'Selesai' : 'Ditolak'}
                                            </span>

                                            {/* Badge Counter Revisi (Hanya muncul jika sudah proses) */}
                                            {(item.status_db !== 'menunggu' && item.status_db !== 'ditolak') && (
                                                <span className="text-xs font-medium px-2 py-0.5 rounded border bg-purple-50 border-purple-200 text-purple-600">
                                                    Revisi ke-{item.revisi_ke}
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                </div>

                                {/* --- KANAN: TOMBOL AKSI (LOGIKA UTAMA) --- */}
                                <div className="flex flex-col md:flex-row gap-2 w-full md:w-auto justify-end">
                                    
                                    {/* 1. TOMBOL DOWNLOAD */}
                                    <a href={route('reviewer.seminar.download', item.id)}>
                                        <Button variant="outline" className="h-9 text-xs gap-2 w-full md:w-auto">
                                            <Download className="w-3.5 h-3.5" />
                                            Download Paper
                                        </Button>
                                    </a>

                                    {/* 2. KONDISI: BARU MASUK (Menunggu Respon) */}
                                    {item.status_db === 'menunggu' && (
                                        <>
                                            <Button onClick={() => handleRejectAssignment(item.id)} variant="outline" className="h-9 text-xs border-red-200 text-red-600 hover:bg-red-50 gap-2 w-full md:w-auto">
                                                <XCircle className="w-3.5 h-3.5" />
                                                Tolak
                                            </Button>
                                            <Button onClick={() => handleAcceptAssignment(item.id)} className="h-9 text-xs bg-black text-white hover:bg-gray-800 gap-2 w-full md:w-auto">
                                                <CheckCircle2 className="w-3.5 h-3.5" />
                                                Terima Review
                                            </Button>
                                        </>
                                    )}

                                    {/* 3. KONDISI: PROSES REVIEW (Proses / Revisi Masuk / Menunggu Perbaikan) */}
                                    {/* User bilang: tombol reviewer TETAP SAMA meski menunggu perbaikan */}
                                    {['proses', 'menunggu_perbaikan', 'revisi_masuk'].includes(item.status_db) && (
                                        <>
                                            {/* Tombol ISI REVIEW */}
                                            {/* Mengarahkan ke halaman form catatan */}
                                            <Link href={route('reviewer.seminar.review', item.id)}>
                                                <Button variant="outline" className="h-9 text-xs border-blue-200 text-blue-700 hover:bg-blue-50 gap-2 w-full md:w-auto">
                                                    <FileText className="w-3.5 h-3.5" />
                                                    {item.status_db === 'menunggu_perbaikan' ? 'Lihat/Tambah Catatan' : 'Isi Review'}
                                                </Button>
                                            </Link>

                                            {/* Tombol KONFIRMASI (ACC) */}
                                            {/* Hanya bisa diklik jika statusnya bukan sedang menunggu perbaikan (opsional, sesuai request tombol tetap ada) */}
                                            <Button 
                                                onClick={() => handleFinalConfirm(item.id)} 
                                                className="h-9 text-xs bg-green-600 text-white hover:bg-green-700 gap-2 w-full md:w-auto"
                                            >
                                                <CheckCircle2 className="w-3.5 h-3.5" />
                                                Konfirmasi
                                            </Button>
                                        </>
                                    )}

                                    {/* 4. KONDISI: SELESAI */}
                                    {item.status_db === 'selesai' && (
                                        <Button disabled className="h-9 text-xs bg-gray-100 text-gray-500 border-none cursor-default w-full md:w-auto">
                                            Paper Telah Disetujui
                                        </Button>
                                    )}

                                     {/* 5. KONDISI: DITOLAK */}
                                     {item.status_db === 'ditolak' && (
                                        <Button disabled className="h-9 text-xs bg-red-50 text-red-500 border-none cursor-default w-full md:w-auto">
                                            Anda Menolak Paper Ini
                                        </Button>
                                    )}
                                </div>

                            </CardContent>
                        </Card>
                    ))}
                    
                    {localPapers.length === 0 && (
                        <p className="text-center text-gray-500 py-10">Belum ada paper masuk.</p>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}