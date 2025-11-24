import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import AppLayout from "@/layouts/app-layout";
import { Head, Link } from "@inertiajs/react";
import { CircleArrowUp, ChevronDown, Plus } from "lucide-react";
import { route } from "ziggy-js";

// Komponen Dropdown Reusable
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

export default function PenghargaanBukuPage({ buku: initialBuku }) {
    // Data Dummy
    const buku = initialBuku || [
        {
            id: 1,
            judul: "Pemrograman Web Modern",
            penulis: "Budi Santoso",
            tahun: "2024",
            status: "diajukan",
        },
        {
            id: 2,
            judul: "Dasar-Dasar Laravel 11",
            penulis: "Siti Aminah",
            tahun: "2023",
            status: "disetujui",
        },
        {
            id: 3,
            judul: "Algoritma & Struktur Data",
            penulis: "Eko Kurniawan",
            tahun: "2022",
            status: "ditolak",
        },
    ];

    const breadcrumbs = [
        { title: "Penghargaan", url: "#" },
        { title: "Buku", url: route("app.penghargaan.buku.index") },
    ];

    const getStatusColor = (status) => {
        const s = status.toLowerCase();
        if (
            s.includes("belum") ||
            s.includes("diajukan") ||
            s.includes("pending")
        )
            return "text-yellow-500";
        if (s.includes("setuju") || s.includes("disetujui"))
            return "text-green-500";
        if (s.includes("tolak")) return "text-red-500";
        return "text-gray-500";
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Penghargaan Buku" />

            <Card className="h-full border-none shadow-none">
                <CardHeader className="p-0 space-y-4">
                    {/* Judul Halaman */}
                    <CardTitle className="text-2xl font-normal px-4">
                        Buku
                    </CardTitle>

                    {/* Tombol Ajukan (Ditempatkan di header agar layout rapi seperti RegisSemi) */}
                    <div className="px-4">
                        <Link href={route("app.penghargaan.buku.create")}>
                            <Button
                                variant="outline"
                                className="justify-between w-full md:w-1/4 max-w-xs font-normal text-base h-10 px-4"
                            >
                                <span>Ajukan Penghargaan Buku</span>
                                <Plus className="h-4 w-4 ml-2 opacity-50" />
                            </Button>
                        </Link>
                    </div>

                    {/* Baris Search & Filter */}
                    <div className="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-2 items-center px-4">
                        <div className="flex-1 flex border border-gray-300 rounded-md overflow-hidden h-10 w-full">
                            <input
                                type="text"
                                placeholder="Type to search"
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
                                label="Search by"
                                options={["Judul", "Penulis"]}
                                className="w-full h-10"
                                onChange={() => {}}
                            />
                        </div>

                        <div className="w-full md:w-[120px]">
                            <SelectDropdown
                                label="Sort by"
                                options={["Terbaru", "Terlama"]}
                                className="w-full h-10"
                                onChange={() => {}}
                            />
                        </div>
                    </div>

                    {/* Garis Pemisah */}
                    <hr className="mt-4 mb-0" />
                </CardHeader>

                <CardContent className="p-0 px-4">
                    <div className="flex flex-col gap-2 mt-4">
                        {buku.map((item) => (
                            <div
                                key={item.id}
                                className="bg-white rounded-lg shadow-md cursor-pointer hover:shadow-lg transition-shadow"
                            >
                                <div className="flex items-stretch p-4">
                                    {/* Icon */}
                                    <div className="mr-4 flex items-center justify-center w-10 h-10 rounded-full bg-black shrink-0">
                                        <CircleArrowUp className="h-5 w-5 text-white" />
                                    </div>

                                    {/* Detail */}
                                    <div className="flex-1 min-w-0 flex flex-col justify-center">
                                        <div className="font-semibold text-lg truncate">
                                            {item.judul}
                                        </div>
                                        <div className="text-sm text-gray-500 truncate">
                                            {item.penulis}
                                        </div>
                                    </div>

                                    {/* Status & Tanggal */}
                                    <div className="text-right ml-4 flex flex-col justify-between h-full min-w-[100px]">
                                        <div
                                            className={`text-sm font-medium ${getStatusColor(
                                                item.status
                                            )}`}
                                        >
                                            Status :{" "}
                                            <span className="capitalize font-normal">
                                                {item.status}
                                            </span>
                                        </div>
                                        <div className="text-gray-500 text-xs mt-2">
                                            {item.tahun
                                                ? `Tahun ${item.tahun}`
                                                : "23 / 11 / 2025"}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ))}

                        {buku.length === 0 && (
                            <div className="text-center py-10 text-muted-foreground bg-muted/10 rounded-lg border border-dashed w-full">
                                Belum ada data buku yang diajukan.
                            </div>
                        )}
                    </div>
                </CardContent>
            </Card>
        </AppLayout>
    );
}
