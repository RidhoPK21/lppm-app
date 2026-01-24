import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { route } from 'ziggy-js';
import { 
    FileText, 
    ArrowRight,
    ExternalLink,
    ArrowLeft,
    Download,
    Paperclip,
    CheckCircle2 // 🔥 PERBAIKAN: Ditambahkan di sini
} from 'lucide-react';

export default function PembayaranDetailPage({ seminar }) {
    
    // Ambil Data Link Artefak dari Dosen
    // Mapping Index berdasarkan Step 4 Dosen:
    // 0: Review Dosen, 1: Submit, 2: LoA (Bukti Makalah), 3: Catatan Panitia, 4: Bukti Registrasi, 5: Final Paper
    const artefakLinks = seminar.kewajiban_penelitian?.link_artefak || {};
    
    // File Surat dari LPPM
    const suratLppm = seminar.kewajiban_penelitian?.file_surat_permohonan;

    // Dokumen Dosen yang Wajib Dicek Keuangan
    const requiredDocs = [
        { label: "Bukti Makalah (LoA)", link: artefakLinks[2] },
        { label: "Bukti Registrasi Keikutsertaan", link: artefakLinks[4] },
        { label: "Softcopy Versi Final Paper", link: artefakLinks[5] },
    ];

    return (
        <AppLayout breadcrumbs={[
            { title: 'Beranda', href: '/keuangan/home' },
            { title: 'Pencairan Dana', href: '/keuangan/pembayaran' },
            { title: 'Detail', href: '#' }
        ]}>
            <Head title="Detail Pencairan" />

            <div className="w-full max-w-5xl mx-auto px-6 py-8 pb-24 space-y-8">
                
                {/* Navigasi Balik */}
                <Link href={route('keuangan.pembayaran')} className="flex items-center text-sm text-gray-500 hover:text-gray-900 w-fit">
                    <ArrowLeft className="w-4 h-4 mr-1" />
                    Kembali
                </Link>

                {/* Header Info */}
                <div className="flex flex-col gap-1">
                    <h1 className="text-2xl font-bold tracking-tight text-gray-900">
                        Verifikasi Kelengkapan Pencairan
                    </h1>
                    <div className="flex items-center gap-3 text-sm text-gray-500">
                        <span className="flex items-center gap-1 font-medium text-gray-900">
                            {seminar.judul_makalah}
                        </span>
                    </div>
                </div>

                <div className="grid md:grid-cols-2 gap-6">
                    
                    {/* PANEL KIRI: Dokumen & Berkas */}
                    <div className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-lg flex items-center gap-2">
                                    <FileText className="w-5 h-5 text-emerald-600" />
                                    Dokumen Wajib (4 Item)
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-6">
                                
                                {/* 1. Surat dari LPPM (Prioritas Utama) */}
                                <div className="border border-emerald-200 bg-emerald-50/50 rounded-lg p-4 space-y-3">
                                    <div className="flex items-start gap-3">
                                        <div className="bg-emerald-100 p-2 rounded-full">
                                            <Paperclip className="w-4 h-4 text-emerald-700" />
                                        </div>
                                        <div className="flex-1">
                                            <Label className="text-emerald-900 font-semibold block">1. Surat Permohonan Dana (LPPM)</Label>
                                            <p className="text-xs text-emerald-600 mt-1">Dokumen resmi pengantar dari Ketua LPPM.</p>
                                        </div>
                                    </div>
                                    
                                    {suratLppm ? (
                                        <a href={`/storage/${suratLppm}`} target="_blank" rel="noreferrer" className="block">
                                            <Button variant="outline" className="w-full bg-white text-emerald-700 hover:bg-emerald-100 border-emerald-200 gap-2 h-10">
                                                <Download className="w-4 h-4" />
                                                Download Surat PDF
                                            </Button>
                                        </a>
                                    ) : (
                                        <div className="bg-white border border-red-200 text-red-600 text-xs p-2 rounded text-center">
                                            File surat belum diunggah oleh LPPM.
                                        </div>
                                    )}
                                </div>

                                {/* 2. Dokumen Link dari Dosen */}
                                <div className="space-y-4">
                                    <h4 className="text-sm font-medium text-gray-900 border-b pb-2">Lampiran Bukti (Dari Dosen)</h4>
                                    
                                    {requiredDocs.map((doc, idx) => (
                                        <div key={idx} className="space-y-1.5">
                                            <Label className="text-xs text-gray-500 uppercase tracking-wider font-semibold">
                                                {idx + 2}. {doc.label}
                                            </Label>
                                            <div className="flex gap-2">
                                                <Input 
                                                    readOnly 
                                                    value={doc.link || '-'} 
                                                    className="bg-gray-50 text-xs h-9 font-mono text-gray-600" 
                                                    placeholder="Link tidak tersedia"
                                                />
                                                {doc.link && (
                                                    <a href={doc.link} target="_blank" rel="noreferrer">
                                                        <Button size="icon" variant="outline" className="h-9 w-9 shrink-0 border-blue-200 hover:bg-blue-50 text-blue-600">
                                                            <ExternalLink className="h-4 w-4" />
                                                        </Button>
                                                    </a>
                                                )}
                                            </div>
                                        </div>
                                    ))}
                                </div>

                            </CardContent>
                        </Card>
                    </div>

                    {/* PANEL KANAN: Informasi Penerima & Aksi */}
                    <div className="space-y-6">
                        <Card className="shadow-md border-t-4 border-t-emerald-600">
                            <CardHeader>
                                <CardTitle>Informasi Penerima</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-1">
                                    <Label className="text-xs text-gray-500 uppercase tracking-wider">Nama Dosen</Label>
                                    <p className="font-medium text-lg">{seminar.dosen?.user?.name}</p>
                                </div>
                                <div className="space-y-1">
                                    <Label className="text-xs text-gray-500 uppercase tracking-wider">NIDN</Label>
                                    <p className="font-mono text-gray-700">{seminar.dosen?.nidn || '-'}</p>
                                </div>
                                <div className="space-y-1">
                                    <Label className="text-xs text-gray-500 uppercase tracking-wider">Program Studi</Label>
                                    <p className="text-gray-700">{seminar.dosen?.prodi || '-'}</p>
                                </div>
                                
                                <div className="pt-6 border-t mt-4">
                                    <Label className="text-sm font-semibold mb-2 block">Status Pencairan</Label>
                                    
                                    {seminar.status_progress === 'menunggu_pembayaran' ? (
                                        <div className="space-y-3">
                                            <div className="bg-yellow-50 text-yellow-800 text-xs p-3 rounded border border-yellow-200">
                                                Dana belum dicairkan. Silakan proses transfer lalu unggah bukti.
                                            </div>
                                            <Link href={route('keuangan.pembayaran.upload', seminar.id)} className="block w-full">
                                                <Button className="w-full bg-black text-white hover:bg-gray-800 gap-2 h-12 text-base shadow-lg">
                                                    Proses Transfer Sekarang
                                                    <ArrowRight className="w-5 h-5" />
                                                </Button>
                                            </Link>
                                        </div>
                                    ) : (
                                        <div className="bg-green-50 text-green-800 p-4 rounded-lg border border-green-200 flex flex-col items-center justify-center gap-2">
                                            {/* Komponen CheckCircle2 sekarang sudah aman digunakan */}
                                            <CheckCircle2 className="h-8 w-8 text-green-600" />
                                            <span className="font-semibold">Dana Telah Dicairkan</span>
                                            {seminar.kewajiban_penelitian?.pembayaran?.tanggal_cair && (
                                                <span className="text-xs">
                                                    Tanggal: {seminar.kewajiban_penelitian.pembayaran.tanggal_cair}
                                                </span>
                                            )}
                                        </div>
                                    )}
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                </div>

            </div>
        </AppLayout>
    );
}