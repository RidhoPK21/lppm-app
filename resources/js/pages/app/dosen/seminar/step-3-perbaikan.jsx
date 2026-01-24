import React, { useState, useEffect } from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import SeminarStepper from '@/components/seminar-stepper';
import { Button } from '@/components/ui/button';
// import { Input } from '@/components/ui/input'; // Input asli kita ganti dengan input HTML biasa + Label custom
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import { ArrowLeft, UploadCloud, AlertCircle, FileCheck } from 'lucide-react'; // Tambah icon FileCheck
import Swal from "sweetalert2";
import { route } from 'ziggy-js';

export default function Step3Perbaikan({ seminar, review }) {
    
    // --- 1. PARSE CATATAN REVIEWER ---
    const catatanList = review?.catatan_review 
        ? review.catatan_review.split('\n').filter(n => n) 
        : ['Tidak ada catatan khusus from reviewer.'];

    // --- 2. STATE UNTUK BALASAN ---
    const [responses, setResponses] = useState(catatanList.map(() => ''));

    // --- 3. SETUP FORM SUBMISSION ---
    const { data, setData, post, processing, errors, progress } = useForm({
        file_revisi: null,
        balasan_revisi: [], 
    });

    useEffect(() => {
        setData('balasan_revisi', responses);
    }, [responses]);

    const handleResponseChange = (index, value) => {
        const newResponses = [...responses];
        newResponses[index] = value;
        setResponses(newResponses);
    };

    // --- 4. HANDLE SUBMIT ---
    const handleSubmit = (e) => {
        e.preventDefault();

        if (!data.file_revisi) {
            Swal.fire("Gagal", "Mohon upload file revisi paper Anda.", "error");
            return;
        }

        Swal.fire({
            title: "Kirim Perbaikan?",
            text: "Paper revisi dan tanggapan Anda akan dikirim ke Reviewer.",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Ya, Kirim",
            confirmButtonColor: "#000000",
        }).then((result) => {
            if (result.isConfirmed) {
                post(route('dosen.seminar.submit_perbaikan', seminar.id), {
                    onSuccess: () => {
                        // 1. Tampilkan notifikasi Sukses
                        Swal.fire({
                            title: "Berhasil",
                            text: "Revisi berhasil dikirim! Status seminar kembali 'Proses'.",
                            icon: "success"
                        }).then(() => {
                            // 2. 🔥 PERBAIKAN: Ganti redirect ke halaman detail seminar (asumsi route 'dosen.seminar.show')
                            router.visit(route('dosen.seminar.step3.hasil', seminar.id)); 
                        });
                    },
                    onError: (errors) => {
                         const errorMsg = errors.file_revisi || "Terjadi kesalahan saat mengirim revisi.";
                         Swal.fire("Gagal", errorMsg, "error");
                    },
                });
            }
        });
    };

    return (
        <AppLayout breadcrumbs={[
            { title: 'Beranda', href: '/dosen/home' },
            { title: 'Registrasi Seminar', href: route('dosen.seminar.index') },
            { title: 'Perbaikan Paper', href: '#' }
        ]}>
            <Head title="Perbaikan Paper" />

            <div className="w-full max-w-6xl mx-auto px-6 py-6 pb-24">
                
                <div className="text-center mb-8">
                    <h1 className="text-2xl font-bold">Pengajuan Dana Registrasi Seminar</h1>
                    <p className="text-gray-500 mt-2">Revisi ke-{review?.revisi_ke || 0} • Status: {review?.status}</p>
                </div>

                <SeminarStepper currentStep={3} />

                <div className="mt-10 space-y-8 bg-white p-8 rounded-xl border shadow-sm">
                    
                    <div className="flex items-center justify-between border-b pb-4">
                        <h2 className="text-xl font-bold text-gray-900">Form Perbaikan Paper</h2>
                        <div className="text-sm text-gray-500">
                            Judul Paper: <span className="font-medium text-black">{seminar.judul_makalah}</span>
                        </div>
                    </div>

                    {/* 1. Upload Paper Revisi (CUSTOM STYLE) */}
                    <div className="grid grid-cols-1 md:grid-cols-12 items-start gap-4">
                        <Label className="md:col-span-2 font-medium text-gray-700 pt-3">
                            Upload File Revisi <span className="text-red-500">*</span>
                        </Label>
                        <div className="md:col-span-10">
                            <div className="flex flex-col gap-3">
                                <div className="flex items-center gap-4">
                                    
                                    {/* INPUT ASLI (Disembunyikan) */}
                                    <input 
                                        id="file-upload"
                                        type="file" 
                                        accept="application/pdf"
                                        className="hidden" 
                                        onChange={e => setData('file_revisi', e.target.files[0])}
                                    />

                                    {/* CUSTOM BUTTON (Label sebagai Trigger) */}
                                    <Label 
                                        htmlFor="file-upload" 
                                        className="cursor-pointer flex items-center justify-center gap-2 px-4 py-2.5 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 transition-all active:scale-95 select-none"
                                    >
                                        <UploadCloud className="w-4 h-4 text-gray-600"/>
                                        <span className="text-sm font-medium text-gray-700">
                                            {data.file_revisi ? 'Ganti File' : 'Pilih File PDF'}
                                        </span>
                                    </Label>

                                    {/* MENAMPILKAN NAMA FILE */}
                                    <div className="text-sm text-gray-600 flex items-center gap-2">
                                        {data.file_revisi ? (
                                            <span className="flex items-center gap-2 text-green-700 bg-green-50 px-3 py-1 rounded-full border border-green-200">
                                                <FileCheck className="w-4 h-4"/>
                                                {data.file_revisi.name}
                                            </span>
                                        ) : (
                                            <span className="italic text-gray-400">Belum ada file dipilih</span>
                                        )}
                                    </div>
                                </div>

                                {/* Progress Bar */}
                                {progress && (
                                    <div className="w-full bg-gray-100 rounded-full h-2 mt-1 overflow-hidden">
                                        <div 
                                            className="bg-black h-2 rounded-full transition-all duration-300" 
                                            style={{ width: `${progress.percentage}%` }}
                                        ></div>
                                    </div>
                                )}
                                
                                {/* Error Message */}
                                {errors.file_revisi && (
                                    <p className="text-red-500 text-xs flex items-center gap-1">
                                        <AlertCircle className="w-3 h-3"/> {errors.file_revisi}
                                    </p>
                                )}
                                <p className="text-xs text-gray-400">Format PDF, Maksimal 10MB.</p>
                            </div>
                        </div>
                    </div>

                    {/* 2. Tabel Catatan & Respon (TEXTAREA) */}
                    <div>
                        <h3 className="text-md font-semibold mb-3">Tanggapan Revisi</h3>
                        <div className="border rounded-md bg-white overflow-hidden">
                            <Table className="table-fixed w-full">
                                <TableHeader>
                                    <TableRow className="bg-gray-50">
                                        <TableHead className="w-1/2 font-bold text-gray-900 p-4">Catatan Reviewer</TableHead>
                                        <TableHead className="w-1/2 font-bold text-gray-900 p-4 border-l">Respon Dosen</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {catatanList.map((note, index) => (
                                        <TableRow key={index}>
                                            <TableCell className="p-4 align-top text-gray-800 bg-gray-50/30 leading-relaxed break-words whitespace-pre-wrap">
                                                <span className="font-semibold mr-2">{index + 1}.</span> {note}
                                            </TableCell>
                                            
                                            <TableCell className="p-4 border-l align-top">
                                                <Textarea 
                                                    placeholder="Tuliskan perbaikan yang telah Anda lakukan..."
                                                    value={responses[index]}
                                                    onChange={(e) => handleResponseChange(index, e.target.value)}
                                                    className="min-h-[100px] bg-white border-gray-300 focus:ring-black"
                                                />
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </div>
                    </div>

                    {/* Tombol Submit */}
                    <div className="flex justify-between pt-6 border-t">
                        <Link href={route('dosen.seminar.index')}>
                            <Button variant="outline" className="border-gray-300">
                                <ArrowLeft className="w-4 h-4 mr-2"/>
                                Kembali
                            </Button>
                        </Link>
                        
                        <Button 
                            onClick={handleSubmit} 
                            disabled={processing}
                            className="bg-black text-white hover:bg-gray-800 px-8"
                        >
                            {processing ? (
                                <>
                                    <UploadCloud className="w-4 h-4 mr-2 animate-bounce"/>
                                    Mengirim...
                                </>
                            ) : "Submit Perbaikan"}
                        </Button>
                    </div>

                </div>
            </div>
        </AppLayout>
    );
}