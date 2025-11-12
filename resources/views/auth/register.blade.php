<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex justify-content-center align-items-center" style="height:100vh;">
    <div class="card p-4 shadow" style="width: 400px;">
        <h4 class="mb-3 text-center">Daftar Akun</h4>
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <form action="/register" method="POST">
            @csrf
            <div class="mb-3"><input type="text" name="name" class="form-control" placeholder="Nama Lengkap"
                    required></div>
            <div class="mb-3"><input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>
            <div class="mb-3"><input type="password" name="password" class="form-control" placeholder="Password"
                    required></div>
            <button class="btn btn-primary w-100">Daftar</button>
        </form>
        <div class="text-center mt-3">
            Sudah punya akun? <a href="/login">Login</a>
        </div>
    </div>
</body>

</html>
