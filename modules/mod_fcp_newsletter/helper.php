<?php
defined('_JEXEC') or die;

use Joomla\CMS\Captcha\Captcha;
use Joomla\CMS\Factory;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;

class ModFcpNewsletterHelper
{
	private const RATE_WINDOW = 3600; // 1 hora
	private const RATE_MAX    = 5;    // tentativas por IP/hora

	public static function ensureTable(): void
	{
		$db     = Factory::getContainer()->get(DatabaseInterface::class);
		$prefix = $db->getPrefix();
		$table  = $prefix . 'fcp_newsletter';

		$sql = "CREATE TABLE IF NOT EXISTS `{$table}` (
			`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
			`email` VARCHAR(190) NOT NULL,
			`name` VARCHAR(190) NOT NULL DEFAULT '',
			`created` DATETIME NOT NULL,
			`ip` VARCHAR(45) DEFAULT NULL,
			`user_agent` VARCHAR(255) DEFAULT NULL,
			`status` TINYINT NOT NULL DEFAULT 1,
			`source` VARCHAR(80) NOT NULL DEFAULT 'module',
			PRIMARY KEY (`id`),
			UNIQUE KEY `email` (`email`),
			KEY `status` (`status`),
			KEY `ip_created` (`ip`, `created`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

		$db->setQuery($sql)->execute();
	}

	/**
	 * com_ajax: index.php?option=com_ajax&module=fcp_newsletter&method=subscribe&format=json
	 */
	public static function subscribeAjax()
	{
		$app   = Factory::getApplication();
		$input = $app->getInput();

		if (!Session::checkToken('post') && !Session::checkToken('request')) {
			throw new \RuntimeException('Token inválido. Atualize a página e tente de novo.');
		}

		// Honeypot
		if (trim((string) $input->getString('website', '')) !== '') {
			throw new \RuntimeException('Não foi possível concluir a inscrição.');
		}

		$ip = substr((string) ($app->input->server->getString('REMOTE_ADDR', '')), 0, 45);
		self::assertRateLimit($app, $ip);

		self::assertCaptcha($input);

		$email  = strtolower(trim((string) $input->getString('email', '')));
		$name   = trim((string) $input->getString('name', ''));
		$source = preg_replace('/[^a-zA-Z0-9._-]/', '', (string) $input->getString('source', 'module')) ?: 'module';

		if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			throw new \RuntimeException('Informe um e-mail válido.');
		}

		if (strlen($email) > 190) {
			throw new \RuntimeException('E-mail muito longo.');
		}

		self::ensureTable();

		$db    = Factory::getContainer()->get(DatabaseInterface::class);
		$query = $db->getQuery(true)
			->select($db->quoteName('id'))
			->select($db->quoteName('status'))
			->from($db->quoteName('#__fcp_newsletter'))
			->where($db->quoteName('email') . ' = ' . $db->quote($email));
		$db->setQuery($query);
		$existing = $db->loadObject();

		$ua  = substr((string) ($app->input->server->getString('HTTP_USER_AGENT', '')), 0, 255);
		$now = Factory::getDate()->toSql();

		if ($existing) {
			if ((int) $existing->status === 1) {
				return 'Este e-mail já está inscrito. Obrigada!';
			}

			$query = $db->getQuery(true)
				->update($db->quoteName('#__fcp_newsletter'))
				->set($db->quoteName('status') . ' = 1')
				->set($db->quoteName('created') . ' = ' . $db->quote($now))
				->set($db->quoteName('ip') . ' = ' . $db->quote($ip))
				->set($db->quoteName('user_agent') . ' = ' . $db->quote($ua))
				->set($db->quoteName('source') . ' = ' . $db->quote($source))
				->where($db->quoteName('id') . ' = ' . (int) $existing->id);
			$db->setQuery($query)->execute();

			self::notifyAdmin($email, $name, true);

			return 'Inscrição reativada com sucesso!';
		}

		$columns = ['email', 'name', 'created', 'ip', 'user_agent', 'status', 'source'];
		$values  = [
			$db->quote($email),
			$db->quote(mb_substr($name, 0, 190)),
			$db->quote($now),
			$db->quote($ip),
			$db->quote($ua),
			1,
			$db->quote($source),
		];

		$query = $db->getQuery(true)
			->insert($db->quoteName('#__fcp_newsletter'))
			->columns($db->quoteName($columns))
			->values(implode(',', $values));
		$db->setQuery($query)->execute();

		self::notifyAdmin($email, $name, false);

		return 'Inscrição realizada com sucesso! ♥';
	}

	private static function assertCaptcha($input): void
	{
		$plugin = (string) Factory::getApplication()->getConfig()->get('captcha', '0');

		// Preferência do módulo via POST opcional; default powcaptcha se plugin ativo
		$requested = (string) $input->getCmd('captcha_plugin', '');
		if ($requested !== '') {
			$plugin = $requested;
		}

		if ($plugin === '' || $plugin === '0') {
			// Se o global estiver desligado, ainda usa powcaptcha se o plugin existir
			if (!PluginHelper::isEnabled('captcha', 'powcaptcha')) {
				return;
			}
			$plugin = 'powcaptcha';
		}

		$code = (string) $input->get('captcha', '', 'raw');

		if ($code === '') {
			// Altcha às vezes envia em outro nome
			$code = (string) $input->get('altcha', '', 'raw');
		}

		if ($code === '') {
			throw new \RuntimeException('Complete a verificação anti-spam.');
		}

		try {
			PluginHelper::importPlugin('captcha');
			$captcha = Captcha::getInstance($plugin);

			if (!$captcha->checkAnswer($code)) {
				throw new \RuntimeException('Verificação anti-spam inválida. Tente novamente.');
			}
		} catch (\RuntimeException $e) {
			throw $e;
		} catch (\Throwable $e) {
			throw new \RuntimeException('Não foi possível validar o captcha. Atualize a página.');
		}
	}

	private static function assertRateLimit($app, string $ip): void
	{
		if ($ip === '') {
			return;
		}

		$session = $app->getSession();
		$key     = 'fcp_nl_rate_' . md5($ip);
		$now     = time();
		$hits    = (array) $session->get($key, []);

		$hits = array_values(array_filter($hits, static function ($ts) use ($now) {
			return is_numeric($ts) && ($now - (int) $ts) < self::RATE_WINDOW;
		}));

		if (count($hits) >= self::RATE_MAX) {
			throw new \RuntimeException('Muitas tentativas. Aguarde um pouco e tente de novo.');
		}

		$hits[] = $now;
		$session->set($key, $hits);

		// Limite também no banco (inscrições recentes do mesmo IP)
		try {
			self::ensureTable();
			$db    = Factory::getContainer()->get(DatabaseInterface::class);
			$since = Factory::getDate('-1 hour')->toSql();
			$query = $db->getQuery(true)
				->select('COUNT(*)')
				->from($db->quoteName('#__fcp_newsletter'))
				->where($db->quoteName('ip') . ' = ' . $db->quote($ip))
				->where($db->quoteName('created') . ' >= ' . $db->quote($since));
			$db->setQuery($query);
			$count = (int) $db->loadResult();

			if ($count >= self::RATE_MAX) {
				throw new \RuntimeException('Muitas tentativas. Aguarde um pouco e tente de novo.');
			}
		} catch (\RuntimeException $e) {
			throw $e;
		} catch (\Throwable $e) {
			// ignora falha de checagem no DB
		}
	}

	private static function notifyAdmin(string $email, string $name, bool $reactivated): void
	{
		try {
			$config = Factory::getApplication()->getConfig();
			$to     = (string) $config->get('mailfrom');

			if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
				return;
			}

			$subject = $reactivated
				? '[Newsletter] Reativação: ' . $email
				: '[Newsletter] Nova inscrição: ' . $email;

			$body = "Nova inscrição na newsletter do site.\n\n"
				. 'E-mail: ' . $email . "\n"
				. 'Nome: ' . ($name !== '' ? $name : '(não informado)') . "\n"
				. 'Data: ' . Factory::getDate()->format('d/m/Y H:i:s') . "\n"
				. 'Site: ' . Uri::root() . "\n";

			$mailer = Factory::getContainer()->get(MailerFactoryInterface::class)->createMailer();
			$mailer->setSender([(string) $config->get('mailfrom'), (string) $config->get('fromname')]);
			$mailer->addRecipient($to);
			$mailer->setSubject($subject);
			$mailer->setBody($body);
			$mailer->Send();
		} catch (\Throwable $e) {
			// Não bloqueia a inscrição se o e-mail falhar
		}
	}
}
