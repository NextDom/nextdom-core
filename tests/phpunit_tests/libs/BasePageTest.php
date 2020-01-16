<?php

use Symfony\Component\Panther\DomCrawler\Crawler;
use Symfony\Component\Panther\PantherTestCase;

class BasePageTest extends PantherTestCase
{
    const BASE_URL = 'http://localhost';

    const CONNECTION_HASH = 'VVZtg2HUxbE4XWStXTVWc2ONs0b0fXtt-test_device';

    const KILL_BETWEEN = 0;
    const KEEP_ALIVE = 1;

    /**
     * @var \Symfony\Component\Panther\Client
     */
    protected $client = null;

    /**
     * @var Crawler
     */
    protected $crawler = null;

    protected static $mode = 0;

    public static function setUpBeforeClass(): void
    {
        exec('bash tests/load_fixtures.sh --reset');
        exec('rm -fr /tmp/nextdom/cache/*');
    }

    /**
     * Go to selected page
     * @param $target
     * @param $auth
     */
    public function goTo($target, $auth = true): void
    {
        $this->client = static::createPantherClient(['external_base_uri' => self::BASE_URL]);
        if (strpos($target, 'http') === false && strpos($target, '/') !== 0) {
            $target = '/' . $target;
        }
        if ($auth) {
            if (strpos($target, '?') !== false) {
                $target .= '&auth=' . self::CONNECTION_HASH;
            }
            else {
                $target .= '?auth=' . self::CONNECTION_HASH;
            }
        }
        $this->crawler = $this->client->request('GET', $target);
        sleep(8);
    }

    /**
     * Get link <a> by url
     *
     * @param $url
     *
     * @return Crawler
     */
    protected function filterLinkUrl($url)
    {
        return $this->crawler->filterXPath('//a[@href="' . $url . '"]');
    }

    protected function checkJs()
    {
        $jsLogs = $this->client->getWebDriver()->manage()->getLog('browser');
        // Show errors help for correction
        $severeLogs = 0;
        if (count($jsLogs) > 0) {
            foreach ($jsLogs as $log) {
                if ($log['level'] === 'SEVERE') {
                    echo $log['message'] . "\n";
                    ++$severeLogs;
                }
            }
        }
        $this->assertEquals(0, $severeLogs);
    }

    /**
     * Click on invisible or covered item
     *
     * @param $cssQuery
     */
    protected function invisibleClick($cssQuery) {
        $this->client->executeScript("document.querySelector('$cssQuery').click();");
    }

    /**
     * Refresh crawler after page reload
     */
    protected function refreshCrawler() {
        $this->crawler = $this->client->refreshCrawler();
    }
}
