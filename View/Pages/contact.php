<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Liên hệ | FOURTECH</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { navy: '#061936', brand: '#1261dc', pale: '#e8f3ff' } } } }</script>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body class="bg-slate-50 text-slate-900">
<?php require __DIR__ . '/../../resources/views/components/header.php'; ?>    <main class="mx-auto max-w-5xl px-4 py-8">
        <h1 class="mb-6 text-3xl font-black text-navy">LIÊN HỆ FOURTECH</h1>
        <div class="grid gap-6 md:grid-cols-2">
            <section class="rounded-2xl bg-navy p-7 text-white">
                <h2 class="text-xl font-bold">Chúng tôi luôn sẵn sàng hỗ trợ</h2>
                <p class="mt-4 leading-8 text-blue-100">Tư vấn sản phẩm<br>Hỗ trợ đơn hàng<br>Chính sách bảo
                    hành<br>Email: support@fourtech.vn</p>
            </section>
            <form onsubmit="event.preventDefault();alert('Đã ghi nhận nội dung demo.')"
                class="rounded-2xl border bg-white p-7">
                <h2 class="mb-4 text-xl font-bold">Gửi lời nhắn</h2><input required
                    class="mb-3 w-full rounded-lg border p-3" placeholder="Họ tên"><input required type="email"
                    class="mb-3 w-full rounded-lg border p-3" placeholder="Email"><textarea required
                    class="mb-3 w-full rounded-lg border p-3" rows="4"
                    placeholder="Nội dung cần hỗ trợ"></textarea><button
                    class="rounded-xl bg-brand px-6 py-3 font-bold text-white">Gửi liên hệ</button>
            </form>
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
    <script src="../../assets/js/main.js"></script></body>

</html>