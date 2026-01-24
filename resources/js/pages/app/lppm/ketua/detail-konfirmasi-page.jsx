import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { route } from 'ziggy-js';
import { 
    FileText, 
    CheckCircle2, 
    XCircle, 
    Copy, 
    ExternalLink,
    User,
    Calendar,
    ArrowLeft,
    Download // 🔥 Tambahan Icon Download
} from 'lucide-react';

export default function DetailKonfirmasiPage({ seminar }) {
    
    // Daftar Artefak (Sama dengan Dosen & Kaprodi)
    const artifacts = [
        "Bukti review oleh sesama dosen",
        "Bukti submit paper ke penyelenggara",
        "Bukti makalah diterima (LoA)",
        "Catatan Reviewer panitia",
        "Bukti registrasi keikutsertaan mengikuti seminar",
        "Softcopy versi final paper"
    ];

    // Ambil Data Link
    const artefakLinks = seminar.kewajiban_penelitian?.link_artefak || {};

    const copyToClipboard = (text) => {
        if (!text) return;
        navigator.clipboard.writeText(text);
        // alert('Link disalin'); // Opsional: Tambahkan toast
    };

    return (
        <AppLayout breadcrumbs={[
            { title: 'Beranda', href: '/lppm/ketua/home' },
            { title: 'Persetujuan Dana', href: '/lppm/ketua/pengajuan-dana' },
            { title: 'Detail', href: '#' }
        ]}>
            <Head title="Detail Pengajuan" />

            <div className="w-full max-w-6xl mx-auto px-6 py-8 pb-24 space-y-8">
                
                {/* Navigasi Balik */}
                <Link href={route('lppm.ketua.pengajuan-dana')} className="flex items-center text-sm text-gray-500 hover:text-gray-900 w-fit">
                    <ArrowLeft className="w-4 h-4 mr-1" />
                    Kembali
                </Link>

                {/* Header Info */}
                <div className="flex flex-col gap-1">
                    <h1 className="text-2xl font-bold tracking-tight text-gray-900">
                        Verifikasi Kelengkapan Dokumen
                    </h1>
                    <div className="flex items-center gap-3 text-sm text-gray-500">
                        <span className="flex items-center gap-1 font-medium text-gray-900">
                            {seminar.judul_makalah}
                        </span>
                    </div>
                    <div className="flex items-center gap-3 text-sm text-gray-500 mt-1">
                        <span className="flex items-center gap-1">
                            <User className="w-3.5 h-3.5" />
                            {seminar.dosen?.user?.name || 'Nama Dosen'}
                        </span>
                        <span>|</span>
                        <span className="flex items-center gap-1">
                            <Calendar className="w-3.5 h-3.5" />
                            {new Date(seminar.created_at).toLocaleDateString('id-ID')}
                        </span>
                    </div>
                </div>

                {/* ========================================================= */}
                {/* 🔥 BAGIAN BARU: DOWNLOAD FORM 3 (PERMOHONAN DANA) 🔥 */}
                {/* ========================================================= */}
                <div className="bg-blue-50/50 border border-blue-100 rounded-xl p-6">
                    <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        
                        <div className="flex items-start gap-4">
                            <div className="bg-white p-3 rounded-lg border border-blue-100 text-blue-600 shadow-sm">
                                <FileText className="w-6 h-6" />
                            </div>
                            <div>
                                <h3 className="font-semibold text-gray-900">Formulir 3 (Permohonan Dana)</h3>
                                <p className="text-sm text-gray-500 mt-1">
                                    Dokumen rincian biaya registrasi seminar dan data dosen pengaju. 
                                    <br className="hidden md:block"/>Silakan unduh untuk melakukan verifikasi nilai dana.
                                </p>
                            </div>
                        </div>

                        <div className="shrink-0">
                            <a 
                                href={route('lppm.ketua.pengajuan-dana.download-f3', seminar.id)} 
                                target="_blank" 
                                rel="noreferrer"
                            >
                                <Button variant="outline" className="bg-white border-blue-200 text-blue-700 hover:bg-blue-50 hover:text-blue-800 gap-2 h-10 shadow-sm transition-all">
                                    <Download className="w-4 h-4" />
                                    Download PDF
                                </Button>
                            </a>
                        </div>

                    </div>
                </div>
                {/* ========================================================= */}


                {/* Form Read-Only (Artefak Links) */}
                <div className="bg-white border rounded-xl p-8 shadow-sm space-y-8">
                    <div className="flex items-center gap-3 pb-4 border-b">
                        <div className="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600">
                            <CheckCircle2 className="h-5 w-5" />
                        </div>
                        <div>
                            <h3 className="font-semibold text-lg text-gray-900">Ceklis Artefak</h3>
                            <p className="text-sm text-gray-500">Silakan periksa validitas dokumen bukti berikut.</p>
                        </div>
                    </div>

                    <div className="grid gap-6">
                        {artifacts.map((label, index) => {
                            const linkValue = artefakLinks[index] || '';

                            return (
                                <div key={index} className="space-y-2">
                                    <Label className="text-sm font-medium text-gray-700">
                                        {label}
                                    </Label>
                                    <div className="flex gap-2">
                                        <Input 
                                            readOnly
                                            value={linkValue}
                                            className="bg-gray-50 text-gray-600 font-mono text-xs h-10"
                                            placeholder="-(Tidak ada link)-"
                                        />
                                        <Button 
                                            variant="outline" 
                                            size="icon"
                                            onClick={() => copyToClipboard(linkValue)}
                                            disabled={!linkValue}
                                            title="Salin Link"
                                            className="shrink-0"
                                        >
                                            <Copy className="h-4 w-4 text-gray-500" />
                                        </Button>
                                        {linkValue && (
                                            <a href={linkValue} target="_blank" rel="noreferrer">
                                                <Button variant="outline" size="icon" className="shrink-0 border-blue-200 bg-blue-50 hover:bg-blue-100">
                                                    <ExternalLink className="h-4 w-4 text-blue-600" />
                                                </Button>
                                            </a>
                                        )}
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div>

                {/* Footer Actions */}
                <div className="flex justify-end gap-4 pt-4 border-t">
                    
                    {/* Tombol Tolak -> Lanjut ke Halaman Alasan */}
                    <Link href={route('lppm.ketua.pengajuan-dana.tolak', seminar.id)}>
                        <Button 
                            variant="outline" 
                            className="border-red-200 text-red-600 hover:bg-red-50 hover:text-red-700 px-6 gap-2 h-11"
                        >
                            <XCircle className="w-4 h-4" />
                            Tolak / Minta Revisi
                        </Button>
                    </Link>

                    {/* Tombol Setuju -> Lanjut ke Upload Surat */}
                    <Link href={route('lppm.ketua.pengajuan-dana.upload', seminar.id)}>
                        <Button className="bg-green-600 hover:bg-green-700 text-white px-6 gap-2 h-11">
                            <CheckCircle2 className="w-4 h-4" />
                            Setujui & Buat Surat
                        </Button>
                    </Link>

                </div>

            </div>
        </AppLayout>
    );
}