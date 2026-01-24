import React from 'react';
import { Head, useForm, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import SeminarStepper from '@/components/seminar-stepper';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { route } from 'ziggy-js';
import { AlertCircle } from 'lucide-react';

export default function Step1Data({ seminar }) {
    
    // 1. Ambil Estimasi Max Dana dari Database (Hasil hitungan Registrasi Awal)
    // Cek di dalam struktur JSON kewajiban_penelitian
    const maxDana = seminar.kewajiban_penelitian?.estimasi_max_dana || 0;
    const kategoriLuaran = seminar.kewajiban_penelitian?.kategori_luaran || 'Belum Ditentukan';

    // Helper Format Rupiah
    const formatRupiah = (number) => {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(number);
    };

    const { data, setData, post, processing, errors } = useForm({
        // Data Utama
        judul_makalah: seminar.judul_makalah || "",
        nama_forum: seminar.nama_forum === '-' ? "" : seminar.nama_forum,
        institusi_penyelenggara: seminar.institusi_penyelenggara === '-' ? "" : seminar.institusi_penyelenggara,
        
        // Tanggal
        tanggal_mulai: seminar.tanggal_mulai ? seminar.tanggal_mulai.split('T')[0] : '', 
        tanggal_selesai: seminar.tanggal_selesai ? seminar.tanggal_selesai.split('T')[0] : '',
        
        tempat_pelaksanaan: seminar.tempat_pelaksanaan === '-' ? "" : seminar.tempat_pelaksanaan,
        mesin_pengindex: seminar.mesin_pengindex || "",
        
        biaya_registrasi: seminar.biaya_registrasi == 0 ? "" : seminar.biaya_registrasi,
        
        website_penyelenggara: seminar.website_penyelenggara || "",
        
        // Kategori Luaran tidak lagi ada di sini!
    });

    const handleChange = (field, value) => {
        setData(field, value);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        // Kirim data update ke Controller
        post(route('dosen.seminar.step1.update', seminar.id));
    };

    // Logic Cek Over Budget
    const isOverBudget = data.biaya_registrasi > maxDana && maxDana > 0;

    return (
        <AppLayout breadcrumbs={[
            { title: 'Beranda', href: '/dosen/home' },
            { title: 'Registrasi Seminar', href: '/dosen/seminar' },
            { title: 'Tahap 1', href: '#' }
        ]}>
            <Head title="Submit Data Seminar" />

            <div className="w-full max-w-6xl mx-auto px-6 py-6 pb-24">
                
                <div className="text-center mb-8">
                    <h1 className="text-2xl font-bold">Pengajuan Dana Registrasi Seminar</h1>
                </div>

                <SeminarStepper currentStep={1} seminarId={seminar.id} />

                <form onSubmit={handleSubmit} className="mt-10 space-y-8">
                    <h2 className="text-2xl font-bold">Lengkapi Data</h2>

                    <div className="space-y-5">
                        
                        {/* Judul Makalah */}
                        <div className="grid grid-cols-1 md:grid-cols-4 items-center gap-4">
                            <Label className="md:text-left font-medium text-gray-700">Judul Makalah</Label>
                            <div className="md:col-span-3">
                                <Input 
                                    value={data.judul_makalah}
                                    onChange={e => handleChange('judul_makalah', e.target.value)}
                                    className="bg-white" 
                                />
                                {errors.judul_makalah && <p className="text-red-500 text-xs mt-1">{errors.judul_makalah}</p>}
                            </div>
                        </div>

                        {/* Nama Forum */}
                        <div className="grid grid-cols-1 md:grid-cols-4 items-center gap-4">
                            <Label className="md:text-left font-medium text-gray-700">Nama Forum</Label>
                            <div className="md:col-span-3">
                                <Input 
                                    value={data.nama_forum}
                                    onChange={e => handleChange('nama_forum', e.target.value)}
                                    className="bg-white" 
                                />
                            </div>
                        </div>

                        {/* Institusi Penyelenggara */}
                        <div className="grid grid-cols-1 md:grid-cols-4 items-center gap-4">
                            <Label className="md:text-left font-medium text-gray-700">Institusi Penyelenggara</Label>
                            <div className="md:col-span-3">
                                <Input 
                                    value={data.institusi_penyelenggara}
                                    onChange={e => handleChange('institusi_penyelenggara', e.target.value)}
                                    className="bg-white" 
                                />
                            </div>
                        </div>

                        {/* Waktu Pelaksanaan */}
                        <div className="grid grid-cols-1 md:grid-cols-4 items-center gap-4">
                            <Label className="md:text-left font-medium text-gray-700">Waktu Pelaksanaan</Label>
                            <div className="md:col-span-3 flex flex-col sm:flex-row items-center gap-3">
                                <div className="relative w-full">
                                    <Input 
                                        type="date" 
                                        value={data.tanggal_mulai}
                                        onChange={e => handleChange('tanggal_mulai', e.target.value)}
                                        className="bg-white w-full" 
                                    />
                                </div>
                                <span className="text-sm font-bold whitespace-nowrap">sampai dengan</span>
                                <div className="relative w-full">
                                    <Input 
                                        type="date" 
                                        value={data.tanggal_selesai}
                                        onChange={e => handleChange('tanggal_selesai', e.target.value)}
                                        className="bg-white w-full" 
                                    />
                                </div>
                            </div>
                        </div>

                        {/* Tempat Pelaksanaan */}
                        <div className="grid grid-cols-1 md:grid-cols-4 items-center gap-4">
                            <Label className="md:text-left font-medium text-gray-700">Tempat Pelaksanaan</Label>
                            <div className="md:col-span-3">
                                <Input 
                                    value={data.tempat_pelaksanaan}
                                    onChange={e => handleChange('tempat_pelaksanaan', e.target.value)}
                                    className="bg-white" 
                                />
                            </div>
                        </div>

                         {/* Mesin Pengindex */}
                         <div className="grid grid-cols-1 md:grid-cols-4 items-center gap-4">
                            <Label className="md:text-left font-medium text-gray-700">Mesin Pengindex</Label>
                            <div className="md:col-span-3">
                                <Input 
                                    value={data.mesin_pengindex}
                                    onChange={e => handleChange('mesin_pengindex', e.target.value)}
                                    className="bg-white" 
                                />
                            </div>
                        </div>

                        {/* Biaya Registrasi (DENGAN LOGIKA ESTIMASI) */}
                        <div className="grid grid-cols-1 md:grid-cols-4 items-start gap-4">
                            <Label className="md:text-left font-medium text-gray-700 pt-3">Biaya Registrasi</Label>
                            <div className="md:col-span-3 space-y-1">
                                <Input 
                                    type="number"
                                    value={data.biaya_registrasi}
                                    onChange={e => handleChange('biaya_registrasi', e.target.value)}
                                    className={`bg-white ${isOverBudget ? 'border-red-500 focus:ring-red-500' : ''}`}
                                />
                                {/* Teks Helper Dinamis (Mengambil Max Dana) */}
                                <div className={`text-[11px] mt-1 flex items-start gap-1 ${isOverBudget ? 'text-red-600 font-medium' : 'text-gray-500'}`}>
                                    {isOverBudget && <AlertCircle className="w-3 h-3 mt-0.5" />}
                                    <p>
                                        [Kriteria: **{kategoriLuaran}**]. Biaya maksimum yang dapat diajukan adalah <strong>{formatRupiah(maxDana)}</strong>.
                                        {isOverBudget && " (Nominal yang Anda ajukan melebihi batas maksimal)"}
                                    </p>
                                </div>
                            </div>
                        </div>

                         {/* --- WEBSITE PENYELENGGARA --- */}
                        <div className="space-y-2">
                            <Label htmlFor="website_penyelenggara">Website Penyelenggara</Label>
                            <Input
                                id="website_penyelenggara"
                                placeholder="https://www.example.com"
                                value={data.website_penyelenggara || ''}
                                onChange={(e) => setData('website_penyelenggara', e.target.value)}
                                // Tambahkan styling border merah jika ada error
                                className={errors.website_penyelenggara ? "border-red-500 focus-visible:ring-red-500" : ""}
                            />
                            
                            {/* Tampilkan Pesan Error dari Server */}
                            {errors.website_penyelenggara && (
                                <p className="text-sm text-red-500 font-medium">
                                    {errors.website_penyelenggara}
                                </p>
                            )}
                            
                            {/* Helper Text (Petunjuk) */}
                            {!errors.website_penyelenggara && (
                                <p className="text-xs text-gray-500">
                                    Wajib diawali dengan <strong>http://</strong> atau <strong>https://</strong>
                                </p>
                            )}
                        </div>
                        
                        {/* Kategori Luaran Dihapus dari sini */}
                    </div>

                    {/* Tombol Submit */}
                    <div className="flex justify-end pt-8 gap-4">
                        <Button asChild variant="outline" type="button">
                            <Link href={route('dosen.seminar.index')}>
                                Kembali
                            </Link>
                        </Button>

                        <Button 
                            type="submit" 
                            disabled={processing}
                            className="bg-black text-white hover:bg-gray-800 px-8 min-w-[100px]"
                        >
                            {processing ? 'Menyimpan...' : (
                                <span className="flex items-center">
                                    Simpan & Lanjut 
                                </span>
                            )}
                        </Button>
                    </div>

                </form>
            </div>  
        </AppLayout>
    );
}