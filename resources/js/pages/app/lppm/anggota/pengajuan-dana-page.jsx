import React from 'react';
import AppLayout from "@/layouts/app-layout";
import { Head, Link } from "@inertiajs/react";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { CircleArrowUp } from "lucide-react";
import { route } from "ziggy-js";

export default function PengajuanDanaPage({ buku = [] }) {
    return (
        <AppLayout breadcrumbs={[
            { title: 'Beranda', href: '/lppm/anggota/home' },
            { title: 'Pengajuan Dana', href: '#' }
        ]}>
            <Head title="Pengajuan Dana" />

            <div className="w-full max-w-6xl mx-auto px-6 py-8 space-y-8">
                
                {/* ========================= HEADER ========================= */}
                <Card className="shadow-sm border-none bg-transparent shadow-none">
                    <CardContent className="p-0">
                        <h1 className="text-2xl font-bold tracking-tight text-gray-900">
                            Daftar Pengajuan Dana
                        </h1>
                        <p className="text-muted-foreground mt-1">
                            Daftar pengajuan yang perlu diverifikasi oleh Anggota LPPM.
                        </p>
                    </CardContent>
                </Card>

                {/* ========================= SEARCH + FILTER ========================= */}
                <div className="flex justify-between items-center gap-4">
                    <div className="relative w-full md:w-1/3">
                        <input
                            type="text"
                            placeholder="Cari pengajuan..."
                            className="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black"
                        />
                    </div>
                    
                    <Button variant="outline" className="h-10 px-4 gap-2">
                        Filter <span>▼</span>
                    </Button>
                </div>

                {/* ========================= CARD LIST ========================= */}
                <div className="space-y-4">
                    {buku.map((item) => (
                        <Card key={item.id} className="border shadow-sm hover:shadow-md transition-shadow">
                            <CardContent className="p-5 flex flex-col md:flex-row items-center justify-between gap-4">
                                
                                {/* LEFT SIDE: Icon & Info */}
                                <div className="flex items-center gap-4 w-full md:w-2/3">
                                    <div className="h-12 w-12 rounded-full bg-black flex items-center justify-center text-white shrink-0">
                                        <CircleArrowUp className="h-6 w-6" />
                                    </div>
                                    <div className="min-w-0">
                                        <h3 className="font-bold text-base text-gray-900 truncate">
                                            {item.judul}
                                        </h3>
                                        <p className="text-sm text-gray-500 truncate">
                                            {item.penulis}
                                        </p>
                                    </div>
                                </div>

                                {/* RIGHT SIDE: Status, Action, Date */}
                                <div className="flex flex-col items-end gap-2 w-full md:w-auto self-stretch justify-between">
                                    
                                    {/* Status Badge */}
                                    <span className="px-2 py-1 rounded-md text-xs font-medium border bg-blue-50 text-blue-700 border-blue-200">
                                        {item.status || "Menunggu konfirmasi LPPM"}
                                    </span>

                                    {/* Action Button */}
                                    <div className="flex gap-2 mt-1">
                                        <Link href={route('lppm.anggota.pengajuan-dana.detail', item.id)}>
                                            <Button className="h-8 text-xs bg-black text-white hover:bg-gray-800 px-6">
                                                Review
                                            </Button>
                                        </Link>
                                    </div>
                                    
                                    {/* Date */}
                                    <span className="text-xs text-muted-foreground font-medium">
                                        {item.tanggal || "dd / mm / yy"}
                                    </span>
                                </div>

                            </CardContent>
                        </Card>
                    ))}

                    {buku.length === 0 && (
                        <div className="text-center py-12 text-muted-foreground bg-gray-50/50 rounded-lg border border-dashed w-full">
                            Belum ada data pengajuan dana.
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}