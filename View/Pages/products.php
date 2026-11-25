<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Điện thoại | FOURTECH</title>
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
        <p class="text-sm text-slate-500">Trang chủ / Điện thoại</p>
        <h1 class="mb-6 mt-2 text-3xl font-black text-navy">TẤT CẢ ĐIỆN THOẠI</h1>
        <div class="grid gap-6 md:grid-cols-[230px_1fr]">
            <aside class="h-fit rounded-2xl border bg-white p-5">
                <h2 class="font-bold">Bộ lọc sản phẩm</h2>
                <p class="mt-4 text-sm font-semibold">Thương hiệu</p><label class="mt-2 block text-sm"><input
                        type="checkbox" name="brand" value="Apple" class="mr-2">Apple</label><label
                    class="mt-2 block text-sm"><input type="checkbox" name="brand" value="Samsung"
                        class="mr-2">Samsung</label><label class="mt-2 block text-sm"><input type="checkbox"
                        name="brand" value="OPPO" class="mr-2">OPPO</label><label class="mt-2 block text-sm"><input
                        type="checkbox" name="brand" value="Xiaomi" class="mr-2">Xiaomi</label><label
                    class="mt-2 block text-sm"><input type="checkbox" name="brand" value="vivo"
                        class="mr-2">vivo</label><label class="mt-5 block text-sm font-semibold">Khoảng
                    giá</label><select id="price-filter" class="mt-2 w-full rounded-lg border p-2">
                    <option value="all">Tất cả mức giá</option>
                    <option value="under5">Dưới 5 triệu</option>
                    <option value="5to10">5 – 10 triệu</option>
                    <option value="over10">Trên 10 triệu</option>
                </select>
            </aside>
            <section>
                <div class="mb-4 text-sm text-slate-500">Chọn thương hiệu và mức giá để lọc sản phẩm.</div>
                <div id="all-products" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3"></div>
            </section>
        </div>
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