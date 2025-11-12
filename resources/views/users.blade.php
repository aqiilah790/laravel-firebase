<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Realtime Firebase Users</title>
    <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-database.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-5">
        <h2 class="mb-4 text-center">👥 User Management (Realtime + Modal)</h2>

        <div class="text-end mb-3">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal" id="addUserBtn">+ Add
                User</button>
        </div>

        <table class="table table-bordered table-striped align-middle text-center">
            <thead class="table-dark">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th style="width:150px">Action</th>
                </tr>
            </thead>
            <tbody id="tbody"></tbody>
        </table>
    </div>

    <!-- 🟢 MODAL: ADD / EDIT -->
    <div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="userForm">
                    <div class="modal-body">
                        <input type="hidden" id="userId">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" id="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 🟠 MODAL: DELETE CONFIRM -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <h5>Are you sure you want to delete this user?</h5>
                    <input type="hidden" id="deleteUserId">
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script type="module">
        import {
            initializeApp
        } from "https://www.gstatic.com/firebasejs/9.23.0/firebase-app.js";
        import {
            getDatabase,
            ref,
            push,
            update,
            remove,
            onValue
        } from "https://www.gstatic.com/firebasejs/9.23.0/firebase-database.js";
        
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

        const app = initializeApp(firebaseConfig);
        const db = getDatabase(app);
        const usersRef = ref(db, 'users');

        const tbody = document.getElementById('tbody');
        const userForm = document.getElementById('userForm');
        const nameInput = document.getElementById('name');
        const emailInput = document.getElementById('email');
        const userIdInput = document.getElementById('userId');
        const modalTitle = document.getElementById('modalTitle');
        const addUserBtn = document.getElementById('addUserBtn');
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        const userModal = new bootstrap.Modal(document.getElementById('userModal'));

        // 🟢 Realtime listener
        onValue(usersRef, (snapshot) => {
            tbody.innerHTML = '';
            const data = snapshot.val();
            if (data) {
                Object.entries(data).forEach(([id, user]) => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                    <td>${user.name}</td>
                    <td>${user.email}</td>
                    <td>
                        <button class="btn btn-sm btn-warning editBtn" data-id="${id}" data-name="${user.name}" data-email="${user.email}">Edit</button>
                        <button class="btn btn-sm btn-danger deleteBtn" data-id="${id}">Delete</button>
                    </td>
                `;
                    tbody.appendChild(tr);
                });
            }
        });

        // 🟢 Tambah user
        addUserBtn.addEventListener('click', () => {
            userIdInput.value = '';
            nameInput.value = '';
            emailInput.value = '';
            modalTitle.textContent = 'Add User';
        });

        // 🟢 Submit form (add/update)
        userForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const id = userIdInput.value;
            const name = nameInput.value.trim();
            const email = emailInput.value.trim();

            if (!name || !email) return;

            if (id) {
                update(ref(db, 'users/' + id), {
                    name,
                    email
                });
            } else {
                push(usersRef, {
                    name,
                    email
                });
            }

            userModal.hide();
        });

        // 🟢 Delegasi edit/delete
        tbody.addEventListener('click', (e) => {
            if (e.target.classList.contains('editBtn')) {
                const btn = e.target;
                userIdInput.value = btn.dataset.id;
                nameInput.value = btn.dataset.name;
                emailInput.value = btn.dataset.email;
                modalTitle.textContent = 'Edit User';
                userModal.show();
            }

            if (e.target.classList.contains('deleteBtn')) {
                const id = e.target.dataset.id;
                document.getElementById('deleteUserId').value = id;
                deleteModal.show();
            }
        });

        // 🟠 Konfirmasi delete
        document.getElementById('confirmDeleteBtn').addEventListener('click', () => {
            const id = document.getElementById('deleteUserId').value;
            remove(ref(db, 'users/' + id));
            deleteModal.hide();
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
