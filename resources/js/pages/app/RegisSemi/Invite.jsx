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
} from "lucide-react";
import { toast } from "sonner";

export default function InviteReviewer({ book, availableReviewers }) {
    const [search, setSearch] = useState("");
    const [processingId, setProcessingId] = useState(null);

    // Filter dosen
    const filteredReviewers = availableReviewers.filter(
        (user) =>
            user.name.toLowerCase().includes(search.toLowerCase()) ||
            user.email.toLowerCase().includes(search.toLowerCase())
    );

    // Handler Undang
    const handleInvite = (userId) => {
        setProcessingId(userId);
        router.post(
            route("regis-semi.store-invite", book.id),
            {
                user_id: userId,
            },
            {
                onSuccess: () => {
                    setProcessingId(null);
                    toast.success("Undangan berhasil dikirim");
                },
                onError: () => {
                    setProcessingId(null);
                    toast.error("Gagal mengundang reviewer");
                },
                preserveScroll: true,
            }
        );
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
                        // [PERBAIKAN DI SINI: Gunakan regis-semi.show]
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
                        <div className="relative">
                            <Search className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                            <Input
                                placeholder="Cari nama dosen atau NIDN..."
                                className="pl-10 h-12 text-base"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                            />
                        </div>

                        <div className="space-y-3">
                            {filteredReviewers.length > 0 ? (
                                filteredReviewers.map((reviewer) => (
                                    <Card
                                        key={reviewer.id}
                                        className="hover:border-primary/50 transition-colors"
                                    >
                                        <CardContent className="p-4 flex items-center justify-between">
                                            <div className="flex items-center gap-4">
                                                <Avatar className="h-10 w-10">
                                                    <AvatarImage
                                                        src={`https://ui-avatars.com/api/?name=${reviewer.name}&background=random`}
                                                    />
                                                    <AvatarFallback>
                                                        {reviewer.initial}
                                                    </AvatarFallback>
                                                </Avatar>
                                                <div>
                                                    <h4 className="font-semibold text-sm md:text-base">
                                                        {reviewer.name}
                                                    </h4>
                                                    <p className="text-xs text-muted-foreground">
                                                        {reviewer.email}
                                                    </p>
                                                </div>
                                            </div>

                                            {reviewer.is_invited ? (
                                                <Button
                                                    variant="secondary"
                                                    disabled
                                                    className="gap-2 bg-green-100 text-green-700 hover:bg-green-100"
                                                >
                                                    <CheckCircle2 className="h-4 w-4" />{" "}
                                                    Terundang
                                                </Button>
                                            ) : (
                                                <Button
                                                    size="sm"
                                                    onClick={() =>
                                                        handleInvite(
                                                            reviewer.id
                                                        )
                                                    }
                                                    disabled={
                                                        processingId ===
                                                        reviewer.id
                                                    }
                                                >
                                                    {processingId ===
                                                    reviewer.id ? (
                                                        <Loader2 className="h-4 w-4 animate-spin" />
                                                    ) : (
                                                        <>
                                                            <UserPlus className="mr-2 h-4 w-4" />{" "}
                                                            Undang
                                                        </>
                                                    )}
                                                </Button>
                                            )}
                                        </CardContent>
                                    </Card>
                                ))
                            ) : (
                                <div className="text-center py-12 text-muted-foreground">
                                    <p>
                                        Tidak ditemukan dosen dengan nama "
                                        {search}".
                                    </p>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Kolom Kanan: Info */}
                    <div className="space-y-6">
                        <Card className="bg-muted/30 border-dashed">
                            <CardHeader>
                                <CardTitle className="text-base">
                                    Informasi
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="text-sm space-y-2 text-muted-foreground">
                                <p>
                                    • Reviewer akan mendapatkan notifikasi email
                                    setelah diundang.
                                </p>
                                <p>
                                    • Anda dapat mengundang lebih dari satu
                                    reviewer.
                                </p>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}