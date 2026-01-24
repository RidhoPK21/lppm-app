import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent } from '@/components/ui/card';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { route } from 'ziggy-js';
import { 
    UploadCloud, 
    CheckCircle2, 
    ArrowLeft,
    Info
} from 'lucide-react';

export default function PengajuanDanaUploadPage({ seminar }) {
    
    const { data, setData, post, processing, errors } = useForm({
        file_surat: null,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('lppm.ketua.pengajuan-dana.store-upload', seminar.id));
    };

    return (
        <AppLayout breadcrumbs={[
            { title: 'Beranda', href: '/lppm/ketua/home' },
            { title: 'Persetujuan Dana', href: '/lppm/ketua/pengajuan-dana' },
            { title: 'Upload Surat', href: '#' }
        ]}>
            <Head title="Upload Surat Permohonan" />

            <div className="w-full max-w-2xl mx-auto px-6 py-10 space-y-8">
                
                {/* Header Nav */}
                <Link href={route('lppm.ketua.pengajuan-dana.konfirmasi', seminar.id)} className="flex items-center text-sm text-gray-500 hover:text-gray-900 transition-colors">
                    <ArrowLeft className="w-4 h-4 mr-1" />
                    Kembali ke Detail
                </Link>

                <div className="space-y-2">
                    <h1 className="text-2xl font-bold tracking-tight text-gray-900">
                        Terbitkan Surat Permohonan Dana
                    </h1>
                    <p className="text-gray-500">
                        Untuk pengajuan: <span className="font-medium text-gray-900">{seminar.judul_makalah}</span>
                    </p>
                </div>

                <Card className="border-green-100 shadow-sm">
                    <CardContent className="p-6 space-y-6">
                        
                        <Alert className="bg-green-50 border-green-200 text-green-800">
                            <Info className="h-5 w-5" />
                            <AlertTitle className="ml-2 font-semibold">Langkah Terakhir</AlertTitle>
                            <AlertDescription className="ml-2 mt-1 text-sm">
                                Silakan unggah <strong>Surat Pengantar/Permohonan Dana</strong> yang telah ditandatangani Ketua LPPM. Dokumen ini akan menjadi dasar bagi Keuangan untuk mencairkan dana.
                            </AlertDescription>
                        </Alert>

                        <form onSubmit={handleSubmit} className="space-y-6">
                            
                            <div className="space-y-3">
                                <Label htmlFor="file_surat" className="text-base font-semibold text-gray-700">
                                    Upload File PDF <span className="text-red-500">*</span>
                                </Label>
                                
                                <div className="border-2 border-dashed border-gray-300 rounded-xl p-8 flex flex-col items-center justify-center text-center hover:bg-gray-50 transition-colors cursor-pointer relative">
                                    <Input 
                                        id="file_surat" 
                                        type="file" 
                                        accept=".pdf"
                                        className="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                        onChange={e => setData('file_surat', e.target.files[0])} 
                                    />
                                    <div className="bg-green-100 p-3 rounded-full mb-3">
                                        <UploadCloud className="h-6 w-6 text-green-600" />
                                    </div>
                                    <span className="text-sm font-medium text-gray-900">
                                        {data.file_surat ? data.file_surat.name : "Klik untuk memilih file"}
                                    </span>
                                    <span className="text-xs text-gray-500 mt-1">
                                        Format PDF (Maks. 5MB)
                                    </span>
                                </div>
                                {errors.file_surat && (
                                    <p className="text-sm text-red-500 font-medium">{errors.file_surat}</p>
                                )}
                            </div>

                            <Button 
                                type="submit" 
                                disabled={processing} 
                                className="w-full bg-green-600 hover:bg-green-700 text-white h-11 gap-2"
                            >
                                {processing ? 'Mengunggah...' : 'Kirim ke Keuangan'}
                                <CheckCircle2 className="w-4 h-4" />
                            </Button>

                        </form>
                    </CardContent>
                </Card>

            </div>
        </AppLayout>
    );
}