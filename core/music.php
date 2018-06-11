<?php
/**
 *
 * 音乐搜索器 - 函数声明
 *
 * @author  MaiCong <i@maicong.me>
 * @link    https://github.com/maicong/music
 * @since   1.6.1
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
function mc_curl($args = [])
{
    $default = [
        'method'     => 'GET',
        'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.50 Safari/537.36',
        'url'        => null,
        'referer'    => 'https://www.google.co.uk',
        'headers'    => null,
        'body'       => null,
        'proxy'      => false
    ];
    $args         = array_merge($default, $args);
    $method       = mb_strtolower($args['method']);
    $method_allow = ['get', 'post'];
    if (null === $args['url'] || !in_array($method, $method_allow, true)) {
        return;
    }
    $curl = new Curl();
    $curl->setUserAgent($args['user-agent']);
    $curl->setReferrer($args['referer']);
    $curl->setTimeout(15);
    $curl->setHeader('X-Requested-With', 'XMLHttpRequest');
    $curl->setOpt(CURLOPT_FOLLOWLOCATION, true);
    if ($args['proxy'] && MC_PROXY) {
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
function mc_is_error($url) {
    $curl = new Curl();
    $curl->setUserAgent('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.50 Safari/537.36');
    $curl->head($url);
    $curl->close();
    return $curl->errorCode;
}

// 音频数据接口地址
function mc_song_urls($value, $type = 'query', $site = 'netease', $page = 1)
{
    if (!$value) {
        return;
    }
    $query             = ('query' === $type) ? $value : '';
    $songid            = ('songid' === $type || 'lrc' === $type) ? $value : '';
    $radio_search_urls = [
        'netease'            => [
            'method'         => 'POST',
            'url'            => 'http://music.163.com/api/linux/forward',
            'referer'        => 'http://music.163.com/',
            'proxy'          => false,
            'body'           => encode_netease_data([
                'method'     => 'POST',
                'url'        => 'http://music.163.com/api/cloudsearch/pc',
                'params'     => [
                    's'      => $query,
                    'type'   => 1,
                    'offset' => $page * 10 - 10,
                    'limit'  => 10
                ]
            ])
        ],
        '1ting'              => [
            'method'         => 'GET',
            'url'            => 'http://so.1ting.com/song/json',
            'referer'        => 'http://h5.1ting.com/',
            'proxy'          => false,
            'body'           => [
                'q'          => $query,
                'page'       => $page,
                'size'       => 10
            ]
        ],
        'baidu'              => [
            'method'         => 'GET',
            'url'            => 'http://musicapi.qianqian.com/v1/restserver/ting',
            'referer'        => 'http://music.baidu.com/',
            'proxy'          => false,
            'body'           => [
                'method'    => 'baidu.ting.search.common',
                'query'     => $query,
                'format'    => 'json',
                'page_no'   => $page,
                'page_size' => 10
            ]
        ],
        'kugou'              => [
            'method'         => 'GET',
            'url'            => 'http://mobilecdn.kugou.com/api/v3/search/song',
            'referer'        => 'http://m.kugou.com/v2/static/html/search.html',
            'proxy'          => false,
            'body'           => [
                'keyword'    => $query,
                'format'     => 'json',
                'page'       => $page,
                'pagesize'   => 10
            ],
            'user-agent'     => 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1'
        ],
        'kuwo'               => [
            'method'         => 'GET',
            'url'            => 'http://search.kuwo.cn/r.s',
            'referer'        => 'http://player.kuwo.cn/webmusic/play',
            'proxy'          => false,
            'body'           => [
                'all'        => $query,
                'ft'         => 'music',
                'itemset'    => 'web_2013',
                'pn'         => $page - 1,
                'rn'         => 10,
                'rformat'    => 'json',
                'encoding'   => 'utf8'
            ]
        ],
        'qq'                 => [
            'method'         => 'GET',
            'url'            => 'http://c.y.qq.com/soso/fcgi-bin/search_for_qq_cp',
            'referer'        => 'http://m.y.qq.com',
            'proxy'          => false,
            'body'           => [
                'w'          => $query,
                'p'          => $page,
                'n'          => 10,
                'format'     => 'json'
            ],
            'user-agent'     => 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1'
        ],
        'xiami'              => [
            'method'         => 'GET',
            'url'            => 'http://api.xiami.com/web',
            'referer'        => 'http://m.xiami.com',
            'proxy'          => false,
            'body'           => [
                'key'        => $query,
                'v'          => '2.0',
                'app_key'    => '1',
                'r'          => 'search/songs',
                'page'       => $page,
                'limit'      => 10
            ],
            'user-agent'     => 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1'
        ],
        '5singyc'            => [
            'method'         => 'GET',
            'url'            => 'http://goapi.5sing.kugou.com/search/search',
            'referer'        => 'http://5sing.kugou.com/',
            'proxy'          => false,
            'body'           => [
                'k'          => $query,
                't'          => '0',
                'filterType' => '1',
                'ps'         => 10,
                'pn'         => $page
            ]
        ],
        '5singfc'            => [
            'method'         => 'GET',
            'url'            => 'http://goapi.5sing.kugou.com/search/search',
            'referer'        => 'http://5sing.kugou.com/',
            'proxy'          => false,
            'body'           => [
                'k'          => $query,
                't'          => '0',
                'filterType' => '2',
                'ps'         => 10,
                'pn'         => 1
            ]
        ],
        'migu'               => [
            'method'         => 'GET',
            'url'            => 'http://m.10086.cn/migu/remoting/scr_search_tag',
            'referer'        => 'http://m.10086.cn',
            'proxy'          => false,
            'body'           => [
                'keyword'    => $query,
                'type'       => '2',
                'pgc'        => $page,
                'rows'       => 10
            ],
            'user-agent'    => 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1'
        ],
        'lizhi'              => [
            'method'         => 'GET',
            'url'            => 'http://m.lizhi.fm/api/search_audio/' . urlencode($query) . '/' . $page,
            'referer'        => 'http://m.lizhi.fm',
            'proxy'          => false,
            'body'           => false,
            'user-agent'     => 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1'
        ],
        'qingting'           => [
            'method'         => 'GET',
            'url'            => 'http://i.qingting.fm/wapi/search',
            'referer'        => 'http://www.qingting.fm',
            'proxy'          => false,
            'body'           => [
                'k'          => $query,
                'page'       => $page,
                'pagesize'   => 10,
                'include'    => 'program_ondemand',
                'groups'     => 'program_ondemand'
            ]
        ],
        'ximalaya'           => [
            'method'         => 'GET',
            'url'            => 'http://search.ximalaya.com/front/v1',
            'referer'        => 'http://www.ximalaya.com',
            'proxy'          => false,
            'body'           => [
                'kw'         => $query,
                'core'       => 'all',
                'page'       => $page,
                'rows'       => 10,
                'is_paid'    => false
            ]
        ],
        'kg'                 => [
            'method'         => 'GET',
            'url'            => 'http://kg.qq.com/cgi/kg_ugc_get_homepage',
            'referer'        => 'http://kg.qq.com',
            'proxy'          => false,
            'body'           => [
                'format'     => 'json',
                'type'       => 'get_ugc',
                'inCharset'  => 'utf8',
                'outCharset' => 'utf-8',
                'share_uid'  => $query,
                'start'      => $page,
                'num'        => 10
            ]
        ]
    ];
    $radio_song_urls = [
        'netease'           => [
            'method'        => 'POST',
            'url'           => 'http://music.163.com/api/linux/forward',
            'referer'       => 'http://music.163.com/',
            'proxy'         => false,
            'body'          => encode_netease_data([
                'method'    => 'GET',
                'url'       => 'http://music.163.com/api/song/detail',
                'params'    => [
                  'id'      => $songid,
                  'ids'     => '[' . $songid . ']'
                ]
            ])
        ],
        '1ting'             => [
            'method'        => 'GET',
            'url'           => 'http://h5.1ting.com/touch/api/song',
            'referer'       => 'http://h5.1ting.com/#/song/' . $songid,
            'proxy'         => false,
            'body'          => [
                'ids'       => $songid
            ]
        ],
        'baidu'             => [
            'method'        => 'GET',
            'url'           => 'http://music.baidu.com/data/music/links',
            'referer'       => 'music.baidu.com/song/' . $songid,
            'proxy'         => false,
            'body'          => [
                'songIds'   => $songid
            ]
        ],
        'kugou'             => [
            'method'        => 'GET',
            'url'           => 'http://m.kugou.com/app/i/getSongInfo.php',
            'referer'       => 'http://m.kugou.com/play/info/' . $songid,
            'proxy'         => false,
            'body'          => [
                'cmd'       => 'playInfo',
                'hash'      => $songid
            ],
            'user-agent'    => 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1'
        ],
        'kuwo'              => [
            'method'        => 'GET',
            'url'           => 'http://player.kuwo.cn/webmusic/st/getNewMuiseByRid',
            'referer'       => 'http://player.kuwo.cn/webmusic/play',
            'proxy'         => false,
            'body'          => [
                'rid'       => 'MUSIC_' . $songid
            ]
        ],
        'qq'                => [
            'method'        => 'GET',
            'url'           => 'http://c.y.qq.com/v8/fcg-bin/fcg_play_single_song.fcg',
            'referer'       => 'http://m.y.qq.com',
            'proxy'         => false,
            'body'          => [
                'songmid'   => $songid,
                'format'    => 'json'
            ],
            'user-agent'    => 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1'
        ],
        'xiami'             => [
            'method'        => 'GET',
            'url'           => 'http://www.xiami.com/song/playlist/id/' . $songid . '/type/0/cat/json',
            'referer'       => 'http://www.xiami.com',
            'proxy'         => false
        ],
        '5singyc'           => [
            'method'        => 'GET',
            'url'           => 'http://mobileapi.5sing.kugou.com/song/newget',
            'referer'       => 'http://5sing.kugou.com/yc/' . $songid . '.html',
            'proxy'         => false,
            'body'          => [
                'songid'    => $songid,
                'songtype'  => 'yc'
            ]
        ],
        '5singfc'           => [
            'method'        => 'GET',
            'url'           => 'http://mobileapi.5sing.kugou.com/song/newget',
            'referer'       => 'http://5sing.kugou.com/fc/' . $songid . '.html',
            'proxy'         => false,
            'body'          => [
                'songid'    => $songid,
                'songtype'  => 'fc'
            ]
        ],
        'migu'              => [
            'method'        => 'GET',
            'url'           => MC_INTERNAL ? 'http://music.migu.cn/v2/async/audioplayer/playurl/' . $songid : 'http://m.10086.cn/migu/remoting/cms_detail_tag',
            'referer'       => 'http://m.10086.cn',
            'proxy'         => false,
            'body'          => MC_INTERNAL ? false : [
                'cid'    => $songid
            ],
            'user-agent'    => 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1'
        ],
        'lizhi'             => [
            'method'        => 'GET',
            'url'           => 'http://m.lizhi.fm/api/audios_with_radio',
            'referer'       => 'http://m.lizhi.fm',
            'proxy'         => false,
            'body'          => false,
            'user-agent'    => 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1'
        ],
        'qingting'          => [
            'method'        => 'GET',
            'url'           => 'http://i.qingting.fm/wapi/channels/' . split_songid($songid, 0) . '/programs/' . split_songid($songid, 1),
            'referer'       => 'http://www.qingting.fm',
            'proxy'         => false,
            'body'          => false
        ],
        'ximalaya'          => [
            'method'        => 'GET',
            'url'           => 'http://mobile.ximalaya.com/v1/track/ca/playpage/' . $songid,
            'referer'       => 'http://www.ximalaya.com',
            'proxy'         => false,
            'body'          => false
        ],
        'kg'                => [
            'method'        => 'GET',
            'url'           => 'http://kg.qq.com/cgi/kg_ugc_getdetail',
            'referer'       => 'http://kg.qq.com',
            'proxy'         => false,
            'body'          => [
                'v'          => 4,
                'format'     => 'json',
                'inCharset'  => 'utf8',
                'outCharset' => 'utf-8',
                'shareid'    => $songid
            ]
        ]
    ];
    $radio_lrc_urls = [
        'netease'           => [
            'method'        => 'POST',
            'url'           => 'http://music.163.com/api/linux/forward',
            'referer'       => 'http://music.163.com/',
            'proxy'         => false,
            'body'          => encode_netease_data([
                'method'    => 'GET',
                'url'       => 'http://music.163.com/api/song/lyric',
                'params'    => [
                  'id' => $songid,
                  'lv' => 1
                ]
            ])
        ],
        '1ting'             => [
            'method'        => 'GET',
            'url'           => 'http://www.1ting.com/api/geci/lrc/' . $songid,
            'referer'       => 'http://www.1ting.com/geci' . $songid . '.html',
            'proxy'         => false,
            'body'          => false
        ],
        'baidu'             => [
            'method'        => 'GET',
            'url'           => 'http://musicapi.qianqian.com/v1/restserver/ting',
            'referer'       => 'http://music.baidu.com/song/' . $songid,
            'proxy'         => false,
            'body'          => [
                'method' => 'baidu.ting.song.lry',
                'songid' => $songid,
                'format' => 'json'
            ]
        ],
        'kugou'             => [
            'method'        => 'GET',
            'url'           => 'http://m.kugou.com/app/i/krc.php',
            'referer'       => 'http://m.kugou.com/play/info/' . $songid,
            'proxy'         => false,
            'body'          => [
                'cmd'        => 100,
                'timelength' => 999999,
                'hash'       => $songid
            ],
            'user-agent'    => 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X] AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1'
        ],
        'kuwo'              => [
            'method'        => 'GET',
            'url'           => 'http://m.kuwo.cn/newh5/singles/songinfoandlrc',
            'referer'       => 'http://m.kuwo.cn/yinyue/' . $songid,
            'proxy'         => false,
            'body'          => [
                'musicId' => $songid
            ],
            'user-agent'    => 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1'
        ],
        'qq'                => [
            'method'        => 'GET',
            'url'           => 'http://c.y.qq.com/lyric/fcgi-bin/fcg_query_lyric.fcg',
            'referer'       => 'http://m.y.qq.com',
            'proxy'         => false,
            'body'          => [
                'songmid'   => $songid,
                'format'    => 'json',
                'nobase64'  => 1,
                'songtype'  => 0,
                'callback'  => 'c'
            ],
            'user-agent'    => 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1'
        ],
        'xiami'             => [
            'method'        => 'GET',
            'url'           => $songid,
            'referer'       => 'http://www.xiami.com',
            'proxy'         => false
        ],
        'kg'                => [
            'method'        => 'GET',
            'url'           => 'http://kg.qq.com/cgi/fcg_lyric',
            'referer'       => 'http://kg.qq.com',
            'proxy'         => false,
            'body'          => [
                'format'     => 'json',
                'inCharset'  => 'utf8',
                'outCharset' => 'utf-8',
                'ksongmid'   => $songid
            ]
        ]
    ];
    if ('query' === $type) {
        return $radio_search_urls[$site];
    }
    if ('songid' === $type) {
        return $radio_song_urls[$site];
    }
    if ('lrc' === $type) {
        return $radio_lrc_urls[$site];
    }
    return;
}

// 获取音频信息 - 关键词搜索
function mc_get_song_by_name($query, $site = 'netease', $page = 1)
{
    if (!$query) {
        return;
    }
    $radio_search_url = mc_song_urls($query, 'query', $site, $page);
    if (empty($query) || empty($radio_search_url)) {
        return;
    }
    $radio_result = mc_curl($radio_search_url);
    if (empty($radio_result)) {
        return;
    }
    $radio_songid = [];
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
            if (empty($radio_data['song_list'])) {
                return;
            }
            foreach ($radio_data['song_list'] as $val) {
                $radio_songid[] = $val['song_id'];
            }
            break;
        case 'kugou':
            $radio_data = json_decode($radio_result, true);
            if (empty($radio_data['data']) || empty($radio_data['data']['info'])) {
                return;
            }
            foreach ($radio_data['data']['info'] as $val) {
                $radio_songid[] = $val['320hash'] ?: $val['hash'];
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
            if (empty($radio_data['musics'])) {
                return;
            }
            foreach ($radio_data['musics'] as $val) {
                $radio_songid[] = $val['id'];
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
        case 'kg':
            $radio_data = json_decode($radio_result, true);
            if (empty($radio_data['data']['ugclist'])) {
                return;
            }
            foreach ($radio_data['data']['ugclist'] as $val) {
                $radio_songid[] = $val['shareid'];
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
    $radio_song_urls = [];
    $site_allow_multiple = [
        'netease',
        '1ting',
        'baidu',
        'qq',
        'xiami',
        'lizhi'
    ];
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
    $radio_result = [];
    foreach ($radio_song_urls as $key => $val) {
        $radio_result[] = mc_curl($val);
    }
    if (empty($radio_result) || !array_key_exists(0, $radio_result)) {
        return;
    }
    $radio_songs = [];
    switch ($site) {
        case '1ting':
            foreach ($radio_result as $val) {
                $radio_data             = json_decode($val, true);
                if (!empty($radio_data)) {
                    foreach ($radio_data as $value) {
                        $radio_song_id  = $value['song_id'];
                        $radio_lrc_urls = mc_song_urls($radio_song_id, 'lrc', $site);
                        if ($radio_lrc_urls) {
                            $radio_lrc  = mc_curl($radio_lrc_urls);
                        }
                        $radio_songs[]  = [
                            'type'   => '1ting',
                            'link'   => 'http://www.1ting.com/player/6c/player_' . $radio_song_id . '.html',
                            'songid' => $radio_song_id,
                            'title'  => $value['song_name'],
                            'author' => $value['singer_name'],
                            'lrc'    => $radio_lrc,
                            'url'    => 'http://h5.1ting.com/file?url=' . str_replace('.wma', '.mp3', $value['song_filepath']),
                            'pic'    => 'http://img.store.sogou.com/net/a/link?&appid=100520102&w=500&h=500&url=' . $value['album_cover']
                        ];
                    }
                }
            }
            break;
        case 'baidu':
            foreach ($radio_result as $val) {
                $radio_json             = json_decode($val, true);
                $radio_data             = $radio_json['data']['songList'];
                if (!empty($radio_data)) {
                    foreach ($radio_data as $value) {
                        $radio_song_id  = $value['songId'];
                        $radio_lrc_urls = mc_song_urls($radio_song_id, 'lrc', $site);
                        if ($radio_lrc_urls) {
                            $radio_lrc  = json_decode(mc_curl($radio_lrc_urls), true);
                        }
                        $radio_songs[]  = [
                            'type'   => 'baidu',
                            'link'   => 'http://music.baidu.com/song/' . $radio_song_id,
                            'songid' => $radio_song_id,
                            'title'  => $value['songName'],
                            'author' => $value['artistName'],
                            'lrc'    => $radio_lrc['lrcContent'],
                            'url'    => str_replace(
                                [
                                    'yinyueshiting.baidu.com',
                                    'zhangmenshiting.baidu.com',
                                    'zhangmenshiting.qianqian.com'
                                ],
                                'gss0.bdstatic.com/y0s1hSulBw92lNKgpU_Z2jR7b2w6buu',
                                $value['songLink']
                            ),
                            'pic'    => $value['songPicBig']
                        ];
                    }
                }
            }
            break;
        case 'kugou':
            foreach ($radio_result as $val) {
                $radio_data           = json_decode($val, true);
                if (!empty($radio_data)) {
                    if (!$radio_data['url']) {
                        if (count($radio_result) === 1) {
                            $radio_songs      = [
                                'error' => $radio_data['privilege'] ? '源站反馈此音频需要付费' : '找不到可用的播放地址',
                                'code' => 403
                            ];
                            break;
                        }
                        // 过滤无效的
                        continue;
                    }
                    $radio_song_id    = $radio_data['hash'];
                    $radio_song_album = str_replace('{size}', '150', $radio_data['album_img']);
                    $radio_song_img   = str_replace('{size}', '150', $radio_data['imgUrl']);
                    $radio_lrc_urls   = mc_song_urls($radio_song_id, 'lrc', $site);
                    if ($radio_lrc_urls) {
                        $radio_lrc    = mc_curl($radio_lrc_urls);
                    }
                    $radio_songs[]    = [
                        'type'   => 'kugou',
                        'link'   => 'http://www.kugou.com/song/#hash=' . $radio_song_id,
                        'songid' => $radio_song_id,
                        'title'  => $radio_data['songName'],
                        'author' => $radio_data['singerName'],
                        'lrc'    => $radio_lrc,
                        'url'    => $radio_data['url'],
                        'pic'    => $radio_song_album ?: $radio_song_img
                    ];
                }
            }
            break;
        case 'kuwo':
            foreach ($radio_result as $val) {
                preg_match_all('/<([\w]+)>(.*?)<\/\\1>/i', $val, $radio_json);
                if (!empty($radio_json[1]) && !empty($radio_json[2])) {
                    $radio_data             = [];
                    foreach ($radio_json[1] as $key => $value) {
                        $radio_data[$value] = $radio_json[2][$key];
                    }
                    $radio_song_id          = $radio_data['music_id'];
                    $radio_lrc_urls         = mc_song_urls($radio_song_id, 'lrc', $site);
                    if ($radio_lrc_urls) {
                        $radio_lrc_info     = json_decode(mc_curl($radio_lrc_urls), true);
                    }
                    $radio_lrclist          = $radio_lrc_info['data']['lrclist'];
                    $radio_songs[]          = [
                        'type'   => 'kuwo',
                        'link'   => 'http://www.kuwo.cn/yinyue/' . $radio_song_id,
                        'songid' => $radio_song_id,
                        'title'  => $radio_data['name'],
                        'author' => $radio_data['singer'],
                        'lrc'    => generate_kuwo_lrc($radio_lrclist),
                        'url'    => 'http://' . $radio_data['mp3dl'] . '/resource/' . $radio_data['mp3path'],
                        'pic'    => $radio_data['artist_pic']
                    ];
                }
            }
            break;
        case 'qq':
            $radio_vkey = json_decode(mc_curl([
                'method'     => 'GET',
                'url'        => 'http://base.music.qq.com/fcgi-bin/fcg_musicexpress.fcg',
                'referer'    => 'http://y.qq.com',
                'proxy'      => false,
                'body'       => [
                    'json'   => 3,
                    'guid'   => 5150825362,
                    'format' => 'json'
                ]
            ]), true);
            foreach ($radio_result as $val) {
                $radio_json                  = json_decode($val, true);
                $radio_data                  = $radio_json['data'];
                $radio_url                   = $radio_json['url'];
                if (!empty($radio_data) && !empty($radio_url)) {
                    foreach ($radio_data as $value) {
                        $radio_song_id       = $value['mid'];
                        $radio_authors       = [];
                        foreach ($value['singer'] as $singer) {
                            $radio_authors[] = $singer['title'];
                        }
                        $radio_author        = implode(',', $radio_authors);
                        $radio_lrc_urls      = mc_song_urls($radio_song_id, 'lrc', $site);
                        if ($radio_lrc_urls) {
                            $radio_lrc       = jsonp2json(mc_curl($radio_lrc_urls));
                        }
                        $radio_music         = 'http://' . str_replace('ws', 'dl', $radio_url[$value['id']]);
                        if (!empty($radio_vkey['key'])) {
                            $radio_music     = generate_qqmusic_url(
                                $radio_song_id,
                                $radio_vkey['key']
                            ) ?: $radio_music;
                        }
                        $radio_album_id      = $value['album']['mid'];
                        $radio_songs[]       = [
                            'type'   => 'qq',
                            'link'   => 'http://y.qq.com/n/yqq/song/' . $radio_song_id . '.html',
                            'songid' => $radio_song_id,
                            'title'  => $value['title'],
                            'author' => $radio_author,
                            'lrc'    => str_decode($radio_lrc['lyric']),
                            'url'    => $radio_music,
                            'pic'    => 'http://y.gtimg.cn/music/photo_new/T002R300x300M000' . $radio_album_id . '.jpg'
                        ];
                    }
                }
            }
            break;
        case 'xiami':
            foreach ($radio_result as $val) {
                $radio_json                 = json_decode($val, true);
                $radio_data                 = $radio_json['data']['trackList'];
                if (!empty($radio_data)) {
                    foreach ($radio_data as $value) {
                        $radio_lrc          = '';
                        $radio_song_id      = $value['songId'];
                        if ($value['lyric']) {
                            $radio_lrc_urls = mc_song_urls($value['lyric'], 'lrc', $site);
                            if ($radio_lrc_urls) {
                                $radio_lrc  = mc_curl($radio_lrc_urls);
                            }
                        }
                        $radio_songs[]      = [
                            'type'   => 'xiami',
                            'link'   => 'http://www.xiami.com/song/' . $radio_song_id,
                            'songid' => $radio_song_id,
                            'title'  => $value['songName'],
                            'author' => $value['singers'],
                            'lrc'    => $radio_lrc,
                            'url'    => decode_xiami_location($value['location']),
                            'pic'    => $value['album_pic']
                        ];
                    }
                } else {
                    if ($radio_json['message']) {
                        $radio_songs        = [
                            'error' => $radio_json['message'],
                            'code' => 403
                        ];
                        break;
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
                    $radio_songs[] = [
                        'type'   => $site,
                        'link'   => 'http://5sing.kugou.com/'.$radio_data['SK'] . '/' . $radio_song_id . '.html',
                        'songid' => $radio_song_id,
                        'title'  => $radio_data['SN'],
                        'author' => $radio_data['user']['NN'],
                        'lrc'    => $radio_data['dynamicWords'],
                        'url'    => $radio_data['KL'],
                        'pic'    => $radio_data['user']['I']
                    ];
                }
            }
            break;
        case 'migu':
            foreach ($radio_result as $val) {
                if (MC_INTERNAL) {
                    $radio_data = json_decode($val, true);
                    if (!empty($radio_data)) {
                        $radio_song_id       = $radio_data['musicId'];
                        $radio_authors       = [];
                        foreach ($radio_data['artistInfoList'] as $author) {
                            $radio_authors[] = $author['artistName'];
                        }
                        $radio_author        = implode(',', $radio_authors);
                        $radio_songs[] = [
                            'type'   => 'migu',
                            'link'   => 'http://music.migu.cn/v2/music/song/' . $radio_song_id,
                            'songid' => $radio_song_id,
                            'title'  => $radio_data['musicName'],
                            'author' => $radio_author,
                            'lrc'    => $radio_data['dynamicLyric'],
                            'url'    => $radio_data['songAuditionUrl'],
                            'pic'    => $radio_data['smallPic']
                        ];
                    }
                } else {
                    $radio_json = json_decode($val, true);
                    $radio_data = $radio_json['data'];
                    if (!empty($radio_data)) {
                        $radio_song_id = $radio_data['songId'];
                        $radio_author  = implode(',', $radio_data['singerName']);
                        $radio_songs[] = [
                            'type'   => 'migu',
                            'link'   => 'http://music.migu.cn/v2/music/song/' . $radio_song_id,
                            'songid' => $radio_song_id,
                            'title'  => $radio_data['songName'],
                            'author' => $radio_author,
                            'lrc'    => $radio_data['lyricLrc'],
                            'url'    => $radio_data['listenUrl'] ?: $radio_data['sst']['listenUrl'],
                            'pic'    => $radio_data['picL']
                        ];
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
                        $radio_streams = [
                            'method'  => 'GET',
                            'url'     => 'http://www.lizhi.fm/media/url/' . $radio_song_id,
                            'referer' => 'http://www.lizhi.fm',
                            'proxy'   => false,
                            'body'    => false
                        ];
                        $radio_info = json_decode(mc_curl($radio_streams), true);
                        $radio_songs[] = [
                            'type'   => 'lizhi',
                            'link'   => 'http://www.lizhi.fm/' . $value['radio']['band'] . '/' . $radio_song_id,
                            'songid' => $radio_song_id,
                            'title'  => $value['audio']['name'],
                            'author' => $value['radio']['name'],
                            'lrc'    => '',
                            'url'    => $radio_info ? $radio_info['data']['url'] : null,
                            'pic'    => 'http://m.lizhi.fm/radio_cover/' . $value['radio']['cover']
                        ];
                    }
                }
            }
            break;
        case 'qingting':
            foreach ($radio_result as $val) {
                $radio_json           = json_decode($val, true);
                $radio_data           = $radio_json['data'];
                if (!empty($radio_data)) {
                    $radio_channels   = [
                        'method'  => 'GET',
                        'url'     => 'http://i.qingting.fm/wapi/channels/' . $radio_data['channel_id'],
                        'referer' => 'http://www.qingting.fm',
                        'proxy'   => false,
                        'body'    => false
                    ];
                    $radio_info       = json_decode(mc_curl($radio_channels), true);
                    if (!empty($radio_info) && !empty($radio_info['data'])) {
                        $radio_author = $radio_info['data']['name'];
                        $radio_pic    = $radio_info['data']['img_url'];
                    }
                    $radio_songs[]    = [
                        'type'   => 'qingting',
                        'link'   => 'http://www.qingting.fm/channels/' . $radio_data['channel_id'] . '/programs/' . $radio_data['id'],
                        'songid' => $radio_data['channel_id'] . '|' . $radio_data['id'],
                        'title'  => $radio_data['name'],
                        'author' => $radio_author,
                        'lrc'    => '',
                        'url'    => 'http://od.qingting.fm/' . $radio_data['file_path'],
                        'pic'    => $radio_pic
                    ];
                }
            }
            break;
        case 'ximalaya':
            foreach ($radio_result as $val) {
                $radio_json        = json_decode($val, true);
                $radio_data        = $radio_json['trackInfo'];
                $radio_user        = $radio_json['userInfo'];
                if (!empty($radio_data) && !empty($radio_user)) {
                    if ($radio_data['isPaid']) {
                        $radio_songs = [
                            'error' => '源站反馈此音频需要付费',
                            'code' => 403
                        ];
                        break;
                    }
                    $radio_songs[] = [
                        'type'   => 'ximalaya',
                        'link'   => 'http://www.ximalaya.com/' . $radio_data['uid'] . '/sound/' . $radio_data['trackId'],
                        'songid' => $radio_data['trackId'],
                        'title'  => $radio_data['title'],
                        'author' => $radio_user['nickname'],
                        'lrc'    => '',
                        'url'    => $radio_data['playUrl64'],
                        'pic'    => $radio_data['coverLarge']
                    ];
                }
            }
            break;
        case 'kg':
            foreach ($radio_result as $key => $val) {
                $radio_json        = json_decode($val, true);
                $radio_data        = $radio_json['data'];
                if (!empty($radio_data)) {
                    $radio_song_id      = is_array($songid) ? $songid[$key] : $songid;
                    $radio_lrc_urls     = mc_song_urls($radio_data['ksong_mid'], 'lrc', $site);
                    if ($radio_lrc_urls) {
                        $radio_lrc_info = json_decode(mc_curl($radio_lrc_urls), true);
                    }
                    $radio_songs[] = [
                        'type'   => 'kg',
                        'link'   => 'https://kg.qq.com/node/play?s=' . $radio_song_id . '&shareuid='. $radio_data['uid'],
                        'songid' => $radio_song_id,
                        'title'  => $radio_data['song_name'],
                        'author' => $radio_data['nick'],
                        'lrc'    => $radio_lrc_info['data']['lyric'],
                        'url'    => $radio_data['playurl'],
                        'pic'    => $radio_data['cover']
                    ];
                }
            }
        break;
        case 'netease':
        default:
            if (MC_INTERNAL) {
                $radio_streams                   = [
                    'method'      => 'POST',
                    'url'         => 'http://music.163.com/api/linux/forward',
                    'referer'     => 'http://music.163.com/',
                    'proxy'       => false,
                    'body'        => encode_netease_data([
                        'method'  => 'POST',
                        'url'     => 'http://music.163.com/api/song/enhance/player/url',
                        'params'  => [
                            'ids' => is_array($songid) ? $songid : [$songid],
                            'br'  => 320000,
                        ]
                    ])
                ];
                $radio_info                      = json_decode(mc_curl($radio_streams), true);
                $radio_urls                      = [];
                if (!empty($radio_info['data'])) {
                    foreach ($radio_info['data'] as $val) {
                        $radio_urls[$val['id']]  = $val['url'];
                    }
                }
            }
            foreach ($radio_result as $val) {
                $radio_json                  = json_decode($val, true);
                $radio_data                  = $radio_json['songs'];
                if (!empty($radio_data)) {
                    foreach ($radio_data as $value) {
                        $radio_song_id       = $value['id'];
                        $radio_authors       = [];
                        foreach ($value['artists'] as $key => $val) {
                            $radio_authors[] = $val['name'];
                        }
                        $radio_author        = implode(',', $radio_authors);
                        $radio_lrc_urls      = mc_song_urls($radio_song_id, 'lrc', $site);
                        if ($radio_lrc_urls) {
                            $radio_lrc       = json_decode(mc_curl($radio_lrc_urls), true);
                        }
                        $radio_songs[]       = [
                            'type'   => 'netease',
                            'link'   => 'http://music.163.com/#/song?id=' . $radio_song_id,
                            'songid' => $radio_song_id,
                            'title'  => $value['name'],
                            'author' => $radio_author,
                            'lrc'    => !empty($radio_lrc['lrc']) ? $radio_lrc['lrc']['lyric'] : '',
                            'url'    => MC_INTERNAL ? $radio_urls[$radio_song_id] : 'http://music.163.com/song/media/outer/url?id=' . $radio_song_id . '.mp3',
                            'pic'    => $value['album']['picUrl'] . '?param=300x300'
                        ];
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
    preg_match('/(www|m)\.xiami\.com\/song\/([a-zA-Z0-9]+)/i', $url, $match_xiami);
    preg_match('/5sing\.kugou\.com\/(m\/detail\/|)yc(-|\/)(\d+)/i', $url, $match_5singyc);
    preg_match('/5sing\.kugou\.com\/(m\/detail\/|)fc(-|\/)(\d+)/i', $url, $match_5singfc);
    preg_match('/music\.migu\.cn(\/(#|v2\/music))?\/song\/(\d+)/i', $url, $match_migu);
    preg_match('/(www|m)\.lizhi\.fm\/(\d+)\/(\d+)/i', $url, $match_lizhi);
    preg_match('/(www|m)\.qingting\.fm\/channels\/(\d+)\/programs\/(\d+)/i', $url, $match_qingting);
    preg_match('/(www|m)\.ximalaya\.com\/(\d+)\/sound\/(\d+)/i', $url, $match_ximalaya);
    preg_match('/kg\d?\.qq\.com\/(node\/)?play\?s=([a-zA-Z0-9_-]+)/i', $url, $match_kg_id);
    preg_match('/kg\d?\.qq\.com\/(node\/)?personal\?uid=([a-z0-9_-]+)/i', $url, $match_kg_uid);
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
        $songid   = $match_migu[3];
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
    }  elseif (!empty($match_kg_id)) {
        $songid   = $match_kg_id[2];
        $songtype = 'kg';
    }  elseif (!empty($match_kg_uid)) {
        return mc_get_song_by_name($match_kg_uid[2], 'kg');
    } else {
        return;
    }
    return mc_get_song_by_id($songid, $songtype);
}

// 解密虾米 location
function decode_xiami_location($location)
{
    $location     = trim($location);
    $result       = [];
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
    return ['eparams' => $data];
}

// 分割 songid 并获取
function split_songid($songid, $index = 0, $delimiter = '|') {
    if (mb_strpos($songid, $delimiter, 0, 'UTF-8') > 0) {
        $array = explode($delimiter, $songid);
        if (count($array) > 1) {
            return $array[$index];
        }
    }
    return;
}

// 生成 QQ 音乐各品质链接
function generate_qqmusic_url($songmid, $key) {
    $quality = array('M800', 'M500', 'C400');
    foreach ($quality as $value) {
        $url = 'http://dl.stream.qqmusic.qq.com/' . $value . $songmid . '.mp3?vkey=' . $key . '&guid=5150825362&fromtag=1';
        if (!mc_is_error($url)) {
            return $url;
        }
    }
}

// 生成酷我音乐歌词
function generate_kuwo_lrc($lrclist) {
    if (!empty($lrclist)) {
        $lrc = '';
        foreach ($lrclist as $val) {
            if ($val['time'] > 60) {
                $time_exp = explode('.', round($val['time'] / 60, 4));
                $minute = $time_exp[0] < 10 ? '0' . $time_exp[0] : $time_exp[0];
                $sec = substr($time_exp[1], 0, 2) . '.' . substr($time_exp[1], 2, 2);
                $time = '[' . $minute . ':' . $sec . ']';
            } else {
                $time = '[00:' . $val['time'] . ']';
            }
            $lrc .= $time . $val['lineLyric'] . "\n";
        }
        return $lrc;
    }
}

// jsonp 转 json
function jsonp2json($jsonp) {
    if ($jsonp[0] !== '[' && $jsonp[0] !== '{') {
        $jsonp = mb_substr($jsonp, mb_strpos($jsonp, '('));
    }
    $json = trim($jsonp, "();");
    if ($json) {
        return json_decode($json, true);
    }
}

// 去除字符串转义
function str_decode($str) {
    $str = str_replace(['&#13;', '&#10;'], ['', "\n"], $str);
    $str = html_entity_decode($str, ENT_QUOTES, 'UTF-8');
    return $str;
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
