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
 */
const BukuItem = ({ id, judul, penulis, status, tanggal, onClick }) => (
    <div
        className="bg-white rounded-lg shadow-sm border border-gray-200 mb-3 cursor-pointer hover:shadow-md transition-shadow"
        onClick={() => onClick(id)}
    >
        <div className="flex items-stretch p-4">
            {/* Ikon Segitiga Putih dalam Lingkaran Hitam */}
            <div className="mr-4 flex items-center justify-center w-8 h-8 rounded-full bg-black flex-shrink-0">
                <Icon.IconTriangle size={16} fill="white" />
            </div>

            {/* Detail Buku */}
            <div className="flex-1 min-w-0 flex flex-col justify-center">
                <div className="font-medium text-base truncate">{judul}</div>
                <div className="text-sm text-gray-500 truncate">{penulis}</div>
            </div>

            {/* Status dan Tanggal */}
            <div className="text-right ml-4 flex flex-col justify-between items-end">
                <div className="text-gray-500 text-sm whitespace-nowrap">
                    Status :{" "}
                    <span
                        className={`capitalize font-normal ${
                            status === "Disetujui (Ke HRD)"
                                ? "text-green-600"
                                : status === "Selesai (Cair)"
                                ? "text-blue-600"
                                : status === "Ditolak/Revisi"
                                ? "text-red-600"
                                : "text-gray-500"
                        }`}
                    >
                        {status === "belum disetujui"
                            ? "belum disetujui"
                            : status}
                    </span>
                </div>
                <div className="text-gray-500 text-xs mt-2">{tanggal}</div>
            </div>
        </div>
    </div>
);

// --- Dropdown/Select Komponen Reusable ---
const SelectDropdown = ({
    label,
    options,
    className = "",
    onChange,
    value,
}) => (
    <DropdownMenu>
        <DropdownMenuTrigger asChild>
            <div
                className={`flex items-center justify-between border border-gray-300 rounded-md bg-white text-sm px-3 h-10 cursor-pointer ${className}`}
            >
                <span className="text-gray-700">{label}</span>
                <ChevronDown className="h-4 w-4 ml-2 opacity-50" />
            </div>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" className="min-w-[120px]">
            {options.map((option) => (
                <DropdownMenuItem
                    key={option}
                    onSelect={() => onChange(option)}
                    className={
                        value === option ? "bg-gray-100 font-medium" : ""
                    }
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

    // [INTEGRASI NAVIGASI] Mengarahkan ke halaman pengajuan baru (asumsi rute 'regis-semi.create' untuk pengajuan baru)
    const handleAjukanBukuClick = () => {
        router.visit(route("regis-semi.create")); // Asumsi rute untuk pengajuan baru
    };

    // Data dropdown
    const searchByOptions = ["Judul", "Dosen"];
    const sortByOptions = ["Terbaru", "Judul"];

    // Filtered data (Logic tidak berubah)
    const filteredSubmissions = submissions
        .filter((item) => {
            const searchTerm = search.toLowerCase();
            if (!searchTerm) return true;

            const targetField =
                searchBy === "Dosen" ? item.nama_dosen : item.judul;

            return targetField.toLowerCase().includes(searchTerm);
        })
        .sort((a, b) => {
            if (sortBy === "Terbaru") {
                return b.id - a.id;
            }
            if (sortBy === "Judul") {
                return a.judul.localeCompare(b.judul);
            }
            return 0;
        });

    return (
        <AppLayout>
            <Card className="h-full border-none shadow-none pt-0">
                {/* Header (Judul, Tombol, dan Search/Filter) */}
                <CardHeader className="p-0 px-4 space-y-4">
                    {/* Judul dan Tombol */}
                    <div className="flex flex-col mb-4">
                        <CardTitle className="text-3xl font-normal text-gray-800 pt-4">
                            Buku
                        </CardTitle>

                        {/* Tombol Ajukan Penghargaan Buku */}
                        <div className="mt-4">
                            <Button
                                variant="default"
                                className="bg-white text-gray-700 hover:bg-gray-50 border border-gray-300 shadow-none font-normal text-sm px-4 py-2 h-auto rounded-lg"
                                onClick={handleAjukanBukuClick}
                            >
                                Ajukan Penghargaan Buku
                            </Button>
                        </div>
                    </div>

                    {/* SEARCH & FILTER (Hapus margin bawah pada div ini) */}
                    <div className="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-4 items-center">
                        <div className="flex-1 flex border border-gray-300 rounded-md overflow-hidden h-10 w-full bg-white">
                            <input
                                type="text"
                                placeholder="Type to search"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                className="flex-1 p-2 focus:outline-none placeholder:text-gray-400 text-sm border-none bg-transparent"
                            />
                            <Button
                                variant="default"
                                className="h-full px-4 bg-white text-gray-800 hover:bg-gray-50 rounded-l-none border-l-0 shadow-none font-normal text-sm"
                            >
                                Search
                            </Button>
                        </div>

                        <div className="w-full md:w-[150px] flex-shrink-0">
                            <SelectDropdown
                                label={searchBy}
                                value={searchBy}
                                options={searchByOptions}
                                className="w-full h-10"
                                onChange={setSearchBy}
                            />
                        </div>

                        <div className="w-full md:w-[120px] flex-shrink-0">
                            <SelectDropdown
                                label={sortBy}
                                value={sortBy}
                                options={sortByOptions}
                                className="w-full h-10"
                                onChange={setSortBy}
                            />
                        </div>
                    </div>
                    {/* Garis pemisah. Dibiarkan di sini untuk memisahkan header/filter dari daftar */}
                    <hr className="mt-4 mb-0 border-gray-200" />
                </CardHeader>

                {/* Content (List Buku) - Ubah pt-4 menjadi pt-2 atau pt-0 untuk merapatkan ke Search/Filter */}
                <CardContent className="p-0 px-4 pt-2">
                    {" "}
                    {/* Mengurangi padding atas di CardContent */}
                    {/* Daftar Buku */}
                    <div className="space-y-3">
                        {/* Jika data kosong */}
                        {filteredSubmissions.length === 0 && (
                            <div className="text-center py-10 text-gray-500">
                                {search ||
                                searchBy !== "Search by" ||
                                sortBy !== "Sort by"
                                    ? "Data pengajuan tidak ditemukan dengan kriteria tersebut."
                                    : "Belum ada pengajuan penghargaan yang masuk."}
                            </div>
                        )}

                        {/* [INTEGRASI REAL DATA] Mapping data dari database */}
                        {filteredSubmissions.map((item) => (
                            <BukuItem
                                key={item.id}
                                id={item.id}
                                judul={item.judul}
                                penulis={item.nama_dosen}
                                status={item.status_label}
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
