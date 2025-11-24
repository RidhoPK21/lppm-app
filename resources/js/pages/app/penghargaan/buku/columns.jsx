import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { MoreHorizontal, Eye, Edit, Trash } from "lucide-react";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";

export const columns = [
    {
        header: "No",
        cell: (info) => info.row.index + 1,
        meta: {
            className: "w-[50px]",
        },
    },
    {
        accessorKey: "judul",
        header: "Judul Buku",
        cell: ({ row }) => (
            <span className="font-medium">{row.original.judul}</span>
        ),
    },
    {
        accessorKey: "penulis",
        header: "Penulis",
    },
    {
        accessorKey: "penerbit",
        header: "Penerbit",
    },
    {
        accessorKey: "tahun",
        header: "Tahun",
    },
    {
        accessorKey: "isbn",
        header: "ISBN",
    },
    {
        accessorKey: "status",
        header: "Status",
        cell: ({ row }) => {
            const status = row.getValue("status");
            let color = "bg-gray-500";

            if (status === "disetujui")
                color = "bg-green-500 hover:bg-green-600";
            if (status === "diajukan")
                color = "bg-yellow-500 hover:bg-yellow-600";
            if (status === "ditolak") color = "bg-red-500 hover:bg-red-600";

            return <Badge className={`${color} capitalize`}>{status}</Badge>;
        },
    },
    {
        id: "actions",
        header: "Aksi",
        // FIX: Menghapus parameter { row } yang tidak terpakai untuk menghindari error ESLint
        cell: () => {
            return (
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button variant="ghost" className="h-8 w-8 p-0">
                            <span className="sr-only">Open menu</span>
                            <MoreHorizontal className="h-4 w-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuLabel>Aksi</DropdownMenuLabel>
                        <DropdownMenuItem>
                            <Eye className="mr-2 h-4 w-4" /> Detail
                        </DropdownMenuItem>
                        <DropdownMenuItem>
                            <Edit className="mr-2 h-4 w-4" /> Edit
                        </DropdownMenuItem>
                        <DropdownMenuItem className="text-red-600 focus:text-red-600">
                            <Trash className="mr-2 h-4 w-4" /> Hapus
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            );
        },
    },
];