import React from 'react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import SeminarStepper from '@/components/seminar-stepper';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Plus, Trash2 } from 'lucide-react';
import { route } from 'ziggy-js';

export default function Step2Paper({ seminar, reviewers }) {
    // 1. Ambil Flash Message dari Props Global (untuk notifikasi error/sukses)
    const { flash } = usePage().props;
    
    // 2. Setup Form dengan Inertia (Support Array Reviewer & File)
    const { data, setData, post, processing, errors } = useForm({
        file_draft: null,
        reviewer_ids: [''], // Mulai dengan 1 slot kosong
    });

    // --- Fungsi Helper Reviewer Dinamis ---

    // Tambah Slot Reviewer (Maksimal 3)
    const addReviewerSlot = () => {
        if (data.reviewer_ids.length < 3) { 
            setData('reviewer_ids', [...data.reviewer_ids, '']);
        }
    };

    // Hapus Slot Reviewer
    const removeReviewerSlot = (index) => {
        const newIds = data.reviewer_ids.filter((_, i) => i !== index);
        setData('reviewer_ids', newIds);
    };

    // Update Pilihan Reviewer pada Index tertentu
    const updateReviewerSelection = (index, value) => {
        const newIds = [...data.reviewer_ids];
        newIds[index] = value;
        setData('reviewer_ids', newIds);
    };

    // --- Handle Submit ---
    const handleSubmit = (e) => {
        e.preventDefault();
        // Post ke route storeStep2 dengan ID seminar
        post(route('dosen.seminar.step2.store', seminar.id));
    };

    return (
        <AppLayout breadcrumbs={[
            { title: 'Beranda', href: '/dosen/home' },
            { title: 'Registrasi Seminar', href: '/dosen/seminar' },
            { title: 'Tahap 2', href: '#' }
        ]}>
            <Head title="Submit Paper" />

            <div className="w-full max-w-6xl mx-auto px-6 py-6 pb-24">
                
                {/* --- FLASH MESSAGES (Error Handling) --- */}
                {flash.success && (
                    <div className="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <strong className="font-bold">Sukses! </strong>
                        <span className="block sm:inline">{flash.success}</span>
                    </div>
                )}
                {flash.error && (
                    <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <strong className="font-bold">Error! </strong>
                        <span className="block sm:inline">{flash.error}</span>
                    </div>
                )}
                {/* --------------------------------------- */}

                <div className="text-center mb-8">
                    <h1 className="text-2xl font-bold">Pengajuan Dana Registrasi Seminar</h1>
                </div>

                {/* Stepper (Indikator Progress) */}
                <SeminarStepper currentStep={2} />

                <form onSubmit={handleSubmit} className="mt-10 space-y-8">
                    <h2 className="text-2xl font-bold">Submit Paper</h2>

                    <div className="space-y-6">
                        
                        {/* --- UPLOAD PAPER --- */}
                        <div className="grid grid-cols-1 md:grid-cols-4 items-start gap-4">
                            <Label className="md:text-left font-medium text-gray-700 mt-2">
                                Upload Draft Paper (PDF) <span className="text-red-500">*</span>
                            </Label>
                            <div className="md:col-span-3">
                                <div className="flex gap-2">
                                    <Input 
                                        type="file" 
                                        accept="application/pdf"
                                        className="bg-white flex-1 cursor-pointer"
                                        onChange={e => setData('file_draft', e.target.files[0])}
                                    />
                                </div>
                                {/* Error Validasi File */}
                                {errors.file_draft && <p className="text-sm text-red-500 mt-1">{errors.file_draft}</p>}
                                <p className="text-xs text-gray-500 mt-1">Maksimal ukuran file 15MB. Format .pdf</p>
                            </div>
                        </div>

                        {/* --- LIST REVIEWER DINAMIS --- */}
                        {data.reviewer_ids.map((selectedId, index) => (
                            <div key={index} className="grid grid-cols-1 md:grid-cols-4 items-start gap-4">
                                <Label className="md:text-left font-medium text-gray-700 mt-2">
                                    Reviewer {index + 1} <span className="text-red-500">*</span>
                                </Label>
                                <div className="md:col-span-3">
                                    <div className="flex gap-2">
                                        <select
                                            className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                            value={selectedId}
                                            onChange={e => updateReviewerSelection(index, e.target.value)}
                                        >
                                            <option value="">-- Pilih Dosen Reviewer --</option>
                                            {reviewers.map((r) => (
                                                // Disable opsi jika sudah dipilih di slot lain (kecuali dirinya sendiri)
                                                <option 
                                                    key={r.value} 
                                                    value={r.value}
                                                    disabled={data.reviewer_ids.includes(String(r.value)) && selectedId !== String(r.value)}
                                                >
                                                    {r.label}
                                                </option>
                                            ))}
                                        </select>

                                        {/* Tombol Hapus (Hanya jika lebih dari 1 slot) */}
                                        {data.reviewer_ids.length > 1 && (
                                            <Button 
                                                type="button"
                                                variant="ghost" 
                                                size="icon" 
                                                onClick={() => removeReviewerSlot(index)}
                                                className="text-red-500 hover:text-red-700 hover:bg-red-50 shrink-0"
                                            >
                                                <Trash2 className="w-4 h-4" />
                                            </Button>
                                        )}
                                    </div>
                                    
                                    {/* Error Message per index */}
                                    {errors[`reviewer_ids.${index}`] && (
                                        <p className="text-sm text-red-500 mt-1">{errors[`reviewer_ids.${index}`]}</p>
                                    )}
                                </div>
                            </div>
                        ))}

                        {/* --- TOMBOL TAMBAH REVIEWER --- */}
                        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div className="md:col-span-1"></div>
                            <div className="md:col-span-3">
                                {data.reviewer_ids.length < 3 && (
                                    <Button 
                                        type="button" 
                                        variant="outline" 
                                        onClick={addReviewerSlot}
                                        className="gap-2 border-gray-300 text-gray-700 hover:bg-gray-50"
                                    >
                                        <Plus className="w-4 h-4" /> Tambah Reviewer Lain
                                    </Button>
                                )}
                                {/* Error Global untuk Array Reviewer */}
                                {errors.reviewer_ids && <p className="text-sm text-red-500 mt-2">{errors.reviewer_ids}</p>}
                            </div>
                        </div>

                    </div>

                    {/* === BAGIAN TOMBOL BAWAH === */}
                    <div className="flex justify-between pt-8 border-t border-gray-100 mt-8">
                        
                        {/* Tombol Kembali (Aman dari Ziggy Error) */}
                        <Link href={route('dosen.seminar.step1', seminar.id)}>
                            <Button type="button" variant="outline" className="px-8 border-gray-300 text-gray-700 hover:bg-gray-50">
                                Kembali
                            </Button>
                        </Link>

                        {/* Tombol Submit */}
                        <Button 
                            type="submit" 
                            disabled={processing}
                            className="bg-black text-white hover:bg-gray-800 px-8 min-w-[100px]"
                        >
                            {processing ? 'Mengupload...' : 'Simpan & Lanjut'}
                        </Button>
                    </div>

                </form>
            </div>
        </AppLayout>
    );
}