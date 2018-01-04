"use strict";

/**
 *
 * 音乐搜索器 - JS 文件
 *
 * @author  MaiCong <i@maicong.me>
 * @link    https://github.com/maicong/music
 * @since   1.5.4
 *
 */

$(function() {
  var player = null;
  var nopic = 'static/img/nopic.jpg';

  // Tab 切换
  $('#j-form').on('click', 'li', function() {
    var holder = {
      name: '例如: 不要说话 陈奕迅',
      id: '例如: 25906124',
      url: '例如: http://music.163.com/#/song?id=25906124',
      pattern_name: '^.+$',
      pattern_id: '^[\\w\\/\\|]+$',
      pattern_url: '^https?:\\/\\/\\S+$'
    };
    var filter = $(this).data('filter');

    $(this)
      .addClass('am-active')
      .siblings('li')
      .removeClass('am-active');

    $('#j-input')
      .data('filter', filter)
      .attr({
        placeholder: holder[filter],
        pattern: holder['pattern_' + filter]
      })
      .removeClass('am-field-valid am-field-error am-active')
      .closest('.am-form-group')
      .removeClass('am-form-success am-form-error')
      .find('.am-alert')
      .hide();

    if (filter === 'url') {
      $('#j-type').hide();
    } else {
      $('#j-type').show();
    }
  });

  // 输入验证
  $('#j-validator').validator({
    onValid: function onValid(v) {
      $(v.field)
        .closest('.am-form-group')
        .find('.am-alert')
        .hide();
    },
    onInValid: function onInValid(v) {
      var $field = $(v.field);
      var $group = $field.closest('.am-form-group');
      var msgs = {
        name: '将 名称 和 作者 一起输入可提高匹配度',
        id: '输入错误，请查看下面的帮助',
        url: '输入错误，请查看下面的帮助'
      };
      var $alert = $group.find('.am-alert');
      var msg = msgs[$field.data('filter')] || this.getValidationMessage(v);

      if (!$alert.length) {
        $alert = $('<div class="am-alert am-alert-danger am-animation-shake"></div>')
          .hide()
          .appendTo($group);
      }
      $alert.html(msg).show();
    },
    submit: function submit(v) {
      v.preventDefault();
      if (this.isFormValid()) {
        var input = $.trim($('#j-input').val());
        var filter = $('#j-input').data('filter');
        var type = filter === 'url' ? '_' : $('input[name="music_type"]:checked').val();
        var page = 1;
        var $more = $('<div class="aplayer-more">载入更多</div>');
        var isload = false;
        var ajax = function (input, filter, type, page) {
          $.ajax({
            type: 'POST',
            url: location.href.split('?')[0],
            timeout: 30000,
            data: {
              input: input,
              filter: filter,
              type: type,
              page: page
            },
            dataType: 'json',
            beforeSend: function () {
              isload = true;
              if (page === 1) {
                $('#j-input').attr('disabled', true);
                $('#j-submit').button('loading');
              } else {
                $more.text('请稍后...');
              }
            },
            success: function (result) {
              if (result.code === 200 && result.data) {
                result.data.map(function(v) {
                  if (!v.title) v.title = '暂无';
                  if (!v.author) v.author = '暂无';
                  if (!v.pic) v.pic = nopic;
                  if (!v.lrc) v.lrc = '[00:00.00] 暂无歌词';
                  if (!/\[00:(\d{2})\./.test(v.lrc)) {
                    v.lrc = '[00:00.00] 无效歌词';
                  }
                });
                var setValue = function setValue(data) {
                  $('#j-link').val(data.link);
                  $('#j-link-btn').attr('href', data.link);
                  $('#j-src').val(data.url);
                  $('#j-src-btn').attr('href', data.url);
                  $('#j-name').val(data.title);
                  $('#j-author').val(data.author);
                };

                if (page === 1) {
                  if (player) {
                    player.pause();
                  }
                  setValue(result.data[0]);
                  $('#j-validator').slideUp();
                  $('#j-main').slideDown();

                  player = new APlayer({
                    element: $('#j-player')[0],
                    autoplay: false,
                    narrow: false,
                    showlrc: 1,
                    mutex: false,
                    mode: 'circulation',
                    preload: 'metadata',
                    theme: '#0e90d2',
                    music: result.data
                  });

                  $('#j-player').append($more);

                  $more.on('click', function() {
                    if (isload) return;
                    page++;
                    ajax(input, filter, type, page);
                  });
                } else {
                  player.addMusic(result.data);
                }

                player.on('canplay', function() {
                  player.play();
                });
                player.on('play', function() {
                  var data = result.data[player.playIndex];
                  var img = new Image();
                  img.src = data.pic;
                  img.onerror = function() {
                    $('.aplayer-pic').css('background-image', 'url(' + nopic + ')');
                  };
                  setValue(data);
                });
                if (result.data.length < 10) {
                  $more.hide();
                } else {
                  $more.text('载入更多');
                }
              } else {
                if (page === 1) {
                  $('#j-input')
                    .closest('.am-form-group')
                    .find('.am-alert')
                    .html(result.error || '(°ー°〃) 服务器好像罢工了')
                    .show();
                } else {
                  $more.text('没有了');
                  setTimeout(function() {
                    $more.slideUp();
                  }, 1000);
                }
              }
            },
            error: function (e, t) {
              if (page === 1) {
                var err = '(°ー°〃) 出了点小问题，请重试';
                if (t === 'timeout') {
                  err = '(°ー°〃) 请求超时了，可能是您的网络慢';
                }
                $('#j-input')
                  .closest('.am-form-group')
                  .find('.am-alert')
                  .html(err)
                  .show();
              } else {
                $more.text('(°ー°〃) 加载失败了，点击重试');
              }
            },
            complete: function () {
              isload = false;
              if (page === 1) {
                $('#j-input').attr('disabled', false);
                $('#j-submit').button('reset');
              }
            }
          });
        }

        ajax(input, filter, type, page);
      }
    }
  });

  $('#j-main input').focus(function() {
    $(this).select();
  });

  $('#j-more').on('click', function() {
    $(this).hide();
    $('#j-quote').removeClass('music-overflow');
  });

  $('#j-back').on('click', function() {
    if (player) {
      player.pause();
    }
    $('#j-validator').slideDown();
    $('#j-main').slideUp();
    $('#j-main input').val('');
  });
});
