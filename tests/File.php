<?php

/**
 *      [CodeZm!] Author CodeZm[codezm@163.com].
 *
 *      对单一文件路径解析.
 *      $Id: File.php 2019-04-24 15:53:00 codezm $
 */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

use \YafFrameworkRbacExtract\YafFrameworkRbacExtract;

class File {
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

        // 设置解析单一文件路径.
        $classFileParse->setDetectPaths(self::APP_PATH . '/modules/Test/controllers/Backend/Abc.php');
        $classFileParse->setDetectPaths(self::APP_PATH . '/controllers/Default.php');
        $classFileParse->setDetectPaths(self::APP_PATH . '/modules/Backend/controllers/Index.php');

        // 解析并获取解析结果.
        $result = $classFileParse->parse();

        // Print result.
        echo '解析成功结果集: ' . PHP_EOL;
        echo '<pre>'; var_dump($result); echo '</pre>';

        echo '解析失败结果集: ' . PHP_EOL;
        echo '<pre>'; var_dump($classFileParse::getErrorData()); echo '</pre>';
    }

}
(new File)->init();
