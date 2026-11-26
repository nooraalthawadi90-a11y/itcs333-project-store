<?php
declare(strict_types=1);

/**
 * ITCS333 Project starter — m2_catalog handler (module under construction).
 * Implement the endpoints in contracts/ for your module.
 * The routing context is in $GLOBALS['__route'] ('area', 'rest', 'uri').
 * Use db() from src/db.php and the helpers in src/http.php.
 */

$rest = $GLOBALS['__route']['rest'] ?? '';

// TODO: implement your module's endpoints here (see contracts/).
// List categories
if (http_method() === 'GET' && $rest === '/categories') {
    $pdo = db();

    $stmt = $pdo->prepare("SELECT id, name FROM categories");
    $stmt->execute();

    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    send_json(200, ['data' => $categories]);
}

// List products, search and filter
if (http_method() === 'GET' && $rest === '/products') {
    $pdo = db();

    $category_id = $_GET['category_id'] ?? '';
    $q = $_GET['q'] ?? '';

    $sql = "SELECT id, category_id, name, price, stock
            FROM products WHERE 1 = 1";

    $params = [];

    if ($category_id !== '') {
        $sql .= " AND category_id = ?";
        $params[] = $category_id;
    }

    if ($q !== '') {
        $sql .= " AND LOWER(name) LIKE LOWER(?)";
        $params[] = '%' . $q . '%';
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as &$product) {
        $product['id'] = (int) $product['id'];
        $product['category_id'] = (int) $product['category_id'];
        $product['price'] = (float) $product['price'];
        $product['stock'] = (int) $product['stock'];
    }
    unset($product);

    send_json(200, ['data' => $products]);
}

// View a single product
if (http_method() === 'GET' &&
    preg_match('#^/products/([0-9]+)$#', $rest, $matches)) {

    $pdo = db();
    $id = (int) $matches[1];

    $stmt = $pdo->prepare(
        "SELECT id, category_id, name, price, stock
         FROM products WHERE id = ?"
    );

    $stmt->execute([$id]);

    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        send_error(404, 'NOT_FOUND');
    }

    $product['id'] = (int) $product['id'];
    $product['category_id'] = (int) $product['category_id'];
    $product['price'] = (float) $product['price'];
    $product['stock'] = (int) $product['stock'];

    send_json(200, $product);
}

send_error(404, 'NOT_FOUND');