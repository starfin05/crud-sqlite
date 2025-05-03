<?php
// Koneksi ke database SQLite
$conn = new PDO('sqlite:database.db');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method = isset($_REQUEST['method']) ? $_REQUEST['method'] : false;
    switch ($method) {
        case 'tambah':
            $tugasBaru = $_POST['tugas'];
            if ($tugasBaru) {
                // SQL untuk memasukkan data ke dalam tabel 'tugas'
                $sql = 'INSERT INTO tugas(deskripsi, waktu) VALUES(:deskripsi, :waktu)';
                $statement = $conn->prepare($sql);
                
                // Waktu untuk tugas (misalnya 60 menit untuk tugas baru)
                $waktu = 60;
                $statement->execute([
                    ':deskripsi' => $tugasBaru,
                    ':waktu' => $waktu
                ]);
            }
            header('Location: ' . $_SERVER['SCRIPT_NAME']);
            break;
        case 'hapus':
            $untukDihapus = $_POST['id'];
            // SQL untuk menghapus tugas berdasarkan ID
            $sql = 'DELETE FROM tugas WHERE id = :id';
            $statement = $conn->prepare($sql);
            $statement->execute([':id' => $untukDihapus]);
            header('Location: ' . $_SERVER['SCRIPT_NAME']);
            break;
        case 'update':
            $untukDiupdate = $_POST['id'];
            $tugasBaru = $_POST['tugas'];
            if ($tugasBaru) {
                // SQL untuk mengupdate data tugas berdasarkan ID
                $sql = 'UPDATE tugas SET deskripsi = :deskripsi WHERE id = :id';
                $statement = $conn->prepare($sql);
                $statement->execute([
                    ':deskripsi' => $tugasBaru,
                    ':id' => $untukDiupdate
                ]);
            }
            header('Location: ' . $_SERVER['SCRIPT_NAME']);
            break;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $method = isset($_GET['method']) ? $_GET['method'] : false;
    switch ($method) {
        case 'hapus-semua':
            // Menghapus semua data dari database (opsional)
            $conn->exec('DELETE FROM tugas');
            header('Location: ' . $_SERVER['SCRIPT_NAME']);
            break;
        case 'edit':
            if (isset($_GET['id'])) {
                $tugasDiedit = isset($_GET['id']) ? $_GET['id'] : false;
                // Ambil data tugas dari database untuk diedit
                $sql = 'SELECT * FROM tugas WHERE id = :id';
                $statement = $conn->prepare($sql);
                $statement->execute([':id' => $tugasDiedit]);
                $tugasDiedit = $statement->fetch(PDO::FETCH_ASSOC);
            } else {
                header('Location: ' . $_SERVER['SCRIPT_NAME']);
            }
            echo renderFormEdit($tugasDiedit['id'], $tugasDiedit['deskripsi']);
            break;
        default:
            // Mengambil data tugas dari database untuk ditampilkan
            $sql = 'SELECT * FROM tugas';
            $statement = $conn->prepare($sql);
            $statement->execute();
            $tugas = $statement->fetchAll(PDO::FETCH_ASSOC);
            echo renderListingTugas($tugas);
            break;
    }
}

function renderListingTugas($daftarTugas) {
    if ($daftarTugas) {
        $tugasTugas = "<ol>";
        foreach ($daftarTugas as $tugas) {
            $tugasTugas .= <<<HTML
            <li>
                {$tugas['deskripsi']} (Waktu: {$tugas['waktu']} menit)
                <a href="{$_SERVER['SCRIPT_NAME']}?method=edit&id={$tugas['id']}">EDIT</a>
                <form style="display:inline-block" method="post" action="{$_SERVER['SCRIPT_NAME']}?method=hapus">
                    <input type="hidden" name="id" value="{$tugas['id']}" />
                    <button type="submit">🗑️</button>
                </form>
            </li>
            HTML;
        }
        $tugasTugas .= "</ol>";
    } else {
        $tugasTugas = 'Belum ada tugas';
    }
    return <<<HTML
    <h1>Apa lagi?</h1>
    <form name="apalagi" method="post" action="{$_SERVER['SCRIPT_NAME']}?method=tambah">
        <input name="tugas" type="text" placeholder="tulis tugas" />
        <button type="submit">Simpan</button>
    </form>
    <h2>Daftar tugas</h2>
    {$tugasTugas}
    <hr />
    <a href="?method=hapus-semua">HAPUS SEMUA ☢️</a>
    HTML;
}

function renderFormEdit($id, $deskripsi) {
    return <<<HTML
    <h1>EDIT</h1>
    <form name="update" method="post" action="{$_SERVER['SCRIPT_NAME']}?method=update">
        <input type="hidden" name="id" value="{$id}" />
        <input name="tugas" value="{$deskripsi}" type="text" placeholder="tulis tugas" />
        <button type="submit">Simpan</button>
    </form>
    HTML;
}
?>
