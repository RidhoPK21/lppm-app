import React from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import SeminarStepper from '@/components/seminar-stepper';

export default function Step3Finalisasi() {
    return (
        <AppLayout breadcrumbs={[
            { title: 'Beranda', href: '/dosen/dashboard' },
            { title: 'Registrasi Seminar', href: '/dosen/seminar' },
            { title: 'Tahap 3', href: '#' }
        ]}>
            <Head title="Finalisasi Paper" />

            <div className="w-full max-w-6xl mx-auto px-6 py-6 pb-24">
                
                {/* Header Judul */}
                <div className="text-center mb-8">
                    <h1 className="text-2xl font-bold">Pengajuan Dana Registrasi Seminar</h1>
                </div>

                {/* Stepper Component (Step 3 Active) */}
                <SeminarStepper currentStep={3} />

                {/* Konten Menunggu */}
                <div className="mt-20 flex flex-col items-center justify-center space-y-8">
                    
                    <h2 className="text-2xl font-bold text-center">
                        Mohon menunggu Konfirmasi dari Reviewer
                    </h2>

                    {/* Loading Spinner / Circle */}
                    <div className="relative flex items-center justify-center">
                        {/* Lingkaran Luar (Static) */}
                        {/* <div className="w-32 h-32 border-2 border-gray-200 rounded-full" /> */}
                        
                        {/* Lingkaran Loading (Animasi) */}
                        <div className="w-24 h-24 border-2 border-gray-800 border-t-transparent rounded-full animate-spin" />
                    </div>

                </div>
            </div>
        </AppLayout>
    );
}