import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent } from '@/components/ui/card';
import { CircleArrowUp, ChevronRight } from 'lucide-react'; // Menggunakan CircleArrowUp
import { route } from 'ziggy-js';

export default function HrdHomePage() {
    // Data Dummy
    const notifications = [
        {
            id: 1,
            type: 'request',
            dosen: "Pak Budi Santoso",
            nip: "198501012010121001",
            judul: "Permintaan Surat Tugas: Seminar Nasional SNTI",
            lokasi: "Jakarta",
            tanggal: "20 Nov 2024",
            status: "Menunggu Tindakan"
        },
        {
            id: 2,
            type: 'request',
            dosen: "Bu Siti Aminah",
            nip: "199005052015042001",
            judul: "Permintaan Surat Tugas: International AI Conference",
            lokasi: "Bali",
            tanggal: "15 Des 2024",
            status: "Menunggu Tindakan"
        },
        {
            id: 3,
            type: 'done',
            dosen: "Pak Joko Anwar",
            nip: "198807072012121003",
            judul: "Surat Tugas: Workshop Big Data",
            lokasi: "Bandung",
            tanggal: "10 Jan 2025",
            status: "Selesai"
        }
    ];

    const getStatusColor = (status) => {
        if (status === 'Menunggu Tindakan') return 'bg-orange-50 text-orange-700 border-orange-200';
        if (status === 'Selesai') return 'bg-green-50 text-green-700 border-green-200';
        return 'bg-gray-50 text-gray-700 border-gray-200';
    };

    return (
        <AppLayout breadcrumbs={[
            { title: 'Beranda', href: '/hrd/dashboard' }
        ]}>
            <Head title="Dashboard HRD" />

            <div className="w-full max-w-6xl mx-auto p-6 pt-0 pb-24 space-y-8">
                
                {/* Header Judul */}
                <Card className="shadow-sm border-none bg-transparent shadow-none">
                    <CardContent className="p-0 flex justify-between items-center">
                        <div>
                            <h1 className="text-2xl font-bold tracking-tight text-gray-900">
                                Daftar Permintaan Surat Tugas
                            </h1>
                            <p className="text-muted-foreground mt-1">
                                Kelola penerbitan surat izin kerja/tugas untuk dosen.
                            </p>
                        </div>
                    </CardContent>
                </Card>

                {/* List Card */}
                <div className="space-y-4">
                    {notifications.map((item) => {
                        const isActionable = item.status === 'Menunggu Tindakan';
                        
                        // Komponen Konten Kartu
                        const CardInner = (
                            <CardContent className="p-6 flex flex-col md:flex-row items-center justify-between gap-4">
                                {/* KIRI: Info Utama */}
                                <div className="flex items-center gap-4 w-full md:w-2/3">
                                    {/* Icon Container: Selalu Hitam dengan CircleArrowUp */}
                                    <div className="shrink-0 h-12 w-12 flex items-center justify-center rounded-full bg-black text-white">
                                        <CircleArrowUp className="w-6 h-6" />
                                    </div>
                                    
                                    <div className="min-w-0">
                                        <h3 className={`font-bold text-base truncate ${isActionable ? 'text-gray-900 group-hover:text-blue-600 transition-colors' : 'text-gray-700'}`}>
                                            {item.judul}
                                        </h3>
                                        <div className="flex flex-col sm:flex-row sm:items-center sm:gap-2 text-sm text-muted-foreground mt-1">
                                            <span>{item.dosen}</span>
                                            <span className="hidden sm:inline">•</span>
                                            <span>{item.lokasi}</span>
                                        </div>
                                    </div>
                                </div>

                                {/* KANAN: Status & Tanggal */}
                                <div className="flex items-center gap-6 w-full md:w-auto justify-between md:justify-end">
                                    <div className="flex flex-col items-end gap-2">
                                        <span className={`px-2 py-1 rounded-md text-xs font-medium border ${getStatusColor(item.status)}`}>
                                            {item.status}
                                        </span>
                                        <span className="text-xs text-muted-foreground font-medium">
                                            {item.tanggal}
                                        </span>
                                    </div>

                                    {/* Indikator Panah (Hanya jika actionable) */}
                                    {isActionable && (
                                        <ChevronRight className="w-5 h-5 text-gray-400 group-hover:text-black transition-colors" />
                                    )}
                                </div>
                            </CardContent>
                        );

                        // Logic Rendering: Bungkus dengan Link jika Actionable
                        return isActionable ? (
                            <Link key={item.id} href={route('hrd.surat-tugas.upload', item.id)} className="block group">
                                <Card className="border shadow-sm group-hover:shadow-md group-hover:border-gray-300 transition-all cursor-pointer">
                                    {CardInner}
                                </Card>
                            </Link>
                        ) : (
                            <Card key={item.id} className="border shadow-sm bg-gray-50/50 border-gray-100 cursor-default opacity-80">
                                {CardInner}
                            </Card>
                        );
                    })}

                    {notifications.length === 0 && (
                        <div className="text-center py-12 border-2 border-dashed rounded-lg text-muted-foreground">
                            Tidak ada permintaan surat tugas baru saat ini.
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}