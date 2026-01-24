import React from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent } from '@/components/ui/card';

export default function KprodiHomePage() {
    return (
        <AppLayout breadcrumbs={[{ title: 'Beranda', href: '/kprodi/dashboard' }]}>
            <Head title="Dashboard Kaprodi" />
            
            <div className="p-6 pt-0 space-y-6">
                {/* Welcome Card Sederhana */}
                <Card className="bg-white border shadow-sm">
                    <CardContent className="p-8 flex flex-col gap-2">
                        <h2 className="text-3xl font-bold tracking-tight text-gray-900">
                            Hay, Josua Saragih
                        </h2>
                        <p className="text-lg text-muted-foreground">
                            Selamat datang di aplikasi LPPM, Kepala Prodi!
                        </p>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}