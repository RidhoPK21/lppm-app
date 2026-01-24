import AppLayout from "@/layouts/app-layout";
import { Head } from "@inertiajs/react";

export default function KeuanganHomePage() {
    return (
        <AppLayout pageName="Dashboard Keuangan">
            <Head title="Dashboard Keuangan" />

            <div className="p-6">
                <div className="border rounded-lg p-6 shadow-sm bg-white">
                    <h1 className="text-2xl font-semibold">👋 Hay, Keuangan</h1>

                    <p className="text-sm text-gray-500 mt-1">
                        Selamat datang di aplikasi ITDel Starter!
                    </p>
                </div>
            </div>
        </AppLayout>
    );
}
