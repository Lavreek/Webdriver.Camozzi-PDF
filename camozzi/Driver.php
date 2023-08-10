<?php

namespace Camozzi\Control;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class Driver
{
    const timeout = 600;

    private RemoteWebDriver $driver;

    public function __construct(RemoteWebDriver $driver = null)
    {
        if ($driver) {
            $this->driver = $driver;

        } else {
            $this->driver = RemoteWebDriver::create(
                "http://localhost:9999",
                $this->setCapabilities(),
                self::timeout * 1000,
                self::timeout * 1000
            );
        }

    }

    public function closeDriver()
    {
        $this->driver->close();
    }

    private function setCapabilities()
    {
        $chromeOptions = new ChromeOptions();
        $chromeOptions->addArguments([
            "user-data-dir=" . ROOT . '/resources/.cookie',
            'start-maximized',
            'disable-infobars',
            '--disable-extensions',
            '--disable-gpu',
            '--disable-dev-shm-usage',
            '--no-sandbox',
            '--no-proxy-server',
        ]);

        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);

        return $capabilities;
    }

    public function getDriver()
    {
        return $this->driver;
    }

    public function getSelection(string $selectionType, string $selectionValue)
    {
        if (empty($selectionType) or empty($selectionValue)) {
            return null;
        }
        switch ($selectionType) {
            case 'css' : {
                return WebDriverBy::cssSelector(".$selectionValue");
            }

            case 'id' : {
                return WebDriverBy::id($selectionValue);
            }

            case 'tagName' : {
                return WebDriverBy::tagName($selectionValue);
            }

            case 'className' : {
                return WebDriverBy::className($selectionValue);
            }

            default : {
                return null;
            }
        }
    }

    public function getElements(bool $multi = false, string $selectionType, string $selectionValue, $element = null)
    {
        if (!$element) {
            $element = $this->driver;
        }

        $selection = $this->getSelection($selectionType, $selectionValue);

        if ($multi) {
            try {
                return $element->findElements($selection);

            } catch (\Exception $exception) {
                return null;
            }
        }

        try {
            return $element->findElement($selection);

        } catch (\Exception $exception) {
            return null;
        }
    }

    public function fillInput(string $selection, string $selector, string $value) : void
    {
        $input = $this->getElements(false, $selection, $selector);
        $input->clear()->sendKeys($value);
    }

    public function clickButton(string $selection, string $selector) : void
    {
        $button = $this->getElements(false, $selection, $selector);
        $button->click();
    }

    public function click(string $selection, string $selector) : void
    {
        $element = $this->getElements(false, $selection, $selector);

        try {
            $element->click();
        } catch (Exception $exception) {
            echo "\n Element can't be clickable. \n";
        }
    }


    public function moveTo(string $url = "") : void
    {
        $this->driver->get($url);
    }

    public function wait($selection = "css", $selector = "qwertyuiop", $time = 60) : void
    {
        $selection = $this->getSelection($selection, $selector);

        try {
            $this->getDriver()->wait($time, $time * 1000)->until(
                WebDriverExpectedCondition::elementToBeClickable($selection)
            );
        } catch (\Exception $exception) {
            echo "\n Function \"Wait\" complete. $time seconds past. \n";
        }
    }
}
