<?php

declare(strict_types=1);

/** @var string $root */
/** @var string $method */
/** @var int $brandId */
/** @var string $brandOperation */
/** @var \App\Services\Admin\BrandAdminService $brandService */
// Included by the loopback-only admin-db-test.php after database and session checks.
if (!isset($brandService, $brandOperation, $brandId) || PHP_SAPI !== 'cli-server') {
    http_response_code(403);
    return;
}
$isPreview = true;
$_SESSION['task15_token'] ??= bin2hex(random_bytes(32));
$brandToken = $_SESSION['task15_token'];
if ($method === 'POST') {
    $token = $_POST['brand_token'] ?? null;
    if (!is_string($token) || !hash_equals($brandToken, $token)) {
        http_response_code(403);
        exit('CSRF token không hợp lệ.');
    }
}
if ($brandOperation === 'list') {
    $brands = $brandService->listing();
    $success = $_SESSION['task15_success'] ?? '';
    $notice = $_SESSION['task15_notice'] ?? '';
    unset($_SESSION['task15_success'], $_SESSION['task15_notice']);
    require $root . '/resources/views/admin/brands/database-test-list.php';
    return;
}
$done = static function (string $message): void {
    $_SESSION['task15_success'] = $message;
    unset($_SESSION['task15_token']);
    header('Location: /admin/brands', true, 303);
};
if (in_array($brandOperation, ['activate', 'deactivate', 'delete'], true)) {
    try {
        $brandService->lifecycle($brandId, $brandOperation);
        $done(match ($brandOperation) {
            'activate' => 'Kích hoạt thương hiệu thành công!',
            'deactivate' => 'Ngừng sử dụng thương hiệu thành công!',
            'delete' => 'Xóa mềm thương hiệu thành công!',
        });
    } catch (OutOfBoundsException $e) {
        http_response_code(404);
        exit('Không tìm thấy thương hiệu.');
    } catch (InvalidArgumentException $e) {
        $_SESSION['task15_notice'] = $e->getMessage();
        header('Location: /admin/brands', true, 303);
    } catch (PDOException $e) {
        error_log('[TASK15 ACTION] SQLSTATE: ' . $e->getCode());
        http_response_code(500);
        exit('Không thực hiện được thao tác thương hiệu.');
    }
    return;
}
$old = [];
$error = '';
if ($brandId > 0) {
    $row = $brandService->find($brandId);
    if ($row === null) {
        http_response_code(404);
        exit('Không tìm thấy thương hiệu.');
    }
    $old = $row;
}
if ($method === 'POST') {
    $old = ['name' => $_POST['name'] ?? '', 'slug' => $_POST['slug'] ?? ''];
    try {
        $brandService->save($old, $brandId);
        $done($brandId === 0 ? 'Thêm thương hiệu thành công!' : 'Cập nhật thương hiệu thành công!');
        return;
    } catch (OutOfBoundsException $e) {
        http_response_code(404);
        exit('Không tìm thấy thương hiệu.');
    } catch (InvalidArgumentException $e) {
        http_response_code(422);
        $error = $e->getMessage();
    } catch (PDOException $e) {
        error_log('[TASK15 SAVE] SQLSTATE: ' . $e->getCode());
        if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
            http_response_code(409);
            $error = 'Slug đã tồn tại.';
        } else {
            http_response_code(500);
            $error = 'Không lưu được thương hiệu. Kiểm tra database test.';
        }
    }
}
require $root . '/resources/views/admin/brands/database-test-form.php';
