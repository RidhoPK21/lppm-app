import AppLayout from "@/layouts/app-layout";

export default function KetuaHomePage() {
    return (
        <AppLayout pageName="Dashboard Ketua LPPM">
            <div className="border rounded-lg p-6 bg-white shadow-sm">
                <h1 className="text-2xl font-semibold">Hay, Ketua LPPM</h1>
                <p className="text-sm text-gray-500 mt-1">
                    Selamat datang di Sistem LPPM IT Del
                </p>
            </div>
        </AppLayout>
    );
}
