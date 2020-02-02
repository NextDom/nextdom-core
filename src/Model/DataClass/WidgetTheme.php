<?php

namespace NextDom\Model\DataClass;


class WidgetTheme
{
    private $icon;
    private $code;

    public function __construct(string $icon)
    {
        $this->icon = $icon;
        $iconFilename = basename($icon);
        $this->code = substr($iconFilename, 0, strpos($iconFilename, '.'));
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }
}