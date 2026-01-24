import React from 'react';
import AppLayout from "@/layouts/app-layout";
import { Head } from "@inertiajs/react";
import { CheckCircle2, Download} from "lucide-react"; 
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { route } from "ziggy-js"; 

export default function SeminarDisetujuiPage({ reviews = [] }) { 
    return (
        <AppLayout breadcrumbs={[
            { title: 'Beranda', href: '/reviewer/home' },
            { title: 'Seminar Disetujui', href: '#' }
        ]}>
            <Head title="Seminar Disetujui" />

            <div className="w-full max-w-6xl mx-auto px-6 py-8 space-y-8">
                
                {/* ==== HEADER ==== */}
                <Card className="shadow-sm border-none bg-transparent shadow-none">
                    <CardContent className="p-0">
                        <h1 className="text-2xl font-bold tracking-tight text-gray-900">
                            Riwayat Seminar Disetujui
                        </h1>
                        <p className="text-muted-foreground mt-1">
                            Daftar paper seminar yang telah selesai direview dan disetujui (ACC).
                        </p>
                    </CardContent>
                </Card>

                {/* ==== LIST ==== */}
                <div className="space-y-4">
                    {reviews.length > 0 ? (
                        reviews.map((item) => (
                            <Card key={item.id} className="border border-green-100 bg-green-50/30 shadow-sm hover:shadow-md transition-shadow">
                                <CardContent className="p-5 flex flex-col md:flex-row items-center justify-between gap-4">
                                    
                                    {/* LEFT: Icon & Info */}
                                    <div className="flex items-center gap-4 w-full md:w-1/2">
                                        <div className="h-12 w-12 rounded-full bg-green-600 flex items-center justify-center text-white shrink-0">
                                            <CheckCircle2 className="h-6 w-6" />
                                        </div>

                                        <div className="min-w-0">
                                            <h3 className="font-bold text-base text-gray-900 truncate" title={item.seminar?.judul_makalah}>
                                                {item.seminar?.judul_makalah || 'Judul Tidak Tersedia'}
                                            </h3>
                                            <p className="text-sm text-gray-500 truncate">
                                                Oleh: {item.seminar?.dosen?.user?.name || 'Dosen Tidak Diketahui'}
                                            </p>
                                            <div className="mt-1">
                                                <span className="text-xs font-medium text-green-700 bg-green-100 px-2 py-0.5 rounded border border-green-200">
                                                    Status: Selesai / Disetujui
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    {/* RIGHT: Action & Date */}
                                    <div className="flex flex-col items-end gap-3 w-full md:w-auto">
                                        
                                        <span className="text-xs text-muted-foreground font-medium">
                                            Disetujui pada: {new Date(item.updated_at).toLocaleDateString('id-ID', {
                                                day: 'numeric', month: 'long', year: 'numeric'
                                            })}
                                        </span>

                                        {/* Action Buttons Group */}
                                        <div className="flex gap-2">
                                            {/* Tombol Download Paper (Satu-satunya tombol aksi) */}
                                            <a 
                                                href={route('reviewer.seminar.download', item.id)} 
                                                target="_blank" 
                                                rel="noopener noreferrer"
                                            >
                                                <Button variant="outline" className="h-9 text-xs gap-2 border-gray-300 bg-white hover:bg-gray-50">
                                                    <Download className="w-3.5 h-3.5" />
                                                    Download Paper
                                                </Button>
                                            </a>
                                        </div>

                                    </div>

                                </CardContent>
                            </Card>
                        ))
                    ) : (
                        /* NO DATA STATE */
                        <div className="flex flex-col items-center justify-center py-16 text-muted-foreground bg-gray-50 rounded-xl border-2 border-dashed border-gray-200 w-full">
                            <div className="bg-white p-3 rounded-full mb-3 shadow-sm">
                                <CheckCircle2 className="h-8 w-8 text-gray-300" />
                            </div>
                            <h3 className="text-lg font-medium text-gray-900">Belum ada riwayat persetujuan</h3>
                            <p className="text-sm text-gray-500 max-w-sm text-center">
                                Paper yang telah Anda review dan setujui akan muncul di halaman ini.
                            </p>
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}