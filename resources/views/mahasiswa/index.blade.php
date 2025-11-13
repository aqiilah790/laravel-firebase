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
                <button class="btn btn-primary mb-3" onclick="setMode('add')">+ Tambah Mahasiswa</button>

                <table class="table table-bordered table-striped">
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
                            <input type="text" id="nama" class="form-control" placeholder="masukkan nama"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" id="email" class="form-control" placeholder="masukkan email"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" id="password" class="form-control" placeholder="Masukkan password">
                            <small class="text-muted" id="passwordHint"></small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">NIM</label>
                            <input type="text" id="nim" class="form-control" placeholder="masukkan nim">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jurusan</label>
                            <input type="text" id="jurusan" class="form-control" placeholder="masukkan jurusan">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Angkatan</label>
                            <input type="number" id="angkatan" class="form-control" placeholder="masukkan angkatan">
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
        // === Konfigurasi Firebase ===
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

        let isEditMode = false;

        // === Realtime listener ===
        db.on("value", snapshot => {
            const data = snapshot.val();
            mahasiswaBody.innerHTML = "";
            if (!data) return;

            Object.keys(data).forEach(uid => {
                const m = data[uid];
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
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="editMahasiswa('${uid}', ${JSON.stringify(m).replace(/"/g, '&quot;')})">Edit</button>
                            <button class="btn btn-sm btn-danger" onclick="hapusMahasiswa('${uid}')">Hapus</button>
                        </td>
                    </tr>
                `;
            });
        });

        // === Mode Tambah/Edit ===
        function setMode(mode) {
            const modalTitle = document.querySelector("#mahasiswaModal .modal-title");
            const passwordInput = document.getElementById("password");
            const passwordHint = document.getElementById("passwordHint");

            if (mode === "add") {
                isEditMode = false;
                resetForm();
                modalTitle.textContent = "Tambah Mahasiswa";
                passwordInput.required = true;
                passwordHint.textContent = "Wajib diisi saat menambah data baru.";
            } else {
                isEditMode = true;
                modalTitle.textContent = "Edit Mahasiswa";
                passwordInput.required = false;
                passwordHint.textContent = "Kosongkan jika tidak ingin mengubah password.";
            }

            new bootstrap.Modal(document.getElementById("mahasiswaModal")).show();
        }

        // === Edit Data ===
        function editMahasiswa(uid, data) {
            setMode("edit");
            document.getElementById("mahasiswaId").value = uid;
            document.getElementById("nama").value = data.nama || "";
            document.getElementById("email").value = data.email || "";
            document.getElementById("nim").value = data.nim || "";
            document.getElementById("jurusan").value = data.jurusan || "";
            document.getElementById("angkatan").value = data.angkatan || "";
            document.getElementById("password").value = "";
        }

        // === Hapus Data (Auth + Realtime Database) ===
        async function hapusMahasiswa(uid) {
            if (!confirm("Yakin ingin menghapus data mahasiswa ini?")) return;

            try {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                const response = await fetch('/admin/hapus-user', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({
                        id: uid
                    })
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.success);
                } else {
                    alert(result.error || "Gagal menghapus data.");
                }
            } catch (err) {
                console.error(err);
                alert("Terjadi kesalahan saat menghapus data.");
            }
        }


        // === Reset Form ===
        function resetForm() {
            document.getElementById("mahasiswaForm").reset();
            document.getElementById("mahasiswaId").value = "";
        }

        // === Simpan Data ===
        document.getElementById("mahasiswaForm").addEventListener("submit", async e => {
            e.preventDefault();

            const uid = document.getElementById("mahasiswaId").value;
            const password = document.getElementById("password").value;

            const data = {
                nama: document.getElementById("nama").value,
                email: document.getElementById("email").value,
                nim: document.getElementById("nim").value,
                jurusan: document.getElementById("jurusan").value,
                angkatan: document.getElementById("angkatan").value,
            };

            if (!isEditMode || password) data.password = password;

            try {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const url = uid ? '/admin/update-user' : '/admin/tambah-user';

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({
                        id: uid,
                        ...data
                    })
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.success);
                    const modal = bootstrap.Modal.getInstance(document.getElementById("mahasiswaModal"));
                    modal.hide();
                    document.body.classList.remove("modal-open");
                    document.querySelectorAll(".modal-backdrop").forEach(el => el.remove());
                    resetForm();
                } else {
                    alert(result.error);
                }
            } catch (err) {
                console.error(err);
                alert("Terjadi kesalahan saat menyimpan data.");
            }
        });
    </script>
</body>

</html>
