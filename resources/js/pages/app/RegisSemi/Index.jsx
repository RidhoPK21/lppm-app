import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import AppLayout from "@/layouts/app-layout";
import * as Icon from "@tabler/icons-react";
import { ChevronDown } from "lucide-react"; 
import * as React from "react";
// Import 'router' dari Inertia jika ingin navigasi yang sebenarnya
// import { router } from "@inertiajs/react";

/**
 * Data Buku Mock (Pengganti data dari server)
 */
const mockBukuList = [
    {
        id: 1,
        judul: "Judul Buku 1",
        penulis: "Penulis 1, Penulis 2",
        status: "belum disetujui",
        tanggal: "dd / mm / yy", 
    },
    {
        id: 2,
        judul: "Judul Buku 2",
        penulis: "Penulis 1, Penulis 2",
        status: "belum disetujui",
        tanggal: "dd / mm / yy",
    },
    {
        id: 3,
        judul: "Judul Buku 3",
        penulis: "Penulis 1, Penulis 2",
        status: "belum disetujui",
        tanggal: "dd / mm / yy",
    },
    {
        id: 4,
        judul: "Judul Buku 4",
        penulis: "Penulis 1, Penulis 2",
        status: "belum disetujui",
        tanggal: "dd / mm / yy",
    },
];

/**
 * Komponen untuk menampilkan setiap item buku dalam daftar
 */
const BukuItem = ({ id, judul, penulis, status, tanggal, onClick }) => (
    <div 
        className="bg-white rounded-lg shadow-md mb-2 cursor-pointer hover:shadow-lg transition-shadow"
        onClick={() => onClick(id)}
    >
        {/* Kontainer utama flex: item-stretch memastikan kolom kanan setinggi p-4 */}
        <div className="flex items-stretch p-4"> 
            {/* Ikon Segitiga Putih dalam Lingkaran Hitam */}
            <div className="mr-4 flex items-center justify-center w-10 h-10 rounded-full bg-black">
                <Icon.IconTriangle size={20} fill="white" /> 
            </div>

            {/* Detail Buku */}
            <div className="flex-1 min-w-0 flex flex-col justify-center">
                <div className="font-semibold text-lg truncate">{judul}</div> 
                <div className="text-sm text-gray-500 truncate">{penulis}</div>
            </div>

            {/* Status dan Tanggal (Perbaikan Final: Menggunakan h-full dan justify-between) */}
            <div className="text-right ml-4 flex flex-col justify-between h-full"> 
                
                {/* Status (didorong ke atas) */}
                <div className="text-gray-500 text-sm">
                    Status : <span className="text-red-500 capitalize font-normal">{status}</span>
                </div>
                
                {/* Tanggal (didorong ke paling bawah) */}
                <div className="text-gray-500 text-xs">{tanggal}</div> 
            </div>
        </div>
    </div>
);

// --- Dropdown/Select Komponen Reusable ---
const SelectDropdown = ({ label, options, className = "", onChange }) => (
    <DropdownMenu>
        <DropdownMenuTrigger asChild>
            {/* Diperbaiki agar Button terlihat seperti input dengan panah ke bawah */}
            <div
                className={`flex items-center justify-between border border-gray-300 rounded-md bg-white text-sm px-3 h-10 cursor-pointer ${className}`}
            >
                {label}
                <ChevronDown className="h-4 w-4 ml-2 opacity-50" />
            </div>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" className="min-w-[120px]">
            {options.map((option) => (
                <DropdownMenuItem key={option} onSelect={() => onChange(option)}>
                    {option}
                </DropdownMenuItem>
            ))}
        </DropdownMenuContent>
    </DropdownMenu>
);


export default function Index() {
    const [search, setSearch] = React.useState("");
    // State tambahan untuk filter/sort (optional, tapi baik untuk praktik nyata)
    const [searchBy, setSearchBy] = React.useState("Search by"); // Ubah default label
    const [sortBy, setSortBy] = React.useState("Sort by"); // Ubah default label

    // Fungsi placeholder untuk navigasi ke halaman Detail (SESUAI PERMINTAAN ASLI)
    // Di lingkungan Inertia, ini akan diganti dengan:
    // const handleBukuClick = (id) => {
    //     router.visit(`/app/regis-semi/${id}/detail`); 
    // };
    const handleBukuClick = (id) => {
        // Untuk tujuan demonstrasi di sini, kita akan mengarahkan ke URL detail dummy
        // Di aplikasi nyata, Anda akan menggunakan Inertia.js atau hook navigasi lainnya.
        console.log(`Navigating to Detail Page for Book ID: ${id}`);
        window.location.href = `/app/regis-semi/${id}/detail`;
    };

    const handleAjukanPenghargaan = () => {
        
    };

    return (
        <AppLayout>
            <Card className="h-full border-none shadow-none"> 
                {/* PADDING MINIMAL (p-0) SESUAI PERMINTAAN */}
                <CardHeader className="p-0 space-y-4">
                    
                    {/* Judul Halaman: Hanya px-4 */}
                    <CardTitle className="text-2xl font-normal px-4">Buku</CardTitle>
                    
                    {/* BARIS UTAMA: Ajukan Penghargaan Buku */}
                    <div className="mb-4 px-4">
                       <Button
                            variant="outline"
                            className="justify-between w-full md:w-1/4 max-w-xs font-normal text-base h-10 px-4"
                            onClick={handleAjukanPenghargaan}
                        >
                            Ajukan Penghargaan Buku
                        </Button>

                    </div>
                    
                    {/* BARIS SEARCH & FILTER (Diperbaiki sesuai gambar) */}
                    <div className="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-2 items-center px-4">
                        
                        {/* Search Input dan Tombol Search dalam satu div */}
                        <div className="flex-1 flex border border-gray-300 rounded-md overflow-hidden h-10 w-full">
                            <input
                                type="text"
                                placeholder="Type to search"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                className="flex-1 p-2 focus:outline-none placeholder:text-gray-400 text-sm border-none"
                                // Menghapus border karena sudah ada di div luar
                            />
                            {/* Tombol Search (digabungkan) */}
                            <Button 
                                variant="default" 
                                className="h-full px-4 bg-gray-100 text-gray-800 hover:bg-gray-200 rounded-l-none border-l border-gray-300 shadow-none font-normal text-sm"
                                onClick={() => console.log("Search button clicked")} // Placeholder action
                            >
                                Search
                            </Button>
                        </div>
                        
                        {/* Search by Dropdown (Diubah menjadi SelectDropdown yang terlihat seperti input) */}
                        <div className="w-full md:w-[150px]"> {/* Mengatur lebar yang sesuai */}
                            <SelectDropdown
                                label={searchBy}
                                options={["Judul", "Penulis"]}
                                className="w-full h-10"
                                onChange={setSearchBy}
                            />
                        </div>
                        
                        {/* Sort by Dropdown (Diubah menjadi SelectDropdown yang terlihat seperti input) */}
                        <div className="w-full md:w-[120px]"> {/* Mengatur lebar yang sesuai */}
                            <SelectDropdown
                                label={sortBy}
                                options={["Tanggal", "Judul"]}
                                className="w-full h-10"
                                onChange={setSortBy}
                            />
                        </div>
                    </div>
                    
                    {/* Garis pemisah */}
                    <hr className="mt-4 mb-0" /> 

                </CardHeader>

                {/* Card Content: Hanya px-4 */}
                <CardContent className="p-0 px-4">
                    <div className="space-y-3">
                        {mockBukuList
                            // Logika filter/sort sederhana
                            .filter(buku => {
                                const searchTerm = search.toLowerCase();
                                // Menggunakan nilai default untuk pencarian jika searchBy belum dipilih secara spesifik
                                const searchField = searchBy === "Search by" || searchBy === "Judul" ? buku.judul : buku.penulis;
                                return searchField.toLowerCase().includes(searchTerm);
                            })
                            .sort((a, b) => {
                                // Mengabaikan sorting jika sortBy masih default
                                if (sortBy === "Sort by") return 0;

                                if (sortBy === "Judul") {
                                    return a.judul.localeCompare(b.judul);
                                }
                                // Sort by Tanggal (asumsi format dd / mm / yy, ini hanya contoh)
                                return a.tanggal.localeCompare(b.tanggal);
                            })
                            .map((buku) => (
                                <BukuItem
                                    key={buku.id}
                                    id={buku.id}
                                    judul={buku.judul}
                                    penulis={buku.penulis}
                                    status={buku.status}
                                    tanggal={buku.tanggal}
                                    onClick={handleBukuClick}
                                />
                            ))}
                    </div>
                </CardContent>
            </Card>
        </AppLayout>
    );
}