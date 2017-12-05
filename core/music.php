<?php
/**
 *
 * 音乐搜索器 - 函数声明
 *
 * @author  MaiCong <i@maicong.me>
 * @link    https://github.com/maicong/music
 * @since   1.4.4
 *
 */

// 非我族类
if (!defined('MC_CORE')) {
    header("Location: /");
    exit();
}

// 显示 PHP 错误报告
error_reporting(MC_DEBUG);

// 引入 Curl
require MC_CORE_DIR . '/vendor/autoload.php';

// 使用 Curl
use \Curl\Curl;

// Curl 内容获取
function mc_curl($args = array())
{
    $default      = array(
        'method'     => 'GET',
        'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.50 Safari/537.36',
        'url'        => null,
        'referer'    => 'https://www.google.co.uk',
        'headers'    => null,
        'body'       => null,
        'proxy'      => false
    );
    $args         = array_merge($default, $args);
    $method       = mb_strtolower($args['method']);
    $method_allow = array('get', 'post');
    if (null === $args['url'] || !in_array($method, $method_allow, true)) {
        return;
    }
    $curl         = new Curl();
    $curl->setUserAgent($args['user-agent']);
    $curl->setReferrer($args['referer']);
    $curl->setTimeout(15);
    $curl->setHeader('X-Requested-With', 'XMLHttpRequest');
    if ($args['proxy'] && define('MC_PROXY')) {
        $curl->setOpt(CURLOPT_HTTPPROXYTUNNEL, 1);
        $curl->setOpt(CURLOPT_PROXY, MC_PROXY);
        $curl->setOpt(CURLOPT_PROXYUSERPWD, MC_PROXYUSERPWD);
    }
    if (!empty($args['headers'])) {
        $curl->setHeaders($args['headers']);
    }
    $curl->$method($args['url'], $args['body']);
    $curl->close();
    if (!$curl->error) {
        return $curl->rawResponse;
    }
}

// 判断地址是否有误
function mc_is_error ($url) {
    $curl = new Curl();
    $curl->setUserAgent('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.50 Safari/537.36');
    $curl->head($url);
    $curl->close();
    return $curl->errorCode;
}

// 音频数据接口地址
function mc_song_urls($value, $type = 'query', $site = 'netease')
{
    if (!$value) {
        return;
    }
    $query             = ('query' === $type) ? $value : '';
    $songid            = ('songid' === $type) ? $value : '';
    $radio_search_urls = array(
        'netease'            => array(
            'method'         => 'POST',
            'url'            => 'http://music.163.com/api/linux/forward',
            'referer'        => 'http://music.163.com/',
            'proxy'          => false,
            'body'           => encode_netease_data(array(
                'method'     => 'POST',
                'url'        => 'http://music.163.com/api/cloudsearch/pc',
                'params'     => array(
                    's'      => $query,
                    'type'   => '1',
                    'offset' => '0',
                    'limit'  => '10'
                )
            ))
        ),
        '1ting'              => array(
            'method'         => 'GET',
            'url'            => 'http://so.1ting.com/song/json',
            'referer'        => 'http://h5.1ting.com/',
            'proxy'          => false,
            'body'           => array(
                'q'          => $query,
                'page'       => '1',
                'size'       => '10'
            )
        ),
        'baidu'              => array(
            'method'         => 'GET',
            'url'            => 'http://sug.music.baidu.com/info/suggestion',
            'referer'        => 'http://music.baidu.com/search?key='.urlencode($query),
            'proxy'          => false,
            'body'           => array(
                'word'       => $query,
                'format'     => 'json',
                'version'    => '2',
                'from'       => '0'
            )
        ),
        'kugou'              => array(
            'method'         => 'GET',
            'url'            => 'http://mobilecdn.kugou.com/api/v3/search/song',
            'referer'        => 'http://m.kugou.com/v2/static/html/search.html',
            'proxy'          => false,
            'body'           => array(
                'keyword'    => $query,
                'format'     => 'json',
                'page'       => '1',
                'pagesize'   => '10'
            ),
            'user-agent'     => 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1'
        ),
        'kuwo'               => array(
            'method'         => 'GET',
            'url'            => 'http://search.kuwo.cn/r.s',
            'referer'        => 'http://player.kuwo.cn/webmusic/play',
            'proxy'          => false,
            'body'           => array(
                'all'        => $query,
                'ft'         => 'music',
                'itemset'    => 'web_2013',
                'pn'         => '0',
                'rn'         => '10',
                'rformat'    => 'json',
                'encoding'   => 'utf8'
            )
        ),
        'qq'                 => array(
            'method'         => 'GET',
            'url'            => 'https://c.y.qq.com/soso/fcgi-bin/search_for_qq_cp',
            'referer'        => 'https://m.y.qq.com/#search',
            'proxy'          => false,
            'body'           => array(
                'w'          => $query,
                'p'          => '1',
                'n'          => '10',
                'format'     => 'json'
            ),
            'user-agent'     => 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1'
        ),
        'xiami'              => array(
            'method'         => 'GET',
            'url'            => 'http://api.xiami.com/web',
            'referer'        => 'http://m.xiami.com',
            'proxy'          => false,
            'body'           => array(
                'key'        => $query,
                'v'          => '2.0',
                'app_key'    => '1',
                'r'          => 'search/songs',
                'page'       => '1',
                'limit'      => '10'
            ),
            'user-agent'     => 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1'
        ),
        '5singyc'            => array(
            'method'         => 'GET',
            'url'            => 'http://goapi.5sing.kugou.com/search/search',
            'referer'        => 'http://5sing.kugou.com/',
            'proxy'          => false,
            'body'           => array(
                'k'          => $query,
                't'          => '0',
                'filterType' => '1',
                'ps'         => 10,
                'pn'         => 1
            )
        ),
        '5singfc'            => array(
            'method'         => 'GET',
            'url'            => 'http://goapi.5sing.kugou.com/search/search',
            'referer'        => 'http://5sing.kugou.com/',
            'proxy'          => false,
            'body'           => array(
                'k'          => $query,
                't'          => '0',
                'filterType' => '2',
                'ps'         => 10,
                'pn'         => 1
            )
        ),
        'migu'               => array(
            'method'         => 'GET',
            'url'            => 'http://m.music.migu.cn/music-h5/search/searchAll.json',
            'referer'        => 'http://m.music.migu.cn/search',
            'proxy'          => false,
            'body'           => array(
                'keyWord'    => $query,
                'type'       => 'song',
                'pageNo'     => '1',
                'pageSize'   => '10'
            )
        ),
        'lizhi'              => array(
            'method'         => 'GET',
            'url'            => 'http://m.lizhi.fm/api/search_audio/'.urlencode($query).'/1',
            'referer'        => 'http://m.lizhi.fm',
            'proxy'          => false,
            'body'           => false,
            'user-agent'     => 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1'
        ),
        'qingting'           => array(
            'method'         => 'GET',
            'url'            => 'http://i.qingting.fm/wapi/search',
            'referer'        => 'http://www.qingting.fm',
            'proxy'          => false,
            'body'           => array(
                'k'          => $query,
                'page'       => '1',
                'pagesize'   => '10',
                'include'    => 'program_ondemand',
                'groups'     => 'program_ondemand'
            )
        ),
        'ximalaya'           => array(
            'method'         => 'GET',
            'url'            => 'http://search.ximalaya.com/front/v1',
            'referer'        => 'http://www.ximalaya.com',
            'proxy'          => false,
            'body'           => array(
                'kw'         => $query,
                'core'       => 'all',
                'page'       => '1',
                'rows'       => '10',
                'is_paid'    => false
            )
        ),
        'soundcloud'         => array(
            'method'         => 'GET',
            'url'            => 'https://api-v2.soundcloud.com/search/tracks',
            'referer'        => 'https://soundcloud.com/',
            'proxy'          => false,
            'body'           => array(
                'q'          => $query,
                'limit'      => '10',
                'offset'     => '0',
                'facet'      => 'genre',
                'client_id'  => MC_SC_CLIENT_ID
            )
        )
    );
    $radio_song_urls = array(
        'netease'           => array(
            'method'        => 'POST',
            'url'           => 'http://music.163.com/api/linux/forward',
            'referer'       => 'http://music.163.com/',
            'proxy'         => false,
            'body'          => encode_netease_data(array(
                'method'    => 'GET',
                'url'       => 'http://music.163.com/api/song/detail',
                'params'    => array(
                  'id'      => $songid,
                  'ids'     => '[' . $songid . ']'
                )
            ))
        ),
        '1ting'             => array(
            'method'        => 'GET',
            'url'           => 'http://h5.1ting.com/touch/api/song',
            'referer'       => 'http://h5.1ting.com/#/song/' . $songid,
            'proxy'         => false,
            'body'          => array(
                'ids'       => $songid
            )
        ),
        'baidu'             => array(
            'method'        => 'GET',
            'url'           => 'http://music.baidu.com/data/music/links',
            'referer'       => 'music.baidu.com/song/' . $songid,
            'proxy'         => false,
            'body'          => array(
                'songIds'   => $songid
            )
        ),
        'kugou'             => array(
            'method'        => 'GET',
            'url'           => 'http://m.kugou.com/app/i/getSongInfo.php',
            'referer'       => 'http://m.kugou.com/play/info/' . $songid,
            'proxy'         => false,
            'body'          => array(
                'cmd'       => 'playInfo',
                'hash'      => $songid
            ),
            'user-agent'    => 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1'
        ),
        'kuwo'              => array(
            'method'        => 'GET',
            'url'           => 'http://player.kuwo.cn/webmusic/st/getNewMuiseByRid',
            'referer'       => 'http://player.kuwo.cn/webmusic/play',
            'proxy'         => false,
            'body'          => array(
                'rid'       => 'MUSIC_' . $songid
            )
        ),
        'qq'                => array(
            'method'        => 'GET',
            'url'           => 'https://c.y.qq.com/v8/fcg-bin/fcg_play_single_song.fcg',
            'referer'       => 'https://y.qq.com/',
            'proxy'         => false,
            'body'          => array(
                'songmid'   => $songid,
                'format'    => 'json'

            ),
            'user-agent'    => 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1'
        ),
        'xiami'             => array(
            'method'        => 'GET',
            'url'           => 'http://www.xiami.com/song/playlist/id/' . $songid . '/type/0/cat/json',
            'referer'       => 'http://www.xiami.com',
            'proxy'         => false
        ),
        '5singyc'           => array(
            'method'        => 'GET',
            'url'           => 'http://mobileapi.5sing.kugou.com/song/newget',
            'referer'       => 'http://5sing.kugou.com/yc/' . $songid . '.html',
            'proxy'         => false,
            'body'          => array(
                'songid'    => $songid,
                'songtype'  => 'yc'
            )
        ),
        '5singfc'           => array(
            'method'        => 'GET',
            'url'           => 'http://mobileapi.5sing.kugou.com/song/newget',
            'referer'       => 'http://5sing.kugou.com/fc/' . $songid . '.html',
            'proxy'         => false,
            'body'          => array(
                'songid'    => $songid,
                'songtype'  => 'fc'
            )
        ),
        'migu'              => array(
            'method'        => 'GET',
            'url'           => 'http://music.migu.cn/webfront/player/findsong.do',
            'referer'       => 'http://music.migu.cn/#/song/' . $songid,
            'proxy'         => false,
            'body'          => array(
                'itemid'    => $songid,
                'type'      => 'song'
            )
        ),
        'lizhi'             => array(
            'method'        => 'GET',
            'url'           => 'http://m.lizhi.fm/api/audios_with_radio',
            'referer'       => 'http://m.lizhi.fm',
            'proxy'         => false,
            'body'          => array(
                'ids'       => $songid
            ),
            'user-agent'    => 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1'
        ),
        'qingting'          => array(
            'method'        => 'GET',
            'url'           => 'http://i.qingting.fm/wapi/channels/' . split_songid($songid, 0) . '/programs/' . split_songid($songid, 1),
            'referer'       => 'http://www.qingting.fm',
            'proxy'         => false,
            'body'          => false
        ),
        'ximalaya'          => array(
            'method'        => 'GET',
            'url'           => 'http://mobile.ximalaya.com/v1/track/ca/playpage/' . $songid,
            'referer'       => 'http://www.ximalaya.com',
            'proxy'         => false,
            'body'          => false
        ),
        'soundcloud'        => array(
            'method'        => 'GET',
            'url'           => 'https://api.soundcloud.com/tracks/' . $songid . '.json',
            'referer'       => 'https://soundcloud.com/',
            'proxy'         => false,
            'body'          => array(
                'client_id' => MC_SC_CLIENT_ID
            )
        )
    );
    if ('query' === $type) {
        return $radio_search_urls[$site];
    }
    if ('songid' === $type) {
        return $radio_song_urls[$site];
    }
    return;
}

// 获取音频信息 - 关键词搜索
function mc_get_song_by_name($query, $site = 'netease')
{
    if (!$query) {
        return;
    }
    $radio_search_url = mc_song_urls($query, 'query', $site);
    if (empty($query) || empty($radio_search_url)) {
        return;
    }
    $radio_result = mc_curl($radio_search_url);
    if (empty($radio_result)) {
        return;
    }
    $radio_songid = array();
    switch ($site) {
        case '1ting':
            $radio_data = json_decode($radio_result, true);
            if (empty($radio_data['results'])) {
                return;
            }
            foreach ($radio_data['results'] as $val) {
                $radio_songid[] = $val['song_id'];
            }
            break;
        case 'baidu':
            $radio_data = json_decode($radio_result, true);
            if (empty($radio_data['data']) || empty($radio_data['data']['song'])) {
                return;
            }
            foreach ($radio_data['data']['song'] as $val) {
                $radio_songid[] = $val['songid'];
            }
            break;
        case 'kugou':
            $radio_data = json_decode($radio_result, true);
            if (empty($radio_data['data']) || empty($radio_data['data']['info'])) {
                return;
            }
            foreach ($radio_data['data']['info'] as $val) {
                $radio_songid[] = $val['320hash'] ? $val['320hash'] : $val['hash'];
            }
            break;
        case 'kuwo':
            $radio_result = str_replace('\'', '"', $radio_result);
            $radio_data   = json_decode($radio_result, true);
            if (empty($radio_data['abslist'])) {
                return;
            }
            foreach ($radio_data['abslist'] as $val) {
                $radio_songid[] = str_replace('MUSIC_', '', $val['MUSICRID']);
            }
            break;
        case 'qq':
            $radio_data = json_decode($radio_result, true);
            if (empty($radio_data['data']) || empty($radio_data['data']['song']) || empty($radio_data['data']['song']['list'])) {
                return;
            }
            foreach ($radio_data['data']['song']['list'] as $val) {
                $radio_songid[] = $val['songmid'];
            }
            break;
        case 'xiami':
            $radio_data = json_decode($radio_result, true);
            if (empty($radio_data['data']) || empty($radio_data['data']['songs'])) {
                return;
            }
            foreach ($radio_data['data']['songs'] as $val) {
                $radio_songid[] = $val['song_id'];
            }
            break;
        case '5singyc':
        case '5singfc':
            $radio_data = json_decode($radio_result, true);
            if (empty($radio_data['data']['songArray'])) {
                return;
            }
            foreach ($radio_data['data']['songArray'] as $val) {
                $radio_songid[] = $val['songId'];
            }
            break;
        case 'migu':
            $radio_data = json_decode($radio_result, true);
            if (empty($radio_data['data']) || empty($radio_data['data']['list'])) {
                return;
            }
            foreach ($radio_data['data']['list'] as $val) {
                $radio_songid[] = $val['songId'];
            }
            break;
        case 'lizhi':
            $radio_data = json_decode($radio_result, true);
            if (empty($radio_data['audio']) || empty($radio_data['audio']['data'])) {
                return;
            }
            foreach ($radio_data['audio']['data'] as $val) {
                $radio_songid[] = $val['audio']['id'];
            }
            break;
        case 'qingting':
            $radio_data = json_decode($radio_result, true);
            if (empty($radio_data['data']) || empty($radio_data['data']['data'])) {
                return;
            }
            foreach ($radio_data['data']['data'][0]['doclist']['docs'] as $val) {
                $radio_songid[] = $val['parent_id'].'|'.$val['id'];
            }
            break;
        case 'ximalaya':
            $radio_data = json_decode($radio_result, true);
            if (empty($radio_data['track']) || empty($radio_data['track']['docs'])) {
                return;
            }
            foreach ($radio_data['track']['docs'] as $val) {
                if (!$val['is_paid']) { // 过滤付费的
                    $radio_songid[] = $val['id'];
                }
            }
            break;
        case 'soundcloud':
            $radio_data = json_decode($radio_result, true);
            if (empty($radio_data['collection'])) {
                return;
            }
            foreach ($radio_data['collection'] as $val) {
                $radio_songid[] = $val['id'];
            }
            break;
        case 'netease':
        default:
            $radio_data = json_decode($radio_result, true);
            if (empty($radio_data['result']) || empty($radio_data['result']['songs'])) {
                return;
            }
            foreach ($radio_data['result']['songs'] as $val) {
                $radio_songid[] = $val['id'];
            }
            break;
    }
    return mc_get_song_by_id($radio_songid, $site, true);
}

// 获取音频信息 - 歌曲ID
function mc_get_song_by_id($songid, $site = 'netease', $multi = false)
{
    if (empty($songid) || empty($site)) {
        return;
    }
    $radio_song_urls = array();
    $site_allow_multiple = array(
        'netease',
        '1ting',
        'baidu',
        'qq',
        'xiami',
        'migu',
        'lizhi'
    );
    if ($multi) {
        if (!is_array($songid)) {
            return;
        }
        if (in_array($site, $site_allow_multiple, true)) {
            $radio_song_urls[] = mc_song_urls(implode(',', $songid), 'songid', $site);
        } else {
            foreach ($songid as $key => $val) {
                $radio_song_urls[] = mc_song_urls($val, 'songid', $site);
            }
        }
    } else {
        $radio_song_urls[] = mc_song_urls($songid, 'songid', $site);
    }
    if (empty($radio_song_urls) || !array_key_exists(0, $radio_song_urls)) {
        return;
    }
    $radio_result = array();
    foreach ($radio_song_urls as $key => $val) {
        $radio_result[] = mc_curl($val);
    }
    if (empty($radio_result) || !array_key_exists(0, $radio_result)) {
        return;
    }
    $radio_songs = array();
    switch ($site) {
        case '1ting':
            foreach ($radio_result as $val) {
                $radio_data            = json_decode($val, true);
                if (!empty($radio_data)) {
                    foreach ($radio_data as $value) {
                        $radio_song_id = $value['song_id'];
                        $radio_songs[] = array(
                            'type'   => '1ting',
                            'link'   => 'http://www.1ting.com/player/6c/player_' . $radio_song_id . '.html',
                            'songid' => $radio_song_id,
                            'name'   => urldecode($value['song_name']),
                            'author' => urldecode($value['singer_name']),
                            'music'  => 'http://h5.1ting.com/file?url=' . str_replace('.wma', '.mp3', $value['song_filepath']),
                            'pic'    => $value['album_cover']
                        );
                    }
                }
            }
            break;
        case 'baidu':
            foreach ($radio_result as $val) {
                $radio_json            = json_decode($val, true);
                $radio_data            = $radio_json['data']['songList'];
                if (!empty($radio_data)) {
                    foreach ($radio_data as $value) {
                        $radio_song_id = $value['songId'];
                        $radio_songs[] = array(
                            'type'   => 'baidu',
                            'link'   => 'http://music.baidu.com/song/' . $radio_song_id,
                            'songid' => $radio_song_id,
                            'name'   => urldecode($value['songName']),
                            'author' => urldecode($value['artistName']),
                            'music'  => str_replace(
                                'yinyueshiting.baidu.com',
                                'gss0.bdstatic.com/y0s1hSulBw92lNKgpU_Z2jR7b2w6buu',
                                $value['songLink']
                            ),
                            'pic'    => $value['songPicBig']
                        );
                    }
                }
            }
            break;
        case 'kugou':
            foreach ($radio_result as $val) {
                $radio_data           = json_decode($val, true);
                if (!$radio_data['url'] && count($radio_result) === 1) {
                    $radio_songs      = array(
                        'error' => $radio_data['privilege'] ? '无法播放需要付费的歌曲' : '找不到可用的播放地址',
                        'code' => 403
                    );
                    break;
                }
                if (!empty($radio_data)) {
                    if (!$radio_data['url']) {
                        // 过滤无效的
                        continue;
                    }
                    $radio_song_id    = $radio_data['hash'];
                    $radio_song_album = str_replace('{size}', '150', $radio_data['album_img']);
                    $radio_song_img   = str_replace('{size}', '150', $radio_data['imgUrl']);
                    $radio_songs[]    = array(
                        'type'   => 'kugou',
                        'link'   => 'http://www.kugou.com/song/#hash=' . $radio_song_id,
                        'songid' => $radio_song_id,
                        'name'   => urldecode($radio_data['songName']),
                        'author' => urldecode($radio_data['singerName']),
                        'music'  => $radio_data['url'],
                        'pic'    => $radio_song_album ? $radio_song_album : $radio_song_img
                    );
                }
            }
            break;
        case 'kuwo':
            foreach ($radio_result as $val) {
                preg_match_all('/<([\w]+)>(.*?)<\/\\1>/i', $val, $radio_json);
                if (!empty($radio_json[1]) && !empty($radio_json[2])) {
                    $radio_data             = array();
                    foreach ($radio_json[1] as $key => $value) {
                        $radio_data[$value] = $radio_json[2][$key];
                    }
                    $radio_song_id          = $radio_data['music_id'];
                    $radio_songs[]          = array(
                        'type'   => 'kuwo',
                        'link'   => 'http://www.kuwo.cn/yinyue/' . $radio_song_id,
                        'songid' => $radio_song_id,
                        'name'   => urldecode($radio_data['name']),
                        'author' => urldecode($radio_data['singer']),
                        'music'  => 'http://' . $radio_data['mp3dl'] . '/resource/' . $radio_data['mp3path'],
                        'pic'    => $radio_data['artist_pic']
                    );
                }
            }
            break;
        case 'qq':
            $radio_vkey = json_decode(mc_curl(array(
                'method'     => 'GET',
                'url'        => 'http://base.music.qq.com/fcgi-bin/fcg_musicexpress.fcg',
                'referer'    => 'https://y.qq.com',
                'proxy'      => false,
                'body'       => array(
                    'json'   => 3,
                    'guid'   => 5150825362,
                    'format' => 'json'
                ),
                'user-agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1'
            )), true);
            foreach ($radio_result as $val) {
                $radio_json                  = json_decode($val, true);
                $radio_data                  = $radio_json['data'];
                $radio_url                   = $radio_json['url'];
                if (!empty($radio_data) && !empty($radio_url)) {
                    foreach ($radio_data as $value) {
                        $radio_song_id       = $value['mid'];
                        $radio_authors       = array();
                        foreach ($value['singer'] as $singer) {
                            $radio_authors[] = $singer['title'];
                        }
                        $radio_author        = implode('/', $radio_authors);
                        if (!empty($radio_vkey['key'])) {
                            $radio_music     = generate_qqmusic_url($radio_song_id, $radio_vkey['key']);
                        } else {
                            $radio_music     = 'https://' . str_replace('ws', 'dl', $radio_url[$value['id']]);
                        }
                        $radio_album_id      = $value['album']['mid'];
                        $radio_songs[]       = array(
                            'type'   => 'qq',
                            'link'   => 'https://y.qq.com/n/yqq/song/' . $radio_song_id . '.html',
                            'songid' => $radio_song_id,
                            'name'   => urldecode($value['title']),
                            'author' => urldecode($radio_author),
                            'music'  => $radio_music,
                            'pic'    => 'http://y.gtimg.cn/music/photo_new/T002R300x300M000' . $radio_album_id . '.jpg'
                        );
                    }
                }
            }
            break;
        case 'xiami':
            foreach ($radio_result as $val) {
                $radio_json            = json_decode($val, true);
                $radio_data            = $radio_json['data']['trackList'];
                if (!empty($radio_data)) {
                    foreach ($radio_data as $value) {
                        $radio_song_id = $value['songId'];
                        $radio_songs[] = array(
                            'type'   => 'xiami',
                            'link'   => 'http://www.xiami.com/song/' . $radio_song_id,
                            'songid' => $radio_song_id,
                            'name'   => urldecode($value['songName']),
                            'author' => urldecode($value['singers']),
                            'music'  => decode_xiami_location($value['location']),
                            'pic'    => $value['album_pic']
                        );
                    }
                }
            }
            break;
        case '5singyc':
        case '5singfc':
            foreach ($radio_result as $val) {
                $radio_json        = json_decode($val, true);
                $radio_data        = $radio_json['data'];
                if (!empty($radio_data)) {
                    $radio_song_id = $radio_data['ID'];
                    $radio_songs[] = array(
                        'type'   => $site,
                        'link'   => 'http://5sing.kugou.com/'.$radio_data['SK'] . '/' . $radio_song_id . '.html',
                        'songid' => $radio_song_id,
                        'name'   => urldecode($radio_data['SN']),
                        'author' => urldecode($radio_data['user']['NN']),
                        'music'  => $radio_data['KL'],
                        'pic'    => $radio_data['user']['I']
                    );
                }
            }
            break;
        case 'migu':
            foreach ($radio_result as $val) {
                $radio_json            = json_decode($val, true);
                $radio_data            = $radio_json['msg'];
                if (!empty($radio_data)) {
                    foreach ($radio_data as $value) {
                        $radio_song_id = $value['songId'];
                        $radio_songs[] = array(
                            'type'   => 'migu',
                            'link'   => 'http://music.migu.cn/#/song/' . $radio_song_id,
                            'songid' => $radio_song_id,
                            'name'   => urldecode($value['songName']),
                            'author' => urldecode($value['singerName']),
                            'music'  => $value['mp3'],
                            'pic'    => $value['poster']
                        );
                    }
                }
            }
            break;
        case 'lizhi':
            foreach ($radio_result as $val) {
                $radio_data            = json_decode($val, true);
                if (!empty($radio_data)) {
                    foreach ($radio_data as $value) {
                        $radio_song_id = $value['audio']['id'];
                        $radio_songs[] = array(
                            'type'   => 'lizhi',
                            'link'   => 'http://www.lizhi.fm/' . $value['radio']['band'] . '/' . $radio_song_id,
                            'songid' => $radio_song_id,
                            'name'   => urldecode($value['audio']['name']),
                            'author' => urldecode($value['radio']['name']),
                            'music'  => $value['audio']['url'],
                            'pic'    => 'http://m.lizhi.fm/radio_cover/' . $value['radio']['cover']
                        );
                    }
                }
            }
            break;
        case 'qingting':
            foreach ($radio_result as $val) {
                $radio_json           = json_decode($val, true);
                $radio_data           = $radio_json['data'];
                if (!empty($radio_data)) {
                    $radio_channels   = array(
                        'method'  => 'GET',
                        'url'     => 'http://i.qingting.fm/wapi/channels/' . $radio_data['channel_id'],
                        'referer' => 'http://www.qingting.fm',
                        'proxy'   => false,
                        'body'    => false
                    );
                    $radio_info       = json_decode(mc_curl($radio_channels), true);
                    if (!empty($radio_info) && !empty($radio_info['data'])) {
                        $radio_author = $radio_info['data']['name'];
                        $radio_pic    = $radio_info['data']['img_url'];
                    }
                    $radio_songs[]    = array(
                        'type'   => 'qingting',
                        'link'   => 'http://www.qingting.fm/channels/' . $radio_data['channel_id'] . '/programs/' . $radio_data['id'],
                        'songid' => $radio_data['channel_id'] . '|' . $radio_data['id'],
                        'name'   => urldecode($radio_data['name']),
                        'author' => urldecode($radio_author),
                        'music'  => 'http://od.qingting.fm/' . $radio_data['file_path'],
                        'pic'    => $radio_pic
                    );
                }
            }
            break;
        case 'ximalaya':
            foreach ($radio_result as $val) {
                $radio_json        = json_decode($val, true);
                $radio_data        = $radio_json['trackInfo'];
                $radio_user        = $radio_json['userInfo'];
                if (!empty($radio_data) && !empty($radio_user)) {
                    $radio_songs[] = array(
                        'type'   => 'ximalaya',
                        'link'   => 'http://www.ximalaya.com/' . $radio_data['uid'] . '/sound/' . $radio_data['trackId'],
                        'songid' => $radio_data['trackId'],
                        'name'   => urldecode($radio_data['title']),
                        'author' => urldecode($radio_user['nickname']),
                        'music'  => $radio_data['playUrl64'],
                        'pic'    => $radio_data['coverLarge']
                    );
                }
            }
            break;
        case 'soundcloud':
            foreach ($radio_result as $val) {
                $radio_data                  = json_decode($val, true);
                if (!empty($radio_data)) {
                    $radio_streams           = array(
                        'method'  => 'GET',
                        'url'     => 'https://api.soundcloud.com/i1/tracks/' . $radio_data['id'] . '/streams',
                        'referer' => 'https://soundcloud.com/',
                        'proxy'   => false,
                        'body'    => array(
                            'client_id' => MC_SC_CLIENT_ID
                        )
                    );
                    $radio_streams_info      = json_decode(mc_curl($radio_streams), true);
                    if (!empty($radio_streams_info)) {
                        $radio_music_http    = $radio_streams_info['http_mp3_128_url'];
                        $radio_music_preview = $radio_streams_info['preview_mp3_128_url'];
                        $radio_music         = $radio_music_http ? $radio_music_http : $radio_music_preview;
                    }
                    $radio_pic_artwork       = $radio_data['artwork_url'];
                    $radio_pic_avatar        = $radio_data['user']['avatar_url'];
                    $radio_pic               = $radio_pic_artwork ? $radio_pic_artwork : $radio_pic_avatar;
                    $radio_songs[]           = array(
                        'type'   => 'soundcloud',
                        'link'   => $radio_data['permalink_url'],
                        'songid' => $radio_data['id'],
                        'name'   => urldecode($radio_data['title']),
                        'author' => urldecode($radio_data['user']['username']),
                        'music'  => $radio_music,
                        'pic'    => $radio_pic
                    );
                }
            }
            break;
        case 'netease':
        default:
            foreach ($radio_result as $val) {
                $radio_json                  = json_decode($val, true);
                $radio_data                  = $radio_json['songs'];
                if (!empty($radio_data)) {
                    $radio_streams           = array(
                      'method'      => 'POST',
                      'url'         => 'http://music.163.com/api/linux/forward',
                      'referer'     => 'http://music.163.com/',
                      'proxy'       => false,
                      'body'        => encode_netease_data(array(
                          'method'  => 'POST',
                          'url'     => 'http://music.163.com/api/song/enhance/player/url',
                          'params'  => array(
                              'ids' => $songid,
                              'br'  => 320000,
                          )
                      ))
                    );
                    $radio_info              = json_decode(mc_curl($radio_streams), true);
                    foreach ($radio_data as $key => $value) {
                        $radio_song_id       = $value['id'];
                        $radio_authors       = array();
                        foreach ($value['artists'] as $key => $val) {
                            $radio_authors[] = $val['name'];
                        }
                        $radio_author        = implode('/', $radio_authors);
                        $radio_music_url     = $value['mp3Url'];
                        if (!$radio_music_url && !empty($radio_info['data'])) {
                            $radio_music_url = $radio_info['data'][$key]['url'];
                        }
                        $radio_songs[]       = array(
                            'type'   => 'netease',
                            'link'   => 'http://music.163.com/#/song?id=' . $radio_song_id,
                            'songid' => $radio_song_id,
                            'name'   => urldecode($value['name']),
                            'author' => urldecode($radio_author),
                            'music'  => $radio_music_url,
                            'pic'    => $value['album']['picUrl'] . '?param=300x300'
                        );
                    }
                }
            }
            break;
    }
    return !empty($radio_songs) ? $radio_songs : '';
}

// 获取音频信息 - url
function mc_get_song_by_url($url)
{
    preg_match('/music\.163\.com\/(#(\/m)?|m)\/song(\?id=|\/)(\d+)/i', $url, $match_netease);
    preg_match('/(www|m)\.1ting\.com\/(player\/b6\/player_|#\/song\/)(\d+)/i', $url, $match_1ting);
    preg_match('/music\.baidu\.com\/song\/(\d+)/i', $url, $match_baidu);
    preg_match('/(m|www)\.kugou\.com\/(play\/info\/|song\/\#hash\=)([a-z0-9]+)/i', $url, $match_kugou);
    preg_match('/www\.kuwo\.cn\/(yinyue|my)\/(\d+)/i', $url, $match_kuwo);
    preg_match('/(y\.qq\.com\/n\/yqq\/song\/|data\.music\.qq\.com\/playsong\.html\?songmid=)([a-zA-Z0-9]+)/i', $url, $match_qq);
    preg_match('/(www|m)\.xiami\.com\/song\/(\d+)/i', $url, $match_xiami);
    preg_match('/5sing\.kugou\.com\/(m\/detail\/|)yc(-|\/)(\d+)/i', $url, $match_5singyc);
    preg_match('/5sing\.kugou\.com\/(m\/detail\/|)fc(-|\/)(\d+)/i', $url, $match_5singfc);
    preg_match('/music\.migu\.cn\/#\/song\/(\d+)/i', $url, $match_migu);
    preg_match('/(www|m)\.lizhi\.fm\/(\d+)\/(\d+)/i', $url, $match_lizhi);
    preg_match('/(www|m)\.qingting\.fm\/channels\/(\d+)\/programs\/(\d+)/i', $url, $match_qingting);
    preg_match('/(www|m)\.ximalaya\.com\/(\d+)\/sound\/(\d+)/i', $url, $match_ximalaya);
    preg_match('/soundcloud\.com\/[\w\-]+\/[\w\-]+/i', $url, $match_soundcloud);
    if (!empty($match_netease)) {
        $songid   = $match_netease[4];
        $songtype = 'netease';
    } elseif (!empty($match_1ting)) {
        $songid   = $match_1ting[3];
        $songtype = '1ting';
    } elseif (!empty($match_baidu)) {
        $songid   = $match_baidu[1];
        $songtype = 'baidu';
    } elseif (!empty($match_kugou)) {
        $songid   = $match_kugou[3];
        $songtype = 'kugou';
    } elseif (!empty($match_kuwo)) {
        $songid   = $match_kuwo[2];
        $songtype = 'kuwo';
    } elseif (!empty($match_qq)) {
        $songid   = $match_qq[2];
        $songtype = 'qq';
    } elseif (!empty($match_xiami)) {
        $songid   = $match_xiami[2];
        $songtype = 'xiami';
    } elseif (!empty($match_5singyc)) {
        $songid   = $match_5singyc[3];
        $songtype = '5singyc';
    } elseif (!empty($match_5singfc)) {
        $songid   = $match_5singfc[3];
        $songtype = '5singfc';
    } elseif (!empty($match_migu)) {
        $songid   = $match_migu[1];
        $songtype = 'migu';
    } elseif (!empty($match_lizhi)) {
        $songid   = $match_lizhi[3];
        $songtype = 'lizhi';
    } elseif (!empty($match_qingting)) {
        $songid   = $match_qingting[2].'|'.$match_qingting[3];
        $songtype = 'qingting';
    } elseif (!empty($match_ximalaya)) {
        $songid   = $match_ximalaya[3];
        $songtype = 'ximalaya';
    } elseif (!empty($match_soundcloud)) {
        $match_resolve = array(
            'method'        => 'GET',
            'url'           => 'http://api.soundcloud.com/resolve.json',
            'referer'       => 'https://soundcloud.com/',
            'proxy'         => false,
            'body'          => array(
                'url'       => $match_soundcloud[0],
                'client_id' => MC_SC_CLIENT_ID
            )
        );
        $match_request = mc_curl($match_resolve);
        preg_match('/tracks\/(\d+)\.json/i', $match_request, $match_location);
        if (!empty($match_location)) {
            $songid   = $match_location[1];
            $songtype = 'soundcloud';
        }
    } else {
        return;
    }
    return mc_get_song_by_id($songid, $songtype);
}

// 解密虾米 location
function decode_xiami_location($location)
{
    $location     = trim($location);
    $result       = array();
    $line         = intval($location[0]);
    $locLen       = strlen($location);
    $rows         = intval(($locLen - 1) / $line);
    $extra        = ($locLen - 1) % $line;
    $location     = substr($location, 1);
    for ($i       = 0; $i < $extra; ++$i) {
        $start    = ($rows + 1) * $i;
        $end      = ($rows + 1) * ($i + 1);
        $result[] = substr($location, $start, $end - $start);
    }
    for ($i       = 0; $i < $line - $extra; ++$i) {
        $start    = ($rows + 1) * $extra + ($rows * $i);
        $end      = ($rows + 1) * $extra + ($rows * $i) + $rows;
        $result[] = substr($location, $start, $end - $start);
    }
    $url          = '';
    for ($i       = 0; $i < $rows + 1; ++$i) {
        for ($j   = 0; $j < $line; ++$j) {
            if ($j >= count($result) || $i >= strlen($result[$j])) {
                continue;
            }
            $url .= $result[$j][$i];
        }
    }
    $url          = urldecode($url);
    $url          = str_replace('^', '0', $url);
    return $url;
}

// 加密网易云音乐 api 参数
function encode_netease_data($data)
{
    $_key     = '7246674226682325323F5E6544673A51';
    $data     = json_encode($data);
    if (function_exists('openssl_encrypt')) {
        $data = openssl_encrypt($data, 'aes-128-ecb', pack('H*', $_key));
    } else {
        $_pad = 16 - (strlen($data) % 16);
        $data = base64_encode(mcrypt_encrypt(
            MCRYPT_RIJNDAEL_128,
            hex2bin($_key),
            $data.str_repeat(chr($_pad), $_pad),
            MCRYPT_MODE_ECB
        ));
    }
    $data     = strtoupper(bin2hex(base64_decode($data)));
    return array('eparams' => $data);
}

// 分割 songid 并获取
function split_songid ($songid, $index = 0, $delimiter = '|') {
    if (mb_strpos($songid, $delimiter, 0, 'UTF-8') > 0) {
        $array = explode($delimiter, $songid);
        if (count($array) > 1) {
            return $array[$index];
        }
    }
    return;
}

// 生成 QQ 音乐各品质链接
function generate_qqmusic_url ($songmid, $key) {
    $quality = array('M800', 'M500', 'C600', 'C400', 'C100');
    foreach ($quality as $value) {
        $url = 'https://dl.stream.qqmusic.qq.com/' . $value . $songmid . '.mp3?vkey=' . $key . '&guid=5150825362&fromtag=1';
        if (!mc_is_error($url)) {
            return $url;
        }
    }
}

// Server
function server($key)
{
    return isset($_SERVER[$key]) ? $_SERVER[$key] : null;
}

// Post
function post($key)
{
    return isset($_POST[$key]) ? $_POST[$key] : null;
}

// Response
function response($data, $code = 200, $error = '')
{
    header('Content-type:text/json; charset=utf-8');
    echo json_encode(array(
        'data'  => $data,
        'code'  => $code,
        'error' => $error
    ));
    exit();
}
