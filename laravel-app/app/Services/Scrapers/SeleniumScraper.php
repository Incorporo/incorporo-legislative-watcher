<?php

namespace App\Services\Scrapers;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Illuminate\Support\Facades\Log;

class SeleniumScraper
{
    protected $driver;

    protected $seleniumUrl;

    protected $userAgent;

    protected $headless;

    protected $timeout = 30;

    public function __construct()
    {
        $this->seleniumUrl = config('scraper.selenium_url', 'http://localhost:4444');
        $this->userAgent = config('scraper.user_agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        $this->headless = config('scraper.selenium_headless', true);
        $this->timeout = config('scraper.timeout_seconds', 30);
    }

    /**
     * Initialize Chrome WebDriver
     */
    public function start()
    {
        try {
            $options = new ChromeOptions;

            // Configure Chrome options
            $arguments = [
                '--no-sandbox',
                '--disable-dev-shm-usage',
                '--disable-blink-features=AutomationControlled',
                '--disable-web-security',
                '--disable-features=IsolateOrigins,site-per-process',
                'user-agent='.$this->userAgent,
            ];

            if ($this->headless) {
                $arguments[] = '--headless';
                $arguments[] = '--disable-gpu';
            }

            $options->addArguments($arguments);

            // Additional preferences to appear more like a real browser
            $options->setExperimentalOption('excludeSwitches', ['enable-automation']);
            $options->setExperimentalOption('useAutomationExtension', false);

            $capabilities = DesiredCapabilities::chrome();
            $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);

            // Start ChromeDriver
            $this->driver = RemoteWebDriver::create(
                $this->seleniumUrl,
                $capabilities,
                $this->timeout * 1000,
                $this->timeout * 1000
            );

            // Set page load timeout
            $this->driver->manage()->timeouts()->pageLoadTimeout($this->timeout);

            // Execute JavaScript to remove webdriver property
            $this->driver->executeScript("Object.defineProperty(navigator, 'webdriver', {get: () => undefined})");

            Log::info('Selenium WebDriver started successfully');

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to start Selenium WebDriver: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Navigate to URL and get page source
     */
    public function getPageSource(string $url): string
    {
        if (! $this->driver) {
            $this->start();
        }

        try {
            Log::info("Selenium: Navigating to {$url}");

            $this->driver->get($url);

            // Wait for body element to be present
            $this->driver->wait($this->timeout)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::tagName('body')
                )
            );

            // Additional wait for dynamic content
            sleep(2);

            $pageSource = $this->driver->getPageSource();

            Log::info("Selenium: Successfully loaded {$url}");

            return $pageSource;
        } catch (\Exception $e) {
            Log::error("Selenium: Failed to load {$url}: ".$e->getMessage());
            throw $e;
        }
    }

    /**
     * Navigate to URL and wait for specific element
     */
    public function getPageSourceWithWait(string $url, string $cssSelector, ?int $wait = null): string
    {
        if (! $this->driver) {
            $this->start();
        }

        $wait = $wait ?? $this->timeout;

        try {
            Log::info("Selenium: Navigating to {$url} and waiting for {$cssSelector}");

            $this->driver->get($url);

            // Wait for specific element
            $this->driver->wait($wait)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector($cssSelector)
                )
            );

            $pageSource = $this->driver->getPageSource();

            Log::info("Selenium: Successfully loaded {$url} with element {$cssSelector}");

            return $pageSource;
        } catch (\Exception $e) {
            Log::error("Selenium: Failed to load {$url} with selector {$cssSelector}: ".$e->getMessage());
            throw $e;
        }
    }

    /**
     * Execute JavaScript on the page
     */
    public function executeScript(string $script, array $args = [])
    {
        if (! $this->driver) {
            throw new \Exception('WebDriver not started');
        }

        return $this->driver->executeScript($script, $args);
    }

    /**
     * Take screenshot (useful for debugging)
     */
    public function takeScreenshot(string $savePath): bool
    {
        if (! $this->driver) {
            return false;
        }

        try {
            $this->driver->takeScreenshot($savePath);
            Log::info("Screenshot saved to {$savePath}");

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to take screenshot: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Get current URL
     */
    public function getCurrentUrl(): string
    {
        if (! $this->driver) {
            return '';
        }

        return $this->driver->getCurrentURL();
    }

    /**
     * Get page title
     */
    public function getTitle(): string
    {
        if (! $this->driver) {
            return '';
        }

        return $this->driver->getTitle();
    }

    /**
     * Find element by CSS selector
     */
    public function findElement(string $cssSelector)
    {
        if (! $this->driver) {
            throw new \Exception('WebDriver not started');
        }

        return $this->driver->findElement(WebDriverBy::cssSelector($cssSelector));
    }

    /**
     * Find elements by CSS selector
     */
    public function findElements(string $cssSelector)
    {
        if (! $this->driver) {
            throw new \Exception('WebDriver not started');
        }

        return $this->driver->findElements(WebDriverBy::cssSelector($cssSelector));
    }

    /**
     * Click element
     */
    public function clickElement(string $cssSelector)
    {
        $element = $this->findElement($cssSelector);
        $element->click();
    }

    /**
     * Type into input field
     */
    public function typeIntoField(string $cssSelector, string $text)
    {
        $element = $this->findElement($cssSelector);
        $element->clear();
        $element->sendKeys($text);
    }

    /**
     * Close the browser
     */
    public function quit()
    {
        if ($this->driver) {
            try {
                $this->driver->quit();
                Log::info('Selenium WebDriver closed');
            } catch (\Exception $e) {
                Log::warning('Error closing WebDriver: '.$e->getMessage());
            }
            $this->driver = null;
        }
    }

    /**
     * Destructor to ensure driver is closed
     */
    public function __destruct()
    {
        $this->quit();
    }
}
