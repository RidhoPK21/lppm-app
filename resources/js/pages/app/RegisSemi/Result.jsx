// File: resources/js/Pages/App/RegisSemi/Result.jsx

import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import AppLayout from "@/layouts/app-layout";
import { Head, router } from '@inertiajs/react';
import { ArrowLeft, User, Calendar, MessageSquare, FileText } from "lucide-react";
import PropTypes from 'prop-types';

/**
 * Komponen untuk menampilkan satu komentar/penilaian dari reviewer
 */
const ReviewerCommentCard = ({ reviewerName, comment, reviewedAt, index }) => {
    return (
        <div className="border-2 border-black dark:border-white rounded-lg overflow-hidden bg-white dark:bg-gray-900 shadow-lg hover:shadow-xl transition-shadow">
            {/* Header Card */}
            <div className="bg-black dark:bg-white p-4 border-b-2 border-black dark:border-white">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <div className="w-10 h-10 rounded-full bg-white dark:bg-black flex items-center justify-center border-2 border-white dark:border-black">
                            <User className="h-5 w-5 text-black dark:text-white" />
                        </div>
                        <div>
                            <div className="text-sm font-bold text-white dark:text-black">
                                Reviewer #{index + 1}
                            </div>
                            <div className="text-xs text-gray-300 dark:text-gray-700 font-medium">
                                {reviewerName}
                            </div>
                        </div>
                    </div>
                    {reviewedAt && (
                        <div className="flex items-center gap-2 text-gray-300 dark:text-gray-700">
                            <Calendar className="h-4 w-4" />
                            <span className="text-xs font-medium">{reviewedAt}</span>
                        </div>
                    )}
                </div>
            </div>

            {/* Comment Content */}
            <div className="p-5 bg-white dark:bg-gray-900">
                <div className="flex items-start gap-2 mb-3">
                    <MessageSquare className="h-5 w-5 text-black dark:text-white mt-0.5 shrink-0" />
                    <h4 className="font-bold text-black dark:text-white">Catatan Review:</h4>
                </div>
                <div className="pl-7">
                    <p className="text-sm text-gray-800 dark:text-gray-200 leading-relaxed whitespace-pre-wrap">
                        {comment}
                    </p>
                </div>
            </div>
        </div>
    );
};

ReviewerCommentCard.propTypes = {
    reviewerName: PropTypes.string.isRequired,
    comment: PropTypes.string.isRequired,
    reviewedAt: PropTypes.string,
    index: PropTypes.number.isRequired
};

/**
 * Komponen Halaman Hasil Penilaian Reviewer
 */
export default function Result({ 
    bukuId, 
    bookTitle, 
    bookIsbn,
    bookAuthor,
    results = [], 
    reviewCount = 0 
}) { 
    const handleGoBack = () => {
        router.visit(route('regis-semi.show', bukuId));
    };

    return (
        <AppLayout>
            <Head title={`Review Hasil - ${bookTitle || 'Buku'}`} />

            <div className="max-w-6xl w-full mx-auto px-4 md:px-8 py-6 space-y-6">
                {/* HEADER */}
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div className="flex items-center gap-4">
                        <Button 
                            onClick={handleGoBack}
                            variant="outline"
                            className="border-2 border-black dark:border-white hover:bg-black hover:text-white dark:hover:bg-white dark:hover:text-black font-semibold transition-colors"
                        >
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Kembali
                        </Button>
                        <div>
                            <h1 className="text-2xl font-bold text-black dark:text-white">
                                Hasil Review Buku
                            </h1>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                Total {reviewCount} review telah diterima
                            </p>
                        </div>
                    </div>
                </div>

                {/* BOOK INFO CARD */}
                <Card className="border-2 border-black dark:border-white shadow-lg">
                    <CardContent className="p-6">
                        <div className="flex items-start gap-3 mb-4">
                            <FileText className="h-6 w-6 text-black dark:text-white mt-1" />
                            <div className="flex-1">
                                <h2 className="text-lg font-bold text-black dark:text-white mb-1">
                                    {bookTitle}
                                </h2>
                                <div className="flex flex-wrap gap-4 text-sm text-gray-600 dark:text-gray-400">
                                    {bookIsbn && (
                                        <span className="flex items-center gap-1">
                                            <span className="font-semibold">ISBN:</span> {bookIsbn}
                                        </span>
                                    )}
                                    {bookAuthor && (
                                        <span className="flex items-center gap-1">
                                            <User className="h-4 w-4" />
                                            <span className="font-semibold">Penulis:</span> {bookAuthor}
                                        </span>
                                    )}
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* REVIEW RESULTS */}
                {results.length > 0 ? (
                    <div className="space-y-5">
                        <h3 className="text-lg font-bold text-black dark:text-white flex items-center gap-2">
                            <MessageSquare className="h-5 w-5" />
                            Catatan dari Reviewer
                        </h3>
                        
                        {results.map((result, index) => (
                            <ReviewerCommentCard 
                                key={result.id}
                                reviewerName={result.reviewer_name}
                                comment={result.comment}
                                reviewedAt={result.formatted_date}
                                index={index}
                            />
                        ))}
                    </div>
                ) : (
                    <Card className="border-2 border-dashed border-gray-300 dark:border-gray-600">
                        <CardContent className="p-12 text-center">
                            <MessageSquare className="h-12 w-12 mx-auto text-gray-400 dark:text-gray-600 mb-4" />
                            <h3 className="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Belum Ada Review
                            </h3>
                            <p className="text-sm text-gray-500 dark:text-gray-400">
                                Buku ini belum mendapatkan review dari reviewer yang diundang.
                            </p>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}

Result.propTypes = {
    bukuId: PropTypes.number.isRequired,
    bookTitle: PropTypes.string,
    bookIsbn: PropTypes.string,
    bookAuthor: PropTypes.string,
    results: PropTypes.arrayOf(
        PropTypes.shape({
            id: PropTypes.number.isRequired,
            reviewer_name: PropTypes.string.isRequired,
            reviewer_email: PropTypes.string,
            comment: PropTypes.string.isRequired,
            reviewed_at: PropTypes.string,
            formatted_date: PropTypes.string
        })
    ),
    reviewCount: PropTypes.number
};