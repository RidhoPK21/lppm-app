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
import { router } from "@inertiajs/react"; // [AKTIFKAN INI]

/**
 * Komponen untuk menampilkan setiap item buku dalam daftar
 * (TAMPILAN TIDAK BERUBAH, TETAP SEPERTI CARD DOSEN)
 */
const BukuItem = ({ id, judul, penulis, status, tanggal, onClick }) => (
    <div
        className="bg-white rounded-lg shadow-md mb-2 cursor-pointer hover:shadow-lg transition-shadow"
        onClick={() => onClick(id)}
    >
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

            {/* Status dan Tanggal */}
            <div className="text-right ml-4 flex flex-col justify-between h-full">
                <div className="text-gray-500 text-sm">
                    Status :{" "}
                    <span
                        className={`capitalize font-normal ${
                            status === "Disetujui (Ke HRD)"
                                ? "text-green-600"
                                : status === "Selesai (Cair)"
                                ? "text-blue-600"
                                : status === "Ditolak/Revisi"
                                ? "text-red-600"
                                : "text-orange-500"
                        }`}
                    >
                        {status}
                    </span>
                </div>
                <div className="text-gray-500 text-xs">{tanggal}</div>
            </div>
        </div>
    </div>
);

// --- Dropdown/Select Komponen Reusable ---
const SelectDropdown = ({ label, options, className = "", onChange }) => (
    <DropdownMenu>
        <DropdownMenuTrigger asChild>
            <div
                className={`flex items-center justify-between border border-gray-300 rounded-md bg-white text-sm px-3 h-10 cursor-pointer ${className}`}
            >
                {label}
                <ChevronDown className="h-4 w-4 ml-2 opacity-50" />
            </div>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" className="min-w-[120px]">
            {options.map((option) => (
                <DropdownMenuItem
                    key={option}
                    onSelect={() => onChange(option)}
                >
                    {option}
                </DropdownMenuItem>
            ))}
        </DropdownMenuContent>
    </DropdownMenu>
);

// [INTEGRASI DATA] Menerima props 'submissions' dari Controller
export default function Index({ submissions = [] }) {
    const [search, setSearch] = React.useState("");
    const [searchBy, setSearchBy] = React.useState("Search by");
    const [sortBy, setSortBy] = React.useState("Sort by");

    // [INTEGRASI NAVIGASI] Mengarahkan ke detail asli
    const handleBukuClick = (id) => {
        router.visit(route("regis-semi.show", id));
    };

    return (
        <AppLayout>
            <Card className="h-full border-none shadow-none">
                <CardHeader className="p-0 space-y-4">
                    <CardTitle className="text-2xl font-normal px-4">
                        Daftar Penghargaan Masuk
                    </CardTitle>

                    {/* SEARCH & FILTER (VISUAL TETAP SAMA) */}
                    <div className="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-2 items-center px-4">
                        <div className="flex-1 flex border border-gray-300 rounded-md overflow-hidden h-10 w-full">
                            <input
                                type="text"
                                placeholder="Cari judul atau nama dosen..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                className="flex-1 p-2 focus:outline-none placeholder:text-gray-400 text-sm border-none"
                            />
                            <Button
                                variant="default"
                                className="h-full px-4 bg-gray-100 text-gray-800 hover:bg-gray-200 rounded-l-none border-l border-gray-300 shadow-none font-normal text-sm"
                            >
                                Search
                            </Button>
                        </div>

                        <div className="w-full md:w-[150px]">
                            <SelectDropdown
                                label={searchBy}
                                options={["Judul", "Dosen"]}
                                className="w-full h-10"
                                onChange={setSearchBy}
                            />
                        </div>

                        <div className="w-full md:w-[120px]">
                            <SelectDropdown
                                label={sortBy}
                                options={["Terbaru", "Judul"]}
                                className="w-full h-10"
                                onChange={setSortBy}
                            />
                        </div>
                    </div>

                    <hr className="mt-4 mb-0" />
                </CardHeader>

                <CardContent className="p-0 px-4">
                    <div className="space-y-3">
                        {/* Jika data kosong */}
                        {submissions.length === 0 && (
                            <div className="text-center py-10 text-gray-500">
                                Belum ada pengajuan penghargaan yang masuk.
                            </div>
                        )}

                        {/* [INTEGRASI REAL DATA] Mapping data dari database */}
                        {submissions
                            .filter((item) => {
                                const searchTerm = search.toLowerCase();
                                const targetField =
                                    searchBy === "Dosen"
                                        ? item.nama_dosen
                                        : item.judul;
                                return targetField
                                    .toLowerCase()
                                    .includes(searchTerm);
                            })
                            .map((item) => (
                                <BukuItem
                                    key={item.id}
                                    id={item.id}
                                    judul={item.judul}
                                    penulis={item.nama_dosen} // Di panel LPPM, kita tampilkan Nama Dosen Pengaju
                                    status={item.status_label} // Menggunakan label yang sudah diformat di Controller
                                    tanggal={item.tanggal_pengajuan}
                                    onClick={handleBukuClick}
                                />
                            ))}
                    </div>
                </CardContent>
            </Card>
        </AppLayout>
    );
}