<?php
/**
 * APLIKASI GACHA NAMA - PEMBUAT KELOMPOK
 * Bahasa: PHP (OOP)
 *
 *  - Kelas induk  : Peserta            (5 atribut, 8 method)
 *  - Kelas anak   : PesertaGacha       (5 atribut sendiri, 6 method) -> INHERITANCE
 *  - Kelas service: GachaKelompok      (5 atribut, 5 method)
 *  - Enkapsulasi  : atribut private + getter/setter (dengan validasi)
 *  - Instansiasi  : new PesertaGacha(...), new GachaKelompok(...)
 */

/* ===================== KELAS INDUK ===================== */
class Peserta
{
    // 5 atribut (ENKAPSULASI: private, diakses lewat getter/setter)
    private int $id;
    private string $nama;
    private string $inisial;
    private string $tanggalDaftar;
    private bool $aktif;

    public function __construct(int $id, string $nama)
    {
        $this->id = $id;
        $this->setNama($nama);
        $this->tanggalDaftar = date('d-m-Y');
        $this->aktif = true;
    }

    // Method 1 : getter nama
    public function getNama(): string
    {
        return $this->nama;
    }

    // Method 2 : setter nama (dengan validasi), sekaligus membuat inisial
    public function setNama(string $nama): void
    {
        $nama = trim($nama);
        if ($nama === '') {
            throw new InvalidArgumentException('Nama tidak boleh kosong.');
        }
        $this->nama = ucwords(strtolower($nama));
        $this->inisial = strtoupper(substr($this->nama, 0, 1));
    }

    // Method 3
    public function getInisial(): string
    {
        return $this->inisial;
    }

    // Method 4
    public function getTanggalDaftar(): string
    {
        return $this->tanggalDaftar;
    }

    // Method 5
    public function getId(): int
    {
        return $this->id;
    }

    // Method 6
    public function isAktif(): bool
    {
        return $this->aktif;
    }

    // Method 7
    public function nonaktifkan(): void
    {
        $this->aktif = false;
    }

    // Method 8 : info (akan di-override oleh kelas anak)
    public function getInfo(): string
    {
        return $this->nama . ' (terdaftar: ' . $this->tanggalDaftar . ')';
    }
}

/* ===================== KELAS ANAK (INHERITANCE) ===================== */
class PesertaGacha extends Peserta
{
    // 5 atribut milik kelas anak
    private int $kelompok = 0;
    private bool $ketua = false;
    private int $nomorUrut = 0;
    private string $waktuGacha = '';
    private string $warna = '#6c757d';

    private const PALET = [
        '#e63946', '#f4a261', '#2a9d8f', '#457b9d', '#8e44ad',
        '#e76f51', '#118ab2', '#06d6a0', '#d62828', '#6a994e',
    ];

    public function __construct(int $id, string $nama)
    {
        parent::__construct($id, $nama); // memanggil constructor induk
    }

    // Method 1 : masukkan peserta ke kelompok hasil gacha
    public function setKelompok(int $no, int $urut): void
    {
        $this->kelompok = $no;
        $this->nomorUrut = $urut;
        $this->waktuGacha = date('H:i:s');
        $this->warna = self::PALET[($no - 1) % count(self::PALET)];
    }

    // Method 2
    public function getKelompok(): int
    {
        return $this->kelompok;
    }

    // Method 3 : jadikan ketua kelompok
    public function jadiKetua(): void
    {
        $this->ketua = true;
    }

    // Method 4
    public function isKetua(): bool
    {
        return $this->ketua;
    }

    // Method 5
    public function getWarna(): string
    {
        return $this->warna;
    }

    // Method 6 : OVERRIDE method induk
    public function getInfo(): string
    {
        $info = parent::getInfo() . ' | Urutan #' . $this->nomorUrut . ' | Digacha: ' . $this->waktuGacha;
        return $this->ketua ? '[KETUA] ' . $info : $info;
    }
}

/* ===================== KELAS SERVICE ===================== */
class GachaKelompok
{
    private array $daftar = [];        // array objek PesertaGacha
    private int $jumlahKelompok;
    private bool $pilihKetua;
    private array $hasil = [];         // [no kelompok => [PesertaGacha, ...]]
    private array $error = [];

    public function __construct(int $jumlahKelompok, bool $pilihKetua = true)
    {
        $this->jumlahKelompok = max(1, $jumlahKelompok);
        $this->pilihKetua = $pilihKetua;
    }

    // Method 1 : parsing teks -> objek (INSTANSIASI kelas ke objek)
    public function muatDariTeks(string $teks): void
    {
        $id = 1;
        foreach (preg_split('/\R/', $teks) as $baris) {
            if (trim($baris) === '') {
                continue;
            }
            try {
                $this->daftar[] = new PesertaGacha($id++, $baris);
            } catch (InvalidArgumentException $e) {
                $this->error[] = $e->getMessage();
            }
        }
    }

    // Method 2 : GACHA / acak lalu bagi rata (round-robin)
    public function gacha(): bool
    {
        $total = count($this->daftar);
        if ($total < $this->jumlahKelompok) {
            $this->error[] = "Jumlah peserta ($total) lebih sedikit dari jumlah kelompok ({$this->jumlahKelompok}).";
            return false;
        }

        shuffle($this->daftar);
        $this->hasil = array_fill(1, $this->jumlahKelompok, []);

        foreach ($this->daftar as $i => $p) {
            $no = ($i % $this->jumlahKelompok) + 1;
            $urut = count($this->hasil[$no]) + 1;
            $p->setKelompok($no, $urut);
            $this->hasil[$no][] = $p;
        }

        if ($this->pilihKetua) {
            foreach ($this->hasil as $anggota) {
                $anggota[array_rand($anggota)]->jadiKetua();
            }
        }
        return true;
    }

    // Method 3
    public function getHasil(): array
    {
        return $this->hasil;
    }

    // Method 4
    public function getError(): array
    {
        return $this->error;
    }

    // Method 5
    public function getTotalPeserta(): int
    {
        return count($this->daftar);
    }
}

/* ===================== LOGIKA HALAMAN ===================== */
$teksInput = $_POST['nama'] ?? '';
$jumlah = (int) ($_POST['jumlah'] ?? 3);
$ketua = isset($_POST['ketua']) || $_SERVER['REQUEST_METHOD'] !== 'POST';
$app = null;
$sukses = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $app = new GachaKelompok($jumlah, $ketua);       // instansiasi objek
    $app->muatDariTeks($teksInput);
    $sukses = $app->gacha();
}

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Gacha Kelompok</title>
<style>
  *{box-sizing:border-box}
  body{margin:0;font-family:system-ui,Segoe UI,Arial,sans-serif;background:#f1f3f9;color:#222}
  header{background:linear-gradient(135deg,#6a11cb,#2575fc);color:#fff;padding:28px 16px;text-align:center}
  header h1{margin:0 0 6px;font-size:1.8rem}
  main{max-width:960px;margin:20px auto;padding:0 14px}
  .card{background:#fff;border-radius:14px;padding:20px;box-shadow:0 3px 12px rgba(0,0,0,.08);margin-bottom:20px}
  label{font-weight:600;display:block;margin:10px 0 6px}
  textarea,input[type=number]{width:100%;padding:10px;border:1px solid #ccd;border-radius:8px;font:inherit}
  textarea{min-height:170px}
  button{background:#2575fc;color:#fff;border:0;padding:12px 26px;border-radius:10px;font-size:1rem;cursor:pointer;margin-top:14px}
  button:hover{background:#1a5ed6}
  .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(270px,1fr));gap:16px}
  .klp{border-radius:14px;overflow:hidden;background:#fff;box-shadow:0 3px 12px rgba(0,0,0,.1)}
  .klp h3{margin:0;padding:12px 16px;color:#fff}
  .klp ul{list-style:none;margin:0;padding:10px 16px}
  .klp li{padding:8px 0;border-bottom:1px dashed #ddd;display:flex;align-items:center;gap:10px}
  .klp li:last-child{border:0}
  .ava{width:32px;height:32px;border-radius:50%;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0}
  .badge{background:#ffd60a;color:#222;font-size:.7rem;font-weight:700;padding:2px 8px;border-radius:20px;margin-left:6px}
  .err{background:#ffe3e3;color:#b00020;padding:10px 14px;border-radius:8px;margin-bottom:12px}
  .hint{color:#666;font-size:.9rem}
</style>
</head>
<body>
<header>
  <h1>🎲 Gacha Nama Kelompok</h1>
  <div>Masukkan nama, tentukan jumlah kelompok, lalu gacha!</div>
</header>

<main>
  <form method="post" class="card">
    <label for="nama">Daftar Nama (satu baris satu orang)</label>
    <textarea id="nama" name="nama" placeholder="Contoh:&#10;Budi Santoso&#10;Siti Aminah&#10;Andi"><?= e($teksInput) ?></textarea>

    <label for="jumlah">Jumlah Kelompok</label>
    <input type="number" id="jumlah" name="jumlah" min="1" max="50" value="<?= $jumlah ?>">

    <label><input type="checkbox" name="ketua" <?= $ketua ? 'checked' : '' ?>> Pilih ketua kelompok secara acak</label>

    <button type="submit">🎲 Gacha Sekarang!</button>
  </form>

<?php if ($app !== null): ?>
  <?php foreach ($app->getError() as $err): ?>
    <div class="err"><?= e($err) ?></div>
  <?php endforeach; ?>

  <?php if ($sukses): ?>
    <div class="card">
      <strong>Hasil Gacha:</strong> <?= $app->getTotalPeserta() ?> peserta dibagi ke <?= $jumlah ?> kelompok.
    </div>
    <div class="grid">
      <?php foreach ($app->getHasil() as $no => $anggota): ?>
        <div class="klp">
          <h3 style="background:<?= e($anggota[0]->getWarna()) ?>">Kelompok <?= $no ?> (<?= count($anggota) ?> orang)</h3>
          <ul>
            <?php foreach ($anggota as $p): ?>
              <li>
                <span class="ava" style="background:<?= e($p->getWarna()) ?>"><?= e($p->getInisial()) ?></span>
                <span>
                  <strong><?= e($p->getNama()) ?></strong>
                  <?php if ($p->isKetua()): ?><span class="badge">KETUA</span><?php endif; ?>
                </span>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
<?php endif; ?>
</main>
</body>
</html>