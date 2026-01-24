import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
// Import Komponen Dialog
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";
import { route } from 'ziggy-js';
import { 
    FileText, 
    CheckCircle2, 
    XCircle, 
    Copy, 
    ExternalLink,
    User,
    Calendar,
} from 'lucide-react';

export default function DetailVerifikasi({ seminar }) {
    
    const { post, processing } = useForm();
    
    // State untuk kontrol Modal
    const [isApproveOpen, setIsApproveOpen] = useState(false);

    const artifacts = [
        "Bukti review oleh sesama dosen",
        "Bukti submit paper ke penyelenggara",
        "Bukti makalah diterima (LoA)",
        "Catatan Reviewer panitia",
        "Bukti registrasi keikutsertaan mengikuti seminar",
        "Softcopy versi final paper"
    ];

    const artefakLinks = seminar.kewajiban_penelitian?.link_artefak || {};

    // Ganti confirm() dengan membuka Dialog
    const openApproveDialog = () => {
        setIsApproveOpen(true);
    };

    // Eksekusi Post saat tombol "Ya, Setuju" di Dialog ditekan
    const confirmApprove = () => {
        post(route('kprodi.verifikasi.approve', seminar.id), {
            onSuccess: () => setIsApproveOpen(false),
        });
    };

    const copyToClipboard = (text) => {
        if (!text) return;
        navigator.clipboard.writeText(text);
    };

    return (
        <AppLayout breadcrumbs={[
            { title: 'Beranda', href: '/kprodi/dashboard' },
            { title: 'Verifikasi', href: '/kprodi/verifikasi' },
            { title: 'Detail Dokumen', href: '#' }
        ]}>
            <Head title="Verifikasi Dokumen" />

            <div className="w-full max-w-6xl mx-auto px-6 py-8 pb-24 space-y-8">
                
                {/* Header Informasi */}
                <div className="flex flex-col gap-1">
                    <h1 className="text-2xl font-bold tracking-tight text-gray-900">
                        Verifikasi Dokumen & Artefak
                    </h1>
                    <div className="flex items-center gap-3 text-sm text-gray-500">
                        <span className="flex items-center gap-1">
                            <User className="w-4 h-4" />
                            {seminar.dosen?.user?.name || 'Nama Dosen'}
                        </span>
                        <span>|</span>
                        <span className="flex items-center gap-1">
                            <Calendar className="w-4 h-4" />
                            {new Date(seminar.created_at).toLocaleDateString('id-ID')}
                        </span>
                    </div>
                </div>

                {/* Form Read-Only */}
                <div className="bg-white border rounded-xl p-8 shadow-sm space-y-8">
                    <div className="flex items-center gap-3 pb-4 border-b">
                        <div className="h-10 w-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                            <FileText className="h-5 w-5" />
                        </div>
                        <div>
                            <h3 className="font-semibold text-lg text-gray-900">Kelengkapan Berkas</h3>
                            <p className="text-sm text-gray-500">Periksa tautan dokumen yang dikirimkan dosen.</p>
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
                    <Link href={route('kprodi.verifikasi.tolak', seminar.id)}>
                        <Button 
                            variant="outline" 
                            className="border-red-200 text-red-600 hover:bg-red-50 hover:text-red-700 px-6 gap-2"
                        >
                            <XCircle className="w-4 h-4" />
                            Tolak Pendanaan
                        </Button>
                    </Link>

                    {/* Button Memicu Dialog */}
                    <Button 
                        onClick={openApproveDialog} 
                        disabled={processing}
                        className="bg-green-600 hover:bg-green-700 text-white px-6 gap-2"
                    >
                        <CheckCircle2 className="w-4 h-4" />
                        Setujui Pendanaan
                    </Button>
                </div>

            </div>

            {/* DIALOG KONFIRMASI (POP-UP) */}
            <Dialog open={isApproveOpen} onOpenChange={setIsApproveOpen}>
                <DialogContent className="sm:max-w-md">
                    <DialogHeader>
                        <div className="mx-auto bg-green-100 h-12 w-12 rounded-full flex items-center justify-center mb-4">
                            <CheckCircle2 className="h-6 w-6 text-green-600" />
                        </div>
                        <DialogTitle className="text-center">Setujui Pengajuan Dana?</DialogTitle>
                        <DialogDescription className="text-center">
                            Apakah Anda yakin dokumen ini valid dan menyetujui pendanaan? <br/>
                            Status akan diteruskan ke <strong>LPPM</strong>.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter className="sm:justify-center gap-2 mt-4">
                        <Button
                            type="button"
                            variant="secondary"
                            onClick={() => setIsApproveOpen(false)}
                        >
                            Batal
                        </Button>
                        <Button
                            type="button"
                            className="bg-green-600 hover:bg-green-700 text-white"
                            onClick={confirmApprove}
                            disabled={processing}
                        >
                            {processing ? 'Memproses...' : 'Ya, Setujui'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

        </AppLayout>
    );
}