<?php

/**
 *      [CodeZm!] Author CodeZm[codezm@163.com].
 *
 *      对 Yaf Framework 解析.
 *      $Id: Yaf.php 2019-04-24 15:53:00 codezm $
 */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

use \YafFrameworkRbacExtract\YafFrameworkRbacExtract;

class Yaf {
    const APP_PATH = '/web/test/webservice/application';
    const PARENT_CLASS_PATHS = [
        self::APP_PATH . '/library/Core/BaseCtl.php',
        self::APP_PATH . '/library/Core/BackendCtl.php',
    ];

    public function init() {
        // Instantiation.
        $classFileParse = new YafFrameworkRbacExtract();

        // 加载即将解析类依赖的父类文件路径.
        $classFileParse->setParentClassPaths(self::PARENT_CLASS_PATHS);

        // 设置 Yaf 框架 application 路径.
        $classFileParse->setYafDetectPaths(self::APP_PATH);

        // 解析并获取解析结果.
        $result = $classFileParse->parse();

        // Print result.
        echo '解析成功结果集: ' . PHP_EOL;
        echo '<pre>'; var_dump($result); echo '</pre>';

        echo '解析失败结果集: ' . PHP_EOL;
        echo '<pre>'; var_dump($classFileParse::getErrorData()); echo '</pre>';
    }

}
(new Yaf)->init();
