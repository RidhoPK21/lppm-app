import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Clock, FileText, ChevronRight } from 'lucide-react';
import { Badge } from '@/components/ui/badge';

export default function VerifikasiPage({ verifikasiList = [] }) {
    return (
        <AppLayout breadcrumbs={[
            { title: 'Beranda', href: '/kprodi/dashboard' },
            { title: 'Verifikasi', href: '#' }
        ]}>
            <Head title="Verifikasi Pengajuan" />

            <div className="w-full max-w-6xl mx-auto px-6 py-8 space-y-8">
                
                <div className="flex flex-col gap-2">
                    <h1 className="text-2xl font-bold tracking-tight">Verifikasi Akademik</h1>
                    <p className="text-muted-foreground">
                        Daftar pengajuan dana seminar dosen yang menunggu persetujuan Anda.
                    </p>
                </div>

                <div className="space-y-4">
                    {verifikasiList.length > 0 ? (
                        verifikasiList.map((item) => (
                            <Card key={item.id} className="hover:shadow-md transition-all border-l-4 border-l-orange-400">
                                <CardContent className="p-5 flex flex-col md:flex-row items-center justify-between gap-4">
                                    
                                    <div className="flex items-start gap-4 flex-1">
                                        <div className="h-12 w-12 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 shrink-0">
                                            <Clock className="h-6 w-6" />
                                        </div>
                                        <div className="space-y-1">
                                            <h3 className="font-bold text-lg text-gray-900">{item.judul_makalah}</h3>
                                            <p className="text-sm text-gray-500 flex items-center gap-2">
                                                <span className="font-medium text-gray-700">{item.dosen?.user?.name || 'Dosen'}</span>
                                                <span>•</span>
                                                <span>{item.nama_forum}</span>
                                            </p>
                                            <Badge variant="outline" className="bg-orange-50 text-orange-700 border-orange-200 mt-1">
                                                Menunggu Persetujuan
                                            </Badge>
                                        </div>
                                    </div>

                                    <div>
                                        <Link href={route('kprodi.verifikasi.detail', item.id)}>
                                            <Button className="gap-2 bg-black text-white hover:bg-gray-800">
                                                Periksa Dokumen
                                                <ChevronRight className="h-4 w-4" />
                                            </Button>
                                        </Link>
                                    </div>

                                </CardContent>
                            </Card>
                        ))
                    ) : (
                        <div className="text-center py-16 bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
                            <FileText className="h-10 w-10 text-gray-300 mx-auto mb-3" />
                            <h3 className="text-lg font-medium text-gray-900">Tidak ada pengajuan baru</h3>
                            <p className="text-sm text-gray-500">Semua pengajuan telah diproses.</p>
                        </div>
                    )}
                </div>

            </div>
        </AppLayout>
    );
}