import { Card, CardContent } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import AppLayout from "@/layouts/app-layout";
import * as React from "react";
import { useState } from "react"; 
import { router } from '@inertiajs/react';
import { ChevronLeft, Check } from 'lucide-react';

// Mock Data - Ganti dengan data dari props
const mockDosenList = [
    { id: 1, name: "Dosen", detail: "Ridho Pakpahan", avatarUrl: "https://placehold.co/40x40/4A5568/FFFFFF?text=R" },
    { id: 2, name: "Dosen", detail: "Budi Santoso", avatarUrl: "https://placehold.co/40x40/7C3AED/FFFFFF?text=B" },
    { id: 3, name: "Dosen", detail: "Siti Aisyah", avatarUrl: "https://placehold.co/40x40/DC2626/FFFFFF?text=S" },
    { id: 4, name: "Dosen", detail: "Ahmad Hanafi", avatarUrl: "https://placehold.co/40x40/EA580C/FFFFFF?text=A" },
    { id: 5, name: "Dosen", detail: "Dewi Puspita", avatarUrl: "https://placehold.co/40x40/059669/FFFFFF?text=D" },
];

const DosenRow = React.memo(({ dosen, isSelected, onToggle }) => {
    return (
        <div
            className={`flex items-center justify-between p-4 cursor-pointer transition duration-150 ${
                isSelected ? "bg-gray-50" : "hover:bg-gray-50"
            }`}
            onClick={() => onToggle(dosen.id)}
        >
            <div className="flex items-center space-x-3">
                <img
                    src={dosen.avatarUrl}
                    alt={dosen.detail}
                    className="w-10 h-10 rounded-full object-cover"
                />
                <div>
                    <div className="text-sm font-medium text-gray-900">
                        {dosen.name}
                    </div>
                    <div className="text-sm text-gray-600">{dosen.detail}</div>
                </div>
            </div>

            <div
                className={`w-6 h-6 flex items-center justify-center border-2 rounded-full transition duration-150 ${
                    isSelected ? "bg-black border-black" : "border-gray-300"
                }`}
            >
                {isSelected && <Check className="w-4 h-4 text-white stroke-[3px]" />}
            </div>
        </div>
    );
});

DosenRow.displayName = "DosenRow";

export default function UndangDosen({ bukuId, dosenList = mockDosenList }) {
    const [searchQuery, setSearchQuery] = useState("");
    const [selectedDosenIds, setSelectedDosenIds] = useState([]);

    const handleGoBack = () => {
        router.visit(`/app/regis-semi/${bukuId}/detail`);
    };

    const handleToggleDosen = (id) => {
        setSelectedDosenIds(prevIds =>
            prevIds.includes(id)
                ? prevIds.filter(dosenId => dosenId !== id)
                : [...prevIds, id]
        );
    };

    const handleSendInvitation = () => {
        if (selectedDosenIds.length === 0) {
            alert("Harap pilih minimal satu dosen untuk diundang.");
            return;
        }

        // Kirim data ke backend menggunakan Inertia
        router.post(`/buku/${bukuId}/undang-dosen`, {
            dosen_ids: selectedDosenIds
        }, {
            onSuccess: () => {
                alert(`Undangan berhasil dikirim ke ${selectedDosenIds.length} dosen!`);
                router.visit(`/buku/${bukuId}`);
            },
            onError: (errors) => {
                console.error('Error:', errors);
                alert('Gagal mengirim undangan. Silakan coba lagi.');
            }
        });
    };

    const filteredDosen = dosenList.filter(
        (dosen) =>
            dosen.detail.toLowerCase().includes(searchQuery.toLowerCase())
    );

    return (
        <AppLayout>
            <div className="w-full px-6 pt-2 pb-6">
                
                {/* Header */}
                <div className="flex items-center space-x-3 mb-3">
                    <Button
                        onClick={handleGoBack}
                        className="flex items-center bg-black text-white hover:bg-gray-800 font-medium text-sm px-3 py-2 h-auto"
                    >
                        <ChevronLeft className="w-4 h-4 mr-1" /> Kembali
                    </Button>
                    <h1 className="text-xl font-semibold text-gray-900">
                        Undang Dosen
                    </h1>
                </div>
                
                {/* Search Bar */}
                <div className="relative mb-3">
                    <Input
                        placeholder="Type to search..."
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                        className="pr-24"
                    />
                    <button className="absolute right-2 top-1/2 transform -translate-y-1/2 bg-white border border-gray-300 rounded-md px-4 py-1.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                        Search
                    </button>
                </div>

                {/* Dosen List */}
                <Card className="mb-24">
                    <CardContent className="p-0 divide-y divide-gray-200">
                        {filteredDosen.length > 0 ? (
                            filteredDosen.map((dosen) => (
                                <DosenRow
                                    key={dosen.id}
                                    dosen={dosen}
                                    isSelected={selectedDosenIds.includes(dosen.id)}
                                    onToggle={handleToggleDosen}
                                />
                            ))
                        ) : (
                            <div className="p-6 text-center text-gray-500">
                                Tidak ada dosen ditemukan. Coba kata kunci lain.
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Floating Button */}
                <div className="fixed bottom-6 right-6 z-50">
                    <Button
                        onClick={handleSendInvitation}
                        disabled={selectedDosenIds.length === 0}
                        className={`px-6 py-3 text-sm rounded-lg font-medium shadow-lg transition duration-200 ${
                            selectedDosenIds.length > 0
                                ? "bg-white border-2 border-gray-900 text-gray-900 hover:bg-gray-50"
                                : "bg-gray-200 text-gray-400 cursor-not-allowed border-2 border-gray-200"
                        }`}
                    >
                        Kirim Link Google Drive
                    </Button>
                </div>
            </div>
        </AppLayout>
    );
}