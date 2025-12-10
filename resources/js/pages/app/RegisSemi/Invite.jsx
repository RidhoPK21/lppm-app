import React, { useState } from "react";
import AppLayout from "@/layouts/app-layout";
import { Head, router } from "@inertiajs/react";
import { route } from "ziggy-js";

// UI Components
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import {
    ArrowLeft,
    Search,
    UserPlus,
    CheckCircle2,
    Loader2,
    Users,
    Mail,
    User,
    Shield,
    Filter,
    BadgeCheck,
} from "lucide-react";
import { toast } from "sonner";

// Buat komponen Badge sederhana
const Badge = ({ children, variant = "default", className = "" }) => {
    const baseStyles = "inline-flex items-center rounded-full px-2 py-1 text-xs font-medium";
    
    const variants = {
        default: "bg-gray-100 text-gray-800",
        green: "bg-green-100 text-green-800",
        blue: "bg-blue-100 text-blue-800",
        yellow: "bg-yellow-100 text-yellow-800",
        red: "bg-red-100 text-red-800",
        outline: "border border-gray-300 text-gray-700",
    };
    
    const variantStyle = variants[variant] || variants.default;
    
    return (
        <span className={`${baseStyles} ${variantStyle} ${className}`}>
            {children}
        </span>
    );
};

export default function Invite({ book, availableReviewers = [], flash }) {
    const [search, setSearch] = useState("");
    const [processingId, setProcessingId] = useState(null);
    const [filterDosenOnly, setFilterDosenOnly] = useState(true); // Default filter hanya Dosen
    const [localReviewers, setLocalReviewers] = useState(availableReviewers);

    // Tampilkan toast dari flash message jika ada
    React.useEffect(() => {
        if (flash?.success) {
            toast.success(flash.success);
        }
        if (flash?.error) {
            toast.error(flash.error);
        }
    }, [flash]);

    // Filter dosen berdasarkan pencarian
    let filteredReviewers = localReviewers.filter(
        (user) =>
            user.name.toLowerCase().includes(search.toLowerCase()) ||
            user.email.toLowerCase().includes(search.toLowerCase()) ||
            user.user_id.toLowerCase().includes(search.toLowerCase())
    );

    // Filter tambahan: hanya tampilkan yang punya akses Dosen
    if (filterDosenOnly) {
        filteredReviewers = filteredReviewers.filter(user => user.has_dosen_akses);
    }

    // Handler Undang - Menggunakan approach yang benar untuk Inertia
    const handleInvite = async (userId) => {
        setProcessingId(userId);
        
        try {
            const response = await fetch(route("regis-semi.store-invite", book.id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    user_id: userId,
                }),
            });

            const data = await response.json();

            if (response.ok) {
                toast.success(data.message || "Undangan berhasil dikirim");
                
                // Update local state untuk menandai reviewer sebagai sudah diundang
                setLocalReviewers(prevReviewers => 
                    prevReviewers.map(reviewer => 
                        reviewer.user_id === userId 
                            ? { ...reviewer, is_invited: true }
                            : reviewer
                    )
                );
            } else {
                toast.error(data.message || "Gagal mengundang reviewer");
            }
        } catch (error) {
            console.error('Error:', error);
            toast.error("Terjadi kesalahan saat mengundang reviewer");
        } finally {
            setProcessingId(null);
        }
    };

    // Alternatif: Menggunakan window.fetch secara langsung
    const handleInviteAlternative = (userId) => {
        setProcessingId(userId);
        
        // Buat form data untuk POST request
        const formData = new FormData();
        formData.append('user_id', userId);
        
        // Gunakan fetch API dengan proper headers
        window.fetch(route("regis-semi.store-invite", book.id), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            setProcessingId(null);
            
            if (data.success || data.message === 'Undangan berhasil dikirim') {
                toast.success(data.message || "Undangan berhasil dikirim");
                
                // Update local state
                setLocalReviewers(prevReviewers => 
                    prevReviewers.map(reviewer => 
                        reviewer.user_id === userId 
                            ? { ...reviewer, is_invited: true }
                            : reviewer
                    )
                );
            } else {
                toast.error(data.message || "Gagal mengundang reviewer");
            }
        })
        .catch(error => {
            setProcessingId(null);
            console.error('Error:', error);
            toast.error("Terjadi kesalahan jaringan");
        });
    };

    // Handler dengan approach terbaik - kombinasi
    const handleInviteFinal = (userId) => {
        setProcessingId(userId);
        
        // Menggunakan window.axios jika tersedia (Laravel default)
        if (window.axios) {
            window.axios.post(route("regis-semi.store-invite", book.id), {
                user_id: userId
            })
            .then(response => {
                setProcessingId(null);
                
                if (response.data.success || response.data.message) {
                    toast.success(response.data.message || "Undangan berhasil dikirim");
                    
                    // Update local state
                    setLocalReviewers(prevReviewers => 
                        prevReviewers.map(reviewer => 
                            reviewer.user_id === userId 
                                ? { ...reviewer, is_invited: true }
                                : reviewer
                        )
                    );
                }
            })
            .catch(error => {
                setProcessingId(null);
                
                if (error.response) {
                    // Server responded with error
                    const errorMessage = error.response.data?.message || 
                                       error.response.data?.error || 
                                       "Gagal mengundang reviewer";
                    toast.error(errorMessage);
                } else if (error.request) {
                    // No response received
                    toast.error("Tidak ada respons dari server");
                } else {
                    // Something else
                    toast.error("Terjadi kesalahan");
                }
            });
        } else {
            // Fallback to fetch
            handleInviteAlternative(userId);
        }
    };

    // Hitung statistik berdasarkan localReviewers
    const stats = {
        totalReviewers: localReviewers.length,
        withDosenAkses: localReviewers.filter(r => r.has_dosen_akses).length,
        available: localReviewers.filter(r => !r.is_invited).length,
        invited: localReviewers.filter(r => r.is_invited).length,
        filteredCount: filteredReviewers.length,
    };

    return (
        <AppLayout>
            <Head title={`Undang Reviewer - ${book.title}`} />

            <div className="max-w-5xl mx-auto p-4 md:px-8 space-y-6 pb-20">
                {/* Header & Back Button */}
                <div className="flex items-center gap-4">
                    <Button
                        variant="ghost"
                        size="icon"
                        onClick={() =>
                            router.visit(route("regis-semi.show", book.id))
                        }
                    >
                        <ArrowLeft className="h-5 w-5" />
                    </Button>
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">
                            Pilih Reviewer
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Cari dosen yang kompeten untuk menilai buku:{" "}
                            <span className="font-medium text-foreground">
                                "{book.title}"
                            </span>
                        </p>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    {/* Kolom Kiri: Search & List */}
                    <div className="lg:col-span-2 space-y-4">
                        <div className="flex flex-col sm:flex-row gap-4 items-center">
                            <div className="relative flex-1 w-full">
                                <Search className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                                <Input
                                    placeholder="Cari berdasarkan user_id, nama, atau email..."
                                    className="pl-10 h-12 text-base"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                />
                            </div>
                            <Button
                                variant={filterDosenOnly ? "default" : "outline"}
                                size="sm"
                                className="h-12 whitespace-nowrap bg-green-600 hover:bg-green-700"
                                onClick={() => setFilterDosenOnly(!filterDosenOnly)}
                            >
                                <Filter className="mr-2 h-4 w-4" />
                                {filterDosenOnly ? "Hanya Dosen" : "Semua Akses"}
                            </Button>
                            <div className="text-sm text-muted-foreground whitespace-nowrap">
                                <span className="font-medium">{stats.filteredCount}</span> tersedia
                            </div>
                        </div>

                        <div className="space-y-3">
                            {filteredReviewers.length > 0 ? (
                                filteredReviewers.map((reviewer) => (
                                    <Card
                                        key={reviewer.user_id}
                                        className={`hover:border-primary/50 transition-colors ${
                                            reviewer.has_dosen_akses ? 'border-l-4 border-l-green-500 bg-green-50/30' : ''
                                        }`}
                                    >
                                        <CardContent className="p-4 flex items-center justify-between">
                                            <div className="flex items-start gap-4">
                                                <Avatar className="h-10 w-10">
                                                    <AvatarImage
                                                        src={`https://ui-avatars.com/api/?name=${encodeURIComponent(reviewer.name)}&background=4ade80`}
                                                    />
                                                    <AvatarFallback className="bg-green-100 text-green-800">
                                                        {reviewer.name.charAt(0)}
                                                    </AvatarFallback>
                                                </Avatar>
                                                <div className="flex-1">
                                                    <div className="flex items-center gap-2">
                                                        <User className="h-3 w-3 text-muted-foreground" />
                                                        <h4 className="font-semibold text-sm md:text-base">
                                                            {reviewer.name}
                                                            {reviewer.has_dosen_akses && (
                                                                <BadgeCheck className="inline ml-2 h-4 w-4 text-green-500" />
                                                            )}
                                                        </h4>
                                                    </div>
                                                    <div className="flex items-center gap-2 mt-1">
                                                        <Mail className="h-3 w-3 text-muted-foreground" />
                                                        <p className="text-xs text-muted-foreground">
                                                            {reviewer.email}
                                                        </p>
                                                    </div>
                                                    <div className="flex items-center gap-2 mt-1">
                                                        <Shield className="h-3 w-3 text-muted-foreground" />
                                                        <p className="text-xs text-muted-foreground font-mono">
                                                            ID: {reviewer.user_id.substring(0, 8)}...
                                                        </p>
                                                    </div>
                                                    
                                                    {/* Hanya tampilkan badge Dosen saja */}
                                                    {reviewer.has_dosen_akses && (
                                                        <div className="flex flex-wrap gap-1 mt-2">
                                                            <Badge variant="green" className="text-xs">
                                                                Dosen
                                                            </Badge>
                                                            {!filterDosenOnly && reviewer.akses_list && reviewer.akses_list.length > 1 && (
                                                                <span className="text-xs text-muted-foreground ml-1">
                                                                    ({reviewer.akses_list.length - 1} akses lainnya)
                                                                </span>
                                                            )}
                                                        </div>
                                                    )}
                                                </div>
                                            </div>

                                            <div className="flex flex-col items-end gap-2">
                                                {reviewer.is_invited ? (
                                                    <Button
                                                        variant="secondary"
                                                        disabled
                                                        className="gap-2 bg-green-100 text-green-700 hover:bg-green-100 border border-green-200"
                                                    >
                                                        <CheckCircle2 className="h-4 w-4" />
                                                        Terundang
                                                    </Button>
                                                ) : (
                                                    <Button
                                                        size="sm"
                                                        onClick={() => handleInviteFinal(reviewer.user_id)}
                                                        disabled={processingId === reviewer.user_id}
                                                        className={reviewer.has_dosen_akses ? "bg-green-600 hover:bg-green-700" : ""}
                                                    >
                                                        {processingId === reviewer.user_id ? (
                                                            <>
                                                                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                                                Mengundang...
                                                            </>
                                                        ) : (
                                                            <>
                                                                <UserPlus className="mr-2 h-4 w-4" />
                                                                Undang
                                                            </>
                                                        )}
                                                    </Button>
                                                )}
                                            </div>
                                        </CardContent>
                                    </Card>
                                ))
                            ) : (
                                <div className="text-center py-12 text-muted-foreground">
                                    {search ? (
                                        <div className="space-y-2">
                                            <Search className="h-12 w-12 mx-auto text-muted-foreground" />
                                            <p>Tidak ditemukan dosen dengan pencarian "{search}".</p>
                                            <Button 
                                                variant="outline" 
                                                size="sm"
                                                onClick={() => setSearch("")}
                                            >
                                                Reset Pencarian
                                            </Button>
                                        </div>
                                    ) : filterDosenOnly ? (
                                        <div className="space-y-2">
                                            <Filter className="h-12 w-12 mx-auto text-muted-foreground" />
                                            <p>Tidak ada reviewer dengan akses Dosen.</p>
                                            <p className="text-sm">Coba matikan filter "Hanya Dosen".</p>
                                            <Button 
                                                variant="outline" 
                                                size="sm"
                                                onClick={() => setFilterDosenOnly(false)}
                                            >
                                                Tampilkan Semua Akses
                                            </Button>
                                        </div>
                                    ) : (
                                        <div className="space-y-2">
                                            <Users className="h-12 w-12 mx-auto text-muted-foreground" />
                                            <p>Tidak ada data reviewer tersedia.</p>
                                            <p className="text-sm">Semua reviewer sudah diundang atau tidak ada data.</p>
                                        </div>
                                    )}
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Kolom Kanan: Info */}
                    <div className="space-y-6">
                        <Card className="bg-muted/30 border-dashed">
                            <CardHeader>
                                <CardTitle className="text-base flex items-center gap-2">
                                    <Users className="h-5 w-5" />
                                    Informasi Reviewer
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="text-sm space-y-3 text-muted-foreground">
                                <p className="flex items-start gap-2">
                                    <BadgeCheck className="h-4 w-4 text-green-500 flex-shrink-0 mt-0.5" />
                                    <span>
                                        <strong>Semua yang ditampilkan adalah Dosen</strong>.
                                    </span>
                                </p>
                                <p className="flex items-start gap-2">
                                    <Filter className="h-4 w-4 text-blue-500 flex-shrink-0 mt-0.5" />
                                    <span>
                                        Filter <strong>"Hanya Dosen"</strong> aktif - hanya menampilkan user dengan akses Dosen.
                                    </span>
                                </p>
                                <p>
                                    • User mungkin memiliki akses lain, tetapi yang relevan untuk review buku adalah akses <strong>Dosen</strong>.
                                </p>
                                <p>
                                    • Anda dapat mengundang lebih dari satu reviewer.
                                </p>
                            </CardContent>
                        </Card>

                        <Card className="border-green-200 bg-green-50/30">
                            <CardHeader>
                                <CardTitle className="text-base flex items-center gap-2 text-green-800">
                                    <BadgeCheck className="h-5 w-5" />
                                    Statistik Dosen
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <div className="flex justify-between items-center">
                                    <span className="text-sm">Total Dosen</span>
                                    <span className="font-semibold text-green-700">{stats.totalReviewers}</span>
                                </div>
                                <div className="flex justify-between items-center">
                                    <div className="flex items-center gap-2">
                                        <BadgeCheck className="h-3 w-3 text-green-500" />
                                        <span className="text-sm">Dengan Akses Dosen</span>
                                    </div>
                                    <span className="font-semibold text-green-600">
                                        {stats.withDosenAkses}
                                    </span>
                                </div>
                                <div className="flex justify-between items-center">
                                    <span className="text-sm">Tersedia</span>
                                    <span className="font-semibold">
                                        {stats.available}
                                    </span>
                                </div>
                                <div className="flex justify-between items-center">
                                    <span className="text-sm">Terundang</span>
                                    <span className="font-semibold text-blue-600">
                                        {stats.invited}
                                    </span>
                                </div>
                                <div className="pt-2 border-t border-green-200">
                                    <div className="flex justify-between items-center">
                                        <span className="text-sm">Mode Tampilan</span>
                                        <Badge variant={filterDosenOnly ? "green" : "outline"} className="text-xs">
                                            {filterDosenOnly ? "Hanya Dosen" : "Semua Akses"}
                                        </Badge>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle className="text-base flex items-center gap-2">
                                    <Shield className="h-5 w-5" />
                                    Informasi Sistem
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="text-sm space-y-2 text-muted-foreground">
                                <p>
                                    Sistem hanya menampilkan user yang memiliki <strong>akses Dosen</strong>.
                                </p>
                                <p>
                                    User mungkin memiliki akses lain (Admin, Staff, dll), tetapi untuk review buku hanya status <strong>Dosen</strong> yang relevan.
                                </p>
                                <div className="mt-2 pt-2 border-t">
                                    <p className="font-medium text-foreground">Prinsip Seleksi:</p>
                                    <p className="text-xs mt-1">1. Memiliki akses Dosen (wajib)</p>
                                    <p className="text-xs">2. Akses lain tidak ditampilkan untuk fokus pada kapasitas sebagai reviewer</p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}