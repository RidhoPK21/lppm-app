import * as React from "react";
import { useState } from "react"; 

// --- START MOCK IMPORTS for Self-Contained Execution ---

// Mock AppLayout - Menggantikan "@/layouts/app-layout"
const AppLayout = ({ children }) => (
    <div className="min-h-screen bg-gray-100 font-sans antialiased">
        <header className="bg-white shadow">
            <div className="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                {/* Header Mock */}
            </div>
        </header>
        <main>{children}</main>
    </div>
);

// Mock router - Menggantikan '@inertiajs/react'
const router = {
    visit: (route) => {
        console.log(`[Mock Router] Simulasi Navigasi ke: ${route}. Di aplikasi nyata, ini akan memuat halaman Inertia baru.`);
        // Mengganti alert() dengan console log dan notifikasi sederhana untuk lingkungan ini
        alert(`Simulasi Navigasi ke: ${route}`);
    }
};

// Mock UI Components (Button, Input, Textarea, Card, Select)
const Button = ({ children, onClick, className, disabled = false }) => (
    <button 
        onClick={onClick} 
        className={`px-4 py-2 font-medium transition duration-150 rounded-md shadow-sm ${className}`}
        disabled={disabled}
    >
        {children}
    </button>
);

const Input = ({ defaultValue, disabled, className, value, onChange, placeholder, type = 'text' }) => (
    <input 
        type={type} 
        defaultValue={defaultValue} 
        disabled={disabled} 
        value={value}
        onChange={onChange}
        placeholder={placeholder}
        className={`h-10 border border-gray-300 rounded-md p-2 w-full focus:ring-indigo-500 focus:border-indigo-500 outline-none transition duration-150 ${className}`}
    />
);

const Textarea = ({ value, onChange, placeholder, className }) => (
    <textarea
        value={value}
        onChange={onChange}
        placeholder={placeholder}
        className={`border border-gray-300 rounded-md p-2 w-full resize-none focus:ring-indigo-500 focus:border-indigo-500 outline-none transition duration-150 ${className}`}
    />
);

const Card = ({ children, className }) => (
    <div className={`bg-white rounded-lg shadow-md ${className}`}>
        {children}
    </div>
);
const CardContent = ({ children, className }) => (
    <div className={className}>
        {children}
    </div>
);

// Mock Select components (menggunakan native select)
const Select = ({ defaultValue, disabled, children }) => (
    <select 
        defaultValue={defaultValue} 
        disabled={disabled} 
        className="h-10 border border-gray-300 bg-white rounded-md w-full p-2 appearance-none cursor-pointer"
    >
        {children}
    </select>
);
// Mock Select parts
const SelectTrigger = ({ children }) => <>{children}</>;
const SelectValue = ({ placeholder }) => <option value="" disabled>{placeholder}</option>;
const SelectContent = ({ children }) => <>{children}</>;
const SelectItem = ({ value, children }) => <option value={value}>{children}</option>;

// --- END MOCK IMPORTS ---


// --- Komponen-komponen Pembantu (Modal) ---
const CommentModal = ({ isOpen, onClose, onSubmit }) => {
    const [comment, setComment] = useState("");
    if (!isOpen) return null;
    const handleSubmit = () => { onSubmit(comment); setComment(""); }
    const handleClose = () => { setComment(""); onClose(); }
    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" onClick={handleClose}>
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
                        placeholder="Masukkan komentar penolakan..." 
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
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" onClick={handleClose}>
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

// --- Data Mock dan Fungsi Pembantu (SideBySideFormField, StackedFormField)
const mockBukuDetail = {
    judul: "The Future of AI Ethics", jenis: "Non-Fiksi", bidang: "Ilmu Komputer", beritaAcara: "BA-AI-1234", hasilScan: "Link Scan", hasilReview: "Link Review", suratPernyataan: "Link Surat Pernyataan", penerbit: "Value", isbn: "Value", halaman: "Value", penulis1: "Value", penulis2: "Value", penulis3: "Value",
};
// Komponen SideBySideFormField dan StackedFormField disederhanakan
const SideBySideFormField = ({ label, children }) => (<div className="flex flex-col md:flex-row md:items-center space-y-1 md:space-y-0 space-x-0 md:space-x-8"><label className="text-sm font-medium text-gray-700 md:w-1/4 min-w-[280px] text-left md:min-w-[300px]">{label}:</label><div className="flex-1 w-full md:w-auto">{children}</div></div>);
const StackedFormField = ({ label, children }) => (<div className="flex flex-col space-y-1"><label className="text-sm font-medium text-gray-700 text-left">{label}:</label><div className="w-full">{children}</div></div>);


/**
 * Komponen Detail Buku
 */
export default function Detail({ bukuId, buku }) { 
    const data = buku || mockBukuDetail;

    const [isCommentModalOpen, setIsCommentModalOpen] = useState(false);
    const [isApproveModalOpen, setIsApproveModalOpen] = useState(false);

    const handleGoBack = () => {
        // Karena fungsi route() tidak terdefinisi, kita mock navigasi
        router.visit('/regis-semi/index'); 
    };

    const handleAction = (action) => {
        if (action === 'Tolak') {
            setIsCommentModalOpen(true); 
        } else if (action === 'Setujui') {
            setIsApproveModalOpen(true);
        } else if (action === 'Minta Penilaian Dosen Lain') {
            // Navigasi ke halaman undangan (menggunakan mock router)
            const targetBukuId = bukuId || 'mock-id';
            // Asumsi: route Inertia-nya adalah 'regis-semi.undangan' yang mengarah ke `/regis-semi/${id}/undangan`
            router.visit(`/regis-semi/${targetBukuId}/undangan`); 
        } else {
            console.log(`Aksi: ${action} diklik!`);
        }
    }

    const handleCommentSubmit = (comment) => {
        console.log("Komentar penolakan:", comment);
        // Ganti alert() dengan custom UI di aplikasi nyata
        alert(`Buku ditolak dengan komentar: ${comment}`);
        setIsCommentModalOpen(false); 
        // Lakukan logic penolakan data di sini
    }

    const handleApproveSubmit = (rewardValue) => {
        console.log("Nilai penghargaan yang disetujui:", rewardValue);
        // Ganti alert() dengan custom UI di aplikasi nyata
        alert(`Buku disetujui dengan penghargaan: ${rewardValue}`);
        setIsApproveModalOpen(false); 
        // Lakukan logic persetujuan data di sini
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
                            <Select defaultValue={data.jenis} disabled>
                                {/* Di lingkungan Inertia nyata, SelectValue dan SelectTrigger adalah komponen */}
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih jenis" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="Fiksi">Fiksi</SelectItem>
                                    <SelectItem value="Non-Fiksi">Non-Fiksi</SelectItem>
                                </SelectContent>
                            </Select>
                        </SideBySideFormField>
                        <SideBySideFormField label="Bidang Keilmuan">
                            <Select defaultValue={data.bidang} disabled>
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih bidang" />
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
                
                {/* Button Group */}
                <div className="flex flex-col items-center space-y-4 pt-10">
                    
                    {/* BARIS 1: Buka Folder, Setujui, Tolak */}
                    <div className="flex w-full justify-between items-center">
                        
                        {/* KIRI: Buka Folder Dokumen Pendukung */}
                        <div className="mr-40"> 
                             <Button 
                                 onClick={() => handleAction('Buka Folder Dokumen Pendukung')}
                                 className="p-3 h-auto text-white bg-black hover:bg-gray-800 rounded-md font-normal text-base min-w-[200px] shadow-lg" 
                            >
                                 Buka Folder Dokumen Pendukung
                            </Button>
                        </div>

                        {/* TENGAH: Setujui */}
                        <div className="flex flex-grow justify-center">
                            <Button 
                                onClick={() => handleAction('Setujui')}
                                className="p-3 h-auto text-white bg-black hover:bg-gray-800 rounded-md font-normal text-base min-w-[120px] shadow-lg"
                            >
                                Setujui
                            </Button>
                        </div>

                        {/* KANAN: Tolak */}
                        <Button 
                            onClick={() => handleAction('Tolak')}
                            className="p-3 h-auto text-white bg-black hover:bg-gray-800 rounded-md font-normal text-base min-w-[120px] shadow-lg" 
                        >
                            Tolak
                        </Button>
                    </div>

                    {/* Baris 2: Minta Penilaian, Lihat Hasil */}
                    <div className="flex justify-center space-x-4 w-full pt-4"> 
                        <Button 
                            onClick={() => handleAction('Minta Penilaian Dosen Lain')}
                            className="p-3 h-auto text-white bg-black hover:bg-gray-800 rounded-md font-normal text-base min-w-[200px] shadow-lg" 
                        >
                            Minta Penilaian Dosen Lain
                        </Button>
                        <Button 
                            onClick={() => handleAction('Lihat Hasil Penilaian Dosen')}
                            className="p-3 h-auto text-white bg-black hover:bg-gray-800 rounded-md font-normal text-base min-w-[200px] shadow-lg" 
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