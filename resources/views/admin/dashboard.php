<?php

$pageTitle = 'Tổng quan';

$stats = [
    ['label' => 'Tổng sản phẩm', 'value' => '—'],
    ['label' => 'Tổng đơn hàng', 'value' => '—'],
    ['label' => 'Tổng khách hàng', 'value' => '—'],
    ['label' => 'Doanh thu', 'value' => '—'],
];

ob_start();

?>

<section>

    <h2 class="text-2xl font-bold">
        Quản trị cửa hàng
    </h2>

    <p class="mt-1 text-slate-500">
        Theo dõi hoạt động của FOURTECH.
    </p>

    <p class="my-5 rounded-lg border border-blue-200 bg-blue-50 p-3 text-sm text-blue-800">
        Giai đoạn 1: giao diện demo, chưa kết nối database.
    </p>

    <!-- Các ô thống kê -->

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <?php foreach ($stats as $stat): ?>

            <article class="rounded-xl border bg-white p-5 shadow-sm">

                <p class="text-sm text-slate-500">
                    <?= htmlspecialchars($stat['label'], ENT_QUOTES, 'UTF-8') ?>
                </p>

                <p class="mt-3 text-3xl font-bold">
                    <?= htmlspecialchars($stat['value'], ENT_QUOTES, 'UTF-8') ?>
                </p>

            </article>

        <?php endforeach; ?>

    </div>

    <!-- Bảng đơn hàng -->

    <div class="mt-8 rounded-xl border bg-white p-5 shadow-sm">

        <h3 class="mb-4 text-lg font-bold">
            Đơn hàng gần đây
        </h3>

        <div class="overflow-x-auto">

            <table class="w-full text-left text-sm">

                <thead class="bg-slate-100">

                    <tr>
                        <th class="p-3">Mã đơn</th>
                        <th class="p-3">Khách hàng</th>
                        <th class="p-3">Tổng tiền</th>
                        <th class="p-3">Trạng thái</th>
                    </tr>

                </thead>

                <tbody>

                    <tr>

                        <td colspan="4"
                            class="p-8 text-center text-slate-500">

                            Chưa kết nối dữ liệu.

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

    </div>

</section>

<?php

$content = ob_get_clean();

require __DIR__ . '/layout.php';
