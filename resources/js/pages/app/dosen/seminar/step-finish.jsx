import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import SeminarStepper from '@/components/seminar-stepper';
import { Button } from '@/components/ui/button';
import { route } from 'ziggy-js';
import { 
    CheckCircle2, 
    Home
} from 'lucide-react';

export default function StepFinish() {
    
    return (
        <AppLayout breadcrumbs={[
            { title: 'Beranda', href: '/dosen/home' },
            { title: 'Registrasi Seminar', href: '/dosen/seminar' },
            { title: 'Selesai', href: '#' }
        ]}>
            <Head title="Pengajuan Selesai" />

            <div className="w-full max-w-6xl mx-auto px-6 py-6 pb-24">
                
                <div className="text-center mb-8">
                    <h1 className="text-2xl font-bold">Pengajuan Dana Registrasi Seminar</h1>
                    <p className="text-green-600 font-medium">Proses Telah Selesai</p>
                </div>

                {/* Stepper (Semua Hijau) */}
                <SeminarStepper currentStep={7} /> 

                <div className="mt-20 flex flex-col items-center justify-center space-y-8 max-w-2xl mx-auto">
                    
                    {/* Icon & Pesan Sukses */}
                    <div className="flex flex-col items-center text-center space-y-6">
                        <div className="w-32 h-32 bg-green-100 rounded-full flex items-center justify-center border-4 border-green-50 shadow-sm animate-in zoom-in duration-500">
                            <CheckCircle2 className="w-16 h-16 text-green-600" />
                        </div>
                        <div className="space-y-2">
                            <h2 className="text-3xl font-bold text-gray-900">Selamat!</h2>
                            <p className="text-lg text-gray-500 leading-relaxed">
                                Seluruh rangkaian proses pengajuan dana seminar Anda telah selesai. <br/>
                                Selamat mengikuti kegiatan seminar dan sukses selalu!
                            </p>
                        </div>
                    </div>

                    {/* Tombol Kembali */}
                    <Link href={route('dosen.home')}>
                        <Button className="bg-black text-white px-8 py-6 text-lg shadow-lg gap-3 hover:bg-gray-800 transition-all">
                            <Home className="w-5 h-5" />
                            Kembali ke Beranda
                        </Button>
                    </Link>

                </div>
            </div>
        </AppLayout>
    );
}