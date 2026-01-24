import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { 
    Briefcase, 
    User, 
    MapPin, 
    ChevronRight, 
    CheckCircle2,
    Clock
} from 'lucide-react';
import { route } from 'ziggy-js';

export default function SuratTugasPage({ suratTugasList = [] }) {
    
    return (
        <AppLayout breadcrumbs={[
            { title: 'Beranda', href: '/hrd/dashboard' },
            { title: 'Surat Tugas', href: '#' }
        ]}>
            <Head title="Daftar Permintaan Surat Tugas" />

            <div className="w-full max-w-6xl mx-auto px-6 py-8 space-y-8">
                
                {/* Header */}
                <div className="flex flex-col gap-2">
                    <h1 className="text-2xl font-bold tracking-tight text-gray-900">
                        Permintaan Surat Tugas
                    </h1>
                    <p className="text-muted-foreground">
                        Daftar dosen yang akan melaksanakan seminar Onsite dan membutuhkan Surat Tugas perjalanan dinas.
                    </p>
                </div>

                {/* List Card */}
                <div className="space-y-4">
                    {suratTugasList.length > 0 ? (
                        suratTugasList.map((item) => {
                            const isSelesai = item.status_progress === 'selesai';

                            return (
                                <Card key={item.id} className={`hover:shadow-md transition-all border-l-4 ${isSelesai ? 'border-l-green-600' : 'border-l-purple-600'}`}>
                                    <CardContent className="p-5 flex flex-col md:flex-row items-center justify-between gap-4">
                                        
                                        {/* Info Kiri */}
                                        <div className="flex items-start gap-4 flex-1">
                                            <div className={`h-12 w-12 rounded-full flex items-center justify-center text-white shrink-0 ${isSelesai ? 'bg-green-600' : 'bg-purple-600'}`}>
                                                <Briefcase className="h-6 w-6" />
                                            </div>
                                            <div className="space-y-1">
                                                <h3 className="font-bold text-lg text-gray-900 line-clamp-1">
                                                    {item.judul_makalah}
                                                </h3>
                                                <div className="flex items-center gap-3 text-sm text-gray-500">
                                                    <span className="flex items-center gap-1">
                                                        <User className="h-3.5 w-3.5" />
                                                        {item.dosen?.user?.name || 'Nama Dosen'}
                                                    </span>
                                                    <span>•</span>
                                                    <span className="flex items-center gap-1">
                                                        <MapPin className="h-3.5 w-3.5" />
                                                        {item.tempat_pelaksanaan || 'Lokasi Seminar'}
                                                    </span>
                                                </div>
                                                <div className="pt-2">
                                                    {isSelesai ? (
                                                        <Badge variant="outline" className="bg-green-50 text-green-700 border-green-200 gap-1">
                                                            <CheckCircle2 className="h-3 w-3" />
                                                            Surat Terbit
                                                        </Badge>
                                                    ) : (
                                                        <Badge variant="outline" className="bg-purple-50 text-purple-700 border-purple-200 gap-1">
                                                            <Clock className="h-3 w-3" />
                                                            Menunggu Penerbitan
                                                        </Badge>
                                                    )}
                                                </div>
                                            </div>
                                        </div>

                                        {/* Tombol Aksi */}
                                        <div>
                                            {!isSelesai ? (
                                                <Link href={route('hrd.surat-tugas.upload', item.id)}>
                                                    <Button className="gap-2 bg-black text-white hover:bg-gray-800">
                                                        Proses Surat
                                                        <ChevronRight className="h-4 w-4" />
                                                    </Button>
                                                </Link>
                                            ) : (
                                                <Button variant="outline" disabled className="gap-2 opacity-70">
                                                    Selesai
                                                    <CheckCircle2 className="h-4 w-4" />
                                                </Button>
                                            )}
                                        </div>

                                    </CardContent>
                                </Card>
                            );
                        })
                    ) : (
                        <div className="text-center py-16 bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
                            <div className="bg-white p-4 rounded-full mb-3 shadow-sm inline-flex">
                                <Briefcase className="h-8 w-8 text-gray-300" />
                            </div>
                            <h3 className="text-lg font-medium text-gray-900">Tidak ada permintaan baru</h3>
                            <p className="text-sm text-gray-500 max-w-sm mx-auto mt-1">
                                Saat ini belum ada dosen yang mengajukan permohonan surat tugas seminar onsite.
                            </p>
                        </div>
                    )}
                </div>

            </div>
        </AppLayout>
    );
}