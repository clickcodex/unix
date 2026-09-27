<?php

namespace App\Helpers;

class SecurityHelper {
    private static function getSecretKey(): string {
        $key = getenv('APP_SECRET') ?: 'unix_ecommerce_master_security_key_2026_salt_90842';
        return hash('sha256', $key, true);
    }

    /**
     * Encrypt an integer or string database ID into a URL-safe encrypted hash string.
     */
    public static function encryptId($id): string {
        if (empty($id)) {
            return '';
        }

        $secret = self::getSecretKey();
        $cipher = "AES-256-CBC";
        $ivLength = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivLength);

        $payload = (string)$id;
        $encrypted = openssl_encrypt($payload, $cipher, $secret, OPENSSL_RAW_DATA, $iv);

        // Prepend IV for decryption and generate HMAC signature to prevent tampering
        $combined = $iv . $encrypted;
        $hmac = hash_hmac('sha256', $combined, $secret, true);
        
        $final = $hmac . $combined;

        // Base64URLSafe encoding
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($final));
    }

    /**
     * Decrypt a URL-safe encrypted hash back to the original integer ID.
     * Returns null if decryption fails or signature is invalid.
     */
    public static function decryptId(?string $hash): ?int {
        if (empty($hash)) {
            return null;
        }

        // Reconstruct Base64
        $b64 = str_replace(['-', '_'], ['+', '/'], $hash);
        $remainder = strlen($b64) % 4;
        if ($remainder) {
            $b64 .= str_repeat('=', 4 - $remainder);
        }

        $raw = base64_decode($b64, true);
        if ($raw === false) {
            return null;
        }

        $secret = self::getSecretKey();
        $cipher = "AES-256-CBC";
        $ivLength = openssl_cipher_iv_length($cipher);
        $hmacLength = 32; // sha256 raw length

        if (strlen($raw) <= ($hmacLength + $ivLength)) {
            return null;
        }

        $hmac = substr($raw, 0, $hmacLength);
        $combined = substr($raw, $hmacLength);

        // Verify HMAC signature
        $calcHmac = hash_hmac('sha256', $combined, $secret, true);
        if (!hash_equals($hmac, $calcHmac)) {
            return null; // Tampered or invalid
        }

        $iv = substr($combined, 0, $ivLength);
        $encrypted = substr($combined, $ivLength);

        $decrypted = openssl_decrypt($encrypted, $cipher, $secret, OPENSSL_RAW_DATA, $iv);
        if ($decrypted === false || !is_numeric($decrypted)) {
            return null;
        }

        return (int)$decrypted;
    }
}

// Global helper function shortcuts
if (!function_exists('encrypt_id')) {
    function encrypt_id($id): string {
        return \App\Helpers\SecurityHelper::encryptId($id);
    }
}

if (!function_exists('decrypt_id')) {
    function decrypt_id(?string $hash): ?int {
        return \App\Helpers\SecurityHelper::decryptId($hash);
    }
}
