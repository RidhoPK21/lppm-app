// File: resources/js/Pages/App/RegisSemi/Result.jsx (FILE BARU)

import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Textarea } from "@/components/ui/textarea"; 
import AppLayout from "@/layouts/app-layout";
import * as React from "react";
import { router } from '@inertiajs/react';

// --- Komponen Pembantu untuk menampilkan satu komentar/penilaian ---
const DosenCommentCard = ({ name, comment }) => {
return (
<div className="p-4 border border-gray-200 rounded-lg shadow-sm bg-white">
<div className="flex items-center space-x-3 mb-3">
<img 
src="/images/default-avatar.png" // Ganti dengan path avatar dosen yang sebenarnya
alt={name}
className="w-8 h-8 rounded-full object-cover"
/>
<div>
<div className="text-sm font-semibold leading-none">Dosen</div>
<div className="text-xs text-gray-500">{name}</div>
</div>
</div>
{/* Area komentar yang terlihat seperti Textarea yang hanya-baca */}
<Textarea
value={comment}
readOnly
className="min-h-[150px] border-gray-300 rounded-md p-3 w-full resize-none bg-gray-50 text-gray-800"
/>
</div>
);
};


/**
 * Komponen Halaman Hasil Penilaian Dosen
 */
export default function Result({ bukuId, results }) { 

// Data mock untuk meniru tampilan
const mockResults = [
{ id: 1, name: "Ridho Pakpahan", comment: "Buku sudah bagus, materi relevan. Tingkatkan lagi di bagian referensi." },
{ id: 2, name: "Ridho Pakpahan", comment: "Secara keseluruhan sudah memenuhi kriteria buku semi-populer. Saya setuju dilanjutkan." },
// Anda bisa menambahkan data dosen lain di sini:
// { id: 3, name: "Nama Dosen 3", comment: "Penilaian ketiga..." },
];
const displayedResults = results || mockResults;

const handleGoBack = () => {
// Kembali ke halaman Detail Buku
if (typeof route !== 'undefined') {
router.visit(route('regis-semi.detail', bukuId)); 
} else {
console.error('Fungsi route() dari Ziggy tidak terdefinisi. Tidak bisa navigasi Inertia.');
}
};

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
Komentar Dosen
</h1>
</div>
</div>
{/* ------------------------------------------------------------- */}

<Card className="max-w-7xl w-full mx-auto shadow-none border-none p-4 md:px-8 md:pt-4 md:pb-8 bg-gray-50 rounded-lg"> 
<CardContent className="space-y-6 p-0 pt-0">

{/* Daftar Komentar/Penilaian */}
<div className="space-y-6 pt-4"> 
{displayedResults.map((result) => (
<DosenCommentCard 
key={result.id}
name={result.name}
comment={result.comment}
/>
))}
</div>
</CardContent>
</Card>

</AppLayout>
);
}