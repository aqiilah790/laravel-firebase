<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Mahasiswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-database-compat.js"></script>
</head>

<body class="bg-light">

    @if (!session('firebase_user'))
        <script>
            window.location.href = "/login";
        </script>
    @endif

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>📘 Manajemen Mahasiswa</h2>
            <a href="/logout" class="btn btn-danger">Logout</a>
        </div>

        <div class="card shadow">
            <div class="card-body">
                <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#mahasiswaModal"
                    onclick="resetForm()">+ Tambah Mahasiswa</button>

                <table class="table table-bordered table-striped" id="mahasiswaTable">
                    <thead class="table-primary">
                        <tr>
                            <th>NIM</th>
                            <th>Nama</th>
                            <th>Jurusan</th>
                            <th>Angkatan</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="mahasiswaBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="mahasiswaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="mahasiswaForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah / Edit Mahasiswa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="mahasiswaId">
                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" id="nama" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" id="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" id="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">NIM</label>
                            <input type="text" id="nim" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jurusan</label>
                            <input type="text" id="jurusan" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Angkatan</label>
                            <input type="number" id="angkatan" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Simpan</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const firebaseConfig = {
            apiKey: "AIzaSyDME_iaPms6Ozpug7hHIAhLto4CgmpoT6M",
            authDomain: "project-laravel-f5576.firebaseapp.com",
            databaseURL: "https://project-laravel-f5576-default-rtdb.asia-southeast1.firebasedatabase.app",
            projectId: "project-laravel-f5576",
            storageBucket: "project-laravel-f5576.firebasestorage.app",
            messagingSenderId: "288374752827",
            appId: "1:288374752827:web:f95ba5f794ba898338fe1e",
            measurementId: "G-X734T2GZL7"
        };

        firebase.initializeApp(firebaseConfig);
        const db = firebase.database().ref("mahasiswa");
        const mahasiswaBody = document.getElementById("mahasiswaBody");

        // Listener realtime
        db.on("value", snapshot => {
            const data = snapshot.val();
            mahasiswaBody.innerHTML = "";
            if (!data) return;

            for (let id in data) {
                const m = data[id];
                const status = m.verified ?
                    `<span class="badge bg-success">Lengkap</span>` :
                    `<span class="badge bg-warning text-dark">Belum Lengkap</span>`;

                mahasiswaBody.innerHTML += `
            <tr>
              <td>${m.nim || '-'}</td>
              <td>${m.nama}</td>
              <td>${m.jurusan || '-'}</td>
              <td>${m.angkatan || '-'}</td>
              <td>${m.email}</td>
              <td>${status}</td>
              <td>${m.verified ? 
                `<button class="btn btn-sm btn-warning" onclick="editMahasiswa('${id}', ${JSON.stringify(m).replace(/"/g, '&quot;')})">Edit</button>
                     <button class="btn btn-sm btn-danger" onclick="hapusMahasiswa('${id}')">Hapus</button>` : `<span class="text-muted">-</span>`}
              </td>
            </tr>
        `;
            }
        });

        // Submit form -> panggil backend
        document.getElementById("mahasiswaForm").addEventListener("submit", async e => {
            e.preventDefault();

            const data = {
                nama: document.getElementById("nama").value,
                email: document.getElementById("email").value,
                password: document.getElementById("password").value,
                nim: document.getElementById("nim").value,
                jurusan: document.getElementById("jurusan").value,
                angkatan: document.getElementById("angkatan").value,
            };

            try {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const response = await fetch('/admin/tambah-user', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.success);
                    bootstrap.Modal.getInstance(document.getElementById("mahasiswaModal")).hide();
                    document.getElementById("mahasiswaForm").reset();
                } else {
                    alert(result.error);
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan saat menambahkan user');
            }
        });

        function editMahasiswa(id, data) {
            document.getElementById("mahasiswaId").value = id;
            document.getElementById("nim").value = data.nim;
            document.getElementById("nama").value = data.nama;
            document.getElementById("jurusan").value = data.jurusan;
            document.getElementById("angkatan").value = data.angkatan;
            document.getElementById("email").value = data.email;
            new bootstrap.Modal(document.getElementById("mahasiswaModal")).show();
        }

        function hapusMahasiswa(id) {
            if (confirm("Yakin ingin menghapus data ini?")) db.child(id).remove();
        }

        function resetForm() {
            document.getElementById("mahasiswaForm").reset();
            document.getElementById("mahasiswaId").value = "";
        }
    </script>

</body>

</html>
