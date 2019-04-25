<?php

/**
 *      [CodeZm!] Author CodeZm[codezm@163.com].
 *
 *      自定义 类Doc、方法Doc、Module-name、Controller-name、Method-name 解析回调.
 *      $Id: Custom.php 2019-04-25 12:31:20 codezm $
 */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

use \YafFrameworkRbacExtract\YafFrameworkRbacExtract;

class Custom {

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
        $classFileParse->setDetectPaths(self::APP_PATH . '/controllers/Test.php');

        // 解析并获取解析结果.
        $result = $classFileParse->parse([
            'classDescFunc' => function ($doc) {
                if (!$doc) {
                    return '';
                }

                preg_match_all('/\h?\*\h+(?<classDesc>.*)(?:\n|\r\n)\h?\*\h+\$/m', $doc, $matches);
                if (isset($matches['classDesc'][0])) {
                    return rtrim(trim($matches['classDesc'][0]), '.');
                }

                return '';
            },
            'methodDescFunc' => function ($doc) {
                if (!$doc) {
                    return '';
                }

                preg_match('/\S+ (?<methodDesc>.*)/m', $doc, $matches);
                if (isset($matches['methodDesc'])) {
                    return rtrim(trim($matches['methodDesc']), '.');
                }

                return '';
            },
            'm' => function($moduleName) {
                return strtolower($moduleName);
            },
            'c' => function($className) {
                return strtolower(strtr($className, [
                    'Controller' => ''
                ]));
            },
            'a' => function($methodName) {
                return strtolower(strtr($methodName, [
                    'Action' => ''
                ]));
            }
        ]);

        // Print result.
        echo '解析成功结果集: ' . PHP_EOL;
        echo '<pre>'; var_dump($result); echo '</pre>';

        echo '解析失败结果集: ' . PHP_EOL;
        echo '<pre>'; var_dump($classFileParse::getErrorData()); echo '</pre>';
    }
}

(new Custom)->init();
