
<?php

function pesan($isi) {
    echo "<!DOCTYPE html>
    <html lang='id'>
    <head>
        <meta charset='UTF-8'>
        <title>Hasil Registrasi</title>
        <link rel='stylesheet' href='style.css'>
    </head>
    <body>
        <div class='container'>
            <div class='form-card'>
                <h1>Hasil Registrasi</h1>
                <p>" . htmlspecialchars($isi) . "</p>
                <a href='index.html'>Kembali ke Form</a>
            </div>
        </div>
    </body>
    </html>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    pesan("Akses tidak valid.");
}

// Mengambil data formulir
$nama = trim($_POST["nama"] ?? "");
$email = trim($_POST["email"] ?? "");
$telepon = trim($_POST["telepon"] ?? "");
$kategori = $_POST["kategori"] ?? "";
$tiket = $_POST["tiket"] ?? "";
$alasan = trim($_POST["alasan"] ?? "");
$setuju = $_POST["setuju"] ?? "";

// Validasi nama
if (strlen($nama) < 3) {
    pesan("Nama wajib diisi minimal 3 karakter.");
}

// Validasi email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    pesan("Format email tidak valid.");
}

// Validasi nomor telepon
if (!preg_match('/^08[0-9]{8,11}$/', $telepon)) {
    pesan("Nomor telepon tidak valid.");
}

// Validasi kategori
if (!in_array($kategori, [
    "Mahasiswa", "Pelajar", "Umum", "Profesional"
], true)) {
    pesan("Kategori peserta tidak valid.");
}

// Validasi tiket
if (!in_array($tiket, [
    "Reguler", "VIP", "Online"
], true)) {
    pesan("Jenis tiket tidak valid.");
}

// Validasi alasan
if (strlen($alasan) > 500) {
    pesan("Alasan maksimal 500 karakter.");
}

// Validasi persetujuan
if ($setuju !== "1") {
    pesan("Persetujuan wajib dicentang.");
}

// Validasi file
if (!isset($_FILES["bukti"]) ||
    $_FILES["bukti"]["error"] !== UPLOAD_ERR_OK) {
    pesan("Bukti identitas wajib diunggah.");
}

$file = $_FILES["bukti"];

if ($file["size"] > 2 * 1024 * 1024) {
    pesan("Ukuran file maksimal 2 MB.");
}

// Memeriksa tipe file sebenarnya
$finfo = new finfo(FILEINFO_MIME_TYPE);
$tipe = $finfo->file($file["tmp_name"]);

$format = [
    "image/jpeg" => "jpg",
    "image/png" => "png",
    "application/pdf" => "pdf"
];

if (!isset($format[$tipe])) {
    pesan("Format file harus JPG, PNG, atau PDF.");
}

// Menyimpan file
$folder = __DIR__ . "/uploads/";

if (!is_dir($folder)) {
    mkdir($folder, 0755, true);
}

$namaFile = "bukti_" . bin2hex(random_bytes(8))
            . "." . $format[$tipe];

if (!move_uploaded_file(
    $file["tmp_name"],
    $folder . $namaFile
)) {
    pesan("File gagal disimpan.");
}

$nama_tampil = htmlspecialchars($nama, ENT_QUOTES, 'UTF-8');
$email_tampil = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
$telepon_tampil = htmlspecialchars($telepon, ENT_QUOTES, 'UTF-8');
$kategori_tampil = htmlspecialchars($kategori, ENT_QUOTES, 'UTF-8');
$tiket_tampil = htmlspecialchars($tiket, ENT_QUOTES, 'UTF-8');

echo <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Berhasil</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="success-page">
    <div class="success-card">

        <div class="success-icon">✓</div>

        <h1>Pendaftaran Berhasil!</h1>

        <p class="success-description">
            Terima kasih telah mendaftar Event Cyber Security.
            Data pendaftaran kamu berhasil divalidasi.
        </p>

        <div class="data-peserta">
            <p><strong>Nama Lengkap:</strong> {$nama_tampil}</p>
            <p><strong>Email:</strong> {$email_tampil}</p>
            <p><strong>Nomor Telepon:</strong> {$telepon_tampil}</p>
            <p><strong>Kategori Peserta:</strong> {$kategori_tampil}</p>
            <p><strong>Jenis Tiket:</strong> {$tiket_tampil}</p>
        </div>

        <a href="index.html" class="btn-kembali">
            Kembali ke Form
        </a>

    </div>
</div>

</body>
</html>
HTML;

exit;
