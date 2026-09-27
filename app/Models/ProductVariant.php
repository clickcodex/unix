<?php

namespace App\Models;

use App\Config\Database;
use App\Helpers\SecurityHelper;

class ProductVariant {
    /**
     * Get all variants for a specific product.
     */
    public static function getVariantsByProductId(int $productId): array {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT v.*,
                   (SELECT GROUP_CONCAT(CONCAT(pag.name, ': ', pa.value) SEPARATOR ' / ')
                    FROM variant_attribute_values vav
                    JOIN product_attributes pa ON vav.attribute_id = pa.id
                    JOIN product_attribute_groups pag ON pa.group_id = pag.id
                    WHERE vav.variant_id = v.id) as attribute_summary
            FROM product_variants v
            WHERE v.product_id = ?
            ORDER BY v.id ASC
        ");
        if (!$stmt) return [];

        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $res = $stmt->get_result();

        $variants = [];
        while ($row = $res->fetch_assoc()) {
            $row['encrypted_id'] = SecurityHelper::encryptId($row['id']);
            $row['product_encrypted_id'] = SecurityHelper::encryptId($row['product_id']);
            $row['attribute_ids'] = self::getVariantAttributeIds((int)$row['id']);
            $variants[] = $row;
        }
        $stmt->close();

        return $variants;
    }

    /**
     * Get single variant by ID.
     */
    public static function getVariantById(int $variantId): ?array {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT v.* FROM product_variants v WHERE v.id = ? LIMIT 1
        ");
        if (!$stmt) return null;

        $stmt->bind_param("i", $variantId);
        $stmt->execute();
        $variant = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$variant) return null;

        $variant['encrypted_id'] = SecurityHelper::encryptId($variant['id']);
        $variant['product_encrypted_id'] = SecurityHelper::encryptId($variant['product_id']);
        $variant['attribute_ids'] = self::getVariantAttributeIds($variantId);
        return $variant;
    }

    /**
     * Fetch all attribute IDs associated with a variant.
     */
    public static function getVariantAttributeIds(int $variantId): array {
        $db = Database::connect();
        $res = $db->query("SELECT attribute_id FROM variant_attribute_values WHERE variant_id = {$variantId}");
        if (!$res) return [];
        $ids = [];
        while ($row = $res->fetch_assoc()) {
            $ids[] = (int)$row['attribute_id'];
        }
        return $ids;
    }

    /**
     * Fetch all attribute groups with their child attributes (Color, Size, Storage, Material).
     */
    public static function getAllAttributeGroupsWithValues(): array {
        $db = Database::connect();
        $resGroups = $db->query("SELECT * FROM product_attribute_groups ORDER BY id ASC");
        if (!$resGroups) return [];

        $groups = [];
        while ($group = $resGroups->fetch_assoc()) {
            $groupId = (int)$group['id'];
            $resAttrs = $db->query("SELECT * FROM product_attributes WHERE group_id = {$groupId} ORDER BY sort_order ASC, id ASC");
            $attrs = [];
            if ($resAttrs) {
                while ($attr = $resAttrs->fetch_assoc()) {
                    $attrs[] = $attr;
                }
            }
            $group['attributes'] = $attrs;
            $groups[] = $group;
        }

        return $groups;
    }

    /**
     * Create a new variant for a product.
     */
    public static function createVariant(int $productId, array $data): ?int {
        $db = Database::connect();

        $sku = trim($data['sku'] ?? '');
        if (empty($sku)) {
            $sku = 'CC-VAR-' . strtoupper(substr(md5(uniqid()), 0, 6));
        }

        $priceOverride = isset($data['price_override']) && $data['price_override'] !== '' ? (float)$data['price_override'] : null;
        $stockQty = (int)($data['stock_qty'] ?? 0);
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;

        $stmt = $db->prepare("
            INSERT INTO product_variants (product_id, sku, price_override, stock_qty, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        if (!$stmt) return null;

        $stmt->bind_param("isdii", $productId, $sku, $priceOverride, $stockQty, $isActive);
        $res = $stmt->execute();
        $variantId = $stmt->insert_id;
        $stmt->close();

        if ($res && !empty($data['attribute_ids']) && is_array($data['attribute_ids'])) {
            $stmtAttr = $db->prepare("INSERT INTO variant_attribute_values (variant_id, attribute_id) VALUES (?, ?)");
            if ($stmtAttr) {
                foreach ($data['attribute_ids'] as $attrId) {
                    $attrIdInt = (int)$attrId;
                    if ($attrIdInt > 0) {
                        $stmtAttr->bind_param("ii", $variantId, $attrIdInt);
                        $stmtAttr->execute();
                    }
                }
                $stmtAttr->close();
            }
        }

        return $res ? $variantId : null;
    }

    /**
     * Update an existing variant.
     */
    public static function updateVariant(int $variantId, array $data): bool {
        $db = Database::connect();

        $sku = trim($data['sku'] ?? '');
        $priceOverride = isset($data['price_override']) && $data['price_override'] !== '' ? (float)$data['price_override'] : null;
        $stockQty = (int)($data['stock_qty'] ?? 0);
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;

        $stmt = $db->prepare("
            UPDATE product_variants 
            SET sku = ?, price_override = ?, stock_qty = ?, is_active = ?, updated_at = NOW()
            WHERE id = ?
        ");
        if (!$stmt) return false;

        $stmt->bind_param("sdiii", $sku, $priceOverride, $stockQty, $isActive, $variantId);
        $res = $stmt->execute();
        $stmt->close();

        // Sync Variant Attribute Values
        $db->query("DELETE FROM variant_attribute_values WHERE variant_id = {$variantId}");
        if (!empty($data['attribute_ids']) && is_array($data['attribute_ids'])) {
            $stmtAttr = $db->prepare("INSERT INTO variant_attribute_values (variant_id, attribute_id) VALUES (?, ?)");
            if ($stmtAttr) {
                foreach ($data['attribute_ids'] as $attrId) {
                    $attrIdInt = (int)$attrId;
                    if ($attrIdInt > 0) {
                        $stmtAttr->bind_param("ii", $variantId, $attrIdInt);
                        $stmtAttr->execute();
                    }
                }
                $stmtAttr->close();
            }
        }

        return $res;
    }

    /**
     * Delete a variant.
     */
    public static function deleteVariant(int $variantId): bool {
        $db = Database::connect();
        $db->query("DELETE FROM variant_attribute_values WHERE variant_id = {$variantId}");
        return $db->query("DELETE FROM product_variants WHERE id = {$variantId}");
    }

    /**
     * Toggle active state of a variant.
     */
    public static function toggleVariantActive(int $variantId): bool {
        $db = Database::connect();
        return $db->query("UPDATE product_variants SET is_active = 1 - is_active WHERE id = {$variantId}");
    }
}
