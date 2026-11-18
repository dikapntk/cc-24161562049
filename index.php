<?php
$dbUrl = getenv("DATABASE_URL");
$appEnv = getenv("APP_ENV") ?: "development";

if (!$dbUrl) {
    die("DATABASE_URL belum tersedia");
}

$u = parse_url($dbUrl);

$dsn = "pgsql:host={$u['host']};port=" . ($u['port'] ?? 5432)
     . ";dbname=" . ltrim($u['path'], '/');

try {
    $pdo = new PDO($dsn, $u['user'], $u['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Koneksi database gagal.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pesan = trim($_POST['pesan'] ?? '');

    if ($nama !== '' && $pesan !== '') {
        $stmt = $pdo->prepare(
            "INSERT INTO buku_tamu (nama, email, pesan)
             VALUES (:nama, :email, :pesan)"
        );
        $stmt->execute([
            ':nama' => $nama,
            ':email' => $email,
            ':pesan' => $pesan
        ]);

        header("Location: /");
        exit;
    }
}

$data = $pdo->query(
    "SELECT * FROM buku_tamu ORDER BY created_at DESC"
)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Buku Tamu</title>
<style>
body { font-family: Arial; background:#f1f5f9; margin:0; padding:30px; }
.container { max-width:750px; margin:auto; }
h1 { color:#166534; text-align:center; }
.card { background:white; padding:22px; border-radius:12px;
        margin-bottom:20px; box-shadow:0 2px 8px #0001; }
input, textarea { width:100%; padding:12px; margin:8px 0;
                  box-sizing:border-box; border:1px solid #ddd;
                  border-radius:7px; }
button { background:#15803d; color:white; padding:12px 22px;
         border:0; border-radius:7px; cursor:pointer; }
small { color:#64748b; }
</style>
</head>
<body>
<div class="container">
    <h1>📖 Buku Tamu</h1>
    <p style="text-align:center">Silakan tinggalkan pesan dan kesan Anda.</p>

    <div class="card">
        <h2>Isi Buku Tamu</h2>
        <form method="POST">
            <input name="nama" placeholder="Nama lengkap" required>
            <input type="email" name="email" placeholder="Email">
            <textarea name="pesan" placeholder="Tulis pesan..." required></textarea>
            <button type="submit">Kirim Pesan</button>
        </form>
    </div>

    <div class="card">
        <h2>Daftar Pesan</h2>
        <?php foreach ($data as $item): ?>
            <div style="border-bottom:1px solid #eee;padding:12px 0">
                <strong><?= htmlspecialchars($item['nama']) ?></strong><br>
                <small><?= htmlspecialchars($item['email'] ?? '') ?></small>
                <p><?= nl2br(htmlspecialchars($item['pesan'])) ?></p>
                <small><?= htmlspecialchars($item['created_at']) ?></small>
            </div>
        <?php endforeach; ?>

        <?php if (!$data): ?>
            <p>Belum ada pesan. Jadilah yang pertama!</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
