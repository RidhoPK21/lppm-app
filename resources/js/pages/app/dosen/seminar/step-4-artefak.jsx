import React from 'react';
import { Head, useForm, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import SeminarStepper from '@/components/seminar-stepper';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { route } from 'ziggy-js';
import { CheckCircle2 } from 'lucide-react';

export default function Step4Artefak({ seminar }) {
    
    // Daftar Label Artefak
    const artifacts = [
        "Bukti review oleh sesama dosen",
        "Bukti submit paper ke penyelenggara",
        "Bukti makalah diterima (LoA)",
        "Catatan Reviewer panitia",
        "Bukti registrasi keikutsertaan mengikuti seminar",
        "Softcopy versi final paper"
    ];

    // 1. Inisialisasi State Form dengan Pengecekan Aman (Defensive Coding)
    // Pastikan defaultnya adalah Object kosong {} atau Array [] agar tidak undefined
    const initialLinks = seminar.kewajiban_penelitian?.link_artefak || {};
    
    const { data, setData, post, processing, errors } = useForm({
        artefak_links: initialLinks || {}, // Paksa jadi object kosong jika null/undefined
    });

    // Handle Perubahan Input
    const handleInputChange = (index, value) => {
        setData('artefak_links', {
            ...data.artefak_links, // Copy state lama
            [index]: value         // Update index spesifik
        });
    };

    // Handle Submit
    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('dosen.seminar.step4.store', seminar.id));
    };

    // Cek Status Read Only
    const isReadOnly = ['menunggu_persetujuan_kprodi', 'menunggu_konfirmasi_lppm', 'menunggu_pembayaran', 'selesai', 'dana_dicairkan'].includes(seminar.status_progress);

    return (
        <AppLayout breadcrumbs={[
            { title: 'Beranda', href: '/dosen/home' },
            { title: 'Registrasi Seminar', href: '/dosen/seminar' },
            { title: 'Tahap 4', href: '#' }
        ]}>
            <Head title="Submit Artefak" />

            <div className="w-full max-w-6xl mx-auto px-6 py-6 pb-24">
                
                {/* Header Judul */}
                <div className="text-center mb-8">
                    <h1 className="text-2xl font-bold">Pengajuan Dana Registrasi Seminar</h1>
                </div>

                {/* Stepper Component */}
                <SeminarStepper currentStep={4} />

                {/* Konten Form Artefak */}
                <div className="mt-10 space-y-8">
                    
                    <div className="flex justify-between items-center">
                        <h2 className="text-2xl font-bold">Submit Artefak</h2>
                        {isReadOnly && (
                            <span className="px-3 py-1 bg-green-100 text-green-700 text-sm font-medium rounded-full border border-green-200 flex items-center gap-2">
                                <CheckCircle2 className="h-4 w-4" />
                                Menunggu Verifikasi
                            </span>
                        )}
                    </div>

                    <form onSubmit={handleSubmit}>
                        <div className="space-y-6">
                            {artifacts.map((label, index) => (
                                <div key={index} className="border rounded-lg p-4 bg-white shadow-sm space-y-3">
                                    <Label className="text-sm font-semibold text-gray-800">
                                        {label}
                                    </Label>
                                    
                                    {/* 🔥 FIX ERROR: Gunakan Optional Chaining (?.) dan Fallback String Kosong */}
                                    <Input 
                                        placeholder="Link gdrive / dokumen" 
                                        className="bg-gray-50 border-gray-200 text-sm"
                                        value={data.artefak_links?.[index] || ''} 
                                        onChange={(e) => handleInputChange(index, e.target.value)}
                                        disabled={isReadOnly}
                                    />
                                    
                                    {/* Menampilkan Error per Index */}
                                    {errors[`artefak_links.${index}`] && (
                                        <p className="text-xs text-red-500 font-medium mt-1">
                                            {errors[`artefak_links.${index}`]}
                                        </p>
                                    )}
                                </div>
                            ))}
                        </div>

                        {/* Tombol Submit */}
                        <div className="flex justify-end pt-8">
                            {!isReadOnly ? (
                                <Button 
                                    type="submit" 
                                    disabled={processing} 
                                    className="bg-black text-white hover:bg-gray-800 px-6 min-w-[150px]"
                                >
                                    {processing ? 'Menyimpan...' : 'Submit Artefak'}
                                </Button>
                            ) : (
                                <Link href={route('dosen.seminar.index')}>
                                    <Button variant="outline" type="button">Kembali ke Daftar</Button>
                                </Link>
                            )}
                        </div>
                    </form>

                </div>
            </div>
        </AppLayout>
    );
}