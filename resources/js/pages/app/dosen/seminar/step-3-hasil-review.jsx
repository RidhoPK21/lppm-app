import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import SeminarStepper from '@/components/seminar-stepper';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Download, Edit3, CheckCircle2, Clock, FileText } from 'lucide-react'; 
import { route } from 'ziggy-js';

export default function Step3HasilReview({ seminar, reviews }) {
    
    // Helper Warna Status
    const getStatusColor = (status) => {
        switch(status) {
            case 'proses': return 'bg-blue-100 text-blue-700'; // Menunggu Review
            case 'menunggu_perbaikan': return 'bg-yellow-100 text-yellow-700'; // Ada Catatan
            case 'selesai': return 'bg-green-100 text-green-700'; // Disetujui
            default: return 'bg-gray-100 text-gray-700';
        }
    };

    // Helper Label Status
    const getStatusLabel = (status) => {
        switch(status) {
            case 'proses': return 'Menunggu Review';
            case 'menunggu_perbaikan': return 'Perlu Perbaikan/Revisi';
            case 'selesai': return 'Disetujui';
            default: return status;
        }
    };

    // Cek apakah SEMUA reviewer sudah menyetujui
    const allApproved = reviews.length > 0 && reviews.every(r => r.status === 'selesai');

    return (
        <AppLayout breadcrumbs={[
            { title: 'Beranda', href: '/dosen/home' },
            { title: 'Registrasi Seminar', href: route('dosen.seminar.index') },
            { title: 'Hasil Review', href: '#' }
        ]}>
            <Head title="Hasil Review" />

            <div className="relative w-full max-w-6xl mx-auto px-6 py-6 pb-32">
                
                <div className="text-center mb-8">
                    <h1 className="text-2xl font-bold">Pengajuan Dana Registrasi Seminar</h1>
                    <p className="text-gray-500 mt-2">Pantau status review dan lakukan perbaikan jika diminta.</p>
                </div>

                <SeminarStepper currentStep={3} />

                <div className="mt-10 space-y-6">
                    <div className="flex justify-between items-center">
                        <h2 className="text-xl font-bold">Daftar Reviewer</h2>
                        <span className="text-sm text-gray-500">Judul: {seminar.judul_makalah}</span>
                    </div>

                    <div className="space-y-4">
                        {reviews.map((review, index) => (
                            <Card key={review.id} className="border shadow-sm hover:shadow-md transition-shadow">
                                <CardContent className="p-6 flex flex-col md:flex-row items-center justify-between gap-4">
                                    
                                    {/* Info Kiri */}
                                    <div className="flex items-center gap-4 md:w-1/3">
                                        <div className="shrink-0 h-12 w-12 flex items-center justify-center rounded-full bg-black text-white font-bold">
                                            {index + 1}
                                        </div>
                                        <div>
                                            <h3 className="font-bold text-base">Reviewer {index + 1}</h3>
                                            <p className="text-sm text-gray-500">
                                                {review.reviewer?.user?.name || 'Nama Reviewer'}
                                            </p>
                                        </div>
                                    </div>

                                    {/* Status & Actions Kanan */}
                                    <div className="flex flex-col items-end gap-3 w-full md:w-auto">
                                        
                                        {/* Status Badge */}
                                        <div className="flex items-center gap-2">
                                            <span className="text-xs text-muted-foreground">Status :</span>
                                            <span className={`text-xs font-medium px-2 py-1 rounded ${getStatusColor(review.status)}`}>
                                                {getStatusLabel(review.status)}
                                            </span>
                                        </div>
                                        
                                        <div className="flex gap-3">
                                            
                                            {/* TOMBOL 1: DOWNLOAD HASIL (FORM 1) */}
                                            {/* Hanya muncul jika ada catatan review (Status: Revisi atau Selesai) */}
                                            {(review.status === 'menunggu_perbaikan' || review.status === 'selesai' || review.catatan_review) && (
                                                <a href={route('dosen.seminar.download_form1', review.id)} target="_blank" rel="noreferrer">
                                                    <Button variant="outline" className="h-9 text-xs font-medium border-gray-300 gap-2">
                                                        <FileText className="w-3.5 h-3.5" />
                                                        Download Hasil Review
                                                    </Button>
                                                </a>
                                            )}

                                            {/* 🔥 TOMBOL 2: DOWNLOAD FORM 2 (PERSETUJUAN - HANYA jika status 'selesai') 🔥 */}
                                            {review.status === 'selesai' && (
                                                <a href={route('dosen.seminar.download_form2', review.id)} target="_blank" rel="noreferrer">
                                                    <Button variant="default" className="h-9 text-xs font-medium bg-green-600 hover:bg-green-700 gap-2">
                                                        <Download className="w-3.5 h-3.5" />
                                                        Download Perbaikan
                                                    </Button>
                                                </a>
                                            )}

                                            {/* TOMBOL 3: AKSI UTAMA (PERBAIKAN / STATUS) */}
                                            {review.status === 'menunggu_perbaikan' ? (
                                                <Link href={route('dosen.seminar.perbaikan', seminar.id)}>
                                                    <Button className="h-9 text-xs font-medium bg-black text-white hover:bg-gray-800 gap-2">
                                                        <Edit3 className="w-3.5 h-3.5" />
                                                        Lakukan Perbaikan
                                                    </Button>
                                                </Link>
                                            ) : review.status === 'selesai' ? (
                                                <Button disabled variant="outline" className="h-9 text-xs font-medium border-green-200 text-green-700 bg-green-50">
                                                    <CheckCircle2 className="w-3.5 h-3.5 mr-2" />
                                                    Disetujui
                                                </Button>
                                            ) : (
                                                <Button disabled variant="ghost" className="h-9 text-xs font-medium text-gray-400 cursor-not-allowed">
                                                    <Clock className="w-3.5 h-3.5 mr-2" />
                                                    Sedang Ditinjau
                                                </Button>
                                            )}

                                        </div>

                                        <div className="text-[10px] text-gray-400 mt-1 w-full text-right flex justify-between md:justify-end gap-4">
                                            <span>Revisi ke-{review.revisi_ke || 0}</span>
                                            <span>{new Date(review.updated_at).toLocaleDateString()}</span>
                                        </div>
                                    </div>

                                </CardContent>
                            </Card>
                        ))}
                    </div>

                    {/* Tombol Tahap Selanjutnya (Hanya jika SEMUA selesai) */}
                    {allApproved && (
                        <div className="flex justify-end pt-8">
                            <Link href={route('dosen.seminar.step4', seminar.id)}>
                                <Button className="bg-black text-white hover:bg-gray-800 px-8 h-10 rounded-md">
                                    Tahap Selanjutnya (Submit Artefak)
                                </Button>
                            </Link>
                        </div>
                    )}

                </div>
            </div>
        </AppLayout>
    );
}