class-file-parse-rbac
=====================
对 PHP 类文件解析，为 RBAC(角色管理) 提供 `URI - description` 数据。
#### 思想来源
项目需要做 rbac 管理，通过对 URI 分析判断用户是否有权限访问。这个组件算是个工具，可以自动帮我们提取某个 Action URI 及对应释义(前提是你得有备注信息)。
本项目主要以鸟哥的 `Yaf` 框架做解析处理的，当然其他框架、类文件也均可。

#### 如何安装本组件？
- composer 自动安装
```bash
composer require -o codezm/class-file-parse-rbac:dev-master
```

#### 如何使用本组件
项目中 `tests` 文件夹有使用示例。
1. 首先需要加载类相关的父类文件。
2. 设置解析类文件目录或者类文件。
3. `parse` 将返回解析结果。
4. 如存在相同类文件可通过 `getErrorData()` 获取未能解析的文件类，因为类文件需要加载，类文件不允许二次加载。


#### 解析结果示例:
```php
array (
    array (
        'uri' => 'default/index',
        'desc' => '首页 - index',
        'router' => array (
            'm' => '',
            'c' => 'default',
            'a' => 'index',
        ),
        'classDescription' => '首页',
        'methodDescription' => 'index',
    ),
    array (
        'uri' => 'default/test',
        'desc' => '首页 - test',
        'router' => array (
            'm' => '',
            'c' => 'default',
            'a' => 'test',
        ),
        'classDescription' => '首页',
        'methodDescription' => 'test',
    ),
    array (
        'uri' => 'backend/index/index',
        'desc' => '后台主页 - 后台主页',
        'router' => array (
            'm' => 'backend',
            'c' => 'index',
            'a' => 'index',
        ),
        'classDescription' => '后台主页',
        'methodDescription' => '后台主页',
    ),
    array (
        'uri' => 'test/backend_abc/index',
        'desc' => '测试多modules模块 - hello world',
        'router' => array (
            'm' => 'test',
            'c' => 'backend_abc',
            'a' => 'index',
        ),
        'classDescription' => '测试多modules模块',
        'methodDescription' => 'hello world',
    )
)
```

#### 解析失败结果示例
```php
array (
    array (
        'className' => 'IndexController',
        'path' => '/web/test/webservice/application/modules/Test/controllers/Index.php',
        'existed_path' => '/web/test/webservice/application/modules/Backend/controllers/Index.php',
    )
)
```

#### 默认解析类文件示例
```php
<?php

/**
 *      [CodeZm!] Author CodeZm[codezm@163.com].
 *
 *      解析示例
 *      $Id: Test.php 2019-04-25 12:38:11 codezm $
 */

class TestController extends \Core_BaseCtl {

    /**
     * 首页
     *
     */
    public function IndexAction() {

    }
}
```
打印结果:
```php
array (
    array (
        'uri' => 'test/index',
        'desc' => '解析示例 - 首页',
        'router' => array (
            'm' => '',
            'c' => 'test',
            'a' => 'index',
        ),
        'classDescription' => '解析示例',
        'methodDescription' => '首页',
    )
)
```
如果你不是使用示例类文件或者解析结果不如意，可以参考 `tests/Custom.php` 文件修改正则匹配模式。
