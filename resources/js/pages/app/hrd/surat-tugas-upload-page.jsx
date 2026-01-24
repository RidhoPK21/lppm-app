import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { route } from 'ziggy-js';
import { 
    UploadCloud, 
    CheckCircle2, 
    ArrowLeft,
    FileText,
    Calendar,
    MapPin,
    User
} from 'lucide-react';

export default function SuratTugasUploadPage({ seminar }) {
    
    const { data, setData, post, processing, errors } = useForm({
        file_surat: null,
        nomor_surat: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('hrd.surat-tugas.store', seminar.id));
    };

    return (
        <AppLayout breadcrumbs={[
            { title: 'Beranda', href: '/hrd/dashboard' },
            { title: 'Surat Tugas', href: '/hrd/surat-tugas' },
            { title: 'Upload', href: '#' }
        ]}>
            <Head title="Terbitkan Surat Tugas" />

            <div className="w-full max-w-5xl mx-auto px-6 py-8 pb-24 space-y-8">
                
                {/* Navigasi Balik */}
                <Link href={route('hrd.surat-tugas.index')} className="flex items-center text-sm text-gray-500 hover:text-gray-900 w-fit transition-colors">
                    <ArrowLeft className="w-4 h-4 mr-1" />
                    Kembali ke Daftar
                </Link>

                {/* Header Info */}
                <div className="flex flex-col gap-1">
                    <h1 className="text-2xl font-bold tracking-tight text-gray-900">
                        Penerbitan Surat Tugas
                    </h1>
                    <p className="text-gray-500">
                        Silakan lengkapi data surat tugas untuk kegiatan seminar berikut.
                    </p>
                </div>

                <div className="grid md:grid-cols-2 gap-6">
                    
                    {/* PANEL KIRI: Detail Kegiatan */}
                    <div className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-lg flex items-center gap-2">
                                    <FileText className="w-5 h-5 text-purple-600" />
                                    Informasi Kegiatan
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-6">
                                
                                <div className="space-y-1">
                                    <Label className="text-xs text-gray-500 uppercase tracking-wider font-semibold">Nama Dosen</Label>
                                    <div className="flex items-center gap-2">
                                        <User className="w-4 h-4 text-gray-400" />
                                        <p className="font-medium text-gray-900">{seminar.dosen?.user?.name}</p>
                                    </div>
                                </div>

                                <div className="space-y-1">
                                    <Label className="text-xs text-gray-500 uppercase tracking-wider font-semibold">Judul Makalah</Label>
                                    <p className="text-gray-700 leading-relaxed">{seminar.judul_makalah}</p>
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div className="space-y-1">
                                        <Label className="text-xs text-gray-500 uppercase tracking-wider font-semibold">Tanggal Mulai</Label>
                                        <div className="flex items-center gap-2">
                                            <Calendar className="w-4 h-4 text-gray-400" />
                                            <p className="text-gray-900">{new Date(seminar.tanggal_mulai).toLocaleDateString('id-ID')}</p>
                                        </div>
                                    </div>
                                    <div className="space-y-1">
                                        <Label className="text-xs text-gray-500 uppercase tracking-wider font-semibold">Lokasi</Label>
                                        <div className="flex items-center gap-2">
                                            <MapPin className="w-4 h-4 text-gray-400" />
                                            <p className="text-gray-900">{seminar.tempat_pelaksanaan}</p>
                                        </div>
                                    </div>
                                </div>

                            </CardContent>
                        </Card>
                    </div>

                    {/* PANEL KANAN: Form Upload */}
                    <div className="space-y-6">
                        <Card className="shadow-md border-t-4 border-t-purple-600">
                            <CardHeader>
                                <CardTitle>Upload Dokumen</CardTitle>
                            </CardHeader>
                            <CardContent className="p-6">
                                <form onSubmit={handleSubmit} className="space-y-6">
                                    
                                    {/* Input Nomor Surat */}
                                    <div className="space-y-2">
                                        <Label htmlFor="nomor_surat">Nomor Surat Tugas</Label>
                                        <Input 
                                            id="nomor_surat" 
                                            placeholder="Contoh: 094/ST/ITDel/XI/2025"
                                            value={data.nomor_surat}
                                            onChange={e => setData('nomor_surat', e.target.value)}
                                            className="font-medium"
                                        />
                                        {errors.nomor_surat && <p className="text-sm text-red-500">{errors.nomor_surat}</p>}
                                    </div>

                                    {/* Upload File */}
                                    <div className="space-y-3">
                                        <Label className="text-base font-semibold text-gray-700">
                                            File Surat Tugas (PDF) <span className="text-red-500">*</span>
                                        </Label>
                                        
                                        <div className="border-2 border-dashed border-gray-300 rounded-xl p-8 flex flex-col items-center justify-center text-center hover:bg-gray-50 transition-colors cursor-pointer relative">
                                            <Input 
                                                type="file" 
                                                accept=".pdf"
                                                className="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                                onChange={e => setData('file_surat', e.target.files[0])} 
                                            />
                                            <div className="bg-purple-100 p-3 rounded-full mb-3">
                                                <UploadCloud className="h-6 w-6 text-purple-600" />
                                            </div>
                                            <span className="text-sm font-medium text-gray-900">
                                                {data.file_surat ? data.file_surat.name : "Klik untuk memilih file PDF"}
                                            </span>
                                            <span className="text-xs text-gray-500 mt-1">
                                                Maksimal 5MB
                                            </span>
                                        </div>
                                        {errors.file_surat && (
                                            <p className="text-sm text-red-500 font-medium">{errors.file_surat}</p>
                                        )}
                                    </div>

                                    <Button 
                                        type="submit" 
                                        disabled={processing} 
                                        className="w-full bg-purple-600 hover:bg-purple-700 text-white h-12 gap-2 mt-2"
                                    >
                                        {processing ? 'Menyimpan...' : 'Terbitkan Surat Tugas'}
                                        <CheckCircle2 className="w-4 h-4" />
                                    </Button>

                                </form>
                            </CardContent>
                        </Card>
                    </div>

                </div>

            </div>
        </AppLayout>
    );
}