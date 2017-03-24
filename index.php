<?php
/**
 *
 * 音乐搜索器 - 入口
 *
 * @author     MaiCong <i@maicong.me>
 * @date       2017-03-23 17:02:39
 * @version    1.1.0
 *
 */

define('MC_CORE', true);

// SoundCloud 客户端 ID，如果失效请更改
define('MC_SC_CLIENT_ID', '2t9loNQH90kzJcsFCODdigxfp325aq4z');

// Curl 代理地址，解决翻墙问题。例如：define('MC_PROXY', 'http://10.10.10.10:8123');
define('MC_PROXY', false);

require_once __DIR__.'/music.php';

if (ajax_post('music_input') && ajax_post('music_filter')) {
    $music_input      = ajax_post('music_input');
    $music_filter     = ajax_post('music_filter');
    $music_type       = ajax_post('music_type');
    $music_type_allow = array('163', '1ting', 'baidu', 'kugou', 'kuwo', 'qq', 'xiami', '5sing', 'ttpod', 'migu', 'soundcloud');
    $music_name       = null;
    $music_id         = null;
    $music_url        = null;
    switch ($music_filter) {
        case 'name':
            $music_valid      = preg_match('/^.+?$/isu', $music_input);
            $music_name       = $music_input;
            $music_type_valid = in_array($music_type, $music_type_allow, true);
            break;
        case 'id':
            $music_valid      = preg_match('/^[\w\/]+$/is', $music_input);
            $music_type_valid = in_array($music_type, $music_type_allow, true);
            $music_id         = $music_input;
            break;
        case 'url':
            $music_valid      = preg_match('/^(http|https|ftp):\/\/{1}([\S]+)$/is', $music_input);
            $music_type_valid = true;
            $music_url        = $music_input;
            break;
        default:
            $music_valid = false;
            break;
    }
    if ($music_valid && $music_type_valid) {
        if (null !== $music_name) {
            $music_name     = htmlspecialchars($music_name, ENT_QUOTES, 'UTF-8');
            $music_response = maicong_get_song_by_name($music_name, $music_type);
        }
        if (null !== $music_id) {
            $music_id       = htmlspecialchars($music_id, ENT_QUOTES, 'UTF-8');
            $music_response = maicong_get_song_by_id($music_id, $music_type);
        }
        if (null !== $music_url) {
            $music_response = maicong_get_song_by_url($music_url);
        }
        if (!empty($music_response)) {
            $reinfo = array('status' => '200', 'msg' => '', 'data' => $music_response);
        } else {
            $reinfo = array('status' => '404', 'msg' => 'ㄟ( ▔, ▔ )ㄏ，没有找到相关信息');
        }
    } else {
        $reinfo = array('status' => '400', 'msg' => '(・-・*)，请检查您的输入是否正确');
    }
    header('Content-type:text/json; charset=utf-8');
    echo json_encode($reinfo);
    exit();
}

include_once __DIR__.'/index.tpl';
