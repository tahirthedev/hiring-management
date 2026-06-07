<?php

function scoreAnswers(array $answers): array {
    $keywords = getKeywordMap();
    $results = [];

    foreach ($answers as $qNum => $answer) {
        $answerLower = strtolower($answer);
        $matched = [];
        $score = 0;

        if (isset($keywords[$qNum])) {
            foreach ($keywords[$qNum] as $keyword => $points) {
                // Check for keyword or close variations
                $variations = getVariations($keyword);
                foreach ($variations as $variant) {
                    if (strpos($answerLower, strtolower($variant)) !== false) {
                        $matched[] = $keyword;
                        $score += $points;
                        break;
                    }
                }
            }
        }

        // Cap each question at 20
        $score = min(20, $score);

        $results[$qNum] = [
            'score' => $score,
            'matched' => $matched
        ];
    }

    return $results;
}

function getKeywordMap(): array {
    return [
        // Q1: Plugins/tools for blog, contact form, payment gateway, user dashboard
        1 => [
            'wordpress' => 2,
            'elementor' => 2,
            'wpforms' => 2,
            'contact form 7' => 2,
            'gravity forms' => 2,
            'ninja forms' => 2,
            'woocommerce' => 3,
            'stripe' => 2,
            'paypal' => 2,
            'razorpay' => 1,
            'buddypress' => 2,
            'ultimate member' => 2,
            'wp-members' => 2,
            'memberpress' => 2,
            'yoast' => 1,
            'rank math' => 1,
            'acf' => 2,
            'advanced custom fields' => 2,
            'custom post type' => 2,
            'jetpack' => 1,
            'dashboard' => 1,
            'plugin' => 1,
        ],

        // Q2: Performance - website loads in 8 seconds
        2 => [
            'cache' => 3,
            'caching' => 3,
            'wp super cache' => 2,
            'w3 total cache' => 2,
            'litespeed' => 2,
            'hosting' => 3,
            'server' => 2,
            'database' => 3,
            'database optimization' => 3,
            'query' => 2,
            'image optimization' => 3,
            'image compress' => 3,
            'smush' => 2,
            'imagify' => 2,
            'webp' => 2,
            'lazy load' => 2,
            'cdn' => 3,
            'cloudflare' => 2,
            'pagespeed' => 3,
            'gtmetrix' => 2,
            'minif' => 3,
            'minification' => 3,
            'gzip' => 2,
            'render block' => 2,
            'defer' => 2,
            'async' => 2,
            'php version' => 2,
            'theme' => 1,
            'plugin' => 1,
        ],

        // Q3: Elementor Pro troubleshooting
        3 => [
            'plugin conflict' => 3,
            'conflict' => 2,
            'deactivat' => 2,
            'debug' => 3,
            'wp_debug' => 3,
            'debug mode' => 3,
            'php version' => 3,
            'php 7' => 2,
            'php 8' => 2,
            'memory' => 3,
            'memory limit' => 3,
            'wp_memory_limit' => 3,
            'safe mode' => 3,
            'rollback' => 3,
            'downgrade' => 2,
            'previous version' => 2,
            'error log' => 2,
            'console' => 1,
            'clear cache' => 2,
            'switch theme' => 2,
            'default theme' => 2,
            'wp-config' => 1,
            'backup' => 1,
        ],

        // Q4: Custom WooCommerce checkout
        4 => [
            'hook' => 3,
            'hooks' => 3,
            'action' => 2,
            'filter' => 3,
            'filters' => 3,
            'template override' => 3,
            'template' => 2,
            'checkout' => 1,
            'custom plugin' => 3,
            'child theme' => 3,
            'functions.php' => 2,
            'woocommerce_checkout' => 2,
            'wc_checkout' => 2,
            'override' => 2,
            'custom field' => 2,
            'css' => 1,
            'javascript' => 1,
            'jquery' => 1,
            'ajax' => 2,
            'payment' => 1,
            'gateway' => 1,
        ],

        // Q5: Most difficult project (general quality indicators)
        5 => [
            'custom theme' => 2,
            'custom plugin' => 2,
            'api' => 2,
            'rest api' => 3,
            'integration' => 2,
            'migration' => 2,
            'multisite' => 2,
            'multilingual' => 2,
            'wpml' => 2,
            'ecommerce' => 2,
            'e-commerce' => 2,
            'woocommerce' => 2,
            'security' => 2,
            'performance' => 2,
            'optimization' => 2,
            'custom post type' => 2,
            'acf' => 2,
            'database' => 2,
            'server' => 1,
            'deploy' => 2,
            'git' => 2,
            'team' => 1,
            'client' => 1,
            'deadline' => 1,
            'responsive' => 1,
        ],
    ];
}

function getVariations(string $keyword): array {
    $variations = [$keyword];

    // Add common variations
    if (strpos($keyword, ' ') !== false) {
        $variations[] = str_replace(' ', '-', $keyword);
        $variations[] = str_replace(' ', '_', $keyword);
    }

    return $variations;
}

function calculateTotalScore(array $scoreResults): int {
    $total = 0;
    foreach ($scoreResults as $result) {
        $total += $result['score'];
    }
    return $total;
}

function determineStatus(int $totalScore): string {
    if ($totalScore >= 70) return 'Shortlisted';
    if ($totalScore >= 50) return 'Manual Review';
    return 'Rejected';
}

function getAutoRejectReason(string $city, int $experience): ?string {
    if (strtolower(trim($city)) !== 'karachi') {
        return 'City is not Karachi';
    }
    if ($experience < 2) {
        return 'Less than 2 years WordPress experience';
    }
    return null;
}
