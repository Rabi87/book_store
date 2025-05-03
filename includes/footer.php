</main>

<style>
/* أنماط مخصصة للفوتر */
.card {
    border-radius: 12px;
    transition: transform 0.3s;
}

.card:hover {
    transform: translateY(-5px);
}

.card-header, .cardo {

    background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%);
    color: white;
}

.btn-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
<footer class="mt-auto">
    <div class="container">
        <!-- قسم المحتوى -->
        <div class="row py-5 g-4">

            <!-- عن الموقع -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="cardo card-body ">
                        <p class="text-muted text-white-50">منصة رقمية متكاملة لاستعارة وشراء الكتب الإلكترونية، مع ميزات تفاعلية
                            لمحبي القراءة.</p>
                    </div>
                </div>
            </div>

            <!-- روابط سريعة -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="cardo card-body ">
                        <ul class="list-unstyled">
                            
                            <ul class="list-unstyled text-secondary">
                                <li><a href="#" class="text-decoration-none text-secondary text-white-50">الرئيسية</a></li>
                                <li><a href="#" class="text-decoration-none text-secondary text-white-50">شكاوي</a></li>
                                <li><a href="#" class="text-decoration-none text-secondary text-white-50">تواصل معنا</a></li>
                            </ul>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- التواصل -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="cardo card-body text-center text-white">
                        <div class="d-flex justify-content-center gap-3">
                            <a href="#" class="btn btn-outline-dark btn-icon rounded-circle text-white-50">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="btn btn-outline-dark btn-icon rounded-circle text-white-50">
                                <i class="fab fa-twitter fa-lg"></i>
                            </a>
                            <a href="#" class="btn btn-outline-dark btn-icon rounded-circle text-white-50">
                                <i class="fab fa-instagram fa-lg"></i>
                            </a>
                            <a href="#" class="btn btn-outline-dark btn-icon rounded-circle text-white-50">
                                <i class="fab fa-linkedin fa-lg"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>


            <!-- حقوق النشر -->
            <div class="py-3 text-center"
                style="background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%); color: white;">
                <p class="mb-0">
                    &copy; <?= date('Y') ?> جميع الحقوق محفوظة |
                    <a href="#" class="text-decoration-none text-white-50">RABI ALKHADDOUR</a>
                </p>
            </div>
        </div>
</footer>
<!-- المكتبات البرمجية -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>
<script src="<?= BASE_URL ?>assets/bootstrap/js/popper.min.js"></script>
<script src="<?= BASE_URL ?>assets/bootstrap/js/jquery-3.7.1.min.js"></script>
<script src="<?= BASE_URL ?>assets/bootstrap/js/bootstrap.js"></script>
</body>

</html>