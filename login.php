<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/header.php';
 ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">تسجيل الدخول</div>
                <div class="card-body">
                    <form action="<?= BASE_URL ?>process.php" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">البريد الإلكتروني</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">كلمة المرور</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary">دخول</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require __DIR__ . '/includes/footer.php';
 ?>