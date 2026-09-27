<?php

namespace App\Models;

use App\Config\Database;
use App\Helpers\SecurityHelper;

class InventoryMovement {

    public static function getMovements(int $limit = 50): array {
        $db = Database::connect();
        $res = $db->query("
            SELECT im.*, p.name as product_name, p.sku as product_sku, u.name as actor_name
            FROM inventory_movements im
            LEFT JOIN products p ON p.id = im.product_id
            LEFT JOIN users u ON u.id = im.created_by
            ORDER BY im.created_at DESC
            LIMIT {$limit}
        ");
        $movements = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $row['encrypted_id'] = SecurityHelper::encryptId($row['id']);
                $movements[] = $row;
            }
        }
        return $movements;
    }

    public static function getLowStockProducts(int $threshold = 10): array {
        $db = Database::connect();
        $res = $db->query("
            SELECT id, name, sku, stock_qty, is_in_stock 
            FROM products 
            WHERE deleted_at IS NULL AND is_active = 1 AND stock_qty <= {$threshold}
            ORDER BY stock_qty ASC
        ");
        $prods = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $row['encrypted_id'] = SecurityHelper::encryptId($row['id']);
                $prods[] = $row;
            }
        }
        return $prods;
    }

    public static function recordMovement(array $data): bool {
        $db = Database::connect();
        $productId     = (int)($data['product_id'] ?? 0);
        $variantId     = !empty($data['variant_id']) ? (int)$data['variant_id'] : null;
        $movementType  = trim($data['movement_type'] ?? 'adjustment');
        $quantity      = (int)($data['quantity'] ?? 0);
        $referenceType = trim($data['reference_type'] ?? 'manual_adjustment');
        $referenceId   = !empty($data['reference_id']) ? (int)$data['reference_id'] : null;
        $createdBy     = (int)($_SESSION['admin_user_id'] ?? ($_SESSION['user_id'] ?? 1));

        if (!$productId || $quantity == 0) return false;

        $stmt = $db->prepare("
            INSERT INTO inventory_movements 
            (product_id, variant_id, movement_type, quantity, reference_type, reference_id, note, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        if (!$stmt) return false;
        $stmt->bind_param("iisisisi", $productId, $variantId, $movementType, $quantity, $referenceType, $referenceId, $note, $createdBy);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            // Update product stock_qty and is_in_stock
            $multiplier = ($movementType === 'in' || $movementType === 'return') ? 1 : -1;
            if ($movementType === 'adjustment') $multiplier = 1; // direct signed adjustment

            $delta = $quantity * $multiplier;
            $db->query("
                UPDATE products 
                SET stock_qty = GREATEST(0, stock_qty + ({$delta})),
                    is_in_stock = IF(stock_qty + ({$delta}) > 0, 1, 0)
                WHERE id = {$productId}
            ");
        }

        return $ok;
    }
}
