import React from 'react';
import { Head, Link, usePage } from '@inertiajs/react'; // Tambah Link & usePage
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table"
import { route } from "ziggy-js";

// ✅ TERIMA PROPS DARI CONTROLLER
export default function DosenHomePage({ recentSeminars }) {
    
    // Ambil data user dari inertia shared props untuk menyapa nama
    const { auth } = usePage().props;
    const userName = auth?.user?.name || "Dosen";

    // Format Tanggal Helper
    const formatDate = (dateString) => {
        if (!dateString) return "-";
        return new Date(dateString).toLocaleDateString('id-ID', {
            day: 'numeric', month: 'long', year: 'numeric'
        });
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'Beranda', href: '/dosen/home' }]}>
            <Head title="Beranda Dosen" />

            <div className="flex flex-1 flex-col gap-6 p-6 pt-0">
                
                {/* 1. KARTU SAMBUTAN */}
                <Card className="w-full bg-white border shadow-sm">
                    <CardContent className="p-6">
                        <h2 className="text-2xl font-semibold tracking-tight flex items-center gap-2">
                            👋 Selamat Datang {userName}!
                        </h2>
                        <p className="text-muted-foreground mt-1">
                            Silakan kelola pengajuan seminar dan penghargaan Anda di sini.
                        </p>
                    </CardContent>
                </Card>

                {/* 2. BAGIAN FILTER DAN JUDUL TABEL */}
                <div className="space-y-4">
                    <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <h3 className="text-xl font-bold text-gray-900">Daftar Seminar Terbaru</h3>
                        
                        {/* Search Bar & Filter (UI Only - Logic searching bisa ditambahkan nanti) */}
                        <div className="flex items-center gap-2 w-full sm:w-auto">
                            <div className="flex w-full max-w-sm items-center space-x-2">
                                <Input type="text" placeholder="Cari judul..." className="w-[250px]" />
                                <Button variant="secondary">Cari</Button>
                            </div>
                        </div>
                    </div>

                    {/* 3. TABEL DATA ASLI */}
                    <div className="rounded-md border bg-white">
                        <Table>
                            <TableHeader>
                                <TableRow className="bg-gray-50 hover:bg-gray-50">
                                    <TableHead className="w-[50px] text-center font-bold text-gray-700">No</TableHead>
                                    <TableHead className="font-bold text-gray-700">Judul Makalah</TableHead>
                                    <TableHead className="font-bold text-gray-700">Forum</TableHead>
                                    <TableHead className="font-bold text-gray-700">Tanggal Mulai</TableHead>
                                    <TableHead className="font-bold text-gray-700">Status</TableHead>
                                    <TableHead className="text-right font-bold text-gray-700">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {/* ✅ MAPPING DATA ASLI DARI DATABASE */}
                                {recentSeminars && recentSeminars.length > 0 ? (
                                    recentSeminars.map((item, index) => (
                                        <TableRow key={item.id}>
                                            <TableCell className="text-center font-medium">{index + 1}</TableCell>
                                            <TableCell className="font-medium">{item.judul_makalah}</TableCell>
                                            <TableCell>{item.nama_forum}</TableCell>
                                            <TableCell>{formatDate(item.tanggal_mulai)}</TableCell>
                                            <TableCell>
                                                {/* Badge Status */}
                                                <span className={`inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset ${
                                                    item.status_progress === 'draft' ? 'bg-gray-50 text-gray-600 ring-gray-500/10' :
                                                    item.status_progress === 'menunggu_reviewer' ? 'bg-yellow-50 text-yellow-800 ring-yellow-600/20' :
                                                    item.status_progress === 'disetujui' ? 'bg-green-50 text-green-700 ring-green-600/20' :
                                                    'bg-blue-50 text-blue-700 ring-blue-700/10'
                                                }`}>
                                                    {item.status_progress ? item.status_progress.replace('_', ' ').toUpperCase() : 'DRAFT'}
                                                </span>
                                            </TableCell>
                                            <TableCell className="text-right">
                                                {/* Logic Tombol Aksi */}
                                                {item.status_progress === 'draft' ? (
                                                    <Button asChild variant="default" size="sm">
                                                        <Link href={route('dosen.seminar.step2', item.id)}>
                                                            Lanjut Upload
                                                        </Link>
                                                    </Button>
                                                ) : (
                                                    <Button asChild variant="outline" size="sm">
                                                        <Link href={route('dosen.seminar.step3', item.id) /* Bisa diarahkan ke detail */}>
                                                            Lihat Detail
                                                        </Link>
                                                    </Button>
                                                )}
                                            </TableCell>
                                        </TableRow>
                                    ))
                                ) : (
                                    <TableRow>
                                        <TableCell colSpan={6} className="h-24 text-center text-muted-foreground">
                                            Belum ada pengajuan seminar. Silakan buat pengajuan baru.
                                        </TableCell>
                                    </TableRow>
                                )}
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}