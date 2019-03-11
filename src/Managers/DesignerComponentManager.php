<?php
/**
 * Created by PhpStorm.
 * User: slobberbone
 * Date: 11/03/19
 * Time: 15:47
 */

namespace NextDom\Managers;


class DesignerComponentManager
{
    /**
     * Get all objects.
     *
     * @return String[]|null
     *
     * @throws \Exception
     */
    public static function all()
    {
        $filePath = NEXTDOM_ROOT . '/views/desktop/designer/';
        $files = glob($filePath . '{*.html.twig}', GLOB_BRACE);
        $designerComponents[] = [];
        foreach ($files as $file){
            array_push($designerComponents, str_replace(NEXTDOM_ROOT.'/views','',$file));
        }
        return $designerComponents;
    }
}