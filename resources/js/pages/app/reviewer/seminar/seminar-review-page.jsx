import React, { useState } from 'react';
import AppLayout from "@/layouts/app-layout";
import { Head, Link, useForm } from "@inertiajs/react";
import { Card, CardContent } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
// Komponen Input tidak lagi wajib jika kita pakai textarea murni, tapi biarkan saja jika ingin dipakai di tempat lain
import Swal from "sweetalert2";
import { route } from "ziggy-js";

export default function SeminarReviewPage({ review = {}, seminar = {} }) {
    
    // --- 1. STATE UNTUK DYNAMIC INPUT ---
    const [notesList, setNotesList] = useState(
        review?.catatan 
        ? review.catatan.split('\n').filter(n => n) 
        : ['', '', '', ''] 
    );

    const { data, setData, put, processing } = useForm({
        catatan: '', 
        status: 'selesai'
    });

    // --- 2. HANDLER UBAH TEXT ---
    const handleNoteChange = (index, value) => {
        const updatedNotes = [...notesList];
        updatedNotes[index] = value;
        setNotesList(updatedNotes);
    };

    // --- 3. HANDLER TAMBAH ---
    const addNoteField = () => {
        setNotesList([...notesList, '']); 
    };

    // --- 4. HANDLER SUBMIT ---
    const handleSubmit = (e) => {
        e.preventDefault();

        const finalCatatan = notesList.filter(note => note.trim() !== "").join('\n');

        if (!finalCatatan) {
            Swal.fire("Peringatan", "Mohon isi setidaknya satu catatan.", "warning");
            return;
        }

        setData("catatan", finalCatatan);

        Swal.fire({
            title: "Submit Review?",
            text: "Pastikan catatan yang Anda berikan sudah sesuai.",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Submit",
            cancelButtonText: "Batal",
            confirmButtonColor: "#000000",
        }).then((result) => {
            if (result.isConfirmed) {
                put(route('reviewer.seminar.update_review_content', review.id), {
                    data: { ...data, catatan: finalCatatan }, 
                    onSuccess: () => {
                        Swal.fire("Berhasil", "Review berhasil disubmit!", "success");
                    },
                    onError: () => {
                        Swal.fire("Gagal", "Terjadi kesalahan saat menyimpan.", "error");
                    }
                });
            }
        });
    };

    return (
        <AppLayout breadcrumbs={[
            { title: 'Seminar Masuk', href: route('reviewer.seminar.masuk') },
            { title: 'Review Paper', href: '#' }
        ]}>
            <Head title="Review Paper" />

            <div className="w-full max-w-5xl mx-auto px-4 py-8 space-y-8">
                
                {/* --- JUDUL UTAMA --- */}
                <Card className="bg-white border shadow-sm rounded-xl p-6">
                    <h1 className="text-2xl font-bold text-black">
                        Review Paper
                    </h1>
                </Card>

                {/* --- FORM AREA --- */}
                <Card className="bg-white border shadow-sm rounded-xl">
                    <CardContent className="p-8 space-y-6">
                        
                        <div className="mb-6 pb-4 border-b">
                            <h2 className="text-lg font-semibold text-gray-800">
                                {seminar?.judul_makalah || 'Judul Paper Tidak Tersedia'}
                            </h2>
                            <p className="text-sm text-gray-500">
                                Oleh: {seminar?.dosen?.user?.name || '-'}
                            </p>
                        </div>

                        {/* Looping Input Catatan */}
                        {notesList.map((note, index) => (
                            <div key={index} className="grid grid-cols-1 md:grid-cols-12 gap-4 items-start">
                                {/* Label di kiri (Catatan 1...) */}
                                <div className="md:col-span-2 pt-3">
                                    <label className="text-sm font-medium text-gray-700">
                                        Catatan {index + 1}
                                    </label>
                                </div>
                                
                                {/* Input di kanan (TEXTAREA) */}
                                <div className="md:col-span-10">
                                    {/* --- PERUBAHAN DISINI: MENGGUNAKAN TEXTAREA --- */}
                                    <textarea 
                                        className="flex min-h-[100px] w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm placeholder:text-gray-400 focus:outline-none focus:ring-1 focus:ring-black focus:border-black disabled:cursor-not-allowed disabled:opacity-50"
                                        placeholder="Tuliskan masukan review Anda di sini..."
                                        value={note}
                                        onChange={(e) => handleNoteChange(index, e.target.value)}
                                    />
                                </div>
                            </div>
                        ))}

                        <div className="flex justify-end pt-2">
                            <Button 
                                type="button" 
                                variant="outline" 
                                onClick={addNoteField}
                                className="border-gray-300 text-black hover:bg-gray-50"
                            >
                                Tambah
                            </Button>
                        </div>

                    </CardContent>
                </Card>

                {/* --- FOOTER BUTTONS --- */}
                <div className="flex justify-between items-center pt-2">
                    <Link href={route('reviewer.seminar.masuk')}>
                        <Button variant="outline" className="min-w-[120px] bg-white border-gray-300 shadow-sm h-11 text-base">
                            Kembali
                        </Button>
                    </Link>

                    <Button 
                        onClick={handleSubmit} 
                        disabled={processing}
                        className="min-w-[120px] bg-white text-black border border-gray-300 shadow-sm hover:bg-gray-50 h-11 text-base font-medium"
                    >
                        {processing ? 'Menyimpan...' : 'Submit'}
                    </Button>
                </div>

            </div>
        </AppLayout>
    );
}