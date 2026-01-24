import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import SeminarStepper from '@/components/seminar-stepper';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { route } from 'ziggy-js';
import { 
    Clock, 
    CheckCircle2, 
    Banknote, 
    ArrowRight,
    FileText,
    Calendar 
} from 'lucide-react';

export default function Step5Pencairan({ seminar }) {
    
    // Tentukan status tampilan berdasarkan data DB
    const isCair = ['dana_dicairkan', 'selesai'].includes(seminar.status_progress);
    
    // Ambil data pembayaran dari metadata JSON (jika ada)
    const infoBayar = seminar.kewajiban_penelitian?.pembayaran || {};

    return (
        <AppLayout breadcrumbs={[
            { title: 'Beranda', href: '/dosen/home' },
            { title: 'Registrasi Seminar', href: '/dosen/seminar' },
            { title: 'Tahap 5', href: '#' }
        ]}>
            <Head title="Pencairan Dana" />

            <div className="w-full max-w-6xl mx-auto px-6 py-6 pb-24">
                
                <div className="text-center mb-8">
                    <h1 className="text-2xl font-bold">Pengajuan Dana Registrasi Seminar</h1>
                    <p className="text-gray-500">Tahap 5: Proses Pencairan Dana</p>
                </div>

                {/* Stepper (Step 5 Active) */}
                <SeminarStepper currentStep={5} />

                <div className="mt-12 flex flex-col items-center justify-center space-y-8 text-center max-w-3xl mx-auto">
                    
                    {/* === KONDISI 1: MENUNGGU PEMBAYARAN === */}
                    {!isCair && (
                        <div className="space-y-6 animate-in fade-in slide-in-from-bottom-4">
                            <div className="relative flex items-center justify-center">
                                <div className="w-24 h-24 bg-orange-100 rounded-full flex items-center justify-center mb-4">
                                    <Clock className="w-12 h-12 text-orange-600 animate-pulse" />
                                </div>
                            </div>
                            
                            <div className="space-y-2">
                                <h2 className="text-2xl font-bold text-gray-900">
                                    Menunggu Pencairan Dana
                                </h2>
                                <p className="text-muted-foreground text-lg">
                                    Pengajuan Anda telah disetujui LPPM. Saat ini Bagian Keuangan sedang memproses transfer dana ke rekening Anda.
                                </p>
                            </div>

                            <Card className="bg-orange-50/50 border-orange-200">
                                <CardContent className="p-4 flex items-center gap-3 text-sm text-orange-800 text-left">
                                    <FileText className="w-5 h-5 shrink-0" />
                                    <span>
                                        Surat Permohonan Dana dari LPPM telah diteruskan ke Keuangan. Anda tidak perlu melakukan tindakan apa pun saat ini.
                                    </span>
                                </CardContent>
                            </Card>

                            <Link href={route('dosen.seminar.index')}>
                                <Button variant="outline" className="mt-4">Kembali ke Dashboard</Button>
                            </Link>
                        </div>
                    )}

                    {/* === KONDISI 2: DANA SUDAH CAIR === */}
                    {isCair && (
                        <div className="space-y-6 w-full animate-in fade-in slide-in-from-bottom-4">
                            <div className="relative flex items-center justify-center">
                                <div className="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mb-4 border-4 border-green-50">
                                    <CheckCircle2 className="w-12 h-12 text-green-600" />
                                </div>
                            </div>

                            <div className="space-y-2">
                                <h2 className="text-2xl font-bold text-gray-900">
                                    Dana Telah Dicairkan!
                                </h2>
                                <p className="text-muted-foreground text-lg">
                                    Bagian Keuangan telah mentransfer dana seminar. Silakan cek rekening Anda.
                                </p>
                            </div>

                            {/* Detail Pembayaran Card */}
                            <Card className="border-green-200 shadow-sm overflow-hidden">
                                <div className="bg-green-50 border-b border-green-100 p-4">
                                    <h3 className="font-semibold text-green-900 flex items-center justify-center gap-2">
                                        <Banknote className="w-5 h-5" />
                                        Rincian Transfer
                                    </h3>
                                </div>
                                <CardContent className="p-6 grid gap-4 md:grid-cols-2 text-left">
                                    <div>
                                        <label className="text-xs text-gray-500 uppercase font-semibold">Nominal Cair</label>
                                        <p className="text-2xl font-bold text-gray-900">
                                            Rp {new Intl.NumberFormat('id-ID').format(infoBayar.jumlah_cair || 0)}
                                        </p>
                                    </div>
                                    <div>
                                        <label className="text-xs text-gray-500 uppercase font-semibold">Tanggal Transfer</label>
                                        <div className="flex items-center gap-2 mt-1">
                                            <Calendar className="w-4 h-4 text-gray-400" />
                                            <span className="text-gray-900 font-medium">
                                                {infoBayar.tanggal_cair ? new Date(infoBayar.tanggal_cair).toLocaleDateString('id-ID', { dateStyle: 'long' }) : '-'}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    {infoBayar.file_bukti && (
                                        <div className="md:col-span-2 pt-2 border-t mt-2">
                                            <a href={`/storage/${infoBayar.file_bukti}`} target="_blank" rel="noreferrer">
                                                <Button variant="outline" className="w-full border-dashed border-green-300 text-green-700 hover:bg-green-50">
                                                    Lihat Bukti Transfer
                                                </Button>
                                            </a>
                                        </div>
                                    )}
                                </CardContent>
                            </Card>

                            <div className="pt-6">
                                <Link href={route('dosen.seminar.step6', seminar.id)}>
                                    <Button className="bg-black text-white hover:bg-gray-800 px-8 py-6 text-lg shadow-lg gap-3">
                                        Lanjut ke Tahap Terakhir
                                        <ArrowRight className="w-5 h-5" />
                                    </Button>
                                </Link>
                            </div>
                        </div>
                    )}

                </div>
            </div>
        </AppLayout>
    );
}