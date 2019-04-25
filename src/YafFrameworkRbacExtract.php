<?php
declare(strict_types = 1);

/**
 *      [CodeZm!] Author CodeZm[codezm@163.com].
 *
 *      解析所有 yaf controller, 获取 controller name、method name.
 *      $Id: parseYafController.php 2019-04-19 09:56:40 codezm $
 */

namespace YafFrameworkRbacExtract;

class YafFrameworkRbacExtract {

    const DEBUG = false;
    private $detectPaths = [];
    private $isYafFramework = false;
    private $controllerSuffix = 'Controller';
    private $methodSuffix = 'Action';
    private $connector = DIRECTORY_SEPARATOR;
    // The Successful Parse Class Save Data.
    private $data = [];
    // The Error Data.
    private static $error = [];
    // The Class Path.
    private $class = [];
    // The Default Ignore Files.
    private $ignoreFiles = [
        '.' => '.',
        '..' => '..',
        '.DS_Store' => '.DS_Store',
    ];

    /**
     * Set Detect Children Class for Parent Class.
     *
     */
    public function setParentClassPaths(array $parentClassPaths) {
        foreach ($parentClassPaths as $parentsClassPathItem) {
            require_once $parentsClassPathItem;

            $this->class[pathinfo($parentsClassPathItem, PATHINFO_FILENAME)] = $parentsClassPathItem;
        }

        unset($parentsClassPathItem);
    }

    /**
     * Set Connector on mudules、controller and action.
     *
     */
    public function setConnector(string $connector) {
        $this->connector = $connector;
    }

    /**
     * Set Detect path with Yaf framework.
     *
     */
    public function setYafDetectPaths(string $detectPath) {
        $this->detectPaths[] = $detectPath . '/controllers';
        $this->detectPaths[] = $detectPath . '/modules';
        $this->isYafFramework = true;
    }

    /**
     * Set Detect path.
     *
     */
    public function setDetectPaths(string $detectPath) {
        $this->detectPaths[] = $detectPath;
    }

    /**
     * Parse Directory.
     *
     */
    public function parseDir($detectPathItem, $callbackFunc) {
        // 递归遍历目录里面的文件
        $dirIterator = new \RecursiveDirectoryIterator($detectPathItem);
        $dirIterator = new \RecursiveIteratorIterator($dirIterator);

        $moduleName = '';
        while($dirIterator->valid()) {
            $multiModuleName = '';
            // Ignore file.
            if (isset($this->ignoreFiles[$dirIterator->getFilename()]) || pathinfo($dirIterator->getFilename(), PATHINFO_EXTENSION) != 'php') {
                $this->log('[IGNORE] file、not-php-file: ' . $dirIterator->getFilename());
                $dirIterator->next();
                continue;
            }

            // Get module name.
            if (basename($detectPathItem) == 'modules') {
                [$moduleName, $multiModuleName] = explode('/controllers', $dirIterator->getSubPath());

                if (!empty($multiModuleName)) {
                    $multiModuleName =  strtr(ltrim($multiModuleName, '/'), '/', '_') . '_';
                }

                // Print log.
                $this->log('module name: ' . $moduleName . ', getSubPath: ' . $dirIterator->getSubPath() . ', getFilename: ' . $dirIterator->getFilename() . ', getBasename: ' . $dirIterator->getBasename());
            }

            // Get class name.
            $className = $multiModuleName . $dirIterator->getBasename('.php'). $this->controllerSuffix;

            // Check class existed.
            if (!$this->parseFile($dirIterator->current(), $callbackFunc, $className, $moduleName)) {
                $dirIterator->next();
                continue;
            }

            $dirIterator->next();
        }

        $dirIterator->rewind();
    }

    /**
     * Parse Single file.
     *
     */
    public function parseFile($filePath, array $callbackFunc, string $className = '', string $moduleName = '') : bool {
        if (empty($className)) {
            $multiModuleName = '';
            $filePathInfo = pathinfo($filePath);
            $className = $filePathInfo['filename'] . $this->controllerSuffix;
            // Get module name.
            if (strpos($filePath, 'modules') !== false) {
                [$moduleName, $multiModuleName] = explode('controllers', $filePathInfo['dirname']);
                preg_match('/modules\/(?<moduleName>.*?)\//i', $moduleName, $matche);

                $moduleName = $matche['moduleName'] ?? '';
                if (!empty($multiModuleName)) {
                    $multiModuleName =  strtr(ltrim($multiModuleName, '/'), '/', '_') . '_';
                }
            }

            $className = $multiModuleName . $className;
        }

        // Check Class Existed.
        if (isset($this->class[$className])) {
            $this->log('[ERROR] Class ' . $className . ' Existed, the path is: ' . $filePath . ', the same include class path is: ' . $this->class[$className]);

            // Save error data.
            self::$error[] = [
                'className' => $className,
                'path' => (string) $filePath,
                'existed_path' => (string) $this->class[$className]
            ];

            return false;
        } else {
            $this->class[$className] = $filePath;
        }

        // OnLoad Class file.
        require_once $filePath;

        // Reflection class.
        $classReflector = new \ReflectionClass($className);
        foreach ($classReflector->getMethods() as $classMethodItem) {
            if ($classMethodItem->class == ($className) && strpos($classMethodItem->name, $this->methodSuffix) !== false) {
                $classDescription = isset($callbackFunc['classDescFunc']) ? $callbackFunc['classDescFunc']($classReflector->getDocComment()) : $this->getClassDescription($classReflector->getDocComment());
                $methodDescription = isset($callbackFunc['methodDescFunc']) ? $callbackFunc['methodDescFunc']($classReflector->getMethod($classMethodItem->name)->getDocComment()) : $this->getMethodDescription($classReflector->getMethod($classMethodItem->name)->getDocComment());
                $uri = [
                    'm' => isset($callbackFunc['m']) ? $callbackFunc['m']($moduleName) : $this->getModuleName($moduleName),
                    'c' => isset($callbackFunc['c']) ? $callbackFunc['c']($classReflector->getName()) : $this->getClassName($classReflector->getName()),
                    'a' => isset($callbackFunc['a']) ? $callbackFunc['a']($classMethodItem->name) : $this->getMethodName($classMethodItem->name),
                ];

                // Print log.
                $this->log(implode($this->connector, array_filter($uri)) . ' [' . $classDescription . ' - ' . $methodDescription . ']');

                // Set save data.
                $this->setData([
                    'uri' => implode($this->connector, array_filter($uri)),
                    'desc' => $classDescription . ' - ' . $methodDescription,
                    'router' => $uri,
                    'classDescription' => $classDescription,
                    'methodDescription' => $methodDescription
                ]);
            }
        }

        return true;
    }

    /**
     * Parse path
     *
     * @return array result data.
     */
    public function parse(array $callbackFunc = []) : array {
        // Directory traversal.
        foreach ($this->detectPaths as $detectPathItem) {
            if (is_dir($detectPathItem)) {
                $this->parseDir($detectPathItem, $callbackFunc);
                continue;
            }

            $this->parseFile($detectPathItem, $callbackFunc);
        }

        return $this->getData();
    }

    /**
     * Get error data.
     *
     */
    public static function getErrorData() {
        return self::$error;
    }

    /**
     * Get module name.
     *
     * @return string.
     */
    public static function getModuleName(string $moduleName) : string {
        return strtolower($moduleName);
    }

    /**
     * Get Class Name.
     *
     * @return string.
     */
    public static function getClassName(string $className) : string {
        return strtolower(strtr($className, [
            'Controller' => ''
        ]));
    }

    /**
     * Get Method Name.
     *
     * @return string.
     */
    public static function getMethodName(string $methodName) : string {
        return strtolower(strtr($methodName, [
            'Action' => ''
        ]));
    }

    /**
     * Set parse result sub data.
     *
     */
    public function setData(array $data) {
        $this->data[] = $data;
    }

    /**
     * Get parse result data.
     *
     * @return array.
     */
    public function getData() : array {
        return $this->data;
    }

    /**
     * Print log message.
     *
     */
    public function log(string $msg, bool $display = false) {
        if (self::DEBUG || $display) {
            echo '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
        }
    }

    /**
     * Get Method Doc Comment
     *
     * @return string.
     */
    public function getMethodDescription($doc) : string {
        if (!$doc) {
            return '';
        }

        preg_match('/\S+ (?<methodDesc>.*)/m', $doc, $matches);
        if (isset($matches['methodDesc'])) {
            return trim($matches['methodDesc']);
        }

        return '';
    }

    /**
     * Get Class Doc Comment
     *
     * @return string.
     */
    public function getClassDescription($doc) : string {
        if (!$doc) {
            return '';
        }

        preg_match_all('/\h?\*\h+(?<classDesc>.*)(?:\n|\r\n)\h?\*\h+\$/m', $doc, $matches);
        if (isset($matches['classDesc'][0])) {
            return trim($matches['classDesc'][0]);
        }

        return '';
    }

}
