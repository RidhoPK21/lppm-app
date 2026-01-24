import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { 
    Banknote, 
    User, 
    Calendar, 
    ChevronRight, 
    Clock,
    CheckCircle2
} from 'lucide-react';
import { route } from 'ziggy-js';

export default function PembayaranPage({ seminars = [] }) {
    
    // Helper Warna Status
    const getStatusInfo = (status) => {
        if (status === 'menunggu_pembayaran') {
            return { label: 'Perlu Dicairkan', color: 'bg-orange-100 text-orange-700 border-orange-200', icon: Clock };
        }
        return { label: 'Dana Cair / Selesai', color: 'bg-green-100 text-green-700 border-green-200', icon: CheckCircle2 };
    };

    return (
        <AppLayout breadcrumbs={[
            { title: 'Beranda', href: '/keuangan/home' },
            { title: 'Pencairan Dana', href: '#' }
        ]}>
            <Head title="Daftar Pencairan Dana" />

            <div className="w-full max-w-6xl mx-auto px-6 py-8 space-y-8">
                
                {/* Header */}
                <div className="flex flex-col gap-2">
                    <h1 className="text-2xl font-bold tracking-tight text-gray-900">
                        Daftar Pencairan Dana
                    </h1>
                    <p className="text-muted-foreground">
                        Kelola antrean pencairan dana seminar dosen yang telah disetujui LPPM.
                    </p>
                </div>

                {/* List Card */}
                <div className="space-y-4">
                    {seminars.length > 0 ? (
                        seminars.map((item) => {
                            const statusInfo = getStatusInfo(item.status_progress);
                            const StatusIcon = statusInfo.icon;

                            return (
                                <Card key={item.id} className="hover:shadow-md transition-all border-l-4 border-l-emerald-600">
                                    <CardContent className="p-5 flex flex-col md:flex-row items-center justify-between gap-4">
                                        
                                        {/* Info Kiri */}
                                        <div className="flex items-start gap-4 flex-1">
                                            <div className="h-12 w-12 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                                                <Banknote className="h-6 w-6" />
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
                                                        <Calendar className="h-3.5 w-3.5" />
                                                        {new Date(item.created_at).toLocaleDateString('id-ID')}
                                                    </span>
                                                </div>
                                                <div className="pt-2">
                                                    <Badge variant="outline" className={`${statusInfo.color} gap-1 px-2.5 py-0.5`}>
                                                        <StatusIcon className="h-3.5 w-3.5" />
                                                        {statusInfo.label}
                                                    </Badge>
                                                </div>
                                            </div>
                                        </div>

                                        {/* Tombol Aksi */}
                                        <div>
                                            <Link href={route('keuangan.pembayaran.detail', item.id)}>
                                                <Button className="gap-2 bg-black text-white hover:bg-gray-800">
                                                    Lihat Detail
                                                    <ChevronRight className="h-4 w-4" />
                                                </Button>
                                            </Link>
                                        </div>

                                    </CardContent>
                                </Card>
                            );
                        })
                    ) : (
                        <div className="text-center py-16 bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
                            <div className="bg-white p-4 rounded-full mb-3 shadow-sm inline-flex">
                                <Banknote className="h-8 w-8 text-gray-300" />
                            </div>
                            <h3 className="text-lg font-medium text-gray-900">Tidak ada antrean pencairan</h3>
                            <p className="text-sm text-gray-500 max-w-sm mx-auto mt-1">
                                Belum ada pengajuan dana baru yang masuk dari LPPM.
                            </p>
                        </div>
                    )}
                </div>

            </div>
        </AppLayout>
    );
}