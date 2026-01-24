import React, { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import SeminarStepper from '@/components/seminar-stepper';
import { Button } from '@/components/ui/button';
import { Download } from 'lucide-react'; // Ikon Check dihapus karena tidak dipakai lagi
import { route } from 'ziggy-js';

export default function Step6Onsite() {
    // State Simulasi: 'waiting' (Menunggu HRD) -> 'done' (Surat Terbit)
    const [status, setStatus] = useState('waiting'); 

    return (
        <AppLayout breadcrumbs={[
            { title: 'Beranda', href: '/dosen/dashboard' },
            { title: 'Registrasi Seminar', href: '/dosen/seminar' },
            { title: 'Tahap 6 (Onsite)', href: '#' }
        ]}>
            <Head title="Surat Izin Kerja" />

            <div className="w-full max-w-6xl mx-auto px-6 py-6 pb-24">
                
                <div className="text-center mb-8">
                    <h1 className="text-2xl font-bold">Pengajuan Dana Registrasi Seminar</h1>
                </div>

                {/* Stepper (Step 6 Active) */}
                <SeminarStepper currentStep={6} />

                <div className="mt-20 flex flex-col items-center justify-center space-y-8 text-center">
                    
                    {/* === STATUS 1: MENUNGGU HRD === */}
                    {status === 'waiting' && (
                        <>
                            <h2 className="text-2xl font-bold">
                                Mohon menunggu Surat Izin dari HRD
                            </h2>
                            
                            {/* Loading Hitam */}
                            <div className="relative flex items-center justify-center pt-6">
                                <div className="w-24 h-24 border-4 border-gray-200 border-t-black rounded-full animate-spin" />
                            </div>
                            
                            <p className="text-muted-foreground max-w-md mt-4">
                                Permintaan Anda sedang diproses. HRD akan mengunggah Surat Izin Kerja setelah verifikasi selesai.
                            </p>

                            {/* TOMBOL SIMULASI DEV */}
                            <div className="fixed bottom-10 right-10 z-50">
                                <Button onClick={() => setStatus('done')} size="sm" variant="outline" className="opacity-50 hover:opacity-100">
                                    Simulate: HRD Upload Surat &gt;
                                </Button>
                            </div>
                        </>
                    )}

                    {/* === STATUS 2: SURAT TERBIT (SELESAI) === */}
                    {status === 'done' && (
                        <>
                            {/* ICON CENTANG DIHAPUS */}

                            <h2 className="text-3xl font-bold text-black pt-10">
                                Surat Ijin Kerja kamu telah dikirimkan !
                            </h2>

                            <div className="flex flex-col gap-4 mt-8 w-full max-w-md">
                                
                                {/* Button Download */}
                                <Button className="h-14 w-full bg-white border-2 border-black text-black hover:bg-gray-50 text-lg font-medium rounded-lg shadow-sm gap-3">
                                    <Download className="w-6 h-6" />
                                    Download Surat Ijin Kerja
                                </Button>

                                {/* Button Selesai */}
                                <Link href={route('dosen.seminar.finish')} className="w-full">
                                    <Button className="h-14 w-full bg-white border-2 border-black text-black hover:bg-gray-50 text-lg font-medium rounded-lg shadow-sm">
                                        Selesai
                                    </Button>
                                </Link>
                            </div>
                        </>
                    )}

                </div>
            </div>
        </AppLayout>
    );
}