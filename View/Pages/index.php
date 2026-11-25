<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Trang chủ | FOURTECH</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: '#061936',
                        brand: '#1261dc',
                        pale: '#e8f3ff'
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body class="bg-slate-50 text-slate-900">
    <?php require __DIR__ . '/../../resources/views/components/header.php'; ?> <main class="mx-auto max-w-7xl px-4 py-6">
        <section
            class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#061936] via-[#0b2d68] to-blue-700 px-7 py-12 text-white md:px-14 md:py-16">
            <div class="relative z-10 max-w-xl">
                <h1 class="text-4xl font-black leading-tight md:text-5xl">ĐIỆN THOẠI MỚI<br>TRẢI NGHIỆM MỚI</h1>
                <p class="mt-3 text-lg text-blue-100">Khám phá lựa chọn phù hợp với bạn</p><a href="products.php"
                    class="mt-6 inline-block rounded-xl bg-blue-600 px-7 py-3 font-bold hover:bg-blue-500">Khám phá
                    ngay</a>
            </div>
            <div class="absolute right-20 top-8 hidden text-[170px] opacity-20 md:block">📱</div>
        </section>
        <div class="my-5 flex flex-wrap gap-3"><a href="products.php"
                class="brand-pill rounded-full bg-blue-100 px-7 py-2 font-semibold text-navy">Tất cả</a><a
                href="products.php?brand=Apple"
                class="brand-pill rounded-full bg-blue-100 px-7 py-2 font-semibold text-navy">Apple</a><a
                href="products.php?brand=Samsung"
                class="brand-pill rounded-full bg-blue-100 px-7 py-2 font-semibold text-navy">Samsung</a><a
                href="products.php?brand=OPPO"
                class="brand-pill rounded-full bg-blue-100 px-7 py-2 font-semibold text-navy">OPPO</a><a
                href="products.php?brand=Xiaomi"
                class="brand-pill rounded-full bg-blue-100 px-7 py-2 font-semibold text-navy">Xiaomi</a><a
                href="products.php?brand=vivo"
                class="brand-pill rounded-full bg-blue-100 px-7 py-2 font-semibold text-navy">vivo</a></div>
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-2xl font-black text-navy md:text-3xl">ĐIỆN THOẠI NỔI BẬT</h2><a
                class="font-semibold text-brand" href="products.php">Xem tất cả ›</a>
        </div>
        <div id="featured-products" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4"></div>
    </main>
    <section
        class="mx-auto my-8 grid max-w-7xl gap-3 rounded-2xl bg-pale px-5 py-5 text-center font-bold text-navy md:grid-cols-3">
        <div>♧ Tư vấn tận tâm</div>
        <div class="border-y border-blue-200 py-3 md:border-x md:border-y-0">🚚 Giao hàng toàn quốc</div>
        <div>♢ Hỗ trợ bảo hành</div>
    </section>
    <?php require __DIR__ . '/../../resources/views/components/footer.php'; ?> <script src="../../assets/js/products.js"></script>
    <script src="../../assets/js/main.js"></script>
</body>

</html>