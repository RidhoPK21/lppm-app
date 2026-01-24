import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent } from '@/components/ui/card';
// Import Komponen Dialog (Pop-up Keren)
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
    AlertTriangle, 
    ArrowLeft,
    Send,
    XCircle
} from 'lucide-react';

export default function PengajuanDanaTolakPage({ seminar }) {
    
    // State untuk kontrol Pop-up
    const [isConfirmOpen, setIsConfirmOpen] = useState(false);

    const { data, setData, post, processing, errors } = useForm({
        alasan: '',
    });

    // 1. Saat tombol "Kirim" ditekan, BUKA POP-UP dulu (jangan langsung kirim)
    const handleSubmit = (e) => {
        e.preventDefault();
        setIsConfirmOpen(true);
    };

    // 2. Eksekusi Kirim Data hanya jika tombol "Ya" di Pop-up ditekan
    const confirmReject = () => {
        post(route('lppm.ketua.pengajuan-dana.store-tolak', seminar.id), {
            onSuccess: () => setIsConfirmOpen(false),
        });
    };

    return (
        <AppLayout breadcrumbs={[
            { title: 'Beranda', href: '/lppm/ketua/home' },
            { title: 'Persetujuan Dana', href: '/lppm/ketua/pengajuan-dana' },
            { title: 'Tolak Pengajuan', href: '#' }
        ]}>
            <Head title="Tolak Pengajuan" />

            <div className="w-full max-w-2xl mx-auto px-6 py-10 space-y-8">
                
                {/* Header Nav */}
                <Link href={route('lppm.ketua.pengajuan-dana.konfirmasi', seminar.id)} className="flex items-center text-sm text-gray-500 hover:text-gray-900 transition-colors">
                    <ArrowLeft className="w-4 h-4 mr-1" />
                    Batal & Kembali
                </Link>

                <div className="space-y-2">
                    <h1 className="text-2xl font-bold tracking-tight text-red-600 flex items-center gap-2">
                        <AlertTriangle className="h-6 w-6" />
                        Pengembalian Pengajuan
                    </h1>
                    <p className="text-gray-600">
                        Anda akan mengembalikan pengajuan <strong>{seminar.judul_makalah}</strong> ke Dosen untuk diperbaiki.
                    </p>
                </div>

                <Card className="border-red-100 shadow-sm">
                    <CardContent className="p-6">
                        <form onSubmit={handleSubmit} className="space-y-6">
                            
                            <div className="space-y-3">
                                <Label htmlFor="alasan" className="text-base font-semibold text-gray-700">
                                    Alasan Penolakan / Catatan Revisi <span className="text-red-500">*</span>
                                </Label>
                                <Textarea 
                                    id="alasan"
                                    placeholder="Contoh: Link Google Drive tidak bisa diakses, atau Dokumen LoA buram..."
                                    className="min-h-[150px] bg-red-50/10 focus:ring-red-500 border-red-200 text-gray-800 placeholder:text-gray-400"
                                    value={data.alasan}
                                    onChange={e => setData('alasan', e.target.value)}
                                />
                                {errors.alasan && (
                                    <p className="text-sm text-red-500 font-medium animate-pulse">{errors.alasan}</p>
                                )}
                                <p className="text-xs text-gray-500">
                                    Catatan ini akan muncul di dashboard Dosen agar mereka dapat segera melakukan perbaikan.
                                </p>
                            </div>

                            <Button 
                                type="submit" 
                                variant="destructive"
                                disabled={processing} 
                                className="w-full h-11 gap-2 font-medium"
                            >
                                {processing ? 'Memproses...' : 'Kirim Catatan Penolakan'}
                                <Send className="w-4 h-4" />
                            </Button>

                        </form>
                    </CardContent>
                </Card>

            </div>

            {/* --- MODAL DIALOG KONFIRMASI --- */}
            <Dialog open={isConfirmOpen} onOpenChange={setIsConfirmOpen}>
                <DialogContent className="sm:max-w-md">
                    <DialogHeader>
                        <div className="mx-auto bg-red-100 h-12 w-12 rounded-full flex items-center justify-center mb-4">
                            <XCircle className="h-6 w-6 text-red-600" />
                        </div>
                        <DialogTitle className="text-center text-red-700">Konfirmasi Penolakan</DialogTitle>
                        <DialogDescription className="text-center">
                            Apakah Anda yakin ingin mengembalikan pengajuan ini ke Dosen? <br/>
                            Status akan berubah kembali menjadi <strong>&quot;Revisi&quot;</strong>.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter className="sm:justify-center gap-3 mt-4">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => setIsConfirmOpen(false)}
                            className="flex-1"
                        >
                            Batal
                        </Button>
                        <Button
                            type="button"
                            variant="destructive"
                            onClick={confirmReject}
                            disabled={processing}
                            className="flex-1 bg-red-600 hover:bg-red-700"
                        >
                            {processing ? 'Mengirim...' : 'Ya, Kembalikan'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

        </AppLayout>
    );
}