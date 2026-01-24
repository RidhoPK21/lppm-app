import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { FileText, User, Calendar, ChevronRight, Inbox } from 'lucide-react';
import { route } from 'ziggy-js';

export default function PengajuanDanaPage({ pengajuanList = [] }) {
    return (
        <AppLayout breadcrumbs={[
            { title: 'Beranda', href: '/lppm/ketua/home' },
            { title: 'Persetujuan Dana', href: '#' }
        ]}>
            <Head title="Persetujuan Dana Seminar" />

            <div className="w-full max-w-6xl mx-auto px-6 py-8 space-y-8">
                
                {/* Header */}
                <div className="flex flex-col gap-2">
                    <h1 className="text-2xl font-bold tracking-tight text-gray-900">
                        Persetujuan Dana Seminar
                    </h1>
                    <p className="text-muted-foreground">
                        Daftar pengajuan yang telah lolos verifikasi Kaprodi dan menunggu persetujuan LPPM.
                    </p>
                </div>

                {/* List Pengajuan */}
                <div className="space-y-4">
                    {pengajuanList.length > 0 ? (
                        pengajuanList.map((item) => (
                            <Card key={item.id} className="hover:shadow-md transition-all border-l-4 border-l-blue-600">
                                <CardContent className="p-5 flex flex-col md:flex-row items-center justify-between gap-4">
                                    
                                    {/* Info Kiri */}
                                    <div className="flex items-start gap-4 flex-1">
                                        <div className="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 shrink-0">
                                            <Inbox className="h-6 w-6" />
                                        </div>
                                        <div className="space-y-1">
                                            <h3 className="font-bold text-lg text-gray-900 line-clamp-1">
                                                {item.judul_makalah}
                                            </h3>
                                            <div className="flex items-center gap-3 text-sm text-gray-500">
                                                <span className="flex items-center gap-1">
                                                    <User className="h-3.5 w-3.5" />
                                                    {item.dosen?.user?.name || 'Dosen'}
                                                </span>
                                                <span>•</span>
                                                <span className="flex items-center gap-1">
                                                    <Calendar className="h-3.5 w-3.5" />
                                                    {new Date(item.created_at).toLocaleDateString('id-ID')}
                                                </span>
                                            </div>
                                            <div className="pt-1">
                                                <Badge variant="secondary" className="bg-blue-50 text-blue-700 border-blue-100">
                                                    Menunggu Verifikasi LPPM
                                                </Badge>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Tombol Aksi */}
                                    <div>
                                        <Link href={route('lppm.ketua.pengajuan-dana.konfirmasi', item.id)}>
                                            <Button className="gap-2 bg-black text-white hover:bg-gray-800">
                                                Proses Pengajuan
                                                <ChevronRight className="h-4 w-4" />
                                            </Button>
                                        </Link>
                                    </div>

                                </CardContent>
                            </Card>
                        ))
                    ) : (
                        /* Empty State */
                        <div className="flex flex-col items-center justify-center py-16 bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
                            <div className="bg-white p-4 rounded-full mb-3 shadow-sm">
                                <FileText className="h-8 w-8 text-gray-300" />
                            </div>
                            <h3 className="text-lg font-medium text-gray-900">Tidak ada pengajuan masuk</h3>
                            <p className="text-sm text-gray-500 max-w-sm text-center mt-1">
                                Saat ini belum ada pengajuan baru dari Kaprodi yang perlu diproses.
                            </p>
                        </div>
                    )}
                </div>

            </div>
        </AppLayout>
    );
}