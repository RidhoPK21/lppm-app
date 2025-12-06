<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INSTITUT TEKNOLOGI DEL - Formulir Permohonan Penghargaan Buku</title>
    <style>
        /* Reset dan pengaturan umum */
        @page {
            margin: 1.3cm 1.8cm;
            size: A4;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.35;
            color: #000;
            margin: 0;
            padding: 0;
        }

        /* Header Kampus */
        .header-container {
            display: flex;
            align-items: center;
            padding-bottom: 5px;
            border-bottom: 3px solid #000;
            margin-bottom: 10px;
        }

        .logo-box {
            margin-right: 12px;
            width: 70px;
            height: auto;
            flex-shrink: 0;
        }

        .logo-box img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        .info-text {
            line-height: 1.1;
            flex-grow: 1;
            text-align: center;
        }

        .title {
            font-size: 15pt;
            font-weight: bold;
            margin: 0;
            letter-spacing: 0.7px;
        }

        .address, .contact {
            font-size: 8pt;
            margin: 0;
        }

        .contact a {
            color: #000;
            text-decoration: none;
        }

        /* Formulir Surat - Halaman 1 */
        .form-title {
            text-align: left;
            margin: 10px 0 8px 0;
            font-size: 11pt;
        }

        .form-date {
            text-align: right;
            margin: 0 0 10px 0;
            font-size: 11pt;
        }

        .perihal-section {
            margin: 10px 0;
        }

        .perihal-section table {
            width: 100%;
            border-collapse: collapse;
        }

        .perihal-section td {
            padding: 1px 0;
            vertical-align: top;
            font-size: 11pt;
        }

        .perihal-section td:first-child {
            width: 75px;
        }

        .perihal-section td:nth-child(2) {
            width: 15px;
        }

        .kepada-yth {
            margin: 12px 0;
            font-size: 11pt;
        }

        .kepada-yth strong {
            font-weight: bold;
        }

        /* Section konten */
        .content-section {
            margin: 10px 0;
        }

        .content-section p {
            margin: 8px 0;
            text-align: justify;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
        }

        .data-table td {
            padding: 2px 0;
            vertical-align: top;
            font-size: 11pt;
        }

        .data-table td:first-child {
            width: 140px;
        }

        .data-table td:nth-child(2) {
            width: 15px;
        }

        ol {
            margin: 0;
            padding-left: 22px;
            line-height: 1.4;
        }

        ol li {
            padding: 1px 0;
        }

        /* Signature */
        .signature-section {
            margin-top: 20px;
        }

        .signature-text {
            font-size: 11pt;
        }

        .signed-indicator {
            margin-top: 40px;
            font-style: italic;
            color: #666;
        }

        .signature-line {
            margin-top: 50px;
            border-bottom: 1px solid #000;
            width: 180px;
        }

        .signature-name {
            margin-top: 3px;
        }

        /* Halaman baru */
        .page-break {
            page-break-before: always;
            margin-top: 0;
        }

        /* Halaman 2 - Checklist */
        .checklist-title {
            font-weight: bold;
            margin: 10px 0 8px 0;
            font-size: 11pt;
        }

        .checklist-table {
            width: 100%;
            border: 1px solid #000;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .checklist-table th,
        .checklist-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
            font-size: 10pt;
        }

        .checklist-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .checklist-table td:first-child {
            text-align: center;
            width: 35px;
        }

        .checklist-table td:last-child {
            word-break: break-all;
            font-size: 9pt;
            width: 180px;
        }
    </style>
</head>
<body>
    <!-- HALAMAN 1 -->
    <!-- Header Kampus -->
    <div class="header-container">
        <div class="logo-box">
            <img src="https://www.del.ac.id/wp-content/uploads/2023/07/logo-it-del.png" alt="Logo Institut Teknologi Del">
        </div>

        <div class="info-text">
            <h1 class="title">INSTITUT TEKNOLOGI DEL</h1>
            <p class="address">Jl. Sisingamangaraja, Kec. Laguboti, Kab. Toba Samosir – 22381</p>
            <p class="address">Sumatera Utara, Indonesia</p>
            <p class="contact">Telp: (0632) 331234, Fax: (632) 331116</p>
            <p class="contact">Website: <a href="http://www.del.ac.id">www.del.ac.id</a>, Email: lppm@del.ac.id</p>
        </div>
    </div>

    <!-- Judul Formulir -->
    <div class="form-title">
        Formulir 8. Surat Permohonan Mendapatkan Penghargaan Buku
    </div>

    <!-- Tanggal -->
    <div class="form-date">
        Laguboti, {{ $date }}
    </div>

    <!-- Perihal dan Lampiran -->
    <div class="perihal-section">
        <table>
            <tr>
                <td>Perihal</td>
                <td>:</td>
                <td>Surat Permohonan Mendapatkan Penghargaan</td>
            </tr>
            <tr>
                <td style="vertical-align: top;">Lampiran</td>
                <td style="vertical-align: top;">:</td>
                <td>
                    <ol>
                        <li>Berita Acara Serah Terima Buku ke Perpustakaan</li>
                        <li>Hasil Scan Penerbitan Buku</li>
                        <li>Hasil Review oleh Penerbit</li>
                        <li>Surat Pernyataan (Penerbitan Tidak Didanai oleh Institusi + Bukti Biaya Penerbitan)</li>
                    </ol>
                </td>
            </tr>
        </table>
    </div>

    <!-- Kepada Yth -->
    <div class="kepada-yth">
        <strong>Kepada Yth.</strong><br>
        <strong>Ketua LPPM Institut Teknologi Del</strong><br>
        <strong>di-</strong><br>
        <span style="margin-left: 40px;"><strong>Tempat</strong></span>
    </div>

    <!-- Konten Surat -->
    <div class="content-section">
        <p>Saya yang bertanda tangan di bawah ini:</p>
        
        <table class="data-table">
            <tr>
                <td>Nama</td>
                <td>:</td>
                <td>{{ $user->name ?? '-' }}</td>
            </tr>
            <tr>
                <td>NIDN</td>
                <td>:</td>
                <td>{{ $user->NIDN ?? '-' }}</td>
            </tr>
            <tr>
                <td>Prodi</td>
                <td>:</td>
                <td>{{ $user->ProgramStudi ?? '-' }}</td>
            </tr>
            <tr>
                <td>Sinta ID</td>
                <td>:</td>
                <td>{{ $user->SintaID ?? '-' }}</td>
            </tr>
            <tr>
                <td>Scopus ID</td>
                <td>:</td>
                <td>{{ $user->ScopusID ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="content-section">
        <p>Dengan ini memohon mendapatkan penghargaan <span style="margin-left: 10px;">+</span> dengan rincian sebagai berikut.</p>
        
        <table class="data-table">
            <tr>
                <td>Judul Buku</td>
                <td>:</td>
                <td>{{ $book->title }}</td>
            </tr>
            <tr>
                <td>Jenis Buku</td>
                <td>:</td>
                <td>{{ 
                    $book->book_type === 'TEACHING' ? 'Buku Ajar' :
                    ($book->book_type === 'REFERENCE' ? 'Buku Referensi' :
                    ($book->book_type === 'MONOGRAPH' ? 'Monograf' :
                    ($book->book_type === 'CHAPTER' ? 'Book Chapter' : $book->book_type)))
                }}</td>
            </tr>
            <tr>
                <td>Bidang Keilmuan</td>
                <td>:</td>
                <td>{{ $user->ProgramStudi ?? '-' }}</td>
            </tr>
            <tr>
                <td>Penerbit</td>
                <td>:</td>
                <td>{{ $book->publisher }}</td>
            </tr>
            <tr>
                <td>ISBN</td>
                <td>:</td>
                <td>{{ $book->isbn }}</td>
            </tr>
            <tr>
                <td>Jumlah Halaman</td>
                <td>:</td>
                <td>{{ $book->total_pages }} halaman</td>
            </tr>
            <tr>
                <td style="vertical-align: top;">Penulis</td>
                <td style="vertical-align: top;">:</td>
                <td>
                    <ol>
                        @foreach($authors as $author)
                            <li>{{ $author }}</li>
                        @endforeach
                    </ol>
                </td>
            </tr>
        </table>
    </div>

    <div class="content-section">
        <p>Demikian surat permohonan ini saya sampaikan, terima kasih.</p>
    </div>

    <!-- Tanda Tangan -->
    <div class="signature-section">
        <div class="signature-text">Hormat Saya,</div>
        <div class="signed-indicator">
            <em>Signed</em>
        </div>
        <div class="signature-line"></div>
        <div class="signature-name">({{ $user->name ?? '...................................................' }})</div>
    </div>

    <!-- HALAMAN 2 -->
    <div class="page-break"></div>

    <!-- Header Kampus Halaman 2 -->
    <div class="header-container">
        <div class="logo-box">
            <img src="https://www.del.ac.id/wp-content/uploads/2023/07/logo-it-del.png" alt="Logo Institut Teknologi Del">
        </div>

        <div class="info-text">
            <h1 class="title">INSTITUT TEKNOLOGI DEL</h1>
            <p class="address">Jl. Sisingamangaraja, Kec. Laguboti, Kab. Toba Samosir – 22381</p>
            <p class="address">Sumatera Utara, Indonesia</p>
            <p class="contact">Telp: (0632) 331234, Fax: (632) 331116</p>
            <p class="contact">Website: <a href="http://www.del.ac.id">www.del.ac.id</a>, Email: lppm@del.ac.id</p>
        </div>
    </div>

    <!-- Ceklis Lampiran -->
    <div class="checklist-title">Ceklis Lampiran</div>

    <table class="checklist-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Artefak Paper</th>
                <th>Link Google Drive</th>
            </tr>
        </thead>
        <tbody>
            @foreach($documentLabels as $index => $label)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $label }}</td>
                <td>{{ $links[$index] ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>