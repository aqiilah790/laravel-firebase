<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Lengkapi Data Mahasiswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex justify-content-center align-items-center" style="height:100vh;">
    <div class="card p-4 shadow" style="width: 400px;">
        <h4 class="mb-3 text-center">Lengkapi Data Mahasiswa</h4>
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <form action="/lengkapi-data" method="POST">
            @csrf
            <div class="mb-3"><input type="text" name="nim" class="form-control" placeholder="NIM" required>
            </div>
            <div class="mb-3"><input type="text" name="jurusan" class="form-control" placeholder="Jurusan"
                    required></div>
            <div class="mb-3"><input type="text" name="angkatan" class="form-control" placeholder="Angkatan"
                    required></div>
            <button class="btn btn-success w-100">Simpan</button>
        </form>
    </div>
</body>

</html>
