<?php
defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

/**
 * Instagram grid — baixa e cacheia thumbs localmente
 * (hotlink direto de instagram.com/p/.../media/ falha no browser).
 *
 * Busca os posts mais recentes do perfil (via mirror público) e
 * usa shortcodes manuais só como fallback.
 */
class ModFcpInstagramHelper
{
	private const CACHE_DIR = 'images/instagram';
	private const CACHE_TTL = 604800; // 7 dias (thumbs)
	private const FEED_TTL  = 21600;  // 6 horas (lista de posts)

	public static function getItems(Registry $params): array
	{
		$username = preg_replace('/[^a-zA-Z0-9._]/', '', (string) $params->get('username', 'felizcompouco'));
		$size     = in_array($params->get('image_size', 'm'), ['t', 'm', 'l'], true) ? $params->get('image_size', 'm') : 'm';
		$count    = max(1, min(24, (int) $params->get('count', 6)));
		$auto     = (int) $params->get('auto_latest', 1) === 1;

		$manual = self::parseShortcodes((string) $params->get('shortcodes', ''));
		$codes  = [];

		if ($auto && $username !== '') {
			$codes = self::fetchLatestShortcodes($username, max($count, 12));
		}

		if (!$codes) {
			$codes = $manual;
		}

		if (!$codes) {
			$codes = [
				'Da5tLVwOPcr', 'Da0-bA6mDOS', 'DaoGD2rN5Yf',
				'DajV9JMOgtK', 'DagoYhmGEve', 'DadNRBVulDE',
			];
		}

		$codes = array_slice($codes, 0, $count);
		$items = [];

		foreach ($codes as $code) {
			$thumb = self::getCachedThumb($code, $size);

			if ($thumb === '') {
				continue;
			}

			$items[] = (object) [
				'shortcode' => $code,
				'permalink' => 'https://www.instagram.com/p/' . $code . '/',
				'thumb'     => $thumb,
				'username'  => $username,
			];
		}

		return $items;
	}

	public static function parseShortcodes(string $raw): array
	{
		$raw = str_replace(["\r\n", "\r", ',', ';', ' '], "\n", $raw);
		$out = [];

		foreach (explode("\n", $raw) as $line) {
			$line = trim($line);

			if ($line === '') {
				continue;
			}

			if (preg_match('#instagram\.com/(?:p|reel)/([A-Za-z0-9_-]+)#', $line, $m)) {
				$out[] = $m[1];
			} elseif (preg_match('#^[A-Za-z0-9_-]{5,}$#', $line)) {
				$out[] = $line;
			}
		}

		return array_values(array_unique($out));
	}

	/**
	 * Busca shortcodes mais recentes do perfil e cacheia em JSON local.
	 */
	public static function fetchLatestShortcodes(string $username, int $limit = 12): array
	{
		$username = preg_replace('/[^a-zA-Z0-9._]/', '', $username);
		$limit    = max(1, min(24, $limit));

		if ($username === '') {
			return [];
		}

		$relDir  = self::CACHE_DIR;
		$absDir  = JPATH_ROOT . '/' . $relDir;
		$absFile = $absDir . '/' . $username . '_feed.json';

		if (!is_dir($absDir)) {
			@mkdir($absDir, 0755, true);
		}

		if (is_file($absFile) && (time() - filemtime($absFile)) < self::FEED_TTL) {
			$data = json_decode((string) @file_get_contents($absFile), true);

			if (!empty($data['codes']) && is_array($data['codes'])) {
				return array_slice(array_values(array_filter($data['codes'], 'is_string')), 0, $limit);
			}
		}

		$codes = self::scrapeLatestShortcodes($username);

		if ($codes) {
			@file_put_contents($absFile, json_encode([
				'username' => $username,
				'fetched'  => date('c'),
				'codes'    => $codes,
			], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
			@chmod($absFile, 0644);

			return array_slice($codes, 0, $limit);
		}

		// Se o scrape falhar, reutiliza feed cacheado mesmo expirado
		if (is_file($absFile)) {
			$data = json_decode((string) @file_get_contents($absFile), true);

			if (!empty($data['codes']) && is_array($data['codes'])) {
				return array_slice(array_values(array_filter($data['codes'], 'is_string')), 0, $limit);
			}
		}

		return [];
	}

	private static function scrapeLatestShortcodes(string $username): array
	{
		$html = self::httpGet('https://imginn.com/' . rawurlencode($username) . '/');

		if ($html === null || $html === '') {
			return [];
		}

		$codes = [];

		if (preg_match_all('#href="/(?:p|reel)/([A-Za-z0-9_-]+)/"#', $html, $m)) {
			$codes = $m[1];
		} elseif (preg_match_all('#instagram\.com/(?:p|reel)/([A-Za-z0-9_-]+)#', $html, $m)) {
			$codes = $m[1];
		}

		$codes = array_values(array_unique(array_filter($codes, static function ($c) {
			return (bool) preg_match('#^[A-Za-z0-9_-]{5,}$#', $c);
		})));

		return $codes;
	}

	/**
	 * Retorna URL pública da thumb local (ou string vazia se falhar).
	 */
	public static function getCachedThumb(string $shortcode, string $size = 'm'): string
	{
		$shortcode = preg_replace('/[^A-Za-z0-9_-]/', '', $shortcode);

		if ($shortcode === '') {
			return '';
		}

		$relDir  = self::CACHE_DIR;
		$absDir  = JPATH_ROOT . '/' . $relDir;
		$relFile = $relDir . '/' . $shortcode . '_' . $size . '.jpg';
		$absFile = JPATH_ROOT . '/' . $relFile;

		if (!is_dir($absDir)) {
			@mkdir($absDir, 0755, true);
		}

		$fresh = is_file($absFile)
			&& filesize($absFile) > 1000
			&& (time() - filemtime($absFile)) < self::CACHE_TTL;

		if (!$fresh) {
			$ok = self::downloadThumb($shortcode, $size, $absFile);

			if (!$ok && is_file($absFile) && filesize($absFile) > 1000) {
				$fresh = true;
			} elseif (!$ok) {
				return '';
			}
		}

		return rtrim(Uri::root(true), '/') . '/' . $relFile . '?v=' . (int) @filemtime($absFile);
	}

	private static function downloadThumb(string $shortcode, string $size, string $dest): bool
	{
		$url = 'https://www.instagram.com/p/' . $shortcode . '/media/?size=' . $size;

		$binary = self::httpGet($url);

		if ($binary === null || strlen($binary) < 1000) {
			return false;
		}

		$mime = '';

		if (class_exists('finfo')) {
			$finfo = new \finfo(FILEINFO_MIME_TYPE);
			$mime  = (string) $finfo->buffer($binary);
		} elseif (function_exists('getimagesizefromstring')) {
			$info = @getimagesizefromstring($binary);
			$mime = is_array($info) && !empty($info['mime']) ? $info['mime'] : '';
		}

		if ($mime !== '' && strpos($mime, 'image/') !== 0) {
			return false;
		}

		if ($mime === '') {
			$head = substr($binary, 0, 12);

			if (strpos($head, "\xFF\xD8\xFF") !== 0
				&& strpos($head, "\x89PNG") !== 0
				&& strpos($head, 'RIFF') !== 0) {
				return false;
			}
		}

		$tmp = $dest . '.tmp';

		if (@file_put_contents($tmp, $binary) === false) {
			return false;
		}

		@rename($tmp, $dest);
		@chmod($dest, 0644);

		return is_file($dest) && filesize($dest) > 1000;
	}

	private static function httpGet(string $url): ?string
	{
		if (function_exists('curl_init')) {
			$ch = curl_init($url);
			curl_setopt_array($ch, [
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_MAXREDIRS      => 5,
				CURLOPT_TIMEOUT        => 20,
				CURLOPT_CONNECTTIMEOUT => 8,
				CURLOPT_SSL_VERIFYPEER => true,
				CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
				CURLOPT_HTTPHEADER     => [
					'Accept: text/html,image/jpeg,image/*,*/*;q=0.8',
					'Accept-Language: en-US,en;q=0.9',
				],
			]);
			$body = curl_exec($ch);
			$code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);

			if ($body !== false && $code >= 200 && $code < 400) {
				return $body;
			}

			return null;
		}

		$ctx = stream_context_create([
			'http' => [
				'timeout'         => 20,
				'follow_location' => 1,
				'user_agent'      => 'Mozilla/5.0 (compatible; FCPInstagram/1.1)',
				'header'          => "Accept: text/html,image/jpeg,image/*,*/*;q=0.8\r\n",
			],
			'ssl' => [
				'verify_peer'      => true,
				'verify_peer_name' => true,
			],
		]);

		$body = @file_get_contents($url, false, $ctx);

		return $body !== false ? $body : null;
	}
}
