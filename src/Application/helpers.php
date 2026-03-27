<?php
function __($key) {
    static $langData = null;

    if ($langData === null) {
        // Detect language from cookie or default to pt-br
        $lang = $_COOKIE['epiguard-lang'] ?? 'pt-br';
        
        // Prevent path traversal
        $lang = basename($lang);
        
        $langFile = __DIR__ . '/../../config/lang/' . $lang . '.php';
        
        if (file_exists($langFile)) {
            $langData = require $langFile;
        } else {
            // Fallback
            $fallbackFile = __DIR__ . '/../../config/lang/pt-br.php';
            if (file_exists($fallbackFile)) {
                $langData = require $fallbackFile;
            } else {
                $langData = [];
            }
        }
    }

    return $langData[$key] ?? $key;
}
