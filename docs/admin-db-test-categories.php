<?php

declare(strict_types=1);

/** @var string $root */
/** @var string $method */
/** @var int $categoryId */
/** @var string $categoryOperation */
/** @var \App\Services\Admin\CategoryAdminService $categoryService */
// Included by the loopback-only admin-db-test.php after database and session checks.
if (!isset($categoryService, $categoryOperation, $categoryId) || PHP_SAPI !== 'cli-server') {
    http_response_code(403);
    return;
}
$isPreview = true;
$_SESSION['task146_token'] ??= bin2hex(random_bytes(32));
$categoryToken = $_SESSION['task146_token'];
if ($method === 'POST') {
    $token = $_POST['category_token'] ?? null;
    if (!is_string($token) || !hash_equals($categoryToken, $token)) {
        http_response_code(403);
        exit('CSRF token không hợp lệ.');
    }
}
if ($categoryOperation === 'list') {
    $categories = $categoryService->listing();
    $success = $_SESSION['task146_success'] ?? '';
    $notice = $_SESSION['task146_notice'] ?? '';
    unset($_SESSION['task146_success'], $_SESSION['task146_notice']);
    require $root . '/resources/views/admin/categories/database-test-list.php';
    return;
}
$done = static function (string $message): void {
    $_SESSION['task146_success'] = $message;
    unset($_SESSION['task146_token']);
    header('Location: /admin/categories', true, 303);
};
if (in_array($categoryOperation, ['activate', 'deactivate', 'delete'], true)) {
    try {
        $categoryService->lifecycle($categoryId, $categoryOperation);
        $done(match ($categoryOperation) {
            'activate' => 'Kích hoạt danh mục thành công!',
            'deactivate' => 'Ngừng sử dụng danh mục thành công!',
            'delete' => 'Xóa mềm danh mục thành công!',
        });
    } catch (OutOfBoundsException $e) {
        http_response_code(404);
        exit('Không tìm thấy danh mục.');
    } catch (InvalidArgumentException $e) {
        $_SESSION['task146_notice'] = $e->getMessage();
        header('Location: /admin/categories', true, 303);
    } catch (PDOException $e) {
        error_log('[TASK146 ACTION] SQLSTATE: ' . $e->getCode());
        http_response_code(500);
        exit('Không thực hiện được thao tác danh mục.');
    }
    return;
}
$old = [];
$error = '';
if ($categoryId > 0) {
    $row = $categoryService->find($categoryId);
    if ($row === null) {
        http_response_code(404);
        exit('Không tìm thấy danh mục.');
    }
    $old = $row;
}
if ($method === 'POST') {
    $old = ['name' => $_POST['name'] ?? '', 'slug' => $_POST['slug'] ?? ''];
    try {
        $categoryService->save($old, $categoryId);
        $done($categoryId === 0 ? 'Thêm danh mục thành công!' : 'Cập nhật danh mục thành công!');
        return;
    } catch (OutOfBoundsException $e) {
        http_response_code(404);
        exit('Không tìm thấy danh mục.');
    } catch (InvalidArgumentException $e) {
        http_response_code(422);
        $error = $e->getMessage();
    } catch (PDOException $e) {
        error_log('[TASK146 SAVE] SQLSTATE: ' . $e->getCode());
        if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
            http_response_code(409);
            $error = 'Slug đã tồn tại.';
        } else {
            http_response_code(500);
            $error = 'Không lưu được danh mục. Kiểm tra database test.';
        }
    }
}
require $root . '/resources/views/admin/categories/database-test-form.php';
