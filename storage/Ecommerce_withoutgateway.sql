-- =============================================================================
-- SINGLE-OWNER E-COMMERCE DATABASE SCHEMA
-- Version      : 2.0.0
-- Engine       : InnoDB | Charset: utf8mb4 | Collation: utf8mb4_unicode_ci
-- Designed for : High scalability, full normalization, future-ready expansion
-- Supports     : Payments, delivery, coupons, inventory, notifications,
--                and advanced e-commerce modules
-- Note         : Vendor layer removed — this store belongs to one owner.
--                Multi-vendor upgrade path: add vendors table + vendor_id FKs.
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO';

-- =============================================================================
-- SECTION 1: USERS & AUTHENTICATION
-- =============================================================================

CREATE TABLE users (
    id                BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    uuid              CHAR(36)         NOT NULL,               -- for public-facing URLs
    name              VARCHAR(120)     NOT NULL,
    email             VARCHAR(180)     NOT NULL,
    phone             VARCHAR(20)      NULL,
    password_hash     VARCHAR(255)     NOT NULL,
    avatar_url        VARCHAR(500)     NULL,
    gender            ENUM('male','female','other','prefer_not_to_say') NULL,
    date_of_birth     DATE             NULL,
    is_active         TINYINT(1)       NOT NULL DEFAULT 1,
    is_verified       TINYINT(1)       NOT NULL DEFAULT 0,
    email_verified_at DATETIME         NULL,
    phone_verified_at DATETIME         NULL,
    last_login_at     DATETIME         NULL,
    created_at        DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at        DATETIME         NULL,                   -- soft delete
    -- Future: role_id FK to roles table for RBAC
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_uuid  (uuid),
    UNIQUE KEY uq_users_email (email),
    KEY idx_users_phone       (phone),
    KEY idx_users_active      (is_active),
    KEY idx_users_deleted     (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE user_addresses (
    id              BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    user_id         BIGINT UNSIGNED  NOT NULL,
    label           VARCHAR(60)      NULL,                   -- "Home", "Office" etc.
    recipient_name  VARCHAR(120)     NOT NULL,
    phone           VARCHAR(20)      NOT NULL,
    address_line1   VARCHAR(255)     NOT NULL,
    address_line2   VARCHAR(255)     NULL,
    city            VARCHAR(100)     NOT NULL,
    state           VARCHAR(100)     NOT NULL,
    country         VARCHAR(100)     NOT NULL DEFAULT 'India',
    postal_code     VARCHAR(20)      NOT NULL,
    latitude        DECIMAL(10,7)    NULL,
    longitude       DECIMAL(10,7)    NULL,
    is_default      TINYINT(1)       NOT NULL DEFAULT 0,
    created_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_addr_user         (user_id),
    KEY idx_addr_default      (user_id, is_default),
    CONSTRAINT fk_addr_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE user_sessions (
    id              BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    user_id         BIGINT UNSIGNED  NOT NULL,
    token_hash      VARCHAR(255)     NOT NULL,               -- hashed session/refresh token
    device_name     VARCHAR(100)     NULL,
    device_type     ENUM('web','ios','android','other') NOT NULL DEFAULT 'web',
    ip_address      VARCHAR(45)      NULL,                   -- supports IPv6
    user_agent      TEXT             NULL,
    expires_at      DATETIME         NOT NULL,
    revoked_at      DATETIME         NULL,
    created_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_sess_user         (user_id),
    KEY idx_sess_token        (token_hash(64)),
    KEY idx_sess_expires      (expires_at),
    CONSTRAINT fk_sess_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE password_resets (
    id              BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    user_id         BIGINT UNSIGNED  NOT NULL,
    token_hash      VARCHAR(255)     NOT NULL,
    expires_at      DATETIME         NOT NULL,
    used_at         DATETIME         NULL,
    created_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_pr_user           (user_id),
    KEY idx_pr_token          (token_hash(64)),
    CONSTRAINT fk_pr_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- SECTION 2: PRODUCT TAXONOMY (categories, subcategories, tags)
-- =============================================================================

CREATE TABLE categories (
    id               BIGINT UNSIGNED   NOT NULL AUTO_INCREMENT,
    parent_id        BIGINT UNSIGNED   NULL,                  -- NULL = root category
    name             VARCHAR(120)      NOT NULL,
    slug             VARCHAR(150)      NOT NULL,
    description      TEXT              NULL,
    image_url        VARCHAR(500)      NULL,
    sort_order       SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_active        TINYINT(1)        NOT NULL DEFAULT 1,
    meta_title       VARCHAR(200)      NULL,
    meta_description VARCHAR(500)      NULL,
    created_at       DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_cat_slug        (slug),
    KEY idx_cat_parent            (parent_id),
    KEY idx_cat_active            (is_active),
    KEY idx_cat_sort              (sort_order),
    CONSTRAINT fk_cat_parent FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE tags (
    id              BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    name            VARCHAR(80)      NOT NULL,
    slug            VARCHAR(100)     NOT NULL,
    tag_type        ENUM(
                        'featured',
                        'top_rated',
                        'new_arrival',
                        'best_seller',
                        'trending',
                        'offer',
                        'limited_edition',
                        'custom'
                    ) NOT NULL DEFAULT 'custom',
    color_hex       VARCHAR(7)       NULL,                   -- badge display color
    is_active       TINYINT(1)       NOT NULL DEFAULT 1,
    created_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tags_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tags (name, slug, tag_type, color_hex) VALUES
('Featured',       'featured',        'featured',       '#6C63FF'),
('Top Rated',      'top-rated',       'top_rated',      '#F59E0B'),
('New Arrival',    'new-arrival',     'new_arrival',    '#10B981'),
('Best Seller',    'best-seller',     'best_seller',    '#EF4444'),
('Trending',       'trending',        'trending',       '#3B82F6'),
('On Offer',       'on-offer',        'offer',          '#F97316'),
('Limited Edition','limited-edition', 'limited_edition','#8B5CF6');

CREATE TABLE units (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,        -- Kilogram
    symbol VARCHAR(20) NOT NULL,      -- kg
    unit_type ENUM(
        'weight',
        'volume',
        'count',
        'length',
        'area'
    ) NOT NULL
);
-- =============================================================================
-- SECTION 3: PRODUCTS
-- =============================================================================

CREATE TABLE products (
    id               BIGINT UNSIGNED   NOT NULL AUTO_INCREMENT,
    uuid             CHAR(36)          NOT NULL,
    category_id      BIGINT UNSIGNED   NOT NULL,
    name             VARCHAR(255)      NOT NULL,
    slug             VARCHAR(300)      NOT NULL,
    sku              VARCHAR(100)      NOT NULL,
    short_description TEXT             NULL,
    description      LONGTEXT          NULL,                  -- rich HTML/markdown
    base_price       DECIMAL(12,2)     NOT NULL,              -- MRP / original price
    sale_price       DECIMAL(12,2)     NULL,                  -- discounted price
    cost_price       DECIMAL(12,2)     NULL,                  -- for margin calc
    currency         CHAR(3)           NOT NULL DEFAULT 'INR',
    tax_rate         DECIMAL(5,2)      NOT NULL DEFAULT 0.00, -- GST %
    weight_grams     INT UNSIGNED      NULL,
    length_mm        SMALLINT UNSIGNED NULL,
    width_mm         SMALLINT UNSIGNED NULL,
    height_mm        SMALLINT UNSIGNED NULL,
    is_active        TINYINT(1)        NOT NULL DEFAULT 1,
    is_featured      TINYINT(1)        NOT NULL DEFAULT 0,    -- denormalized for fast query
    is_in_stock      TINYINT(1)        NOT NULL DEFAULT 1,    -- denormalized flag
    sort_order       SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    -- SEO
    meta_title       VARCHAR(200)      NULL,
    meta_description VARCHAR(500)      NULL,
    -- Aggregated counters (updated by triggers/jobs)
    review_count     INT UNSIGNED      NOT NULL DEFAULT 0,
    average_rating   DECIMAL(3,2)      NOT NULL DEFAULT 0.00,
    total_sold       INT UNSIGNED      NOT NULL DEFAULT 0,
    wishlist_count   INT UNSIGNED      NOT NULL DEFAULT 0,
    -- Future: has_variants TINYINT(1) DEFAULT 0, bundle_id FK
    created_at       DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at       DATETIME          NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_products_uuid  (uuid),
    UNIQUE KEY uq_products_sku   (sku),
    UNIQUE KEY uq_products_slug  (slug),
    KEY idx_prod_category        (category_id),
    KEY idx_prod_active          (is_active, deleted_at),
    KEY idx_prod_featured        (is_featured, is_active),
    KEY idx_prod_rating          (average_rating DESC),
    KEY idx_prod_sold            (total_sold DESC),
    KEY idx_prod_price           (base_price, sale_price),
    FULLTEXT KEY ft_prod_name    (name, short_description),
    CONSTRAINT fk_prod_category FOREIGN KEY (category_id) REFERENCES categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
ALTER TABLE products
ADD unit_id BIGINT UNSIGNED NULL,
ADD unit_value DECIMAL(10,2) NULL,
ADD CONSTRAINT fk_product_unit
FOREIGN KEY (unit_id) REFERENCES units(id);

CREATE TABLE product_images (
    id              BIGINT UNSIGNED   NOT NULL AUTO_INCREMENT,
    product_id      BIGINT UNSIGNED   NOT NULL,
    image_url       VARCHAR(500)      NOT NULL,
    alt_text        VARCHAR(255)      NULL,
    sort_order      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_primary      TINYINT(1)        NOT NULL DEFAULT 0,
    created_at      DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_pimg_product    (product_id),
    KEY idx_pimg_primary    (product_id, is_primary),
    KEY idx_pimg_sort       (product_id, sort_order),
    CONSTRAINT fk_pimg_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE product_videos (
    id              BIGINT UNSIGNED   NOT NULL AUTO_INCREMENT,
    product_id      BIGINT UNSIGNED   NOT NULL,
    video_type      ENUM('upload','youtube','vimeo') NOT NULL DEFAULT 'upload',
    video_url       VARCHAR(500)      NOT NULL,              -- S3/CDN URL or embed ID
    thumbnail_url   VARCHAR(500)      NULL,
    title           VARCHAR(255)      NULL,
    sort_order      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    created_at      DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_pvid_product    (product_id),
    CONSTRAINT fk_pvid_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE product_specifications (
    id              BIGINT UNSIGNED   NOT NULL AUTO_INCREMENT,
    product_id      BIGINT UNSIGNED   NOT NULL,
    spec_group      VARCHAR(100)      NULL,                  -- "General", "Display" etc.
    spec_key        VARCHAR(100)      NOT NULL,
    spec_value      TEXT              NOT NULL,
    sort_order      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_pspec_product   (product_id),
    CONSTRAINT fk_pspec_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE product_tags (
    product_id      BIGINT UNSIGNED  NOT NULL,
    tag_id          BIGINT UNSIGNED  NOT NULL,
    assigned_at     DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (product_id, tag_id),
    KEY idx_ptag_tag        (tag_id),
    CONSTRAINT fk_ptag_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_ptag_tag    FOREIGN KEY (tag_id)     REFERENCES tags(id)     ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Product variants (future multi-variant support — structure is ready)
CREATE TABLE product_attribute_groups (
    id              BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    name            VARCHAR(80)      NOT NULL,               -- "Color", "Size", "Material"
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE product_attributes (
    id              BIGINT UNSIGNED   NOT NULL AUTO_INCREMENT,
    group_id        BIGINT UNSIGNED   NOT NULL,
    value           VARCHAR(100)      NOT NULL,              -- "Red", "XL", "Cotton"
    sort_order      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_pattr_group     (group_id),
    CONSTRAINT fk_pattr_group FOREIGN KEY (group_id) REFERENCES product_attribute_groups(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE product_variants (
    id              BIGINT UNSIGNED   NOT NULL AUTO_INCREMENT,
    product_id      BIGINT UNSIGNED   NOT NULL,
    sku             VARCHAR(100)      NOT NULL,
    price_override  DECIMAL(12,2)     NULL,                  -- NULL = inherit from product
    stock_qty       INT               NOT NULL DEFAULT 0,
    is_active       TINYINT(1)        NOT NULL DEFAULT 1,
    image_id        BIGINT UNSIGNED   NULL,
    created_at      DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_var_sku       (sku),
    KEY idx_var_product         (product_id),
    CONSTRAINT fk_var_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `units` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `symbol` varchar(20) NOT NULL,
  `unit_type` enum('weight','volume','count','length','area') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE variant_attribute_values (
    variant_id      BIGINT UNSIGNED  NOT NULL,
    attribute_id    BIGINT UNSIGNED  NOT NULL,
    PRIMARY KEY (variant_id, attribute_id),
    CONSTRAINT fk_vav_variant   FOREIGN KEY (variant_id)   REFERENCES product_variants(id) ON DELETE CASCADE,
    CONSTRAINT fk_vav_attribute FOREIGN KEY (attribute_id) REFERENCES product_attributes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- SECTION 4: OFFERS & PROMOTIONS
-- =============================================================================

CREATE TABLE offers (
    id               BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    name             VARCHAR(150)     NOT NULL,
    description      TEXT             NULL,
    offer_type       ENUM(
                         'percentage',       -- % off
                         'flat',             -- fixed amount off
                         'buy_x_get_y',      -- BXGY
                         'combo'             -- combo deal
                     ) NOT NULL DEFAULT 'percentage',
    discount_value   DECIMAL(10,2)    NOT NULL,              -- % or flat amount
    min_order_value  DECIMAL(12,2)    NULL,
    max_discount_cap DECIMAL(12,2)    NULL,
    starts_at        DATETIME         NOT NULL,
    ends_at          DATETIME         NULL,
    is_active        TINYINT(1)       NOT NULL DEFAULT 1,
    -- Future: coupon_code, usage_limit, user_limit FK
    created_at       DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_offers_active       (is_active),
    KEY idx_offers_dates        (starts_at, ends_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Offer applicability: can be global, per category, or per product
CREATE TABLE offer_applicability (
    id              BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    offer_id        BIGINT UNSIGNED  NOT NULL,
    scope           ENUM('all','category','product') NOT NULL DEFAULT 'all',
    ref_id          BIGINT UNSIGNED  NULL,                   -- category_id or product_id
    PRIMARY KEY (id),
    KEY idx_oa_offer            (offer_id),
    KEY idx_oa_scope_ref        (scope, ref_id),
    CONSTRAINT fk_oa_offer FOREIGN KEY (offer_id) REFERENCES offers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- SECTION 5: BANNERS & SLIDERS
-- =============================================================================

CREATE TABLE banners (
    id               BIGINT UNSIGNED   NOT NULL AUTO_INCREMENT,
    title            VARCHAR(200)      NOT NULL,
    subtitle         VARCHAR(255)      NULL,
    image_url        VARCHAR(500)      NOT NULL,
    mobile_image_url VARCHAR(500)      NULL,                 -- separate mobile image
    link_url         VARCHAR(500)      NULL,
    link_target      ENUM('_self','_blank') NOT NULL DEFAULT '_self',
    banner_type      ENUM(
                         'hero_slider',      -- homepage hero carousel
                         'offer_banner',     -- promotional banner section
                         'category_banner',  -- category page header
                         'custom'
                     ) NOT NULL DEFAULT 'hero_slider',
    placement        VARCHAR(100)      NULL,                 -- "homepage_top", "sidebar" etc.
    sort_order       SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    starts_at        DATETIME          NULL,
    ends_at          DATETIME          NULL,
    is_active        TINYINT(1)        NOT NULL DEFAULT 1,
    created_at       DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_banner_type         (banner_type, is_active),
    KEY idx_banner_placement    (placement),
    KEY idx_banner_sort         (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- SECTION 6: PRODUCT REVIEWS & RATINGS
-- =============================================================================

CREATE TABLE reviews (
    id                   BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    product_id           BIGINT UNSIGNED  NOT NULL,
    user_id              BIGINT UNSIGNED  NOT NULL,
    order_item_id        BIGINT UNSIGNED  NULL,              -- FK added after orders table; verified buyers only
    rating               TINYINT UNSIGNED NOT NULL,          -- 1–5
    title                VARCHAR(200)     NULL,
    body                 TEXT             NULL,
    status               ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    is_verified_purchase TINYINT(1)       NOT NULL DEFAULT 0,
    helpful_count        INT UNSIGNED     NOT NULL DEFAULT 0,
    created_at           DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at           DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_review_user_product (user_id, product_id),  -- one review per product
    KEY idx_rev_product         (product_id, status),
    KEY idx_rev_rating          (product_id, rating),
    KEY idx_rev_user            (user_id),
    CONSTRAINT fk_rev_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_rev_user    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    CONSTRAINT chk_rev_rating  CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE review_images (
    id              BIGINT UNSIGNED   NOT NULL AUTO_INCREMENT,
    review_id       BIGINT UNSIGNED   NOT NULL,
    image_url       VARCHAR(500)      NOT NULL,
    sort_order      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_rimg_review         (review_id),
    CONSTRAINT fk_rimg_review FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- SECTION 7: WISHLIST
-- =============================================================================

CREATE TABLE wishlists (
    id              BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    user_id         BIGINT UNSIGNED  NOT NULL,
    name            VARCHAR(100)     NOT NULL DEFAULT 'My Wishlist',
    is_public       TINYINT(1)       NOT NULL DEFAULT 0,
    -- Future: share_token for shareable wishlists
    created_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_wl_user             (user_id),
    CONSTRAINT fk_wl_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE wishlist_items (
    id              BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    wishlist_id     BIGINT UNSIGNED  NOT NULL,
    product_id      BIGINT UNSIGNED  NOT NULL,
    variant_id      BIGINT UNSIGNED  NULL,
    added_at        DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_wli_wishlist_product (wishlist_id, product_id, variant_id),
    KEY idx_wli_product         (product_id),
    CONSTRAINT fk_wli_wishlist FOREIGN KEY (wishlist_id) REFERENCES wishlists(id)        ON DELETE CASCADE,
    CONSTRAINT fk_wli_product  FOREIGN KEY (product_id)  REFERENCES products(id)         ON DELETE CASCADE,
    CONSTRAINT fk_wli_variant  FOREIGN KEY (variant_id)  REFERENCES product_variants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- SECTION 8: SHOPPING CART
-- =============================================================================

CREATE TABLE carts (
    id              BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    user_id         BIGINT UNSIGNED  NULL,                   -- NULL = guest cart
    session_token   VARCHAR(255)     NULL,                   -- for guest identification
    coupon_code     VARCHAR(50)      NULL,                   -- Future: FK to coupons
    notes           TEXT             NULL,
    expires_at      DATETIME         NULL,
    created_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_cart_user           (user_id),
    KEY idx_cart_session        (session_token),
    KEY idx_cart_expires        (expires_at),
    CONSTRAINT fk_cart_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE cart_items (
    id              BIGINT UNSIGNED   NOT NULL AUTO_INCREMENT,
    cart_id         BIGINT UNSIGNED   NOT NULL,
    product_id      BIGINT UNSIGNED   NOT NULL,
    variant_id      BIGINT UNSIGNED   NULL,
    quantity        SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    unit_price      DECIMAL(12,2)     NOT NULL,              -- price at time of adding
    added_at        DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_ci_cart_product_variant (cart_id, product_id, variant_id),
    KEY idx_ci_product          (product_id),
    CONSTRAINT fk_ci_cart    FOREIGN KEY (cart_id)    REFERENCES carts(id)             ON DELETE CASCADE,
    CONSTRAINT fk_ci_product FOREIGN KEY (product_id) REFERENCES products(id),
    CONSTRAINT fk_ci_variant FOREIGN KEY (variant_id) REFERENCES product_variants(id)  ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- SECTION 9: ORDERS & ORDER ITEMS
-- =============================================================================

CREATE TABLE orders (
    id              BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    uuid            CHAR(36)         NOT NULL,               -- public order reference
    order_number    VARCHAR(30)      NOT NULL,               -- e.g. ORD-20240601-0001
    user_id         BIGINT UNSIGNED  NOT NULL,
    -- Shipping snapshot (denormalized at order time — address can change later)
    shipping_name   VARCHAR(120)     NOT NULL,
    shipping_phone  VARCHAR(20)      NOT NULL,
    shipping_line1  VARCHAR(255)     NOT NULL,
    shipping_line2  VARCHAR(255)     NULL,
    shipping_city   VARCHAR(100)     NOT NULL,
    shipping_state  VARCHAR(100)     NOT NULL,
    shipping_country VARCHAR(100)    NOT NULL DEFAULT 'India',
    shipping_postal VARCHAR(20)      NOT NULL,
    -- Financials
    subtotal        DECIMAL(14,2)    NOT NULL,
    discount_amount DECIMAL(14,2)    NOT NULL DEFAULT 0.00,
    shipping_charge DECIMAL(14,2)    NOT NULL DEFAULT 0.00,
    tax_amount      DECIMAL(14,2)    NOT NULL DEFAULT 0.00,
    total_amount    DECIMAL(14,2)    NOT NULL,
    currency        CHAR(3)          NOT NULL DEFAULT 'INR',
    -- Offer/coupon context
    offer_id        BIGINT UNSIGNED  NULL,
    coupon_code     VARCHAR(50)      NULL,
    -- Status
    status          ENUM(
                        'pending',           -- order just created, awaiting confirmation
                        'confirmed',         -- store owner confirmed
                        'processing',        -- being packed/prepared
                        'shipped',           -- dispatched
                        'delivered',         -- marked delivered
                        'cancelled',         -- cancelled
                        'return_requested',
                        'returned',
                        'refunded'
                    ) NOT NULL DEFAULT 'pending',
    payment_status  ENUM('unpaid','paid','refunded','partially_refunded') NOT NULL DEFAULT 'unpaid',
    -- Future: payment_method_id FK, payment_gateway, gateway_txn_id
    -- Future: delivery_partner_id FK, tracking_number
    notes           TEXT             NULL,
    placed_at       DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    confirmed_at    DATETIME         NULL,
    shipped_at      DATETIME         NULL,
    delivered_at    DATETIME         NULL,
    cancelled_at    DATETIME         NULL,
    created_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_orders_uuid         (uuid),
    UNIQUE KEY uq_orders_number       (order_number),
    KEY idx_ord_user                  (user_id),
    KEY idx_ord_status                (status),
    KEY idx_ord_placed                (placed_at),
    KEY idx_ord_payment               (payment_status),
    CONSTRAINT fk_ord_user  FOREIGN KEY (user_id)  REFERENCES users(id),
    CONSTRAINT fk_ord_offer FOREIGN KEY (offer_id) REFERENCES offers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE order_items (
    id              BIGINT UNSIGNED   NOT NULL AUTO_INCREMENT,
    order_id        BIGINT UNSIGNED   NOT NULL,
    product_id      BIGINT UNSIGNED   NOT NULL,
    variant_id      BIGINT UNSIGNED   NULL,
    -- Snapshot of product data at time of purchase
    product_name    VARCHAR(255)      NOT NULL,
    product_sku     VARCHAR(100)      NOT NULL,
    variant_label   VARCHAR(200)      NULL,                  -- "Color: Red, Size: L"
    image_url       VARCHAR(500)      NULL,
    unit_price      DECIMAL(12,2)     NOT NULL,
    sale_price      DECIMAL(12,2)     NOT NULL,              -- actual price charged
    quantity        SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    tax_rate        DECIMAL(5,2)      NOT NULL DEFAULT 0.00,
    tax_amount      DECIMAL(12,2)     NOT NULL DEFAULT 0.00,
    line_total      DECIMAL(14,2)     NOT NULL,
    -- Future: is_returned TINYINT(1) DEFAULT 0, return_qty
    created_at      DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_oi_order            (order_id),
    KEY idx_oi_product          (product_id),
    CONSTRAINT fk_oi_order   FOREIGN KEY (order_id)   REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_oi_product FOREIGN KEY (product_id) REFERENCES products(id),
    CONSTRAINT fk_oi_variant FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Add the deferred FK from reviews → order_items
ALTER TABLE reviews
    ADD CONSTRAINT fk_rev_order_item
    FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE SET NULL;


-- =============================================================================
-- SECTION 10: ORDER TRACKING
-- =============================================================================

CREATE TABLE order_status_history (
    id              BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    order_id        BIGINT UNSIGNED  NOT NULL,
    status          ENUM(
                        'pending',
                        'confirmed',
                        'processing',
                        'shipped',
                        'delivered',
                        'cancelled',
                        'return_requested',
                        'returned',
                        'refunded'
                    ) NOT NULL,
    note            TEXT             NULL,                   -- admin comment / public msg
    is_public       TINYINT(1)       NOT NULL DEFAULT 1,     -- show to customer?
    changed_by      BIGINT UNSIGNED  NULL,                   -- user_id of admin/staff
    -- Future: tracking_number, carrier_name, estimated_delivery_date
    created_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_osh_order           (order_id),
    KEY idx_osh_status          (status),
    KEY idx_osh_created         (created_at),
    CONSTRAINT fk_osh_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- SECTION 11: SEARCH & NAVIGATION
-- =============================================================================

-- Saved/recent searches per user
CREATE TABLE search_history (
    id              BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    user_id         BIGINT UNSIGNED  NULL,
    session_token   VARCHAR(255)     NULL,
    query           VARCHAR(500)     NOT NULL,
    result_count    INT UNSIGNED     NULL,
    searched_at     DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_sh_user             (user_id),
    KEY idx_sh_query            (query(100)),
    CONSTRAINT fk_sh_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Product views / browse history for recommendations
CREATE TABLE product_views (
    id              BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    product_id      BIGINT UNSIGNED  NOT NULL,
    user_id         BIGINT UNSIGNED  NULL,
    session_token   VARCHAR(255)     NULL,
    ip_address      VARCHAR(45)      NULL,
    viewed_at       DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_pv_product          (product_id),
    KEY idx_pv_user             (user_id),
    KEY idx_pv_date             (viewed_at),
    CONSTRAINT fk_pv_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- SECTION 12: MEDIA LIBRARY (centralised asset management)
-- =============================================================================

CREATE TABLE media (
    id              BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    filename        VARCHAR(255)     NOT NULL,
    original_name   VARCHAR(255)     NOT NULL,
    mime_type       VARCHAR(100)     NOT NULL,
    size_bytes      BIGINT UNSIGNED  NOT NULL,
    width           SMALLINT UNSIGNED NULL,
    height          SMALLINT UNSIGNED NULL,
    storage_disk    VARCHAR(30)      NOT NULL DEFAULT 'local', -- 'local','s3','gcs'
    storage_path    VARCHAR(500)     NOT NULL,
    public_url      VARCHAR(500)     NOT NULL,
    uploaded_by     BIGINT UNSIGNED  NULL,
    created_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_media_uploader      (uploaded_by),
    KEY idx_media_type          (mime_type),
    CONSTRAINT fk_media_uploader FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- SECTION 13: SETTINGS & SYSTEM CONFIGURATION
-- =============================================================================

CREATE TABLE settings (
    id              BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    setting_key     VARCHAR(100)     NOT NULL,
    setting_value   LONGTEXT         NULL,
    value_type      ENUM('string','integer','decimal','boolean','json') NOT NULL DEFAULT 'string',
    group_name      VARCHAR(60)      NULL,                   -- "general", "mail", "seo"
    label           VARCHAR(150)     NULL,
    is_public       TINYINT(1)       NOT NULL DEFAULT 0,     -- expose in API?
    updated_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_settings_key  (setting_key),
    KEY idx_settings_group      (group_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings (setting_key, setting_value, value_type, group_name, label, is_public) VALUES
('site_name',           'My Store',     'string',  'general', 'Site Name',              1),
('currency',            'INR',          'string',  'general', 'Default Currency',       1),
('currency_symbol',     '₹',            'string',  'general', 'Currency Symbol',        1),
('tax_inclusive',       'false',        'boolean', 'general', 'Prices Tax Inclusive',   1),
('free_shipping_above', '999',          'decimal', 'shipping','Free Shipping Threshold',1),
('order_prefix',        'ORD',          'string',  'orders',  'Order Number Prefix',    0),
('review_auto_approve', 'false',        'boolean', 'reviews', 'Auto-approve Reviews',   0);


-- =============================================================================
-- SECTION 14: ADMIN / STAFF (RBAC skeleton)
-- =============================================================================

CREATE TABLE roles (
    id              BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    name            VARCHAR(60)      NOT NULL,
    slug            VARCHAR(60)      NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_roles_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO roles (name, slug) VALUES
('Super Admin', 'super_admin'),
('Admin',       'admin'),
('Staff',       'staff'),
('Customer',    'customer');


CREATE TABLE permissions (
    id              BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    name            VARCHAR(100)     NOT NULL,
    slug            VARCHAR(100)     NOT NULL,
    module          VARCHAR(60)      NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_perms_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE role_permissions (
    role_id         BIGINT UNSIGNED  NOT NULL,
    permission_id   BIGINT UNSIGNED  NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    CONSTRAINT fk_rp_role       FOREIGN KEY (role_id)       REFERENCES roles(id)       ON DELETE CASCADE,
    CONSTRAINT fk_rp_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE user_roles (
    user_id         BIGINT UNSIGNED  NOT NULL,
    role_id         BIGINT UNSIGNED  NOT NULL,
    PRIMARY KEY (user_id, role_id),
    CONSTRAINT fk_ur_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_ur_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- SECTION 15: NOTIFICATIONS
-- =============================================================================

CREATE TABLE notifications (
    id              BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    user_id         BIGINT UNSIGNED  NOT NULL,
    type            VARCHAR(100)     NOT NULL,               -- 'order_shipped', 'review_approved'
    channel         ENUM('in_app','email','sms','push') NOT NULL DEFAULT 'in_app',
    title           VARCHAR(200)     NULL,
    body            TEXT             NULL,
    data            JSON             NULL,                   -- arbitrary payload
    is_read         TINYINT(1)       NOT NULL DEFAULT 0,
    read_at         DATETIME         NULL,
    sent_at         DATETIME         NULL,
    created_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_notif_user          (user_id, is_read),
    KEY idx_notif_type          (type),
    KEY idx_notif_created       (created_at),
    CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- SECTION 16: AUDIT LOG (immutable event trail)
-- =============================================================================

CREATE TABLE audit_logs (
    id              BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    user_id         BIGINT UNSIGNED  NULL,
    event           VARCHAR(150)     NOT NULL,               -- 'order.status_changed'
    model           VARCHAR(100)     NULL,                   -- 'Order'
    model_id        BIGINT UNSIGNED  NULL,
    old_data        JSON             NULL,
    new_data        JSON             NULL,
    ip_address      VARCHAR(45)      NULL,
    created_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_audit_user          (user_id),
    KEY idx_audit_model         (model, model_id),
    KEY idx_audit_event         (event),
    KEY idx_audit_created       (created_at)
    -- No FK on user_id intentionally — audit logs persist even after user deletion
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- SECTION 17: FUTURE-READY STUBS (fully expandable)
-- =============================================================================

-- Coupons (plug into orders.coupon_code when ready)
CREATE TABLE coupons (
    id              BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    code            VARCHAR(50)      NOT NULL,
    description     VARCHAR(255)     NULL,
    discount_type   ENUM('percentage','flat') NOT NULL DEFAULT 'percentage',
    discount_value  DECIMAL(10,2)    NOT NULL,
    min_order_value DECIMAL(12,2)    NULL,
    max_discount    DECIMAL(12,2)    NULL,
    usage_limit     INT UNSIGNED     NULL,
    used_count      INT UNSIGNED     NOT NULL DEFAULT 0,
    per_user_limit  TINYINT UNSIGNED NOT NULL DEFAULT 1,
    starts_at       DATETIME         NULL,
    ends_at         DATETIME         NULL,
    is_active       TINYINT(1)       NOT NULL DEFAULT 1,
    created_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_coupon_code (code),
    KEY idx_coupon_active   (is_active, starts_at, ends_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Inventory movements (stock management module)
CREATE TABLE inventory_movements (
    id              BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    product_id      BIGINT UNSIGNED  NOT NULL,
    variant_id      BIGINT UNSIGNED  NULL,
    movement_type   ENUM('in','out','adjustment','return') NOT NULL,
    quantity        INT              NOT NULL,               -- can be negative for out
    reference_type  VARCHAR(50)      NULL,                   -- 'order', 'purchase', 'manual'
    reference_id    BIGINT UNSIGNED  NULL,
    note            VARCHAR(255)     NULL,
    created_by      BIGINT UNSIGNED  NULL,
    created_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_inv_product         (product_id),
    KEY idx_inv_ref             (reference_type, reference_id),
    CONSTRAINT fk_inv_product FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Payment transactions (payment gateway integration)
CREATE TABLE payment_transactions (
    id              BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    order_id        BIGINT UNSIGNED  NOT NULL,
    gateway         VARCHAR(50)      NULL,                   -- 'razorpay','stripe','cod'
    gateway_txn_id  VARCHAR(200)     NULL,
    amount          DECIMAL(14,2)    NOT NULL,
    currency        CHAR(3)          NOT NULL DEFAULT 'INR',
    status          ENUM('initiated','success','failed','refunded','pending') NOT NULL DEFAULT 'initiated',
    payload         JSON             NULL,                   -- raw gateway response
    processed_at    DATETIME         NULL,
    created_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_pay_order           (order_id),
    KEY idx_pay_status          (status),
    KEY idx_pay_gateway_txn     (gateway_txn_id),
    CONSTRAINT fk_pay_order FOREIGN KEY (order_id) REFERENCES orders(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Shipment tracking (delivery partner integration)
CREATE TABLE shipments (
    id                 BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    order_id           BIGINT UNSIGNED  NOT NULL,
    carrier_name       VARCHAR(100)     NULL,
    tracking_number    VARCHAR(200)     NULL,
    tracking_url       VARCHAR(500)     NULL,
    shipped_at         DATETIME         NULL,
    estimated_delivery DATETIME         NULL,
    delivered_at       DATETIME         NULL,
    status             VARCHAR(60)      NOT NULL DEFAULT 'pending',
    -- Future: partner_id FK, webhook_data JSON
    created_at         DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at         DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_ship_order          (order_id),
    CONSTRAINT fk_ship_order FOREIGN KEY (order_id) REFERENCES orders(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- HELPFUL VIEWS FOR COMMON QUERIES
-- =============================================================================

-- Active products with primary image and effective price
CREATE OR REPLACE VIEW v_products_active AS
SELECT
    p.id,
    p.uuid,
    p.name,
    p.slug,
    p.base_price,
    p.sale_price,
    COALESCE(p.sale_price, p.base_price) AS effective_price,
    p.average_rating,
    p.review_count,
    p.total_sold,
    p.is_featured,
    p.is_in_stock,
    c.name     AS category_name,
    c.slug     AS category_slug,
    pi.image_url AS primary_image
FROM products p
JOIN categories c   ON c.id = p.category_id
LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
WHERE p.is_active = 1 AND p.deleted_at IS NULL;


-- Order summary per user
CREATE OR REPLACE VIEW v_user_order_summary AS
SELECT
    u.id                AS user_id,
    u.name,
    u.email,
    COUNT(o.id)         AS total_orders,
    SUM(o.total_amount) AS lifetime_value,
    MAX(o.placed_at)    AS last_order_at
FROM users u
LEFT JOIN orders o ON o.user_id = u.id
WHERE u.deleted_at IS NULL
GROUP BY u.id;


-- Latest order status per order (for tracking display)
CREATE OR REPLACE VIEW v_order_tracking AS
SELECT
    o.id            AS order_id,
    o.order_number,
    o.uuid          AS order_uuid,
    o.status        AS current_status,
    o.placed_at,
    o.shipped_at,
    o.delivered_at,
    o.cancelled_at,
    osh.note        AS latest_note,
    osh.created_at  AS status_updated_at
FROM orders o
LEFT JOIN order_status_history osh
    ON osh.id = (
        SELECT id FROM order_status_history
        WHERE order_id = o.id
        ORDER BY created_at DESC
        LIMIT 1
    );

-- =============================================================================
-- END OF SCHEMA
-- Total tables : 37  (-1 vendors table from original)
-- Total views  : 3
-- =============================================================================


-- Adds a dedicated icon column to categories.
-- Stores a Material Icons ligature name (e.g. "phone_android"), nullable —
-- when empty the app falls back to a name-based guess (see pickIcon() in the view).

ALTER TABLE categories
    ADD COLUMN icon VARCHAR(50) NULL AFTER image_url;