<?php
/**
 *
 * 音乐搜索器 - 入口
 *
 * @author  MaiCong <i@maicong.me>
 * @link    https://github.com/maicong/music
 * @since   1.6.2
 *
 */

// 定义核心
define('MC_CORE', true);

// 定义版本
define('MC_VERSION', '1.6.2');

// 核心文件目录
define('MC_CORE_DIR', __DIR__ . '/core');

// 模版文件目录
define('MC_TEMP_DIR', __DIR__ . '/template');

// 调试模式，0为关闭，-1为打开
define('MC_DEBUG', 0);

// Curl 代理地址，例如：define('MC_PROXY', 'someproxy.com:9999')
define('MC_PROXY', false);

// Curl 代理用户名和密码，例如：define('MC_PROXYUSERPWD', 'username:password')
define('MC_PROXYUSERPWD', false);

// 服务器是否在国内
define('MC_INTERNAL', 1);

// PHP 版本判断
if (version_compare(phpversion(), '5.4', '<')) {
    header('Content-type:text/html;charset=utf-8');
    echo sprintf(
        '<h3>程序运行失败：</h3><blockquote>您的 PHP 版本低于最低要求 5.4，当前版本为 %s</blockquote>',
        phpversion()
    );
    exit;
}

// 判断是否启用 Curl
if (!extension_loaded('curl')) {
    header('Content-type:text/html;charset=utf-8');
    echo '<h3>程序运行失败：</h3><blockquote>请启用 Curl 模块</blockquote>';
    exit;
}


include_once MC_CORE_DIR . '/music.php';

// 支持的网站
$music_type_list = array(
    'netease'    => '网易',
    'qq'         => 'ＱＱ',
    'kugou'      => '酷狗',
    'kuwo'       => '酷我',
    'xiami'      => '虾米',
    'baidu'      => '百度',
    '1ting'      => '一听',
    'migu'       => '咪咕',
    'lizhi'      => '荔枝',
    'qingting'   => '蜻蜓',
    'ximalaya'   => '喜马拉雅',
    'kg'         => '全民K歌',
    '5singyc'    => '5sing原创',
    '5singfc'    => '5sing翻唱'
);

if (server('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest') {
    $music_input          = trim(post('input'));
    $music_filter         = post('filter');
    $music_type           = post('type');
    $music_page           = (int) post('page');
    $music_valid_patterns = array(
        'name' => '/^.+$/i',
        'id' => '/^[\w\/\|]+$/i',
        'url' => '/^https?:\/\/\S+$/i'
    );

    if (!$music_input || !$music_filter || !$music_type) {
        response('', 403, '(°ー°〃) 传入的数据不对啊');
    }

    if ($music_filter !== 'url' && !in_array($music_type, array_keys($music_type_list), true)) {
        response('', 403, '(°ー°〃) 目前还不支持这个网站');
    }

    if (!preg_match($music_valid_patterns[$music_filter], $music_input)) {
        response('', 403, '(・-・*) 请检查您的输入是否正确');
    }

    switch ($music_filter) {
        case 'name':
            if (!$music_page) {
                $music_page = 1;
            }
            $music_response = mc_get_song_by_name($music_input, $music_type, $music_page);
            break;
        case 'id':
            $music_response = mc_get_song_by_id($music_input, $music_type);
            break;
        case 'url':
            $music_response = mc_get_song_by_url($music_input);
            break;
    }

    if (empty($music_response)) {
        response('', 404, 'ㄟ( ▔, ▔ )ㄏ 没有找到相关信息');
    }

    if ($music_response['error']) {
        response('', $music_response['code'], '(°ー°〃) ' . $music_response['error']);
    }

    response($music_response, 200, '');
}

include_once(MC_TEMP_DIR . '/index.php');
