<?php

session_start();

date_default_timezone_set('Europe/Prague');

require_once __DIR__ . '/../src/Repository/ProductRepository.php';
require_once __DIR__ . '/../src/Repository/CategoryRepository.php';
require_once __DIR__ . '/../src/Repository/OrderRepository.php';
require_once __DIR__ . '/../src/Repository/CouponRepository.php';

require_once __DIR__ . '/../src/Service/CatalogService.php';
require_once __DIR__ . '/../src/Service/CartService.php';
require_once __DIR__ . '/../src/Service/OrderService.php';
require_once __DIR__ . '/../src/Service/CheckoutService.php';


require_once __DIR__ . '/../src/Models/Product.php';
require_once __DIR__ . '/../src/Models/Category.php';
require_once __DIR__ . '/../src/Models/CartLineItem.php';
require_once __DIR__ . '/../src/Models/Cart.php';
require_once __DIR__ . '/../src/Models/Customer.php';
require_once __DIR__ . '/../src/Models/Order.php';
require_once __DIR__ . '/../src/Models/Coupon.php';

$dataDir = __DIR__ . '/../data';

$productRepository = new ProductRepository($dataDir . '/products.json');
$categoryRepository = new CategoryRepository($dataDir . '/categories.json');

$catalogService = new CatalogService($productRepository, $categoryRepository);
$cartService = new CartService($productRepository);

$couponRepository = new CouponRepository($dataDir . '/coupons.json');
$checkoutService = new CheckoutService($couponRepository);

$orderRepository = new OrderRepository($dataDir . '/orders.json');
$orderService = new OrderService($orderRepository, $cartService, $productRepository, $checkoutService);

$action = isset($_GET['action']) ? $_GET['action'] : 'productList';
$id = isset($_GET['id']) ? $_GET['id'] : null;
$categoryId = isset($_GET['category']) ? $_GET['category'] : null;
$qty = isset($_GET['qty']) ? (int) $_GET['qty'] : null;
$name = isset($_GET['name']) ? $_GET['name'] : '';
$email = isset($_GET['email']) ? $_GET['email'] : '';
$orderNumber = isset($_GET['order']) ? $_GET['order'] : null;
$coupon = isset($_GET['coupon']) ? $_GET['coupon'] : '';

header('Content-Type: application/json');

try {
    switch ($action) {
        case 'categories':
            echo json_encode($catalogService->getCategories());
            break;

        case 'productList':
            echo json_encode($catalogService->getProducts($categoryId));
            break;
        case 'productDetail':
            if (!$id) {
                throw new Exception('Missing id');
            }
            echo json_encode($catalogService->getProductDetail($id));
            break;

        case 'cartView':
            echo json_encode($cartService->getCartView());
            break;
        case 'cartAdd':
            if (!$id) {
                throw new Exception('Missing id');
            }
            $addQty = ($qty === null) ? 1 : $qty;
            echo json_encode($cartService->addToCart($id, $addQty));
            break;
        case 'cartUpdate':
            if (!$id) {
                throw new Exception('Missing id');
            }
            if ($qty === null) {
                throw new Exception('Missing qty');
            }
            echo json_encode($cartService->setQuantity($id, $qty));
            break;
        case 'cartRemove':
            if (!$id) {
                throw new Exception('Missing id');
            }
            echo json_encode($cartService->removeFromCart($id));
            break;

        case 'placeOrder':
            echo json_encode($orderService->placeOrder($name, $email, $coupon));
            break;
        case 'adminOrders':
            echo json_encode($orderService->getAllOrders());
            break;
        case 'adminOrderDetail':
            if (!$orderNumber) {
                throw new Exception('Missing order');
            }
            echo json_encode($orderService->getOrderDetail($orderNumber));
            break;

        case 'cartPreview':
            echo json_encode($orderService->getCartPreview($coupon));
            break;

        default:
            throw new Exception('Unknown action: ' . $action);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(array('error' => $e->getMessage()));
}
