<?php
/**
 * 
 * 音乐搜索器 - 入口
 * 
 * @author     MaiCong <i@maicong.me>
 * @date  2015-06-13 23:28:19
 * @version    1.0.3
 *
 */

define('MC_CORE', true);

require_once( dirname( __FILE__ ).'/music.php' );

if (ajax_post('music_input') && ajax_post('music_filter')) {
    $music_input = ajax_post('music_input');
    $music_filter = ajax_post('music_filter');
    $music_type = ajax_post('music_type');
    $music_type_allow = array('163', '1ting', 'baidu', 'kugou', 'kuwo', 'qq', 'xiami', '5sing', 'ttpod', 'migu');
    $music_name = null;
    $music_id = null;
    $music_url = null;
    switch ($music_filter) {
        case 'name':
            $music_valid = preg_match('/^[\w\x{4e00}-\x{9fa5}\-\'\’\s]+$/isu', $music_input);
            $music_name = $music_input;
            $music_type_valid = in_array($music_type, $music_type_allow);
            break;
        case 'id':
            $music_valid = preg_match('/^[\w\/]+$/is', $music_input);
            $music_type_valid = in_array($music_type, $music_type_allow);
            $music_id = $music_input;
            break;
        case 'url':
            $music_valid = preg_match('/^(http|https|ftp):\/\/{1}([\S]+)$/is', $music_input);
            $music_type_valid = true;
            $music_url = $music_input;
            break;
        default:
            $music_valid = false;
            break;
    }
    if ($music_valid && $music_type_valid) {
        if ($music_name !== null) {
            $music_name = htmlspecialchars($music_name, ENT_QUOTES, 'UTF-8');
            $music_response = maicong_get_song_by_name($music_name, $music_type);
        }
        if ($music_id !== null) {
            $music_id = htmlspecialchars($music_id, ENT_QUOTES, 'UTF-8');
            $music_response = maicong_get_song_by_id($music_id, $music_type);
        }
        if ($music_url !== null) {
            $music_response = maicong_get_song_by_url($music_url);
        }
        if (!empty($music_response)) {
            $reinfo = array('status' => '200', 'msg' => '', 'data' => $music_response);
        } else {
            $reinfo = array('status' => '0', 'msg' => 'ㄟ( ▔, ▔ )ㄏ，没有找到相关信息');
        }
    } else {
        $reinfo = array('status' => '0', 'msg' => '(・-・*)，请检查您的输入是否正确');
    }
    header('Content-type:text/json');
    echo json_encode($reinfo);
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>音乐搜索器|网易一听百度酷狗酷我QQ虾米5sing音乐搜索|音乐在线试听 - by 麦田一根葱</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Cache-Control" content="no-transform">
    <meta http-equiv="Cache-Control" content="no-siteapp">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="author" content="Maicong.me">
    <meta name="keywords" content="音乐搜索,网易云音乐,一听音乐,百度音乐,酷狗音乐网页版,酷我音乐盒,QQ音乐网页版,虾米音乐,5sing原创音乐,音乐在线试听">
    <meta name="description" content="麦葱特制网易一听百度酷狗酷我QQ虾米5sing音乐搜索解决方案。">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="音乐搜索器">
    <meta name="application-name" content="音乐搜索器">
    <meta name="format-detection" content="telephone=no">
    <link rel="shortcut icon" href="static/favicon.ico">
    <link rel="apple-touch-icon" href="static/apple-touch-icon.png">
    <link rel="canonical" href="http://music.2333.me/">
    <link rel="stylesheet" href="http://cdn.amazeui.org/amazeui/2.3.0/css/amazeui.min.css">
    <link rel="stylesheet" href="static/style.css">
</head>
<body>
    <header class="am-topbar am-topbar-fixed-top">
        <div class="am-container">
            <h1 class="am-topbar-brand">
                <a href="/">2333 实验室</a>
            </h1>
            <button class="am-topbar-btn am-topbar-toggle am-btn am-btn-sm am-btn-secondary am-show-sm-only" data-am-collapse="{target: '#collapse-head'}">
                <span class="am-sr-only">导航切换</span>
                <span class="am-icon-bars"></span>
            </button>
            <nav class="am-collapse am-topbar-collapse am-fr" id="collapse-head">
                <ul class="am-nav am-nav-pills am-topbar-nav">
                    <li><a href="http://img.2333.me">图片反盗链</a></li>
                    <li><a href="http://crx.2333.me">CRX下载</a></li>
                    <li class="am-active"><a href="http://music.2333.me">音乐搜索器</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <section class="am-g about">
        <div class="am-container am-margin-vertical-xl">
            <header class="am-padding-vertical">
                <h2 class="about-title about-color">音乐搜索器</h2>
                <p class="am-text-center">麦葱特制音乐搜索解决方案</p>
            </header>
            <hr>
            <div class="am-u-lg-12 am-padding-vertical">
                <form class="am-form am-margin-bottom-lg" method="post" id="form-vld">
                    <div class="am-u-md-12 am-u-sm-centered">
                        <ul id="form-tabs" class="am-nav am-nav-pills am-nav-justify am-margin-bottom music-tabs">
                            <li class="am-active" data-filter="name"><a>音乐名称</a></li>
                            <li data-filter="id"><a>音乐ID</a></li>
                            <li data-filter="url"><a>音乐地址</a></li>
                        </ul>
                        <div class="am-form-group">
                            <input type="text" id="music_input" data-filter="name" class="am-form-field am-input-lg am-text-center am-radius" minlength="1" placeholder="例如: 不要说话 陈奕迅" data-am-loading="{loadingText: ' '}" pattern="^[\w\u4e00-\u9fa5\s\-\'\’]+$" required>
                            <div class="am-alert am-alert-danger am-animation-shake"></div>
                        </div>
                        <div class="am-form-group am-text-center music-type">
                            <label class="am-radio-inline">
                                <input type="radio" name="music_type" value="163" data-am-ucheck checked> 网易
                            </label>
                            <label class="am-radio-inline">
                                <input type="radio" name="music_type" value="1ting" data-am-ucheck> 一听
                            </label>
                            <label class="am-radio-inline">
                                <input type="radio" name="music_type" value="baidu" data-am-ucheck> 百度
                            </label>
                            <label class="am-radio-inline">
                                <input type="radio" name="music_type" value="kugou" data-am-ucheck> 酷狗
                            </label>
                            <label class="am-radio-inline">
                                <input type="radio" name="music_type" value="kuwo" data-am-ucheck> 酷我
                            </label>
                            <label class="am-radio-inline">
                                <input type="radio" name="music_type" value="qq" data-am-ucheck> ＱＱ
                            </label>
                            <label class="am-radio-inline">
                                <input type="radio" name="music_type" value="xiami" data-am-ucheck> 虾米
                            </label>
                            <label class="am-radio-inline">
                                <input type="radio" name="music_type" value="5sing" data-am-ucheck> 5sing
                            </label>
                            <label class="am-radio-inline">
                                <input type="radio" name="music_type" value="ttpod" data-am-ucheck> 天天动听
                            </label>
                            <label class="am-radio-inline">
                                <input type="radio" name="music_type" value="migu" data-am-ucheck> 咪咕
                            </label>
                        </div>
                        <button type="submit" id="submit" class="am-btn am-btn-primary am-btn-lg am-btn-block am-radius" data-am-loading="{spinner: 'cog', loadingText: '正在搜索相关音乐...', resetText: 'Get &#x221A;'}">Get &#x221A;</button>
                    </div>
                </form>
                <form class="am-form am-u-md-12 am-u-sm-centered music-main">
                    <a type="button" id="getit" class="am-btn am-btn-success am-btn-lg am-btn-block am-radius am-margin-bottom-lg">成功 Get &#x221A; 返回继续 <i class="am-icon-reply am-icon-fw"></i></a>
                    <div class="am-input-group am-input-group-sm am-margin-bottom-sm" data-am-popover="{content: '音乐链接', trigger: 'hover'}">
                        <span class="am-input-group-label"><i class="am-icon-music am-icon-fw"></i></span>
                        <input type="text" id="music-src" class="am-form-field">
                    </div>
                    <div class="am-g">
                        <div class="am-u-lg-6">
                            <div class="am-input-group am-input-group-sm am-margin-bottom-sm" data-am-popover="{content: '音乐名称', trigger: 'hover'}">
                                <span class="am-input-group-label"><i class="am-icon-tag am-icon-fw"></i></span>
                                <input type="text" id="music-name" class="am-form-field">
                            </div>
                        </div>
                        <div class="am-u-lg-6">
                            <div class="am-input-group am-input-group-sm am-margin-bottom-sm" data-am-popover="{content: '音乐作者', trigger: 'hover'}">
                                <span class="am-input-group-label"><i class="am-icon-user am-icon-fw"></i></span>
                                <input type="text" id="music-author" class="am-form-field">
                            </div>
                        </div>
                    </div>
                    <div id="music-show" class="am-margin-vertical"></div>
                </form>
                <div class="am-u-md-12 am-u-sm-centered am-margin-vertical music-tips">
                    <h4>帮助：</h4>
                    <p><b>标红</b> 为 <strong>音乐ID</strong>，<u>下划线</u> 表示 <strong>音乐地址</strong></p>
                    <p><span>网易：</span><u>http://music.163.com/#/song?id=<b>25906124</b></u></p>
                    <p><span>一听：</span><u>http://www.1ting.com/player/b6/player_<b>220513</b>.html</u></p>
                    <p><span>百度：</span><u>http://music.baidu.com/song/<b>556113</b></u></p>
                    <p><span>酷狗：</span><u>http://m.kugou.com/play/info/<b>08228af3cb404e8a4e7e9871bf543ff6</b></u></p>
                    <p><span>酷我：</span><u>http://www.kuwo.cn/yinyue/<b>382425</b>/</u></p>
                    <p><span>ＱＱ：</span><u>http://y.qq.com/#type=song&amp;mid=<b>002B2EAA3brD5b</b>&amp;play=0</u></p>
                    <p><span>虾米：</span><u>http://www.xiami.com/song/<b>2113248</b></u></p>
                    <p><span>5sing：</span><u>http://5sing.kugou.com/<b>fc/2277364</b>.html</u></p>
                    <p><span>天天动听：</span><u>http://m.ttpod.com/#a=gqxq&amp;from=ss&amp;neid=<b>1029409</b>&amp;singerid=...</u></p>
                    <p><span>咪咕：</span><u>http://music.migu.cn/#/song/<b>477803</b>/P7Z1Y1L1N1/1/001002C</u></p>
                </div>
            </div>
        </div>
        <div class="am-popup" id="update-info">
          <div class="am-popup-inner">
            <div class="am-popup-hd">
              <h4 class="am-popup-title">更新日志</h4>
              <span data-am-modal-close
                    class="am-close">&times;</span>
            </div>
            <div class="am-popup-bd">
                <ul>
                    <li>2015.06.13 <code>v1.0.3</code> 增加对 天天动听、咪咕 的支持</li>
                    <li>2015.06.12 <code>v1.0.2</code> 增加对 5sing 的支持</li>
                    <li>2015.06.12 <code>v1.0.1</code> 代码优化 + BUG修复</li>
                    <li>2015.06.10 <code>v1.0.0</code> 音乐搜索器上线</li>
                </ul>
            </div>
          </div>
        </div>
        <div class="am-popup" id="copr-info">
          <div class="am-popup-inner">
            <div class="am-popup-hd">
              <h4 class="am-popup-title">版权声明</h4>
              <span data-am-modal-close
                    class="am-close">&times;</span>
            </div>
            <div class="am-popup-bd">
                <p>本站音频文件来自各网站接口，本站不会修改任何音频文件</p>
                <p>音频版权来自各网站，本站只提供数据查询服务，不提供任何音频存储和贩卖服务</p>
            </div>
          </div>
        </div>
    </section>
    <footer class="footer am-topbar-fixed-bottom">
        <p class="am-text-sm">如果获取失败，请 <a href="http://www.yuxiaoxi.com/guestbook#respond" target="_blank" rel="author">@麦葱</a> © 2013-2015 <a href="javascript:;" data-am-modal="{target: '#update-info'}">更新日志</a> <a href="javascript:;" data-am-modal="{target: '#copr-info'}">版权声明</a> <a href="https://github.com/maicong/music" target="_blank">Github 开源共享</a></p>
    </footer>
    <script src="http://libs.baidu.com/jquery/1.11.1/jquery.min.js"></script>
    <script src="http://cdn.amazeui.org/amazeui/2.3.0/js/amazeui.min.js"></script>
    <script src="static/music.js"></script>
</body>
</html>