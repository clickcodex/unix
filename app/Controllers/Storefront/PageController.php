<?php

namespace App\Controllers\Storefront;

class PageController {

    public function about(): void {
        $siteTitle = 'About Us — ClickCodex';
        $metaDescription = 'Learn more about ClickCodex marketplace, our story, mission, values, and quality assurance.';
        require __DIR__ . '/../../Views/front/pages/about.php';
    }

    public function contact(): void {
        $siteTitle = 'Contact & Support — ClickCodex';
        $metaDescription = 'Get in touch with our customer support team for inquiries, order updates, and seller questions.';
        require __DIR__ . '/../../Views/front/pages/contact.php';
    }

    public function contactSubmit(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        
        $name    = trim($input['name'] ?? '');
        $email   = trim($input['email'] ?? '');
        $subject = trim($input['subject'] ?? '');
        $message = trim($input['message'] ?? '');

        if (empty($name) || empty($email) || empty($message)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
            exit;
        }

        // Output success response
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Thank you for reaching out! Our support team will get back to you within 24 hours.']);
        exit;
    }

    public function terms(): void {
        $siteTitle = 'Terms & Conditions — ClickCodex';
        $metaDescription = 'Read our official terms and conditions for using ClickCodex online marketplace.';
        require __DIR__ . '/../../Views/front/pages/terms.php';
    }

    public function privacy(): void {
        $siteTitle = 'Privacy Policy — ClickCodex';
        $metaDescription = 'Learn how ClickCodex collects, stores, protects, and handles your personal data.';
        require __DIR__ . '/../../Views/front/pages/privacy.php';
    }

    public function faq(): void {
        $siteTitle = 'Frequently Asked Questions (FAQ) — ClickCodex';
        $metaDescription = 'Find answers to common questions regarding ordering, payment options, shipping, returns, and account security.';
        require __DIR__ . '/../../Views/front/pages/faq.php';
    }
}
