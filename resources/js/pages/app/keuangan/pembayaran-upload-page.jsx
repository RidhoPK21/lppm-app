import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent } from '@/components/ui/card';
import { route } from 'ziggy-js';
import { 
    UploadCloud, 
    CheckCircle2, 
    ArrowLeft,
    CreditCard
} from 'lucide-react';

export default function PembayaranUploadPage({ seminar }) {
    
    const { data, setData, post, processing, errors } = useForm({
        bukti_transfer: null,
        jumlah_cair: '',
        tanggal_cair: new Date().toISOString().split('T')[0], // Default hari ini
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('keuangan.pembayaran.store-upload', seminar.id));
    };

    return (
        <AppLayout breadcrumbs={[
            { title: 'Beranda', href: '/keuangan/home' },
            { title: 'Pencairan Dana', href: '/keuangan/pembayaran' },
            { title: 'Upload Bukti', href: '#' }
        ]}>
            <Head title="Konfirmasi Transfer" />

            <div className="w-full max-w-2xl mx-auto px-6 py-10 space-y-8">
                
                {/* Header Nav */}
                <Link href={route('keuangan.pembayaran.detail', seminar.id)} className="flex items-center text-sm text-gray-500 hover:text-gray-900 transition-colors">
                    <ArrowLeft className="w-4 h-4 mr-1" />
                    Kembali
                </Link>

                <div className="space-y-2">
                    <h1 className="text-2xl font-bold tracking-tight text-emerald-700 flex items-center gap-2">
                        <CreditCard className="h-6 w-6" />
                        Konfirmasi Transfer Dana
                    </h1>
                    <p className="text-gray-600">
                        Pastikan Anda telah melakukan transfer ke rekening dosen sebelum mengisi form ini.
                    </p>
                </div>

                <Card className="border-emerald-100 shadow-sm">
                    <CardContent className="p-8">
                        <form onSubmit={handleSubmit} className="space-y-6">
                            
                            {/* Input Nominal */}
                            <div className="space-y-2">
                                <Label htmlFor="jumlah_cair">Nominal yang Ditransfer (Rp)</Label>
                                <Input 
                                    id="jumlah_cair" 
                                    type="number" 
                                    placeholder="Contoh: 1500000"
                                    value={data.jumlah_cair}
                                    onChange={e => setData('jumlah_cair', e.target.value)}
                                    className="text-lg font-semibold"
                                />
                                {errors.jumlah_cair && <p className="text-sm text-red-500">{errors.jumlah_cair}</p>}
                            </div>

                            {/* Input Tanggal */}
                            <div className="space-y-2">
                                <Label htmlFor="tanggal_cair">Tanggal Transaksi</Label>
                                <Input 
                                    id="tanggal_cair" 
                                    type="date" 
                                    value={data.tanggal_cair}
                                    onChange={e => setData('tanggal_cair', e.target.value)}
                                />
                                {errors.tanggal_cair && <p className="text-sm text-red-500">{errors.tanggal_cair}</p>}
                            </div>

                            {/* Upload Bukti */}
                            <div className="space-y-3 pt-2">
                                <Label className="text-base font-semibold text-gray-700">
                                    Upload Bukti Transfer <span className="text-red-500">*</span>
                                </Label>
                                
                                <div className="border-2 border-dashed border-gray-300 rounded-xl p-8 flex flex-col items-center justify-center text-center hover:bg-gray-50 transition-colors cursor-pointer relative">
                                    <Input 
                                        type="file" 
                                        accept="image/*,.pdf"
                                        className="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                        onChange={e => setData('bukti_transfer', e.target.files[0])} 
                                    />
                                    <div className="bg-emerald-100 p-3 rounded-full mb-3">
                                        <UploadCloud className="h-6 w-6 text-emerald-600" />
                                    </div>
                                    <span className="text-sm font-medium text-gray-900">
                                        {data.bukti_transfer ? data.bukti_transfer.name : "Klik untuk memilih file bukti"}
                                    </span>
                                    <span className="text-xs text-gray-500 mt-1">
                                        Format: JPG, PNG, PDF (Maks. 5MB)
                                    </span>
                                </div>
                                {errors.bukti_transfer && (
                                    <p className="text-sm text-red-500 font-medium">{errors.bukti_transfer}</p>
                                )}
                            </div>

                            <Button 
                                type="submit" 
                                disabled={processing} 
                                className="w-full bg-emerald-600 hover:bg-emerald-700 text-white h-12 gap-2 mt-4"
                            >
                                {processing ? 'Menyimpan...' : 'Simpan & Selesaikan'}
                                <CheckCircle2 className="w-4 h-4" />
                            </Button>

                        </form>
                    </CardContent>
                </Card>

            </div>
        </AppLayout>
    );
}