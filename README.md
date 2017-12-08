# 音乐搜索器 - 麦葱特制多站合一音乐搜索解决方案

[![PHP version](https://img.shields.io/badge/php-%3E%205.4-orange.svg)](https://github.com/php-src/php)
[![jQuery](https://img.shields.io/badge/jquery-1.11.1-blue.svg)](https://github.com/jquery/jquery)
[![Amazeui](https://img.shields.io/badge/amazeui-2.3.0-blue.svg)](https://github.com/amazeui/amazeui)
[![APlayer](https://img.shields.io/badge/aplayer-1.6.0-blue.svg)](https://github.com/MoePlayer/APlayer)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](#LICENSE)
[![Maicong's Blog](https://img.shields.io/badge/blog-maicong.me-green.svg)](https://maicong.me/)

> **(๑•̀ㅂ•́)و✧ 开源项目，请勿商用 🚫**

目前支持搜索试听以下网站音乐：

> [网易云音乐](http://music.163.com/)
> [QQ音乐](http://y.qq.com/)
> [酷狗音乐](http://www.kugou.com/)
> [酷我音乐](http://www.kuwo.cn/)
> [虾米音乐](http://www.xiami.com/)
> [百度音乐](http://music.baidu.com/)
> [一听音乐](http://www.1ting.com/)
> [咪咕音乐](http://music.migu.cn/)
> [荔枝FM](http://www.lizhi.fm/)
> [蜻蜓FM](http://www.qingting.fm/)
> [喜马拉雅FM](http://www.ximalaya.com/)
> [5sing音乐](http://5sing.kugou.com/)
> [SoundCloud](https://soundcloud.com/)

数据调用的是各音频网站 JSON 接口！

问我怎么来的？噗，抓包发现的。。。

有的接口并不是开放的 API 接口，随时可能失效，所以本项目相关代码仅供参考。

[📦 下载当前开发代码](https://github.com/maicong/music/archive/master.zip)

[📦 下载发布版本代码](https://github.com/maicong/music/releases)

## Demo / 演示

[🔗 http://music.2333.me/](http://music.2333.me/ "音乐搜索器")

如果获取失败什么的，可以到 [我的博客](https://maicong.me/msg) 留言告诉我。

## Changelog / 更新日志

-   2017.12.08 `v1.5.1` 优化代码、更新说明
-   2017.12.08 `v1.5.0` 更换播放器、增加歌词显示、更新接口

<details>
<summary>查看更多</summary>

-   2017.12.05 `v1.4.5` 修复网易云音乐音频数据不对应问题
-   2017.12.05 `v1.4.4` 优化网易云音乐、虾米音乐、百度音乐接口
-   2017.12.05 `v1.4.3` 优化酷狗音乐接口，支持 320k 音频
-   2017.12.04 `v1.4.2` 优化代码
-   2017.12.04 `v1.4.1` 优化 QQ 音乐品质
-   2017.11.28 `v1.4.0` 优化接口和代码、优化 Curl 模块加载方式
-   2017.09.12 `v1.3.0` 更新 QQ 音乐 API 接口
-   2017.09.08 `v1.2.9` 优化模版代码，更新说明
-   2017.09.06 `v1.2.8` 更新 5sing 接口，优化代码
-   2017.09.04 `v1.2.7` 修复低版本提示显示编码问题
-   2017.08.03 `v1.2.6` 更新页脚和注释
-   2017.08.03 `v1.2.6` 增加低版本提示，优化 蜻蜓 FM 的 songid 代码
-   2017.08.01 `v1.2.5` 增加对 喜马拉雅 FM 的支持，修复 url 无法获取问题
-   2017.07.26 `v1.2.4` 优化代码兼容性
-   2017.07.24 `v1.2.3` 优化目录结构和模版
-   2017.07.20 `v1.2.2` 优化回调代码
-   2017.07.20 `v1.2.1` 更新正则匹配规则
-   2017.07.19 `v1.2.0` 修复正则表达式问题
-   2017.07.19 `v1.1.9` 增加对蜻蜓 FM 的支持 (resolve [#6](https://github.com/maicong/music/issues/6))
-   2017.07.10 `v1.1.8` 修复 api 请求接口问题
-   2017.07.05 `v1.1.7` 增加对 荔枝 FM 的支持
-   2017.06.26 `v1.1.6` 修复数组写法兼容性
-   2017.05.19 `v1.1.5` 修复 网易云音乐 音乐链接失效问题
-   2017.04.28 `v1.1.4` 更新 QQ 音乐 API 接口，优化代码
-   2017.04.21 `v1.1.3` 优化代码和播放器视觉
-   2017.04.20 `v1.1.2` 更新音乐地址匹配规则
-   2017.03.24 `v1.1.1` 移除对天天动听的支持，修复无法获取咪咕音乐的问题，更新 SoundCloud client_id
-   2017.03.23 `v1.1.0` 更新外链资源地址，优化代码
-   2015.06.15 `v1.0.4` 增加对 SoundCloud 的支持，增加代理支持，修复音乐名称识别问题，优化代码
-   2015.06.13 `v1.0.3` 增加对 天天动听、咪咕 的支持
-   2015.06.12 `v1.0.2` 增加对 5sing 的支持 (开源发布)
-   2015.06.12 `v1.0.1` 代码优化 + BUG修复
-   2015.06.10 `v1.0.0` 音乐搜索器上线

</details>

## Help / 数据获取失败解决方案

方案1：

```
修改 index.php 文件里的 MC_PROXY 为您的代理地址
将 core/music.php 里需要代理的 URL 'proxy' => false 改为 'proxy' => true
```

方案2：

```
在 music.php 里查找 setTimeout，将其后面的数值 20 改为更大。
在 static/music.js 里查找 data: post_data，将其上面的数值 30000 改为更大。
```

方案3：

```
服务器要支持 curl。
更换服务器，选择延迟更低的服务器。
```

## Disclaimer / 免责声明

```
1、本站音频文件来自各网站接口，本站不会修改任何音频文件
2、音频版权来自各网站，本站只提供数据查询服务，不提供任何音频存储和贩卖服务
```

## License / 开源协议

```
The MIT License (MIT)

Copyright (c) 2017 Maicong

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```
