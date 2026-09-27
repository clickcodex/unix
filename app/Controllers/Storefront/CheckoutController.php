<?php

namespace App\Controllers\Storefront;

use App\Models\Cart;
use App\Models\UserAddress;
use App\Models\Order;
use App\Models\User;
use App\Helpers\SecurityHelper;

class CheckoutController {

    private function json(bool $ok, string $msg, array $extra = [], int $code = 200): void {
        if (ob_get_length()) ob_clean();
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => $ok, 'message' => $msg], $extra));
        exit;
    }

    /**
     * Display Checkout Page.
     * GET /checkout
     */
    public function index(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login?redirect=checkout');
            exit;
        }

        $cartItems = Cart::getItemsForUser($userId);
        if (empty($cartItems)) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/cart');
            exit;
        }

        $user      = User::findById($userId);
        $addresses = UserAddress::getAddresses($userId);

        // Subtotal calculation
        $subtotal = 0;
        $mrpTotal = 0;
        foreach ($cartItems as $ci) {
            $qty       = (int)($ci['quantity'] ?? 1);
            $price     = (float)($ci['unit_price'] ?? 0);
            $basePrice = (float)($ci['base_price'] ?? $price);
            $subtotal  += ($price * $qty);
            $mrpTotal  += ($basePrice * $qty);
        }

        $discount = max(0, $mrpTotal - $subtotal);
        $shipping = $subtotal >= 499 ? 0 : 50;
        $total    = $subtotal + $shipping;

        $baseUrl   = defined('BASE_URL') ? BASE_URL : '';
        $siteTitle = 'Checkout — ClickCodex';

        require_once __DIR__ . '/../../Views/front/checkout.php';
    }

    /**
     * POST /checkout/place-order — Place Order.
     */
    public function placeOrder(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) $this->json(false, 'Unauthorized.', [], 401);

        $cartItems = Cart::getItemsForUser($userId);
        if (empty($cartItems)) {
            $this->json(false, 'Your cart is empty.', [], 400);
        }

        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $rawAddrId = $data['address_id'] ?? null;
        $addressId = is_numeric($rawAddrId) ? (int)$rawAddrId : SecurityHelper::decryptId((string)$rawAddrId);
        $payMethod = trim($data['payment_method'] ?? 'COD');

        if (!$addressId) {
            $this->json(false, 'Please select a delivery address.', [], 400);
        }

        $address = UserAddress::getAddressById($userId, $addressId);
        if (!$address) {
            $this->json(false, 'Invalid delivery address.', [], 400);
        }

        $subtotal = 0;
        $mrpTotal = 0;
        foreach ($cartItems as $ci) {
            $qty       = (int)($ci['quantity'] ?? 1);
            $price     = (float)($ci['unit_price'] ?? 0);
            $basePrice = (float)($ci['base_price'] ?? $price);
            $subtotal  += ($price * $qty);
            $mrpTotal  += ($basePrice * $qty);
        }

        $discount = max(0, $mrpTotal - $subtotal);
        $shipping = $subtotal >= 499 ? 0 : 50;
        $grandTotal = $subtotal + $shipping;

        $db = \App\Config\Database::connect();

        $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 4));
        $status      = 'pending';
        $isOnlinePay = in_array($payMethod, ['PhonePe', 'UPI', 'Card']);
        $payStatus   = $isOnlinePay ? 'pending' : 'unpaid';

        $fullAddr = "{$address['recipient_name']} ({$address['phone']}), {$address['address_line1']}" .
                    (!empty($address['address_line2']) ? ", {$address['address_line2']}" : "") .
                    ", {$address['city']}, {$address['state']} - {$address['postal_code']}, {$address['country']}";

        // 1. Inspect existing columns of `orders` table dynamically
        $orderColsRes = $db->query("SHOW COLUMNS FROM orders");
        $orderCols = [];
        if ($orderColsRes) {
            while ($c = $orderColsRes->fetch_assoc()) {
                $orderCols[$c['Field']] = true;
            }
        }

        $fields = ['order_number', 'user_id', 'status'];
        $values = [$orderNumber, $userId, $status];
        $types  = 'sis';

        if (isset($orderCols['uuid'])) {
            $fields[] = 'uuid';
            $values[] = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
            $types   .= 's';
        }
        if (isset($orderCols['currency'])) {
            $fields[] = 'currency';
            $values[] = 'INR';
            $types   .= 's';
        }

        $couponCode = strtoupper(trim($data['coupon_code'] ?? ''));

        // Enforce 1-time use rule per customer for coupons
        if (!empty($couponCode)) {
            if (\App\Models\Coupon::hasUserUsedCoupon($userId, $couponCode)) {
                $this->json(false, 'You have already used this coupon code. Each coupon can only be used once per customer.', [], 400);
            }

            // Verify active coupon in DB
            $stmtC = $db->prepare("SELECT * FROM coupons WHERE UPPER(code) = UPPER(?) AND is_active = 1 AND (ends_at IS NULL OR ends_at >= NOW())");
            if ($stmtC) {
                $stmtC->bind_param("s", $couponCode);
                $stmtC->execute();
                $cRow = $stmtC->get_result()->fetch_assoc();
                $stmtC->close();

                if (!$cRow) {
                    $this->json(false, 'Invalid or expired coupon code.', [], 400);
                }
                if (!empty($cRow['min_order_value']) && $subtotal < (float)$cRow['min_order_value']) {
                    $this->json(false, 'Cart amount does not meet the minimum order value required for this coupon.', [], 400);
                }
            }
        }

        if (isset($orderCols['payment_status'])) {
            $fields[] = 'payment_status';
            $values[] = $payStatus;
            $types   .= 's';
        }
        if (isset($orderCols['coupon_code']) && !empty($couponCode)) {
            $fields[] = 'coupon_code';
            $values[] = $couponCode;
            $types   .= 's';
        }
        if (isset($orderCols['payment_method'])) {
            $fields[] = 'payment_method';
            $values[] = $payMethod;
            $types   .= 's';
        }
        if (isset($orderCols['payment_gateway'])) {
            $fields[] = 'payment_gateway';
            $values[] = $payMethod;
            $types   .= 's';
        }
        if (isset($orderCols['payment_mode'])) {
            $fields[] = 'payment_mode';
            $values[] = $payMethod;
            $types   .= 's';
        }

        if (isset($orderCols['subtotal'])) {
            $fields[] = 'subtotal';
            $values[] = $subtotal;
            $types   .= 'd';
        }
        if (isset($orderCols['discount_amount'])) {
            $fields[] = 'discount_amount';
            $values[] = $discount;
            $types   .= 'd';
        }
        if (isset($orderCols['shipping_charge'])) {
            $fields[] = 'shipping_charge';
            $values[] = $shipping;
            $types   .= 'd';
        }
        if (isset($orderCols['total_amount'])) {
            $fields[] = 'total_amount';
            $values[] = $grandTotal;
            $types   .= 'd';
        }
        if (isset($orderCols['grand_total'])) {
            $fields[] = 'grand_total';
            $values[] = $grandTotal;
            $types   .= 'd';
        }
        if (isset($orderCols['shipping_address'])) {
            $fields[] = 'shipping_address';
            $values[] = $fullAddr;
            $types   .= 's';
        }
        if (isset($orderCols['shipping_name'])) {
            $fields[] = 'shipping_name';
            $values[] = $address['recipient_name'];
            $types   .= 's';
        }
        if (isset($orderCols['shipping_phone'])) {
            $fields[] = 'shipping_phone';
            $values[] = $address['phone'];
            $types   .= 's';
        }
        if (isset($orderCols['shipping_city'])) {
            $fields[] = 'shipping_city';
            $values[] = $address['city'];
            $types   .= 's';
        }
        if (isset($orderCols['shipping_state'])) {
            $fields[] = 'shipping_state';
            $values[] = $address['state'];
            $types   .= 's';
        }
        if (isset($orderCols['shipping_postal'])) {
            $fields[] = 'shipping_postal';
            $values[] = $address['postal_code'];
            $types   .= 's';
        }
        if (isset($orderCols['placed_at'])) {
            $fields[] = 'placed_at';
        }
        if (isset($orderCols['created_at'])) {
            $fields[] = 'created_at';
        }
        if (isset($orderCols['updated_at'])) {
            $fields[] = 'updated_at';
        }

        $placeholders = array_map(fn($f) => in_array($f, ['placed_at', 'created_at', 'updated_at']) ? 'NOW()' : '?', $fields);
        $sql = "INSERT INTO orders (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";

        $stmt = $db->prepare($sql);
        if (!$stmt) {
            $this->json(false, 'Database error creating order: ' . $db->error, [], 500);
        }

        $stmt->bind_param($types, ...$values);
        $ok = $stmt->execute();
        $orderId = $stmt->insert_id;
        $stmtErr = $stmt->error;
        $stmt->close();

        if (!$ok) {
            $this->json(false, 'Failed to place order: ' . ($stmtErr ?: $db->error), [], 500);
        }

        if (!empty($couponCode)) {
            $db->query("UPDATE coupons SET used_count = used_count + 1 WHERE UPPER(code) = UPPER('" . $db->real_escape_string($couponCode) . "')");
        }

        // 2. Inspect existing columns of `order_items` table dynamically
        $itemColsRes = $db->query("SHOW COLUMNS FROM order_items");
        $itemCols = [];
        if ($itemColsRes) {
            while ($c = $itemColsRes->fetch_assoc()) {
                $itemCols[$c['Field']] = true;
            }
        }

        foreach ($cartItems as $ci) {
            $pId      = (int)$ci['product_id'];
            $pName    = $ci['name'] ?? 'Product';
            $pSku     = !empty($ci['sku']) ? $ci['sku'] : ('SKU-' . $pId);
            $qty      = max(1, (int)($ci['quantity'] ?? 1));
            $uPrc     = (float)($ci['unit_price'] ?? 0);
            $sPrc     = (float)($ci['sale_price'] ?? $uPrc);
            $tPrc     = $uPrc * $qty;
            $taxRate  = (float)($ci['tax_rate'] ?? 0.00);
            $taxAmt   = round($tPrc * ($taxRate / 100), 2);
            $imgUrl   = $ci['image_url'] ?? null;
            $varId    = !empty($ci['variant_id']) ? (int)$ci['variant_id'] : null;
            $varLabel = $ci['variant_label'] ?? null;

            $iFields = ['order_id', 'product_id', 'quantity', 'unit_price'];
            $iVals   = [$orderId, $pId, $qty, $uPrc];
            $iTypes  = 'iiid';

            if (isset($itemCols['product_name'])) {
                $iFields[] = 'product_name';
                $iVals[]   = $pName;
                $iTypes   .= 's';
            }
            if (isset($itemCols['product_sku'])) {
                $iFields[] = 'product_sku';
                $iVals[]   = $pSku;
                $iTypes   .= 's';
            }
            if (isset($itemCols['sale_price'])) {
                $iFields[] = 'sale_price';
                $iVals[]   = $sPrc;
                $iTypes   .= 'd';
            }
            if (isset($itemCols['line_total'])) {
                $iFields[] = 'line_total';
                $iVals[]   = $tPrc;
                $iTypes   .= 'd';
            }
            if (isset($itemCols['total_price'])) {
                $iFields[] = 'total_price';
                $iVals[]   = $tPrc;
                $iTypes   .= 'd';
            }
            if (isset($itemCols['tax_rate'])) {
                $iFields[] = 'tax_rate';
                $iVals[]   = $taxRate;
                $iTypes   .= 'd';
            }
            if (isset($itemCols['tax_amount'])) {
                $iFields[] = 'tax_amount';
                $iVals[]   = $taxAmt;
                $iTypes   .= 'd';
            }
            if (isset($itemCols['image_url']) && !empty($imgUrl)) {
                $iFields[] = 'image_url';
                $iVals[]   = $imgUrl;
                $iTypes   .= 's';
            }
            if (isset($itemCols['variant_id']) && $varId !== null) {
                $iFields[] = 'variant_id';
                $iVals[]   = $varId;
                $iTypes   .= 'i';
            }
            if (isset($itemCols['variant_label']) && !empty($varLabel)) {
                $iFields[] = 'variant_label';
                $iVals[]   = $varLabel;
                $iTypes   .= 's';
            }
            if (isset($itemCols['created_at'])) {
                $iFields[] = 'created_at';
            }

            $iPlaceholders = array_map(fn($f) => ($f === 'created_at' ? 'NOW()' : '?'), $iFields);
            $iSql = "INSERT INTO order_items (" . implode(', ', $iFields) . ") VALUES (" . implode(', ', $iPlaceholders) . ")";

            $stmtItem = $db->prepare($iSql);
            if ($stmtItem) {
                $stmtItem->bind_param($iTypes, ...$iVals);
                $stmtItem->execute();
                if ($stmtItem->error) {
                    error_log("Failed to insert order item: " . $stmtItem->error);
                }
                $stmtItem->close();
            } else {
                error_log("Failed to prepare order item insert: " . $db->error);
            }
        }

        $encOrderId = SecurityHelper::encryptId($orderId);
        $baseUrl    = defined('BASE_URL') ? BASE_URL : '';

        if ($isOnlinePay) {
            // Online Payment via PhonePe Gateway
            $this->json(true, 'Order created! Redirecting to PhonePe Secure Gateway...', [
                'order_number'       => $orderNumber,
                'encrypted_order_id' => $encOrderId,
                'redirect_url'       => $baseUrl . '/payment/phonepe/initiate/' . $encOrderId
            ]);
        } else {
            // COD -> Clear user cart
            $cart = Cart::getOrCreate($userId);
            if (!empty($cart['id'])) {
                $db->query("DELETE FROM cart_items WHERE cart_id = " . (int)$cart['id']);
            }

            $this->json(true, 'Order placed successfully!', [
                'order_number'       => $orderNumber,
                'encrypted_order_id' => $encOrderId,
                'redirect_url'       => $baseUrl . '/checkout/success/' . $encOrderId
            ]);
        }
    }

    /**
     * GET /checkout/success/{id} — Order Success View.
     */
    public function success(string $encOrderId): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);

        if (!$userId) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
            exit;
        }

        $orderId = is_numeric($encOrderId) ? (int)$encOrderId : SecurityHelper::decryptId($encOrderId);
        $order = Order::getOrderById($orderId);

        if (!$order || (int)$order['user_id'] !== $userId) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/');
            exit;
        }

        $orderItems = Order::getOrderItems($orderId);
        $baseUrl    = defined('BASE_URL') ? BASE_URL : '';
        $siteTitle  = 'Order Placed Successfully — ClickCodex';

        require_once __DIR__ . '/../../Views/front/checkout_success.php';
    }
}
