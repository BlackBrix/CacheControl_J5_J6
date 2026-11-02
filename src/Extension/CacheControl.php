<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.CacheControl
 * @license     GNU General Public License version 2 or later
 */
namespace BlackBrix\Plugin\System\CacheControl\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

final class CacheControl extends CMSPlugin implements SubscriberInterface
{
    private bool $matched = false;
    private $originalCaching = null;
    private bool $logRegistered = false;
    /** One identifier shared by all Cache Control log messages in this PHP request. */
    private static ?string $requestId = null;

    public static function getSubscribedEvents(): array
    {
        return [
            'onAfterRoute' => 'onAfterRoute',
            'onAfterDispatch' => 'onAfterDispatch',
        ];
    }

    public function onAfterRoute(Event $event): void
    {
        $app = $this->getApplication();

        if ($app === null || !$app->isClient('site')) {
            return;
        }

        $definitions = preg_split('/\R/u', (string) $this->params->get('definitions', ''));
        if ($definitions === false) {
            return;
        }

        foreach ($definitions as $lineNumber => $line) {
            $rule = trim($line);
            if ($rule === '' || str_starts_with($rule, '#') || str_starts_with($rule, ';')) {
                continue;
            }

            $result = [];
            parse_str($rule, $result);
            if ($result === []) {
                continue;
            }

            if (!$this->ruleMatches($result)) {
                continue;
            }

            $this->matched = true;
            $this->originalCaching = $app->get('caching');
            $app->set('caching', 0);
            $this->debug('Rule ' . ($lineNumber + 1) . ' matched: ' . $rule . '; caching disabled.');
            return;
        }

        $input = $app->getInput();
        $actualId = $input->get('id', null, 'RAW');
        $idDescription = is_scalar($actualId) ? (string) $actualId : '(missing or non-scalar)';
        $this->debug('No matching rule; caching left unchanged. Routed parameters: option='
            . (string) $input->get('option', '', 'CMD')
            . ', view=' . (string) $input->get('view', '', 'CMD')
            . ', id=' . $idDescription
            . ', Itemid=' . (string) $input->getInt('Itemid', 0) . '.');
    }

    public function onAfterDispatch(Event $event): void
    {
        if (!$this->matched || (int) $this->params->get('reenable_afterdispatch', 0) !== 1) {
            return;
        }

        $app = $this->getApplication();
        if ($app === null || !$app->isClient('site')) {
            return;
        }

        $app->set('caching', $this->originalCaching);
        $this->debug('Original caching configuration restored after dispatch.');
    }

    /** Only match simple scalar parameters, as used in the original rule syntax. */
    private function ruleMatches(array $conditions): bool
    {
        $input = $this->getApplication()->getInput();

        foreach ($conditions as $key => $expected) {
            if (!is_string($key) || !preg_match('/^[A-Za-z][A-Za-z0-9_\-]*$/D', $key) || !is_string($expected)) {
                return false;
            }

            $actual = $input->get($key, null, 'RAW');
            if ($actual === null || !is_scalar($actual)) {
                return false;
            }

            if ($expected === '?') {
                continue;
            }

            // Joomla may encode an article ID as "123:article-alias" when SEF is off.
            // Restrict this normalization to explicitly scoped com_content article rules.
            if ($key === 'id'
                && ($conditions['option'] ?? null) === 'com_content'
                && ($conditions['view'] ?? null) === 'article'
                && preg_match('/^[0-9]+$/D', $expected)
                && preg_match('/^([0-9]+):[^:]+$/D', (string) $actual, $matches)
                && $matches[1] === $expected
            ) {
                continue;
            }

            if ((string) $actual !== $expected) {
                return false;
            }
        }

        return true;
    }

    /** Logging must be explicitly enabled AND Joomla's system debug mode enabled. */
    private function debug(string $message): void
    {
        $app = $this->getApplication();
        if (!$app || !(int) $this->params->get('debug_logging', 0) || !(bool) $app->get('debug', false)) {
            return;
        }

        if (!$this->logRegistered) {
            Log::addLogger(
                ['text_file' => 'plg_system_cachecontrol.php'],
                Log::DEBUG,
                ['plg_system_cachecontrol']
            );
            $this->logRegistered = true;
        }

        if (self::$requestId === null) {
            self::$requestId = bin2hex(random_bytes(6));
        }

        Log::add('[req:' . self::$requestId . '] ' . $message, Log::DEBUG, 'plg_system_cachecontrol');
    }
}
