<?php

abstract class BaseControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test unused data in page data
     *
     * @param string $twigFile Path of the twig file
     * @param array $pageData Page data after show
     */
    public function pageDataVars($twigFile, $pageData, $ignoreSpecials = [])
    {
        $pageDataKeys = array_keys($pageData);
        $twigFileContent = file_get_contents(__DIR__ . '/../../../views/' . $twigFile);
        foreach ($pageDataKeys as $key) {
            if (strpos($key, 'JS_') === false && strpos($key, 'CSS_') === false && $key !== 'TITLE' && !in_array($key, $ignoreSpecials)) {
                $this->assertContains($key, $twigFileContent);
            }
        }
    }
}