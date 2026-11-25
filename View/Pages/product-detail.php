<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chi tiết sản phẩm | FOURTECH</title>
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
    <?php require __DIR__ . '/../../resources/views/components/header.php'; ?> <main class="mx-auto max-w-7xl px-4 py-8">
        <main class="mx-auto max-w-7xl px-4 py-8">
            <p class="mb-5 text-sm text-slate-500">Trang chủ / Điện thoại / Chi tiết sản phẩm</p>
            <div id="product-detail" class="grid gap-8 rounded-2xl border bg-white p-5 md:grid-cols-2 md:p-8"></div>
        </main>
        <section
            class="mx-auto my-8 grid max-w-7xl gap-3 rounded-2xl bg-pale px-5 py-5 text-center font-bold text-navy md:grid-cols-3">
            <div>♧ Tư vấn tận tâm</div>
            <div class="border-y border-blue-200 py-3 md:border-x md:border-y-0">🚚 Giao hàng toàn quốc</div>
            <div>♢ Hỗ trợ bảo hành</div>
        </section>
        <?php require __DIR__ . '/../../resources/views/components/footer.php'; ?>
        <script src="../../assets/js/products.js"></script>
        <script src="../../assets/js/main.js"></script>
</body>

</html>