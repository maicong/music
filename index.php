<?php
/**
 *
 * 音乐搜索器 - 入口
 *
 * @author  MaiCong <i@maicong.me>
 * @link    https://github.com/maicong/music
 * @since   1.2.2
 *
 */

define('MC_CORE', true);

define('MC_VERSION', '1.2.2');

// SoundCloud 客户端 ID，如果失效请更改
define('MC_SC_CLIENT_ID', '2t9loNQH90kzJcsFCODdigxfp325aq4z');

// Curl 代理地址，解决翻墙问题。例如：define('MC_PROXY', 'http://10.10.10.10:8123');
define('MC_PROXY', false);

require_once __DIR__.'/music.php';

if (server('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest') {
    $music_input          = trim(post('music_input'));
    $music_filter         = post('music_filter');
    $music_type           = post('music_type');
    $music_type_allows    = array(
        '163',
        '1ting',
        'baidu',
        'kugou',
        'kuwo',
        'qq',
        'xiami',
        '5sing',
        'migu',
        'lizhi',
        'qingting',
        'soundcloud'
    );
    $music_valid_patterns = array(
        'name' => '/^.+$/i',
        'id' => '/^[\w\/\|]+$/i',
        'url' => '/^https?:\/\/\S+$/i'
    );

    if (!$music_input || !$music_filter || !$music_type) {
        response('', 403, '(°ー°〃) 传入的数据不对啊');
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

include_once __DIR__.'/index.tpl';
