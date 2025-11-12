<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Verifikasi Email</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex justify-content-center align-items-center" style="height:100vh;">
    <div class="card p-4 shadow" style="width: 400px;">
        <h4 class="mb-3 text-center">Verifikasi Email</h4>
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <p class="text-center">
            Silakan cek email kamu untuk melakukan verifikasi.<br>
            Setelah itu, login kembali untuk melengkapi data.
        </p>
        <a href="/login" class="btn btn-primary w-100 mt-3">Sudah Verifikasi? Login Sekarang</a>
    </div>
</body>

</html>
