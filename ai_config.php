<?php
// Central AI configuration for server-side calls
// Provider: 'deepseek' (default) or 'gemini'

if (!defined('AI_PROVIDER')) {
	$envProvider = getenv('AI_PROVIDER');
	define('AI_PROVIDER', $envProvider ? strtolower($envProvider) : 'deepseek');
}

if (AI_PROVIDER === 'gemini') {
	$key = getenv('GEMINI_API_KEY');
	if (!$key) {
		$keyFile = __DIR__ . '/gemini.key';
		if (file_exists($keyFile)) {
			$key = trim(@file_get_contents($keyFile));
		}
	}
	define('AI_API_KEY', $key ?: '');
	define('AI_BASE_URL', 'https://generativelanguage.googleapis.com');
} else {
	// Default DeepSeek
	$key = getenv('DEEPSEEK_API_KEY');
	if (!$key) {
		$keyFile = __DIR__ . '/deepseek.key';
		if (file_exists($keyFile)) {
			$key = trim(@file_get_contents($keyFile));
		}
	}
	define('AI_API_KEY', $key ?: '');
	define('AI_BASE_URL', 'https://api.deepseek.com');
}
?>