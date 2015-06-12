<?php
/**
 * 
 * 音乐搜索器 - 函数声明
 * 
 * @author     MaiCong <i@maicong.me>
 * @date  2015-06-12 15:06:37
 * @version    1.0.2
 *
 */

if ( ! defined( 'MC_CORE' ) ) exit; 

error_reporting(0);

// 引入 curl
require dirname(__FILE__).'/Curlclass/Curl.php';
require dirname(__FILE__).'/Curlclass/MultiCurl.php';
use Curlclass\Curl;
use Curlclass\MultiCurl;

// 参数处理
function stripslashes_deep($value) {
    if ( is_array($value) ) {
        $value = array_map('stripslashes_deep', $value);
    } elseif ( is_object($value) ) {
        $vars = get_object_vars( $value );
        foreach ($vars as $key=>$data) {
            $value->{$key} = stripslashes_deep( $data );
        }
    } elseif ( is_string( $value ) ) {
        $value = stripslashes($value);
    }

    return $value;
}
function maicong_parse_str( $string, &$array ) {
    parse_str( $string, $array );
    if ( get_magic_quotes_gpc() ) {
        $array = stripslashes_deep( $array );
    }
}
function maicong_parse_args( $args, $defaults = array() ) {
    if ( is_object( $args ) ) {
        $r = get_object_vars( $args );
    } elseif ( is_array( $args ) ) {
        $r =& $args;
    }else {
        maicong_parse_str( $args, $r );
    }
    if ( is_array( $defaults ) ) {
        return array_merge( $defaults, $r );
    }
    return $r;
}

// Curl 内容获取
function maicong_curl($args = array() ) {
    $default = array(
        'method' => 'GET',
        'user-agent' => 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.4 Safari/537.36',
        'url' => null,
        'referer' => 'https://www.google.co.uk',
        'headers' => null,
        'body' => null,
        'sslverify' => false
    );
    $args = maicong_parse_args($args, $default);
    $method = mb_strtolower($args['method']);
    $method_allow = array('get', 'post', 'put', 'patch', 'delete', 'head', 'options');
    if ( is_null($args['url']) || !in_array($method, $method_allow)) return;
    $curl = new Curl();
    $curl->setOpt(CURLOPT_SSL_VERIFYPEER, $args['sslverify']);
    $curl->setUserAgent($args['user-agent']);
    $curl->setReferrer($args['referer']);
    $curl->setTimeout(15);
    $curl->setHeader('X-Requested-With', 'XMLHttpRequest');
    if (!empty($args['headers'])) {
        foreach ($args['headers'] as $key => $val) {
            $curl->setHeader($key, $val);
        }
    }
    $curl->$method($args['url'], $args['body']);
    $curl->close();
    $response = $curl->raw_response;
    if (!empty($response)) {
        return $response;
    }
    return;
}

// 音频数据接口地址
function maicong_song_urls($value, $type = 'query', $site = '163'){
    if ( !$value ) return;
    $query = ($type === 'query') ? $value : '';
    $songid = ($type === 'songid') ? $value : '';
    $radio_search_urls = array(
        '163' => array(
            'method' => 'POST',
            'url' => 'http://music.163.com/api/search/suggest/web',
            'referer' => 'http://music.163.com/',
            'body' => array(
                'csrf_token' => '',
                'limit' => '5',
                's' => $query
            )
        ),
        '1ting' => array(
            'method' => 'GET',
            'url' => 'http://so.1ting.com/song/json',
            'referer' => 'http://m.1ting.com/',
            'body' => array(
                'page' => '1',
                'size' => '5',
                'q' => $query
            )
        ),
        'baidu' => array(
            'method' => 'GET',
            'url' => 'http://sug.music.baidu.com/info/suggestion',
            'referer' => 'http://music.baidu.com/search?key=' . $query,
            'body' => array(
                'format' => 'json',
                'version' => '2',
                'from' => '0',
                'word' => $query
            )
        ),
        'kugou' => array(
            'method' => 'GET',
            'url' => 'http://mobilecdn.kugou.com/api/v3/search/song',
            'referer' => 'http://m.kugou.com/v2/static/html/search.html',
            'body' => array(
                'format' => 'json',
                'page' => '1',
                'pagesize' => '5',
                'keyword' => $query
            ),
            'user-agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 8_0 like Mac OS X) AppleWebKit/600.1.3 (KHTML, like Gecko) Version/8.0 Mobile/12A4345d Safari/600.1.4'
        ),
        'kuwo' => array(
            'method' => 'GET',
            'url' => 'http://search.kuwo.cn/r.s',
            'referer' => 'http://player.kuwo.cn/webmusic/play',
            'body' => array(
                'ft' => 'music',
                'itemset' => 'web_2013',
                'pn' => '0',
                'rn' => '5',
                'rformat' => 'json',
                'encoding' => 'utf8',
                'all' => $query
            )
        ),
        'qq' => array(
            'method' => 'GET',
            'url' => 'http://open.music.qq.com/fcgi-bin/fcg_weixin_music_search.fcg',
            'referer' => 'http://m.y.qq.com/',
            'body' => array(
                'curpage' => '1',
                'perpage' => '5',
                'w' => $query
            ),
            'user-agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 8_0 like Mac OS X) AppleWebKit/600.1.3 (KHTML, like Gecko) Version/8.0 Mobile/12A4345d Safari/600.1.4'
        ),
        'xiami' => array(
            'method' => 'GET',
            'url' => 'http://api.xiami.com/web',
            'referer' => 'http://m.xiami.com/',
            'body' => array(
                'v' => '2.0',
                'app_key' => '1',
                'r' => 'search/songs',
                'page' => '1',
                'limit' => '5',
                'key' => $query
            ),
            'user-agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 8_0 like Mac OS X) AppleWebKit/600.1.3 (KHTML, like Gecko) Version/8.0 Mobile/12A4345d Safari/600.1.4'
        ),
        '5sing' => array(
            'method' => 'GET',
            'url' => 'http://search.5sing.kugou.com/home/QuickSearch',
            'referer' => 'http://5sing.kugou.com/',
            'body' => array(
                'keyword' => $query
            )
        )
    );
    $radio_song_urls = array(
        '163' => array(
            'method' => 'GET',
            'url' => 'http://music.163.com/api/song/detail/',
            'referer' => 'http://music.163.com/#/song?id='.$songid,
            'body' => array(
                'id' => $songid,
                'ids' => '['.$songid.']'
            )
        ),
        '1ting' => array(
            'method' => 'GET',
            'url' => 'http://m.1ting.com/touch/api/song',
            'referer' => 'http://m.1ting.com/#/song/'.$songid,
            'body' => array(
                'ids' => $songid
            )
        ),
        'baidu' => array(
            'method' => 'GET',
            'url' => 'http://tingapi.ting.baidu.com/v2/restserver/ting',
            'referer' => 'music.baidu.com/song/' . $songid,
            'body' => array(
                'method' => 'baidu.ting.song.play',
                'format' => 'json',
                'songid' => $songid
            )
        ),
        'kugou' => array(
            'method' => 'GET',
            'url' => 'http://m.kugou.com/app/i/getSongInfo.php',
            'referer' => 'http://m.kugou.com/play/info/'.$songid,
            'body' => array(
                'cmd' => 'playInfo',
                'hash' => $songid
            ),
            'user-agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 8_0 like Mac OS X) AppleWebKit/600.1.3 (KHTML, like Gecko) Version/8.0 Mobile/12A4345d Safari/600.1.4'
        ),
        'kuwo' => array(
            'method' => 'GET',
            'url' => 'http://player.kuwo.cn/webmusic/st/getNewMuiseByRid',
            'referer' => 'http://player.kuwo.cn/webmusic/play',
            'body' => array(
                'rid' => 'MUSIC_'.$songid
            )
        ),
        'qq' => array(
            'method' => 'GET',
            'url' => 'http://i.y.qq.com/s.plcloud/fcgi-bin/fcg_list_songinfo_cp.fcg',
            'referer' => 'http://data.music.qq.com/playsong.html?songmid='.$songid,
            'body' => array(
                'url' => '1',
                'midlist' => $songid
            ),
            'user-agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 8_0 like Mac OS X) AppleWebKit/600.1.3 (KHTML, like Gecko) Version/8.0 Mobile/12A4345d Safari/600.1.4'
        ),
        'xiami' => array(
            'method' => 'GET',
            'url' => 'http://www.xiami.com/song/playlist/id/'.$songid.'/object_name/default/object_id/0/cat/json',
            'referer' => 'http://m.xiami.com/song/'.$songid,
            'user-agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 8_0 like Mac OS X) AppleWebKit/600.1.3 (KHTML, like Gecko) Version/8.0 Mobile/12A4345d Safari/600.1.4'
        ),
        '5sing' => array(
            'method' => 'GET',
            'url' => 'http://5sing.kugou.com/'.$songid.'.html',
            'referer' => 'http://5sing.kugou.com/'.$songid.'.html'
        )
    );
    if ($type === 'query') {
        return $radio_search_urls[$site];
    }
    if ($type === 'songid') {
        return $radio_song_urls[$site];
    }
    return;
}

// 获取音频信息 - 关键词搜索
function maicong_get_song_by_name($query, $site = '163') {
    if ( !$query ) return;

    $radio_search_url = maicong_song_urls($query, 'query', $site);

    if (empty($query) || empty($radio_search_url)) {
        return;
    }
    $radio_result = maicong_curl($radio_search_url);
    if (empty($radio_result)) {
        return;
    }
    $radio_songid = array();
    switch ($site) {
        case '1ting':
            $radio_data = json_decode($radio_result, true);
            foreach ($radio_data['results'] as $key => $val) {
                $radio_songid[] = $val['song_id'];
            }
            break;
        case 'baidu':
            $radio_data = json_decode($radio_result, true);
            foreach ($radio_data['data']['song'] as $key => $val) {
                if ($key>4) break;
                $radio_songid[] = $val['songid'];
            }
            break;
        case 'kugou':
            $radio_data = json_decode($radio_result, true);
            foreach ($radio_data['data']['info'] as $key => $val) {
                $radio_songid[] = $val['hash'];
            }
            break;
        case 'kuwo':
            $radio_result = str_replace('\'', '"', $radio_result);
            $radio_data = json_decode($radio_result, true);
            foreach ($radio_data['abslist'] as $key => $val) {
                $radio_songid[] = str_replace('MUSIC_', '', $val['MUSICRID']);
            }
            break;
        case 'qq':
            $radio_data = json_decode($radio_result, true);
            foreach ($radio_data['list'] as $key => $val) {
                $radio_hash = explode('|', $val['f']);
                $radio_songid[] = $radio_hash[20];
            }
            break;
        case 'xiami':
            $radio_data = json_decode($radio_result, true);
            foreach ($radio_data['data']['songs'] as $key => $val) {
                $radio_songid[] = $val['song_id'];
            }
            break;
        case '5sing':
            $radio_data = json_decode(substr($radio_result,1,-1), true);
            foreach ($radio_data['songs'] as $key => $val) {
                if ($key>4) break;
                $radio_songid[] = $val['type'].'/'.$val['songId'];
            }
            break;
        case '163':
        default:
            $radio_data = json_decode($radio_result, true);
            foreach ($radio_data['result']['songs'] as $key => $val) {
                $radio_songid[] = $val['id'];
            }
            break;
    }
    return maicong_get_song_by_id($radio_songid, $site, true);
}

// 获取音频信息 - 歌曲ID
function maicong_get_song_by_id($songid, $site = '163', $multi = false){
    if (empty($songid) || empty($site)) {
        return;
    }
    $radio_song_urls = array();
    if ($multi) {
        if (!is_array($songid)) return;
        foreach ($songid as $key => $val) {
            $radio_song_urls[] = maicong_song_urls($val, 'songid', $site);
        }
    } else {
        $radio_song_urls[] = maicong_song_urls($songid, 'songid', $site);
    }
    if (empty($radio_song_urls) || !array_key_exists(0, $radio_song_urls)) {
        return;
    }
    $radio_result = array();
    foreach ($radio_song_urls as $key => $val) {
        $radio_result[] = maicong_curl($val);
    }
    if (empty($radio_result) || !array_key_exists(0, $radio_result)) {
        return;
    }
    $radio_songs = array();
    switch ($site) {
        case '1ting':
            foreach ($radio_result as $key => $val) {
                $radio_data = json_decode($val, true);
                $radio_detail = $radio_data;
                if (!empty($radio_detail)) {
                    $radio_songs[]= array(
                        'type' => '1ting',
                        'songid' => $radio_detail[0]['song_id'],
                        'name' => $radio_detail[0]['song_name'],
                        'author' => $radio_detail[0]['singer_name'],
                        'music' => 'http://96.1ting.com'.str_replace('.wma', '.mp3', $radio_detail[0]['song_filepath']),
                        'pic' => $radio_detail[0]['album_cover']
                    );
                }
            }
            break;
        case 'baidu':
            foreach ($radio_result as $key => $val) {
                $radio_data = json_decode($val, true);
                $radio_detail = $radio_data;
                if (!empty($radio_detail) && !empty($radio_detail['songinfo'])) {
                    // 注： 百度不允许外链 ~ 自信解决吧
                    $radio_songs[] = array(
                        'type' => 'baidu',
                        'songid' => $radio_detail['songinfo']['song_id'],
                        'name' => $radio_detail['songinfo']['title'],
                        'author' => $radio_detail['songinfo']['author'],
                        'music' => $radio_detail['bitrate']['file_link'],
                        'pic' => $radio_detail['songinfo']['pic_big']
                    );
                }
            }
            break;
        case 'kugou':
            foreach ($radio_result as $key => $val) {
                $radio_data = json_decode($val, true);
                $radio_detail = $radio_data;
                if (!empty($radio_detail) && $radio_data['status']) {
                    $radio_name = explode(' - ', $radio_detail['fileName']);
                    $radio_img = array(
                        'url' => 'http://mobilecdn.kugou.com/new/app/i/yueku.php',
                        'body' => array(
                            'cmd' => '104',
                            'size' => '100',
                            'singer' => $radio_name[0]
                        )
                    );
                    $radio_imginfo = json_decode(maicong_curl($radio_img), true);
                    if (!empty($radio_imginfo)) {
                        $radio_pic = $radio_imginfo['url'];
                    }
                    $radio_songs[] = array(
                        'type' => 'kugou',
                        'songid' => $radio_detail['hash'],
                        'name' => $radio_name[1],
                        'author' => $radio_name[0],
                        'music' => $radio_detail['url'],
                        'pic' => $radio_pic
                    );
                }
            }
            break;
        case 'kuwo':
            foreach ($radio_result as $key => $val) {
                preg_match_all('/<([\w]+)>(.*?)<\/\\1>/', $val, $radio_data);
                if (!empty($radio_data[1]) && !empty($radio_data[2])) {
                    $radio_detail = array();
                    foreach ($radio_data[1] as $key => $val) {
                        $radio_detail[$val] = $radio_data[2][$key];
                    }
                    $radio_songs[] = array(
                        'type' => 'kuwo',
                        'songid' => $radio_detail['music_id'],
                        'name' => $radio_detail['name'],
                        'author' => $radio_detail['singer'],
                        'music' => 'http://'.$radio_detail['mp3dl'].'/resource/'.$radio_detail['mp3path'],
                        'pic' => $radio_detail['artist_pic']
                    );
                }
            }
            break;
        case 'qq':
            foreach ($radio_result as $key => $val) {
                $radio_data = json_decode($val, true);
                $radio_detail = $radio_data['data'];
                if (!empty($radio_detail) && $radio_data['url']) {
                    $radio_pic = substr($radio_detail[0]['albummid'], -2, 1).'/'.substr($radio_detail[0]['albummid'], -1, 1).'/'.$radio_detail[0]['albummid'];
                    $radio_songs[] = array(
                        'type' => 'qq',
                        'songid' => $radio_detail[0]['songmid'],
                        'name' => $radio_detail[0]['songname'],
                        'author' => $radio_detail[0]['singer'][0]['name'],
                        'music' => $radio_data['url'][$radio_detail[0]['songid']],
                        'pic' => 'http://imgcache.qq.com/music/photo/mid_album_300/'.$radio_pic.'.jpg'
                    );
                }
            }
            break;
        case 'xiami':
            foreach ($radio_result as $key => $val) {
                $radio_data = json_decode($val, true);
                $radio_detail = $radio_data['data']['trackList'];
                if (!empty($radio_detail)) {
                    $radio_songs[] = array(
                        'type' => 'xiami',
                        'songid' => $radio_detail[0]['song_id'],
                        'name' => $radio_detail[0]['title'],
                        'author' => $radio_detail[0]['artist'],
                        'music' => maicong_decode_xiami_location($radio_detail[0]['location']),
                        'pic' => $radio_detail[0]['album_pic']
                    );
                }
            }
            break;
        case '5sing':
            foreach ($radio_result as $key => $val) {
                preg_match('/ticket"\: "(.*?)"/is', $val, $radio_match);
                if (!empty($radio_match[1])){
                    $radio_detail = json_decode(base64_decode($radio_match[1]), true);
                    if (!empty($radio_detail)){
                        $radio_songs[] = array(
                            'type' => '5sing',
                            'songid' => $radio_detail['songID'],
                            'name' => $radio_detail['songName'],
                            'author' => $radio_detail['singer'],
                            'music' => $radio_detail['file'],
                            'pic' => $radio_detail['avatar']
                        );
                    }
                }
            }
            break;
        case '163':
        default:
            foreach ($radio_result as $key => $val) {
                $radio_data = json_decode($val, true);
                $radio_detail = $radio_data['songs'];
                if (!empty($radio_detail)) {
                    $radio_songs[] = array(
                        'type' => '163',
                        'songid' => $radio_detail[0]['id'],
                        'name' => $radio_detail[0]['name'],
                        'author' => $radio_detail[0]['artists'][0]['name'],
                        'music' => $radio_detail[0]['mp3Url'],
                        'pic' => $radio_detail[0]['album']['picUrl'].'?param=100x100'
                    );
                }
            }
            
            break;
    }
    return !empty($radio_songs) ? $radio_songs : '';
}

// 获取音频信息 - url
function maicong_get_song_by_url($url){
    preg_match('/http(s)?:\/\/music\.163\.com\/(#|m)\/song(\?id=|\/)(\d+)/i', $url, $match_163);
    preg_match('/http(s)?:\/\/(www|m)\.1ting\.com\/(player\/b6\/player_|#\/song\/)(\d+)(\.html|)/i', $url, $match_1ting);
    preg_match('/http(s)?:\/\/music\.baidu\.com\/song\/(\d+)/i', $url, $match_baidu);
    preg_match('/http(s)?:\/\/m\.kugou\.com\/play\/info\/([a-z0-9]+)/i', $url, $match_kugou);
    preg_match('/http(s)?:\/\/www\.kuwo\.cn\/(yinyue|my)\/(\d+)/i', $url, $match_kuwo);
    preg_match('/http(s)?:\/\/(y\.qq\.com\/#type=song&mid=|data\.music\.qq\.com\/playsong\.html\?songmid=)([a-zA-Z0-9]+)/i', $url, $match_qq);
    preg_match('/http(s)?:\/\/(www|m)\.xiami\.com\/song\/(\d+)/i', $url, $match_xiami);
    preg_match('/http(s)?:\/\/5sing\.kugou\.com\/(m\/detail\/|)(\w+)(-|\/)(\d+)(-1|)\.html/i', $url, $match_5sing);
    if (!empty($match_163)) {
        $songid = $match_163[4];
        $songtype = '163';
    } elseif (!empty($match_1ting)) {
        $songid = $match_1ting[4];
        $songtype = '1ting';
    } elseif (!empty($match_baidu)) {
        $songid = $match_baidu[2];
        $songtype = 'baidu';
    } elseif (!empty($match_kugou)) {
        $songid = $match_kugou[2];
        $songtype = 'kugou';
    } elseif (!empty($match_kuwo)) {
        $songid = $match_kuwo[3];
        $songtype = 'kuwo';
    } elseif (!empty($match_qq)) {
        $songid = $match_qq[3];
        $songtype = 'qq';
    } elseif (!empty($match_xiami)) {
        $songid = $match_xiami[3];
        $songtype = 'xiami';
    } elseif (!empty($match_5sing)) {
        $songid = $match_5sing[3].'/'.$match_5sing[5];
        $songtype = '5sing';
    } else {
        return;
    }
    return maicong_get_song_by_id($songid, $songtype);
}
// 解密虾米 location
function maicong_decode_xiami_location($location) {
    $location = trim($location);
    $result   = array();
    $line     = intval($location[0]);
    $locLen   = strlen($location);
    $rows     = intval(($locLen - 1) / $line);
    $extra    = ($locLen - 1) % $line;
    $location = substr($location, 1);
    for ($i = 0; $i < $extra; ++$i) {
        $start    = ($rows + 1) * $i;
        $end      = ($rows + 1) * ($i + 1);
        $result[] = substr($location, $start, $end - $start);
    }
    for ($i = 0; $i < $line - $extra; ++$i) {
        $start    = ($rows + 1) * $extra + ($rows * $i);
        $end      = ($rows + 1) * $extra + ($rows * $i) + $rows;
        $result[] = substr($location, $start, $end - $start);
    }
    $url = '';
    for ($i = 0; $i < $rows + 1; ++$i) {
        for ($j = 0; $j < $line; ++$j) {
            if ($j >= count($result) || $i >= strlen($result[$j])) {
                continue;
            }
            $url .= $result[$j][$i];
        }
    }
    $url = urldecode($url);
    $url = str_replace('^', '0', $url);
    return $url;
}

// Ajax Post
function ajax_post($key){
    return (!empty($_POST[$key]) && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ? $_POST[$key] : null;
}