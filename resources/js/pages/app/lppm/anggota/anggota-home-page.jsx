import AppLayout from "@/layouts/app-layout";

export default function AnggotaHomePage() {
    return (
        <AppLayout>
            <div className="space-y-6">
                {/* ================================ */}
                {/* CARD WELCOME */}
                {/* ================================ */}
                <div className="border rounded-lg p-6 shadow-sm bg-white">
                    <h1 className="text-2xl font-semibold">
                        👋 Hay, Josua Saragih
                    </h1>

                    <p className="text-sm text-gray-500 mt-1">
                        Selamat datang di aplikasi ITDel Starter!
                    </p>
                </div>
            </div>
        </AppLayout>
    );
}
 