// File: resources/js/Pages/App/RegisSemi/Detail.jsx (FINAL CODE - Blur Minimal)

import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea"; 
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import AppLayout from "@/layouts/app-layout";
import * as React from "react";
import { useState } from "react"; 

import { router } from '@inertiajs/react';

// --- Komponen-komponen Pembantu (SideBySideFormField, StackedFormField, Modal) Dihilangkan untuk brevity ---
// Catatan: Pastikan komponen-komponen ini tetap ada di file Anda
const SideBySideFormField = ({ label, children }) => (<div className="flex flex-col md:flex-row md:items-center space-y-1 md:space-y-0 space-x-0 md:space-x-8"><label className="text-sm font-medium text-gray-700 md:w-1/4 min-w-[280px] text-left md:min-w-[300px]">{label}:</label><div className="flex-1 w-full md:w-auto">{children}</div></div>);
const StackedFormField = ({ label, children }) => (<div className="flex flex-col space-y-1"><label className="text-sm font-medium text-gray-700 text-left">{label}:</label><div className="w-full">{children}</div></div>);


const CommentModal = ({ isOpen, onClose, onSubmit }) => {
    const [comment, setComment] = useState("");
    if (!isOpen) return null;
    const handleSubmit = () => { onSubmit(comment); setComment(""); }
    const handleClose = () => { setComment(""); onClose(); }
    return (
        // PERBAIKAN: Mengganti bg-black/40 menjadi bg-black/20 untuk mengurangi kegelapan
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/20 backdrop-blur-sm" onClick={handleClose}>
            <div 
                className="bg-white rounded-lg shadow-2xl w-full max-w-md mx-4"
                onClick={(e) => e.stopPropagation()}
            >
                <div className="p-4 border-b border-gray-200">
                    <h3 className="text-lg font-semibold">Beri Komentar</h3>
                </div>
                <div className="p-4">
                    <Textarea
                        value={comment}
                        onChange={(e) => setComment(e.target.value)}
                        placeholder="Value" 
                        className="min-h-[150px] border-gray-300 rounded-md p-2 w-full resize-none"
                    />
                </div>
                <div className="flex justify-end space-x-3 p-4 border-t border-gray-200">
                    <Button onClick={handleClose} className="bg-gray-200 text-black hover:bg-gray-300 rounded-md font-normal text-sm px-4 h-9">
                        Kembali
                    </Button>
                    <Button onClick={handleSubmit} className="bg-black text-white hover:bg-gray-800 rounded-md font-normal text-sm px-4 h-9">
                        Kirim
                    </Button>
                </div>
            </div>
        </div>
    );
};

const ApproveModal = ({ isOpen, onClose, onSubmit }) => {
    const [rewardValue, setRewardValue] = useState("Value"); 
    if (!isOpen) return null;
    const handleSubmit = () => { onSubmit(rewardValue); }
    const handleClose = () => { onClose(); }
    return (
        // PERBAIKAN: Mengganti bg-black/40 menjadi bg-black/20 untuk mengurangi kegelapan
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/20 backdrop-blur-sm" onClick={handleClose}>
            <div 
                className="bg-white rounded-lg shadow-2xl w-full max-w-md mx-4"
                onClick={(e) => e.stopPropagation()}
            >
                <div className="p-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 className="text-lg font-semibold">Tentukan Penghargaan</h3>
                    <button onClick={handleClose} className="text-gray-500 hover:text-gray-700 text-2xl leading-none">
                        &times;
                    </button>
                </div>
                <div className="p-4">
                    <Input
                        value={rewardValue}
                        onChange={(e) => setRewardValue(e.target.value)}
                        className="h-10 border-gray-300 rounded-md p-2 w-full"
                    />
                </div>
                <div className="flex justify-end space-x-3 p-4 border-t border-gray-200">
                    <Button onClick={handleClose} className="bg-gray-200 text-black hover:bg-gray-300 rounded-md font-normal text-sm px-4 h-9">
                        Kembali
                    </Button>
                    <Button onClick={handleSubmit} className="bg-black text-white hover:bg-gray-800 rounded-md font-normal text-sm px-4 h-9">
                        Kirim
                    </Button>
                </div>
            </div>
        </div>
    );
};

// --- Data Mock dan Fungsi Pembantu 
const mockBukuDetail = {
    judul: "Value", jenis: "Select an item", bidang: "Select an item", beritaAcara: "Value", hasilScan: "Value", hasilReview: "Value", suratPernyataan: "Value", penerbit: "Value", isbn: "Value", halaman: "Value", penulis1: "Value", penulis2: "Value", penulis3: "Value",
};


/**
 * Komponen Detail Buku
 */
export default function Detail({ bukuId, buku }) { 
    const data = buku || mockBukuDetail;

    const [isCommentModalOpen, setIsCommentModalOpen] = useState(false);
    const [isApproveModalOpen, setIsApproveModalOpen] = useState(false);

    const handleGoBack = () => {
        if (typeof route !== 'undefined') {
            router.visit(route('regis-semi.index')); 
        } else {
            console.error('Fungsi route() dari Ziggy tidak terdefinisi. Tidak bisa navigasi Inertia.');
        }
    };

    const handleAction = (action) => {
        if (action === 'Tolak') {
            setIsCommentModalOpen(true); 
        } else if (action === 'Setujui') {
            setIsApproveModalOpen(true);
        } else if (action === 'Minta Penilaian Dosen Lain') {
            router.visit(route('invite', bukuId)); // <= Navigasi ke Invite.jsx
        } else if (action === 'Lihat Hasil Penilaian Dosen') { 
            router.visit(route('regis-semi.result', bukuId)); // <-- Navigasi ke Result.jsx
        } else {
            console.log(`Aksi: ${action} diklik!`);
        }
    };
    
    const handleCommentSubmit = (comment) => {
        console.log("Komentar penolakan:", comment);
        // Tambahkan logika InertiaJS POST/PUT request ke backend di sini
        setIsCommentModalOpen(false); 
    }

    const handleApproveSubmit = (rewardValue) => {
        console.log("Nilai penghargaan yang disetujui:", rewardValue);
        // Tambahkan logika InertiaJS POST/PUT request ke backend di sini
        setIsApproveModalOpen(false); 
    }

    return (
        <AppLayout>
            
            {/* HEADER (Tombol Kembali dan Judul Halaman) */}
            <div className="max-w-7xl w-full mx-auto p-4 md:px-8">
                <div className="flex items-center space-x-4">
                    <Button 
                        onClick={handleGoBack}
                        className="bg-black text-white hover:bg-gray-800 rounded-md font-normal text-sm px-4 h-9"
                    >
                        &lt; Kembali
                    </Button>
                    <h1 className="text-xl font-semibold text-gray-900">
                        Detail Buku
                    </h1>
                </div>
            </div>
            {/* ------------------------------------------------------------- */}


        
            <Card className="max-w-7xl w-full mx-auto shadow-none border-none p-4 md:px-8 md:pt-4 md:pb-8 bg-white rounded-lg"> 
                <CardContent className="space-y-6 p-0 pt-0">
                    
                    {/* FORM Detail Buku (Konten Form...) */}
                    <div className="space-y-4 pt-4"> 
                        <SideBySideFormField label="Judul Buku">
                            <Input defaultValue={data.judul} disabled className="h-10 border-gray-300 bg-white rounded-md p-2 w-full" />
                        </SideBySideFormField>
                        <SideBySideFormField label="Jenis Buku">
                            <Select defaultValue={data.jenis === "Select an item" ? undefined : data.jenis} disabled>
                                <SelectTrigger className="h-10 border-gray-300 bg-white rounded-md w-full">
                                    <SelectValue placeholder="Select an item" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="Fiksi">Fiksi</SelectItem>
                                    <SelectItem value="Non-Fiksi">Non-Fiksi</SelectItem>
                                </SelectContent>
                            </Select>
                        </SideBySideFormField>
                        <SideBySideFormField label="Bidang Keilmuan">
                            <Select defaultValue={data.bidang === "Select an item" ? undefined : data.bidang} disabled>
                                <SelectTrigger className="h-10 border-gray-300 bg-white rounded-md w-full">
                                    <SelectValue placeholder="Select an item" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="Ilmu Komputer">Ilmu Komputer</SelectItem>
                                    <SelectItem value="Teknik">Teknik</SelectItem>
                                </SelectContent>
                            </Select>
                        </SideBySideFormField>
                        <StackedFormField label="Berita Acara Serah Terima Buku ke Perpustakaan">
                            <Input defaultValue={data.beritaAcara || "Value"} disabled className="h-10 border-gray-300 bg-white rounded-md p-2 w-full" />
                        </StackedFormField>
                        <StackedFormField label="Hasil Scan Penerbitan Buku">
                            <Input defaultValue={data.hasilScan || "Value"} disabled className="h-10 border-gray-300 bg-white rounded-md p-2 w-full" />
                        </StackedFormField>
                        <StackedFormField label="Hasil Review Penerbitan Buku">
                            <Input defaultValue={data.hasilReview || "Value"} disabled className="h-10 border-gray-300 bg-white rounded-md p-2 w-full" />
                        </StackedFormField>
                        <StackedFormField label="Surat Pernyataan ( Penerbitan Tidak Didanai oleh Institusi + Bukti Biaya Penerbitan )">
                            <Input defaultValue={data.suratPernyataan || "Value"} disabled className="h-10 border-gray-300 bg-white rounded-md p-2 w-full" />
                        </StackedFormField>
                    </div>
                </CardContent>
                
                {/* Button Group: Disesuaikan agar rapi dan seimbang */}
                <div className="flex flex-col items-center space-y-6 pt-10 w-full"> 
                    
                    {/* BARIS 1: Tiga Tombol Rata Kiri-Tengah-Kanan */}
                    <div className="flex w-full justify-between items-center max-w-5xl mx-auto px-4 md:px-0">
                        
                        {/* KIRI: Buka Folder Dokumen Pendukung */}
                        <Button 
                            onClick={() => handleAction('Buka Folder Dokumen Pendukung')}
                            className="p-3 h-auto text-white bg-black hover:bg-gray-800 rounded-md font-normal text-base flex-1 mx-2 shadow-lg" 
                        >
                            Buka Folder Dokumen Pendukung
                        </Button>

                        {/* TENGAH: Setujui */}
                        <Button 
                            onClick={() => handleAction('Setujui')}
                            className="p-3 h-auto text-white bg-black hover:bg-gray-800 rounded-md font-normal text-base flex-1 mx-2 shadow-lg"
                        >
                            Setujui
                        </Button>

                        {/* KANAN: Tolak */}
                        <Button 
                            onClick={() => handleAction('Tolak')}
                            className="p-3 h-auto text-white bg-black hover:bg-gray-800 rounded-md font-normal text-base flex-1 mx-2 shadow-lg" 
                        >
                            Tolak
                        </Button>
                    </div>

                    {/* BARIS 2: Dua Tombol di Tengah */}
                    <div className="flex justify-center space-x-8 w-full pt-4 max-w-3xl mx-auto px-4 md:px-0"> 
                        <Button 
                            onClick={() => handleAction('Minta Penilaian Dosen Lain')}
                            className="p-3 h-auto text-white bg-black hover:bg-gray-800 rounded-md font-normal text-base flex-1 shadow-lg" 
                        >
                            Minta Penilaian Dosen Lain
                        </Button>
                        <Button 
                            onClick={() => handleAction('Lihat Hasil Penilaian Dosen')}
                            className="p-3 h-auto text-white bg-black hover:bg-gray-800 rounded-md font-normal text-base flex-1 shadow-lg" 
                        >
                            Lihat Hasil Penilaian Dosen
                        </Button>
                    </div>
                </div>
            </Card>

            {/* Render Modals */}
            <CommentModal 
                isOpen={isCommentModalOpen}
                onClose={() => setIsCommentModalOpen(false)}
                onSubmit={handleCommentSubmit}
            />
            
            <ApproveModal
                isOpen={isApproveModalOpen}
                onClose={() => setIsApproveModalOpen(false)}
                onSubmit={handleApproveSubmit}
            />
        </AppLayout>
    );
}