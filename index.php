<?php
/**
 *
 * 音乐搜索器 - 入口
 *
 * @author  MaiCong <i@maicong.me>
 * @link    https://github.com/maicong/music
 * @since   1.2.6
 *
 */

define('MC_CORE', true);

define('MC_VERSION', '1.2.6');

// SoundCloud 客户端 ID，如果失效请更改
define('MC_SC_CLIENT_ID', '2t9loNQH90kzJcsFCODdigxfp325aq4z');

// Curl 代理地址，解决翻墙问题。例如：define('MC_PROXY', 'http://10.10.10.10:8123');
define('MC_PROXY', false);

// 核心文件目录;
define('MC_CORE_DIR', __DIR__.'/core');

// 模版文件目录;
define('MC_TPL_DIR', __DIR__.'/template');

// PHP 版本判断
if (version_compare(phpversion(), '5.4', '<')) {
    echo sprintf(
        '<h3>程序运行失败：</h3><blockquote>您的 PHP 版本低于最低要求 5.4，当前版本为 %s</blockquote>',
        phpversion()
    );
    exit;
}

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
    '5sing'      => '5sing',
    'soundcloud' => 'SoundCloud'
);

require_once(MC_CORE_DIR.'/music.php');

if (server('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest') {
    $music_input          = trim(post('music_input'));
    $music_filter         = post('music_filter');
    $music_type           = post('music_type');
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
            $music_response = maicong_get_song_by_name($music_input, $music_type);
            break;
        case 'id':
            $music_response = maicong_get_song_by_id($music_input, $music_type);
            break;
        case 'url':
            $music_response = maicong_get_song_by_url($music_input);
            break;
    }

    if (empty($music_response)) {
        response('', 404, 'ㄟ( ▔, ▔ )ㄏ 没有找到相关信息');
    }

    response($music_response, 200, '');
}

include_once(MC_TPL_DIR.'/index.php');
