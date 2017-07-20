/**
 *
 * 音乐搜索器 - JS 文件
 *
 * @author  MaiCong <i@maicong.me>
 * @link    https://github.com/maicong/music
 * @since   1.2.2
 *
 */

var AmazingAudioPlatforms = {
  flashInstalled: function() {
    var flashInstalled = false;
    try {
      if (new ActiveXObject('ShockwaveFlash.ShockwaveFlash')) {
        flashInstalled = true;
      }
    } catch (e) {
      if (navigator.mimeTypes['application/x-shockwave-flash']) {
        flashInstalled = true;
      }
    }
    return flashInstalled;
  },
  html5VideoSupported: function() {
    return !!document.createElement('video').canPlayType;
  },
  isChrome: function() {
    return navigator.userAgent.match(/Chrome/i) != null;
  },
  isFirefox: function() {
    return navigator.userAgent.match(/Firefox/i) != null;
  },
  isOpera: function() {
    return navigator.userAgent.match(/Opera/i) != null;
  },
  isSafari: function() {
    return navigator.userAgent.match(/Safari/i) != null;
  },
  isAndroid: function() {
    return navigator.userAgent.match(/Android/i) != null;
  },
  isIPad: function() {
    return navigator.userAgent.match(/iPad/i) != null;
  },
  isIPhone: function() {
    return (
      navigator.userAgent.match(/iPod/i) != null ||
      navigator.userAgent.match(/iPhone/i) != null
    );
  },
  isIOS: function() {
    return this.isIPad() || this.isIPhone();
  },
  isMobile: function() {
    return this.isIPad() || this.isIPhone() || this.isAndroid();
  },
  isIE9: function() {
    return navigator.userAgent.match(/MSIE 9/i) != null && !this.isOpera();
  },
  isIE8: function() {
    return navigator.userAgent.match(/MSIE 8/i) != null && !this.isOpera();
  },
  isIE7: function() {
    return navigator.userAgent.match(/MSIE 7/i) != null && !this.isOpera();
  },
  isIE6: function() {
    return navigator.userAgent.match(/MSIE 6/i) != null && !this.isOpera();
  },
  isIE678: function() {
    return this.isIE6() || this.isIE7() || this.isIE8();
  },
  isIE6789: function() {
    return this.isIE6() || this.isIE7() || this.isIE8() || this.isIE9();
  },
  css33dTransformSupported: function() {
    return (
      !this.isIE6() &&
      !this.isIE7() &&
      !this.isIE8() &&
      !this.isIE9() &&
      !this.isOpera()
    );
  },
  applyBrowserStyles: function(object, applyToValue) {
    var ret = {};
    for (var key in object) {
      ret[key] = object[key];
      ret['-webkit-' + key] = applyToValue
        ? '-webkit-' + object[key]
        : object[key];
      ret['-moz-' + key] = applyToValue ? '-moz-' + object[key] : object[key];
      ret['-ms-' + key] = applyToValue ? '-ms-' + object[key] : object[key];
      ret['-o-' + key] = applyToValue ? '-o-' + object[key] : object[key];
    }
    return ret;
  }
};
(function($) {
  $.fn.amazingaudioplayer = function(options) {
    var PlayerSkin = function(amazingPlayer, container, options, id) {
      this.amazingPlayer = amazingPlayer;
      this.container = container;
      this.options = options;
      this.id = id;
      this.volumeSaved = 1;
      var instance = this;
      var isTouch = 'ontouchstart' in window;
      var eStart = isTouch ? 'touchstart' : 'mousedown';
      var eMove = isTouch ? 'touchmove' : 'mousemove';
      var eCancel = isTouch ? 'touchcancel' : 'mouseup';
      var formatSeconds = function(secs) {
        var hours = Math.floor(secs / 3600),
          minutes = Math.floor(secs % 3600 / 60),
          seconds = Math.ceil(secs % 3600 % 60);
        return (
          (hours === 0
            ? ''
            : hours > 0 && hours.toString().length < 2
              ? '0' + hours + ':'
              : hours + ':') +
          (minutes.toString().length < 2 ? '0' + minutes : minutes) +
          ':' +
          (seconds.toString().length < 2 ? '0' + seconds : seconds)
        );
      };
      if (this.options.showimage) {
        this.$image = $("<div class='amazingaudioplayer-image'></div>");
        this.$image.appendTo(this.container);
        this.container.bind('amazingaudioplayer.updateinfo', function(
          event,
          data
        ) {
          if (data.image.length > 0) {
            instance.$image.html("<img src='" + data.image + "' />");
          } else {
            instance.$image.empty();
          }
        });
      }
      if (this.options.showtitle || this.options.showinfo) {
        this.$text = $("<div class='amazingaudioplayer-text'></div>");
        this.$text.appendTo(this.container);
        if (this.options.showtitle) {
          this.$title = $("<div class='amazingaudioplayer-title'></div>");
          this.$title.appendTo(this.$text);
          this.container.bind('amazingaudioplayer.updateinfo', function(
            event,
            data
          ) {
            var t = instance.options.titleformat.replace(
              /%TITLE%/g,
              data.title
            );
            t = t.replace(/%ALBUM%/g, data.album);
            t = t.replace(/%ARTIST%/g, data.artist);
            t = t.replace(/%INFO%/g, data.info);
            t = t.replace(/%DURATION%/g, duration);
            t = t.replace(/%ID%/g, data.id);
            if (data.source.length > 0) {
              t = t.replace(/%AUDIO%/g, data.source[0].src);
              t = t.replace(/%AUDIOURL%/g, encodeURI(data.source[0].src));
            }
            instance.$title.html(t);
          });
        }
        if (this.options.showinfo) {
          this.$info = $("<div class='amazingaudioplayer-info'></div>");
          this.$info.appendTo(this.$text);
          this.container.bind('amazingaudioplayer.updateinfo', function(
            event,
            data
          ) {
            var duration = data.duration ? formatSeconds(data.duration) : '';
            var t = instance.options.infoformat.replace(/%TITLE%/g, data.title);
            t = t.replace(/%ALBUM%/g, data.album);
            t = t.replace(/%ARTIST%/g, data.artist);
            t = t.replace(/%INFO%/g, data.info);
            t = t.replace(/%DURATION%/g, duration);
            t = t.replace(/%ID%/g, data.id);
            if (data.source.length > 0) {
              t = t.replace(/%AUDIO%/g, data.source[0].src);
              t = t.replace(/%AUDIOURL%/g, encodeURI(data.source[0].src));
            }
            instance.$info.html(t);
          });
        }
      }
      var $bar = $("<div class='amazingaudioplayer-bar'></div>");
      $bar.appendTo(this.container);
      var $playpause = $("<div class='amazingaudioplayer-playpause'></div>");
      var $play = $(
        "<div class='amazingaudioplayer-play amazingaudioplayer-icon'><i class='am-icon-play'></i></div>"
      );
      var $pause = $(
        "<div class='amazingaudioplayer-pause amazingaudioplayer-icon display-none'><i class='am-icon-pause'></i></div>"
      );
      $playpause.appendTo($bar);
      $play.appendTo($playpause);
      $pause.appendTo($playpause);
      $play.click(function() {
        instance.amazingPlayer.playAudio();
      });
      $pause.click(function() {
        instance.amazingPlayer.pauseAudio();
      });
      this.container.bind('amazingaudioplayer.played', function(
        event,
        currentItem
      ) {
        $play.addClass('display-none').removeClass('display-block');
        $pause.addClass('display-block').removeClass('display-none');
      });
      this.container.bind('amazingaudioplayer.paused', function(
        event,
        currentItem
      ) {
        $play.addClass('display-block').removeClass('display-none');
        $pause.addClass('display-none').removeClass('display-block');
      });
      this.container.bind('amazingaudioplayer.stopped', function(
        event,
        currentItem
      ) {
        $play.addClass('display-block').removeClass('display-none');
        $pause.addClass('display-none').removeClass('display-block');
      });
      if (this.options.showprevnext) {
        var $prev = $(
          "<div class='amazingaudioplayer-prev amazingaudioplayer-icon'><i class='am-icon-step-backward'></i></div>"
        );
        var $next = $(
          "<div class='amazingaudioplayer-next amazingaudioplayer-icon'><i class='am-icon-step-forward'></i></div>"
        );
        $prev.appendTo($bar);
        $next.appendTo($bar);
        $prev.click(function() {
          instance.amazingPlayer.audioRun(
            -2,
            instance.amazingPlayer.audioPlayer.isPlaying
          );
        });
        $next.click(function() {
          instance.amazingPlayer.audioRun(
            -1,
            instance.amazingPlayer.audioPlayer.isPlaying
          );
        });
      }
      if (this.options.showloop) {
        var $loop = $(
          "<div class='amazingaudioplayer-loop amazingaudioplayer-icon'><i class='am-icon-reorder'></i></div>"
        );
        $loop.appendTo($bar);
        $loop.click(function() {
          if (instance.options.loop >= 2) {
            instance.options.loop = 0;
          } else {
            instance.options.loop++;
          }
          switch (instance.options.loop) {
            case 0:
              $loop.html("<i class='am-icon-reorder'></i>");
              break;
            case 1:
              $loop.html("<i class='am-icon-retweet'></i>");
              break;
            case 2:
              $loop.html("<i class='am-icon-history'></i>");
              break;
          }
        });
      }
      if (
        this.options.showvolume &&
        !AmazingAudioPlatforms.isIOS() &&
        !AmazingAudioPlatforms.isAndroid()
      ) {
        this.$volume = $("<div class='amazingaudioplayer-volume'></div>");
        this.$volume.appendTo($bar);
        this.$volumeButton = $(
          "<div class='amazingaudioplayer-volume-button amazingaudioplayer-icon'><i class='am-icon-volume-up'></i></div>"
        );
        this.$volumeButton.appendTo(this.$volume);
        this.$volumeButton.click(function() {
          var volume = parseFloat(
            instance.amazingPlayer.audioPlayer.getVolume()
          ).toFixed(1);
          if (volume > 0) {
            instance.volumeSaved = volume;
            volume = 0;
            instance.$volumeButton.html("<i class='am-icon-volume-off'></i>");
          } else {
            volume = instance.volumeSaved;
            instance.$volumeButton.html("<i class='am-icon-volume-up'></i>");
          }
          if (volume > 0 && volume < 0.5) {
            instance.$volumeButton.html("<i class='am-icon-volume-down'></i>");
          }
          instance.amazingPlayer.audioPlayer.setVolume(volume);
          if (instance.options.showvolumebar) {
            instance.$volumeBarAdjustActive.css({
              height: Math.round(volume * 100) + '%'
            });
          }
        });
        if (this.options.showvolumebar) {
          this.$volumeBar = $(
            "<div class='amazingaudioplayer-volume-bar'></div>"
          );
          this.$volumeBar.appendTo(this.$volume);
          this.$volumeBarAdjust = $(
            "<div class='amazingaudioplayer-volume-bar-adjust'></div>"
          );
          this.$volumeBarAdjust.appendTo(this.$volumeBar);
          this.$volumeBarAdjustActive = $(
            "<div class='amazingaudioplayer-volume-bar-adjust-active'></div>"
          );
          this.$volumeBarAdjustActive.appendTo(this.$volumeBarAdjust);
          this.$volumeBarAdjust
            .bind(eStart, function(e) {
              var e0 = isTouch ? e.originalEvent.touches[0] : e;
              var vol = parseFloat(
                1 -
                  (e0.pageY - instance.$volumeBarAdjust.offset().top) /
                    instance.$volumeBarAdjust.height()
              ).toFixed(1);
              vol = vol > 1 ? 1 : vol < 0 ? 0 : vol;
              instance.$volumeBarAdjustActive.css({
                height: Math.round(vol * 100) + '%'
              });
              instance.amazingPlayer.audioPlayer.setVolume(vol);
              instance.$volumeBarAdjust.bind(eMove, function(e) {
                var e0 = isTouch ? e.originalEvent.touches[0] : e;
                var vol = parseFloat(
                  1 -
                    (e0.pageY - instance.$volumeBarAdjust.offset().top) /
                      instance.$volumeBarAdjust.height()
                ).toFixed(1);
                vol = vol > 1 ? 1 : vol < 0 ? 0 : vol;
                if (vol <= 0) {
                  instance.$volumeButton.html(
                    "<i class='am-icon-volume-off'></i>"
                  );
                } else if (vol > 0 && vol < 0.5) {
                  instance.$volumeButton.html(
                    "<i class='am-icon-volume-down'></i>"
                  );
                } else {
                  instance.$volumeButton.html(
                    "<i class='am-icon-volume-up'></i>"
                  );
                }
                instance.$volumeBarAdjustActive.css({
                  height: Math.round(vol * 100) + '%'
                });
                instance.amazingPlayer.audioPlayer.setVolume(vol);
              });
            })
            .bind(eCancel, function() {
              instance.$volumeBarAdjust.unbind(eMove);
            });
          this.hideVolumeBarTimeout = null;
          this.$volume.hover(
            function() {
              clearTimeout(instance.hideVolumeBarTimeout);
              if (AmazingAudioPlatforms.isIE678()) {
                instance.$volumeBar.show();
              } else {
                instance.$volumeBar.fadeIn();
              }
            },
            function() {
              clearTimeout(instance.hideVolumeBarTimeout);
              instance.hideVolumeBarTimeout = setTimeout(function() {
                if (AmazingAudioPlatforms.isIE678()) {
                  instance.$volumeBar.hide();
                } else {
                  instance.$volumeBar.fadeOut();
                }
              }, 500);
            }
          );
        }
        this.container.bind('amazingaudioplayer.setvolume', function(
          event,
          volume
        ) {
          volume = volume > 1 ? 1 : volume < 0 ? 0 : volume;
          if (instance.options.showvolumebar) {
            instance.$volumeBarAdjustActive.css({
              height: Math.round(volume * 100) + '%'
            });
          }
        });
      }
      if (this.options.showtime) {
        var $time = $("<div class='amazingaudioplayer-time'></div>");
        $time.appendTo($bar);
        this.container.bind('amazingaudioplayer.playprogress', function(
          event,
          data
        ) {
          var current = isNaN(data.current) ? 0 : data.current;
          var duration =
            isNaN(data.duration) || !isFinite(data.duration)
              ? 0
              : data.duration;
          var left = formatSeconds(Math.ceil(duration - current / 1e3));
          current = formatSeconds(Math.ceil(current / 1e3));
          duration = formatSeconds(Math.ceil(duration / 1e3));
          var t;
          if (data.live) {
            t = instance.options.timeformatlive.replace('%CURRENT%', current);
          } else {
            t = instance.options.timeformat
              .replace('%CURRENT%', current)
              .replace('%DURATION%', duration)
              .replace('%LEFT%', left);
          }
          $time.html(t);
        });
        this.container.bind('amazingaudioplayer.played', function(
          event,
          currentItem
        ) {
          if (instance.options.showloading) {
            $time.html(instance.options.loadingformat);
          }
        });
      }
      if (this.options.showprogress) {
        var $progress = $("<div class='amazingaudioplayer-progress'></div>");
        var $progressLoaded = $(
          "<div class='amazingaudioplayer-progress-loaded'></div>"
        );
        var $progressPlayed = $(
          "<div class='amazingaudioplayer-progress-played'></div>"
        );
        $progressLoaded.appendTo($progress);
        $progressPlayed.appendTo($progress);
        $progress.appendTo($bar);
        $progress
          .bind(eStart, function(e) {
            var e0 = isTouch ? e.originalEvent.touches[0] : e;
            var pos = (e0.pageX - $progress.offset().left) / $progress.width();
            instance.amazingPlayer.setTime(pos);
            $progress.bind(eMove, function(e) {
              var e0 = isTouch ? e.originalEvent.touches[0] : e;
              var pos =
                (e0.pageX - $progress.offset().left) / $progress.width();
              instance.amazingPlayer.setTime(pos);
            });
          })
          .bind(eCancel, function() {
            $progress.unbind(eMove);
          });
        this.container.bind('amazingaudioplayer.loadprogress', function(
          event,
          progress
        ) {
          $progressLoaded.css({
            width: progress + '%'
          });
        });
        this.container.bind('amazingaudioplayer.playprogress', function(
          event,
          data
        ) {
          if (data.live) {
            return;
          }
          var progress = 0;
          if (
            !isNaN(data.duration) &&
            isFinite(data.duration) &&
            data.duration > 0
          ) {
            progress = Math.ceil(data.current * 100 / data.duration);
          }
          $progressPlayed.css({
            width: progress + '%'
          });
        });
      }
      if (this.options.showtracklist) {
        this.$tracklistwrapper = $(
          "<div class='amazingaudioplayer-tracklist'></div>"
        );
        this.$tracklistwrapper.appendTo(this.container);
        this.$tracklistcontainer = $(
          "<div class='amazingaudioplayer-tracklist-container'></div>"
        );
        this.$tracklistcontainer.appendTo(this.$tracklistwrapper);
        this.$tracklist = $(
          "<div class='amazingaudioplayer-tracks-wrapper'></div>"
        );
        this.$tracklist.appendTo(this.$tracklistcontainer);
        this.$tracks = $("<ul class='amazingaudioplayer-tracks'></ul>");
        this.$tracks.appendTo(this.$tracklist);
        this.container.bind('amazingaudioplayer.switched', function(
          event,
          data
        ) {
          if (data.previous >= 0) {
            instance.trackitems[data.previous].removeClass(
              'amazingaudioplayer-track-item-active'
            );
          }
          if (data.current >= 0) {
            instance.trackitems[data.current].addClass(
              'amazingaudioplayer-track-item-active'
            );
          }
        });
        this.tracklistItemHeight = 0;
        this.trackitems = [];
        for (var i = 0; i < this.amazingPlayer.elemLength; i++) {
          var $track = $("<li class='amazingaudioplayer-track-item'></li>");
          var data = this.amazingPlayer.elemArray[i];
          var duration = data.duration ? formatSeconds(data.duration) : '';
          var t = this.options.tracklistitemformat.replace(
            /%TITLE%/g,
            data.title
          );
          t = t.replace(/%ALBUM%/g, data.album);
          t = t.replace(/%ARTIST%/g, data.artist);
          t = t.replace(/%INFO%/g, data.info);
          t = t.replace(/%DURATION%/g, duration);
          t = t.replace(/%ID%/g, data.id);
          t = t.replace('%ORDER%', data.id);
          if (data.source.length > 0) {
            t = t.replace(/%AUDIO%/g, data.source[0].src);
            t = t.replace(/%AUDIOURL%/g, encodeURI(data.source[0].src));
          }
          $track.data('order', i);
          $track.html(t);
          $track.appendTo(this.$tracks);
          this.tracklistItemHeight = $track.height();
          this.trackitems.push($track);
          $track.click(function() {
            instance.amazingPlayer.audioRun($(this).data('order'), true);
          });
          $track.hover(
            function() {
              $(this).addClass('amazingaudioplayer-track-item-active');
            },
            function() {
              if (
                $(this).data('order') !== instance.amazingPlayer.currentItem
              ) {
                $(this).removeClass('amazingaudioplayer-track-item-active');
              }
            }
          );
        }
      }
    };
    var FlashHTML5Player = function(amazingPlayer, flashPlayerEngine) {
      this.amazingPlayer = amazingPlayer;
      this.container = this.amazingPlayer.container;
      this.id = this.amazingPlayer.id;
      this.flashPlayerEngine = flashPlayerEngine;
      this.html5Object = null;
      this.flashObject = null;
      this.isHtml5 = false;
      this.isPlaying = false;
      this.html5LoadUpdate = null;
      this.audioItem = null;
      var a = document.createElement('audio');
      this.supportMp3 = !!(
        a.canPlayType && a.canPlayType('audio/mpeg;').replace(/no/, '')
      );
      this.supportOgg = !!(
        a.canPlayType &&
        a.canPlayType('audio/ogg; codecs="vorbis"').replace(/no/, '')
      );
      this.loadFlashTimeout = 0;
    };
    FlashHTML5Player.prototype = {
      initFlash: function() {
        var objectId = 'amazingflashaudioplayer-' + this.id;
        var flashCodes =
          "<div class='amazingaudioplayer-flash-container' style='position:absolute;top:0px;left:0px;'><div id='" +
          objectId +
          "' style='position:absolute;top:0px;left:0px;'></div></div>";
        this.container.append(flashCodes);
        AmazingSWFObject.embedSWF(
          this.flashPlayerEngine,
          objectId,
          '1',
          '1',
          '9.0.0',
          false,
          {
            playerid: this.id
          },
          {
            wmode: 'transparent',
            allowscriptaccess: 'always',
            allownetworking: 'all'
          },
          {}
        );
        this.flashAudioLoaded = false;
        this.flashAudioLoadedSucceed = false;
        this.playAudioAfterLoadedSucceed = false;
        this.html5AudioLoaded = false;
      },
      initHtml5: function() {
        var html5Object = $(
          "<audio class='display-none' preload='" +
            (this.amazingPlayer.options.preloadaudio ? 'auto' : 'none') +
            "'></audio>"
        );
        html5Object.appendTo(this.container);
        var html5Audio = html5Object.get(0);
        var instance = this;
        html5Audio.addEventListener('ended', function() {
          instance.amazingPlayer.onAudioEnded();
        });
        html5Audio.addEventListener('timeupdate', function() {
          instance.amazingPlayer.playProgress(
            html5Audio.currentTime * 1e3,
            html5Audio.duration * 1e3
          );
        });
        html5Audio.addEventListener('durationchange', function() {
          if (instance.isPlaying) html5Audio.play();
        });
        return html5Object;
      },
      load: function(audioItem) {
        this.audioItem = audioItem;
        var audioSource = audioItem.source;
        var i;
        this.isHtml5 = false;
        if (!AmazingAudioPlatforms.isIE6789())
          for (i = 0; i < audioSource.length; i++)
            if (
              (audioSource[i].type == 'audio/mpeg' && this.supportMp3) ||
              (audioSource[i].type == 'audio/ogg' && this.supportOgg)
            ) {
              this.isHtml5 = true;
              break;
            }
        if (
          this.amazingPlayer.options.forcefirefoxflash &&
          AmazingAudioPlatforms.isFirefox() &&
          !AmazingAudioPlatforms.isMobile()
        )
          this.isHtml5 = false;
        if (this.isHtml5) {
          if (!this.html5Object) this.html5Object = this.initHtml5();
          this.html5Object.get(0).pause();
          this.html5Object.empty();
          this.html5Object.currentTime = 0;
          this.amazingPlayer.playProgress(0, 0);
          for (i = 0; i < audioSource.length; i++)
            if (
              (audioSource[i].type == 'audio/mpeg' && this.supportMp3) ||
              (audioSource[i].type == 'audio/ogg' && this.supportOgg)
            )
              this.html5Object.append(
                "<source src='" +
                  audioSource[i].src +
                  "' type='" +
                  audioSource[i].type +
                  "'></source>"
              );
          this.amazingPlayer.playProgress(0, 0);
          this.html5AudioLoaded = false;
          if (this.amazingPlayer.options.preloadaudio) this.html5LoadAudio();
        } else {
          if (!this.flashObject) this.initFlash();
          this.amazingPlayer.playProgress(0, 0);
          this.flashAudioLoaded = false;
          this.flashAudioLoadedSucceed = false;
          this.playAudioAfterLoadedSucceed = false;
          if (this.amazingPlayer.options.preloadaudio)
            this.flashLoadAudio(this.getMp3Src(), false);
        }
      },
      html5LoadAudio: function() {
        this.html5AudioLoaded = true;
        this.html5Object.get(0).load();
        var html5Audio = this.html5Object.get(0);
        var instance = this;
        this.html5LoadUpdate = setInterval(function() {
          if (
            html5Audio.buffered &&
            html5Audio.buffered.length > 0 &&
            !isNaN(html5Audio.buffered.end(0)) &&
            !isNaN(html5Audio.duration)
          ) {
            var percent = Math.ceil(
              html5Audio.buffered.end(0) * 100 / html5Audio.duration
            );
            instance.amazingPlayer.loadProgress(percent);
            if (percent >= 100) clearInterval(instance.html5LoadUpdate);
            instance.amazingPlayer.playProgress(
              html5Audio.currentTime * 1e3,
              html5Audio.duration * 1e3
            );
          }
        }, 100);
      },
      flashLoadAudio: function(mp3Src, playAudio) {
        this.flashAudioLoaded = true;
        var instance = this;
        if (!AmazingFlashAudioPlayerReady[this.id]) {
          this.loadFlashTimeout += 100;
          if (this.loadFlashTimeout < 6e3) {
            setTimeout(function() {
              instance.flashLoadAudio(mp3Src, playAudio);
            }, 100);
            return;
          }
        } else {
          if (!this.flashObject)
            this.flashObject = AmazingSWFObject.getObjectById(
              'amazingflashaudioplayer-' + this.id
            );
          this.flashObject.loadAudio(mp3Src);
          this.flashAudioLoadedSucceed = true;
          if (playAudio || this.playAudioAfterLoadedSucceed)
            this.flashObject.playAudio();
          this.playAudioAfterLoadedSucceed = false;
        }
      },
      play: function() {
        if (this.isHtml5) {
          if (!this.html5AudioLoaded) this.html5LoadAudio();
          this.html5Object.get(0).play();
        } else if (this.flashAudioLoadedSucceed) this.flashObject.playAudio();
        else if (this.flashAudioLoaded) this.playAudioAfterLoadedSucceed = true;
        else this.flashLoadAudio(this.getMp3Src(), true);
        this.isPlaying = true;
      },
      getMp3Src: function() {
        var audioSource = this.audioItem.source;
        var mp3Src = '';
        for (var i = 0; i < audioSource.length; i++)
          if (audioSource[i].type == 'audio/mpeg') mp3Src = audioSource[i].src;
        return mp3Src;
      },
      pause: function() {
        if (this.isHtml5) this.html5Object.get(0).pause();
        else this.flashObject.pauseAudio();
        this.isPlaying = false;
      },
      stop: function() {
        if (this.isHtml5) {
          this.html5Object.get(0).pause();
          this.html5Object.get(0).currentTime = 0;
        } else this.flashObject.stopAudio();
        this.isPlaying = false;
      },
      setTime: function(pos, duration) {
        if (this.isHtml5)
          if (
            !isNaN(this.html5Object.get(0).duration) &&
            isFinite(this.html5Object.get(0).duration) &&
            this.html5Object.get(0).duration > 0
          )
            this.html5Object.get(0).currentTime =
              this.html5Object.get(0).duration * pos;
          else this.html5Object.get(0).currentTime = duration * pos;
        else this.flashObject.setTime(pos);
      },
      getVolume: function() {
        if (this.isHtml5) return this.html5Object.get(0).volume;
        else return this.flashObject.getVolume();
      },
      setVolume: function(val) {
        if (this.isHtml5) this.html5Object.get(0).volume = val;
        else this.flashObject.setVolume(val);
      }
    };
    var AmazingAudioPlayer = function(container, options, id) {
      this.container = container;
      this.options = options;
      this.id = id;
      $('.amazingaudioplayer-engine').css({
        display: 'none'
      });
      this.currentItem = -1;
      this.elemArray = [];
      this.elemLength = 0;
      this.audioPlayer = new FlashHTML5Player(this, 'player.swf');
      this.initData(this.init);
    };
    AmazingAudioPlayer.prototype = {
      initData: function(onSuccess) {
        this.readTags();
        onSuccess(this);
      },
      readTags: function() {
        var instance = this;
        $('.amazingaudioplayer-audios', this.container)
          .find('a')
          .each(function() {
            var $this = $(this);
            var audioSource = [];
            var audioId = instance.elemArray.length + 1;
            audioSource.push({
              src: $(this).data('src'),
              type: $(this).data('type').toLowerCase()
            });
            instance.elemArray.push({
              id: audioId,
              source: audioSource,
              title: $this.data('title') + '',
              artist: $this.data('artist') + '',
              album: $this.data('album') + '',
              info: $this.data('info') + '',
              duration: $this.data('duration') ? $this.data('duration') : '',
              image: $this.data('image') + '',
              live: $this.data('live') ? true : false
            });
          });
        instance.elemLength = instance.elemArray.length;
      },
      init: function(instance) {
        var i;
        if (instance.elemLength <= 0) {
          return;
        }
        if (instance.options.random) {
          for (i = instance.elemLength - 1; i > 0; i--) {
            if (i === 1 && Math.random() < 0.5) {
              break;
            }
            var index = Math.floor(Math.random() * i);
            var temp = instance.elemArray[index];
            instance.elemArray[index] = instance.elemArray[i];
            instance.elemArray[i] = temp;
          }
          for (i = 0; i < instance.elemLength; i++) {
            instance.elemArray[i].id = i + 1;
          }
        }
        instance.isPlaying = false;
        instance.loopCount = 0;
        instance.createSkin();
        var params = instance.getParams();
        var firstId = 0;
        if (
          'firstaudioid' in params &&
          !isNaN(params.firstaudioid) &&
          params.firstaudioid >= 0 &&
          params.firstaudioid < instance.elemLength
        ) {
          firstId = params.firstaudioid;
        }
        instance.audioRun(firstId, false);
        if ('autoplayaudio' in params) {
          if (params.autoplayaudio === '1') {
            instance.options.autoplay = true;
          } else if (params.autoplayaudio === '0') {
            instance.options.autoplay = false;
          }
        }
        if (
          instance.options.autoplay &&
          !AmazingAudioPlatforms.isIOS() &&
          !AmazingAudioPlatforms.isAndroid()
        ) {
          window.setTimeout(function() {
            instance.playAudio();
          }, instance.options.autoplaytimeout);
        }
        if (instance.options.defaultvolume >= 0) {
          instance.setVolume(
            parseFloat(instance.options.defaultvolume / 100).toFixed(1)
          );
        }
        instance.container.bind('amazingaudioplayer.stop', function(event, id) {
          if (id !== instance.id && instance.audioPlayer.isPlaying) {
            instance.pauseAudio();
          }
        });
      },
      createSkin: function() {
        return new PlayerSkin(this, this.container, this.options, this.id);
      },
      getParams: function() {
        var result = {};
        var params = window.location.search.substring(1).split('&');
        for (var i = 0; i < params.length; i++) {
          var value = params[i].split('=');
          if (value && value.length === 2) {
            result[value[0].toLowerCase()] = unescape(value[1]);
          }
        }
        return result;
      },
      audioRun: function(index, autoPlay) {
        if (index < -2 || index >= this.elemLength) {
          return;
        }
        var nextItem;
        if (index === -2) {
          nextItem =
            this.currentItem <= 0 ? this.elemLength - 1 : this.currentItem - 1;
        } else if (index === -1) {
          nextItem =
            this.currentItem >= this.elemLength - 1 ? 0 : this.currentItem + 1;
        } else {
          nextItem = index;
        }

        this.container.trigger('amazingaudioplayer.switched', {
          previous: this.currentItem,
          current: nextItem
        });
        this.currentItem = nextItem;
        this.container.trigger(
          'amazingaudioplayer.updateinfo',
          this.elemArray[this.currentItem]
        );
        this.audioPlayer.load(this.elemArray[this.currentItem]);
        if (autoPlay) {
          this.playAudio();
        }
      },
      onAudioEnded: function() {
        this.container.trigger('amazingaudioplayer.ended', this.currentItem);
        switch (this.options.loop) {
          case 0:
            if (
              !this.options.noncontinous &&
              this.currentItem < this.elemLength - 1
            ) {
              this.audioRun(-1, true);
            } else {
              this.stopAudio();
            }
            break;
          case 1:
            this.audioRun(-1, true);
            break;
          case 2:
            this.audioRun(this.currentItem, true);
            break;
        }
      },
      playAudio: function() {
        this.audioPlayer.play();
        this.container.trigger('amazingaudioplayer.played', this.currentItem);
        if (this.options.stopotherplayers) {
          if (amazingAudioPlayerObjects && amazingAudioPlayerObjects.objects) {
            for (var i = 0; i < amazingAudioPlayerObjects.objects.length; i++) {
              if (amazingAudioPlayerObjects.objects[i].id === this.id) {
                continue;
              }
              amazingAudioPlayerObjects.objects[i].container.trigger(
                'amazingaudioplayer.stop',
                this.id
              );
            }
          }
        }
      },
      pauseAudio: function() {
        this.audioPlayer.pause();
        this.container.trigger('amazingaudioplayer.paused', this.currentItem);
      },
      stopAudio: function() {
        this.audioPlayer.stop();
        this.container.trigger('amazingaudioplayer.stopped', this.currentItem);
        this.container.trigger('amazingaudioplayer.playprogress', {
          current: 0,
          duration: 0,
          live: this.elemArray[this.currentItem].live
        });
      },
      setVolume: function(volume) {
        this.audioPlayer.setVolume(volume);
        this.container.trigger('amazingaudioplayer.setvolume', volume);
      },
      loadProgress: function(progress) {
        this.container.trigger('amazingaudioplayer.loadprogress', progress);
      },
      playProgress: function(current, duration) {
        if (current === 0 && duration === 1e5) {
          return;
        }
        var d0 =
          this.elemArray[this.currentItem].duration * 1e3 > duration ||
          isNaN(duration) ||
          !isFinite(duration)
            ? this.elemArray[this.currentItem].duration * 1e3
            : duration;
        this.container.trigger('amazingaudioplayer.playprogress', {
          current: current,
          duration: d0,
          live: this.elemArray[this.currentItem].live
        });
      },
      setTime: function(pos) {
        this.audioPlayer.setTime(
          pos,
          this.elemArray[this.currentItem].duration
        );
      }
    };
    var bts = function(string) {
      var ret = '';
      var bytes = string.split(',');
      for (var i = 0; i < bytes.length; i++) {
        ret += String.fromCharCode(bytes[i]);
      }
      return ret;
    };
    options = options || {};
    for (var key in options) {
      if (key.toLowerCase() !== key) {
        options[key.toLowerCase()] = options[key];
        delete options[key];
      }
    }
    this.each(function() {
      this.options = $.extend({}, options);
      if (
        $(this).data('skin') &&
        typeof AMAZINGAUDIOPLAYER_SKIN_OPTIONS !== 'undefined'
      ) {
        if ($(this).data('skin') in AMAZINGAUDIOPLAYER_SKIN_OPTIONS) {
          this.options = $.extend(
            {},
            AMAZINGAUDIOPLAYER_SKIN_OPTIONS[$(this).data('skin')],
            this.options
          );
        }
      }
      var instance = this;
      $.each($(this).data(), function(key, value) {
        instance.options[key.toLowerCase()] = value;
      });
      if (
        typeof amazingaudioplayer_options !== 'undefined' &&
        amazingaudioplayer_options
      ) {
        this.options = $.extend(this.options, amazingaudioplayer_options);
      }
      if ($('div#amazingaudioplayer_options').length) {
        $.each($('div#amazingaudioplayer_options').data(), function(
          key,
          value
        ) {
          instance.options[key.toLowerCase()] = value;
        });
      }
      var searchoptions = {};
      var searchstring = window.location.search.substring(1).split('&');
      for (var i = 0; i < searchstring.length; i++) {
        var keyvalue = searchstring[i].split('=');
        if (keyvalue && keyvalue.length === 2) {
          var key = keyvalue[0].toLowerCase();
          var value = unescape(keyvalue[1]).toLowerCase();
          if (value === 'true') {
            searchoptions[key] = true;
          } else if (value === 'false') {
            searchoptions[key] = false;
          } else {
            searchoptions[key] = value;
          }
        }
      }
      this.options = $.extend(this.options, searchoptions);
      var defaultOptions = {
        autoplay: true,
        autoplaytimeout: 1e3,
        loop: 0,
        random: false,
        stopotherplayers: true,
        forcefirefoxflash: false,
        noncontinous: false,
        preloadaudio: true,
        defaultvolume: 60,
        showprevnext: true,
        showloop: true,
        showprogress: true,
        showtime: true,
        timeformat: '%CURRENT% / %DURATION%',
        timeformatlive: '%CURRENT% / LIVE',
        showloading: true,
        loadingformat: 'Loading...',
        showvolume: true,
        showvolumebar: true,
        showtitle: true,
        titleformat: '%TITLE%',
        showinfo: true,
        infoformat: '%ARTIST%',
        showimage: true,
        imagewidth: 100,
        imageheight: 100,
        imagefullwidth: false,
        showtracklist: true,
        tracklistitem: 10,
        tracklistitemformat: '%ID%. %TITLE% <span>%ARTIST%</span>'
      };
      this.options = $.extend(defaultOptions, this.options);
      this.options.htmlfolder = window.location.href.substr(
        0,
        window.location.href.lastIndexOf('/') + 1
      );
      var audioplayerid;
      if ('audioplayerid' in this.options)
        audioplayerid = this.options.audioplayerid;
      else {
        audioplayerid = amazingaudioplayerId;
        amazingaudioplayerId++;
      }
      var object = new AmazingAudioPlayer($(this), this.options, audioplayerid);
      $(this).data('object', object);
      $(this).data('id', audioplayerid);
      amazingAudioPlayerObjects.addObject(object);
    });
  };
})(jQuery);
if (typeof amazingaudioplayerId === 'undefined') var amazingaudioplayerId = 0;
if (typeof amazingAudioPlayerObjects === 'undefined')
  var amazingAudioPlayerObjects = new function() {
    this.objects = [];
    this.addObject = function(obj) {
      this.objects.push(obj);
    };
  }();
if (typeof AmazingFlashAudioPlayerReady === 'undefined') {
  var AmazingFlashAudioPlayerReady = [];

  function onAmazingFlashAudioPlayerReady(playerid) {
    AmazingFlashAudioPlayerReady[playerid] = true;
  }

  function amazingFlashAudioPlayerEventHandler(event, playerid, param, param1) {
    var id = playerid;
    for (var i = 0; i < amazingAudioPlayerObjects.objects.length; i++)
      if (amazingAudioPlayerObjects.objects[i].id == playerid) {
        id = i;
        break;
      }
    switch (event) {
      case 'completed':
        amazingAudioPlayerObjects.objects[id].onAudioEnded();
        break;
      case 'loadprogress':
        amazingAudioPlayerObjects.objects[id].loadProgress(param);
        break;
      case 'playprogress':
        amazingAudioPlayerObjects.objects[id].playProgress(param, param1);
        break;
    }
  }
}
var AmazingSWFObject = (function() {
  var UNDEF = 'undefined',
    OBJECT = 'object',
    SHOCKWAVE_FLASH = 'Shockwave Flash',
    SHOCKWAVE_FLASH_AX = 'ShockwaveFlash.ShockwaveFlash',
    FLASH_MIME_TYPE = 'application/x-shockwave-flash',
    EXPRESS_INSTALL_ID = 'SWFObjectExprInst',
    ON_READY_STATE_CHANGE = 'onreadystatechange',
    win = window,
    doc = document,
    nav = navigator,
    plugin = false,
    domLoadFnArr = [main],
    regObjArr = [],
    objIdArr = [],
    listenersArr = [],
    storedAltContent,
    storedAltContentId,
    storedCallbackFn,
    storedCallbackObj,
    isDomLoaded = false,
    isExpressInstallActive = false,
    dynamicStylesheet,
    dynamicStylesheetMedia,
    autoHideShow = true,
    ua = (function() {
      var w3cdom =
          typeof doc.getElementById != UNDEF &&
          typeof doc.getElementsByTagName != UNDEF &&
          typeof doc.createElement != UNDEF,
        u = nav.userAgent.toLowerCase(),
        p = nav.platform.toLowerCase(),
        windows = p ? /win/.test(p) : /win/.test(u),
        mac = p ? /mac/.test(p) : /mac/.test(u),
        webkit = /webkit/.test(u)
          ? parseFloat(u.replace(/^.*webkit\/(\d+(\.\d+)?).*$/, '$1'))
          : false,
        ie = !+'\v1',
        playerVersion = [0, 0, 0],
        d = null;
      if (
        typeof nav.plugins != UNDEF &&
        typeof nav.plugins[SHOCKWAVE_FLASH] == OBJECT
      ) {
        d = nav.plugins[SHOCKWAVE_FLASH].description;
        if (
          d &&
          !(
            typeof nav.mimeTypes != UNDEF &&
            nav.mimeTypes[FLASH_MIME_TYPE] &&
            !nav.mimeTypes[FLASH_MIME_TYPE].enabledPlugin
          )
        ) {
          plugin = true;
          ie = false;
          d = d.replace(/^.*\s+(\S+\s+\S+$)/, '$1');
          playerVersion[0] = parseInt(d.replace(/^(.*)\..*$/, '$1'), 10);
          playerVersion[1] = parseInt(d.replace(/^.*\.(.*)\s.*$/, '$1'), 10);
          playerVersion[2] = /[a-zA-Z]/.test(d)
            ? parseInt(d.replace(/^.*[a-zA-Z]+(.*)$/, '$1'), 10)
            : 0;
        }
      } else if (typeof win.ActiveXObject != UNDEF)
        try {
          var a = new ActiveXObject(SHOCKWAVE_FLASH_AX);
          if (a) {
            d = a.GetVariable('$version');
            if (d) {
              ie = true;
              d = d.split(' ')[1].split(',');
              playerVersion = [
                parseInt(d[0], 10),
                parseInt(d[1], 10),
                parseInt(d[2], 10)
              ];
            }
          }
        } catch (e) {}
      return {
        w3: w3cdom,
        pv: playerVersion,
        wk: webkit,
        ie: ie,
        win: windows,
        mac: mac
      };
    })(),
    onDomLoad = (function() {
      if (!ua.w3) return;
      if (
        (typeof doc.readyState != UNDEF && doc.readyState == 'complete') ||
        (typeof doc.readyState == UNDEF &&
          (doc.getElementsByTagName('body')[0] || doc.body))
      )
        callDomLoadFunctions();
      if (!isDomLoaded) {
        if (typeof doc.addEventListener != UNDEF)
          doc.addEventListener('DOMContentLoaded', callDomLoadFunctions, false);
        if (ua.ie && ua.win) {
          doc.attachEvent(ON_READY_STATE_CHANGE, function() {
            if (doc.readyState == 'complete') {
              doc.detachEvent(ON_READY_STATE_CHANGE, arguments.callee);
              callDomLoadFunctions();
            }
          });
          if (win == top)
            (function() {
              if (isDomLoaded) return;
              try {
                doc.documentElement.doScroll('left');
              } catch (e) {
                setTimeout(arguments.callee, 0);
                return;
              }
              callDomLoadFunctions();
            })();
        }
        if (ua.wk)
          (function() {
            if (isDomLoaded) return;
            if (!/loaded|complete/.test(doc.readyState)) {
              setTimeout(arguments.callee, 0);
              return;
            }
            callDomLoadFunctions();
          })();
        addLoadEvent(callDomLoadFunctions);
      }
    })();

  function callDomLoadFunctions() {
    if (isDomLoaded) return;
    try {
      var t = doc
        .getElementsByTagName('body')[0]
        .appendChild(createElement('span'));
      t.parentNode.removeChild(t);
    } catch (e) {
      return;
    }
    isDomLoaded = true;
    var dl = domLoadFnArr.length;
    for (var i = 0; i < dl; i++) domLoadFnArr[i]();
  }

  function addDomLoadEvent(fn) {
    if (isDomLoaded) fn();
    else domLoadFnArr[domLoadFnArr.length] = fn;
  }

  function addLoadEvent(fn) {
    if (typeof win.addEventListener != UNDEF)
      win.addEventListener('load', fn, false);
    else if (typeof doc.addEventListener != UNDEF)
      doc.addEventListener('load', fn, false);
    else if (typeof win.attachEvent != UNDEF) addListener(win, 'onload', fn);
    else if (typeof win.onload == 'function') {
      var fnOld = win.onload;
      win.onload = function() {
        fnOld();
        fn();
      };
    } else win.onload = fn;
  }

  function main() {
    if (plugin) testPlayerVersion();
    else matchVersions();
  }

  function testPlayerVersion() {
    var b = doc.getElementsByTagName('body')[0];
    var o = createElement(OBJECT);
    o.setAttribute('type', FLASH_MIME_TYPE);
    var t = b.appendChild(o);
    if (t) {
      var counter = 0;
      (function() {
        if (typeof t.GetVariable != UNDEF) {
          var d = t.GetVariable('$version');
          if (d) {
            d = d.split(' ')[1].split(',');
            ua.pv = [
              parseInt(d[0], 10),
              parseInt(d[1], 10),
              parseInt(d[2], 10)
            ];
          }
        } else if (counter < 10) {
          counter++;
          setTimeout(arguments.callee, 10);
          return;
        }
        b.removeChild(o);
        t = null;
        matchVersions();
      })();
    } else matchVersions();
  }

  function matchVersions() {
    var rl = regObjArr.length;
    if (rl > 0)
      for (var i = 0; i < rl; i++) {
        var id = regObjArr[i].id;
        var cb = regObjArr[i].callbackFn;
        var cbObj = {
          success: false,
          id: id
        };
        if (ua.pv[0] > 0) {
          var obj = getElementById(id);
          if (obj)
            if (
              hasPlayerVersion(regObjArr[i].swfVersion) &&
              !(ua.wk && ua.wk < 312)
            ) {
              setVisibility(id, true);
              if (cb) {
                cbObj.success = true;
                cbObj.ref = getObjectById(id);
                cb(cbObj);
              }
            } else if (regObjArr[i].expressInstall && canExpressInstall()) {
              var att = {};
              att.data = regObjArr[i].expressInstall;
              att.width = obj.getAttribute('width') || '0';
              att.height = obj.getAttribute('height') || '0';
              if (obj.getAttribute('class'))
                att.styleclass = obj.getAttribute('class');
              if (obj.getAttribute('align'))
                att.align = obj.getAttribute('align');
              var par = {};
              var p = obj.getElementsByTagName('param');
              var pl = p.length;
              for (var j = 0; j < pl; j++)
                if (p[j].getAttribute('name').toLowerCase() != 'movie')
                  par[p[j].getAttribute('name')] = p[j].getAttribute('value');
              showExpressInstall(att, par, id, cb);
            } else {
              displayAltContent(obj);
              if (cb) cb(cbObj);
            }
        } else {
          setVisibility(id, true);
          if (cb) {
            var o = getObjectById(id);
            if (o && typeof o.SetVariable != UNDEF) {
              cbObj.success = true;
              cbObj.ref = o;
            }
            cb(cbObj);
          }
        }
      }
  }

  function getObjectById(objectIdStr) {
    var r = null;
    var o = getElementById(objectIdStr);
    if (o && o.nodeName == 'OBJECT')
      if (typeof o.SetVariable != UNDEF) r = o;
      else {
        var n = o.getElementsByTagName(OBJECT)[0];
        if (n) r = n;
      }
    return r;
  }

  function canExpressInstall() {
    return (
      !isExpressInstallActive &&
      hasPlayerVersion('6.0.65') &&
      (ua.win || ua.mac) &&
      !(ua.wk && ua.wk < 312)
    );
  }

  function showExpressInstall(att, par, replaceElemIdStr, callbackFn) {
    isExpressInstallActive = true;
    storedCallbackFn = callbackFn || null;
    storedCallbackObj = {
      success: false,
      id: replaceElemIdStr
    };
    var obj = getElementById(replaceElemIdStr);
    if (obj) {
      if (obj.nodeName == 'OBJECT') {
        storedAltContent = abstractAltContent(obj);
        storedAltContentId = null;
      } else {
        storedAltContent = obj;
        storedAltContentId = replaceElemIdStr;
      }
      att.id = EXPRESS_INSTALL_ID;
      if (
        typeof att.width == UNDEF ||
        (!/%$/.test(att.width) && parseInt(att.width, 10) < 310)
      )
        att.width = '310';
      if (
        typeof att.height == UNDEF ||
        (!/%$/.test(att.height) && parseInt(att.height, 10) < 137)
      )
        att.height = '137';
      doc.title = doc.title.slice(0, 47) + ' - Flash Player Installation';
      var pt = ua.ie && ua.win ? 'ActiveX' : 'PlugIn',
        fv =
          'MMredirectURL=' +
          win.location.toString().replace(/&/g, '%26') +
          '&MMplayerType=' +
          pt +
          '&MMdoctitle=' +
          doc.title;
      if (typeof par.flashvars != UNDEF) par.flashvars += '&' + fv;
      else par.flashvars = fv;
      if (ua.ie && ua.win && obj.readyState != 4) {
        var newObj = createElement('div');
        replaceElemIdStr += 'SWFObjectNew';
        newObj.setAttribute('id', replaceElemIdStr);
        obj.parentNode.insertBefore(newObj, obj);
        obj.style.display = 'none';
        (function() {
          if (obj.readyState == 4) obj.parentNode.removeChild(obj);
          else setTimeout(arguments.callee, 10);
        })();
      }
      createSWF(att, par, replaceElemIdStr);
    }
  }

  function displayAltContent(obj) {
    if (ua.ie && ua.win && obj.readyState != 4) {
      var el = createElement('div');
      obj.parentNode.insertBefore(el, obj);
      el.parentNode.replaceChild(abstractAltContent(obj), el);
      obj.style.display = 'none';
      (function() {
        if (obj.readyState == 4) obj.parentNode.removeChild(obj);
        else setTimeout(arguments.callee, 10);
      })();
    } else obj.parentNode.replaceChild(abstractAltContent(obj), obj);
  }

  function abstractAltContent(obj) {
    var ac = createElement('div');
    if (ua.win && ua.ie) ac.innerHTML = obj.innerHTML;
    else {
      var nestedObj = obj.getElementsByTagName(OBJECT)[0];
      if (nestedObj) {
        var c = nestedObj.childNodes;
        if (c) {
          var cl = c.length;
          for (var i = 0; i < cl; i++)
            if (
              !(c[i].nodeType == 1 && c[i].nodeName == 'PARAM') &&
              !(c[i].nodeType == 8)
            )
              ac.appendChild(c[i].cloneNode(true));
        }
      }
    }
    return ac;
  }

  function createSWF(attObj, parObj, id) {
    var r,
      el = getElementById(id);
    if (ua.wk && ua.wk < 312) return r;
    if (el) {
      if (typeof attObj.id == UNDEF) attObj.id = id;
      if (ua.ie && ua.win) {
        var att = '';
        for (var i in attObj)
          if (attObj[i] != Object.prototype[i])
            if (i.toLowerCase() == 'data') parObj.movie = attObj[i];
            else if (i.toLowerCase() == 'styleclass')
              att += ' class="' + attObj[i] + '"';
            else if (i.toLowerCase() != 'classid')
              att += ' ' + i + '="' + attObj[i] + '"';
        var par = '';
        for (var j in parObj)
          if (parObj[j] != Object.prototype[j])
            par += '<param name="' + j + '" value="' + parObj[j] + '" />';
        el.outerHTML =
          '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"' +
          att +
          '>' +
          par +
          '</object>';
        objIdArr[objIdArr.length] = attObj.id;
        r = getElementById(attObj.id);
      } else {
        var o = createElement(OBJECT);
        o.setAttribute('type', FLASH_MIME_TYPE);
        for (var m in attObj)
          if (attObj[m] != Object.prototype[m])
            if (m.toLowerCase() == 'styleclass')
              o.setAttribute('class', attObj[m]);
            else if (m.toLowerCase() != 'classid') o.setAttribute(m, attObj[m]);
        for (var n in parObj)
          if (parObj[n] != Object.prototype[n] && n.toLowerCase() != 'movie')
            createObjParam(o, n, parObj[n]);
        el.parentNode.replaceChild(o, el);
        r = o;
      }
    }
    return r;
  }

  function createObjParam(el, pName, pValue) {
    var p = createElement('param');
    p.setAttribute('name', pName);
    p.setAttribute('value', pValue);
    el.appendChild(p);
  }

  function removeSWF(id) {
    var obj = getElementById(id);
    if (obj && obj.nodeName == 'OBJECT')
      if (ua.ie && ua.win) {
        obj.style.display = 'none';
        (function() {
          if (obj.readyState == 4) removeObjectInIE(id);
          else setTimeout(arguments.callee, 10);
        })();
      } else obj.parentNode.removeChild(obj);
  }

  function removeObjectInIE(id) {
    var obj = getElementById(id);
    if (obj) {
      for (var i in obj) if (typeof obj[i] == 'function') obj[i] = null;
      obj.parentNode.removeChild(obj);
    }
  }

  function getElementById(id) {
    var el = null;
    try {
      el = doc.getElementById(id);
    } catch (e) {}
    return el;
  }

  function createElement(el) {
    return doc.createElement(el);
  }

  function addListener(target, eventType, fn) {
    target.attachEvent(eventType, fn);
    listenersArr[listenersArr.length] = [target, eventType, fn];
  }

  function hasPlayerVersion(rv) {
    var pv = ua.pv,
      v = rv.split('.');
    v[0] = parseInt(v[0], 10);
    v[1] = parseInt(v[1], 10) || 0;
    v[2] = parseInt(v[2], 10) || 0;
    return pv[0] > v[0] ||
    (pv[0] == v[0] && pv[1] > v[1]) ||
    (pv[0] == v[0] && pv[1] == v[1] && pv[2] >= v[2])
      ? true
      : false;
  }

  function createCSS(sel, decl, media, newStyle) {
    if (ua.ie && ua.mac) return;
    var h = doc.getElementsByTagName('head')[0];
    if (!h) return;
    var m = media && typeof media == 'string' ? media : 'screen';
    if (newStyle) {
      dynamicStylesheet = null;
      dynamicStylesheetMedia = null;
    }
    if (!dynamicStylesheet || dynamicStylesheetMedia != m) {
      var s = createElement('style');
      s.setAttribute('type', 'text/css');
      s.setAttribute('media', m);
      dynamicStylesheet = h.appendChild(s);
      if (
        ua.ie &&
        ua.win &&
        typeof doc.styleSheets != UNDEF &&
        doc.styleSheets.length > 0
      )
        dynamicStylesheet = doc.styleSheets[doc.styleSheets.length - 1];
      dynamicStylesheetMedia = m;
    }
    if (ua.ie && ua.win) {
      if (dynamicStylesheet && typeof dynamicStylesheet.addRule == OBJECT)
        dynamicStylesheet.addRule(sel, decl);
    } else if (dynamicStylesheet && typeof doc.createTextNode != UNDEF)
      dynamicStylesheet.appendChild(
        doc.createTextNode(sel + ' {' + decl + '}')
      );
  }

  function setVisibility(id, isVisible) {
    if (!autoHideShow) return;
    var v = isVisible ? 'visible' : 'hidden';
    if (isDomLoaded && getElementById(id))
      getElementById(id).style.visibility = v;
    else createCSS('#' + id, 'visibility:' + v);
  }

  function urlEncodeIfNecessary(s) {
    var regex = /[\\\"<>\.;]/;
    var hasBadChars = regex.exec(s) != null;
    return hasBadChars && typeof encodeURIComponent != UNDEF
      ? encodeURIComponent(s)
      : s;
  }
  var cleanup = (function() {
    if (ua.ie && ua.win)
      window.attachEvent('onunload', function() {
        var ll = listenersArr.length;
        for (var i = 0; i < ll; i++)
          listenersArr[i][0].detachEvent(
            listenersArr[i][1],
            listenersArr[i][2]
          );
        var il = objIdArr.length;
        for (var j = 0; j < il; j++) removeSWF(objIdArr[j]);
        for (var k in ua) ua[k] = null;
        ua = null;
        for (var l in AmazingSWFObject) AmazingSWFObject[l] = null;
        AmazingSWFObject = null;
      });
  })();
  return {
    registerObject: function(
      objectIdStr,
      swfVersionStr,
      xiSwfUrlStr,
      callbackFn
    ) {
      if (ua.w3 && objectIdStr && swfVersionStr) {
        var regObj = {};
        regObj.id = objectIdStr;
        regObj.swfVersion = swfVersionStr;
        regObj.expressInstall = xiSwfUrlStr;
        regObj.callbackFn = callbackFn;
        regObjArr[regObjArr.length] = regObj;
        setVisibility(objectIdStr, false);
      } else if (callbackFn)
        callbackFn({
          success: false,
          id: objectIdStr
        });
    },
    getObjectById: function(objectIdStr) {
      if (ua.w3) return getObjectById(objectIdStr);
    },
    embedSWF: function(
      swfUrlStr,
      replaceElemIdStr,
      widthStr,
      heightStr,
      swfVersionStr,
      xiSwfUrlStr,
      flashvarsObj,
      parObj,
      attObj,
      callbackFn
    ) {
      var callbackObj = {
        success: false,
        id: replaceElemIdStr
      };
      if (
        ua.w3 &&
        !(ua.wk && ua.wk < 312) &&
        swfUrlStr &&
        replaceElemIdStr &&
        widthStr &&
        heightStr &&
        swfVersionStr
      ) {
        setVisibility(replaceElemIdStr, false);
        addDomLoadEvent(function() {
          widthStr += '';
          heightStr += '';
          var att = {};
          if (attObj && typeof attObj === OBJECT)
            for (var i in attObj) att[i] = attObj[i];
          att.data = swfUrlStr;
          att.width = widthStr;
          att.height = heightStr;
          var par = {};
          if (parObj && typeof parObj === OBJECT)
            for (var j in parObj) par[j] = parObj[j];
          if (flashvarsObj && typeof flashvarsObj === OBJECT)
            for (var k in flashvarsObj)
              if (typeof par.flashvars != UNDEF)
                par.flashvars += '&' + k + '=' + flashvarsObj[k];
              else par.flashvars = k + '=' + flashvarsObj[k];
          if (hasPlayerVersion(swfVersionStr)) {
            var obj = createSWF(att, par, replaceElemIdStr);
            if (att.id == replaceElemIdStr)
              setVisibility(replaceElemIdStr, true);
            callbackObj.success = true;
            callbackObj.ref = obj;
          } else if (xiSwfUrlStr && canExpressInstall()) {
            att.data = xiSwfUrlStr;
            showExpressInstall(att, par, replaceElemIdStr, callbackFn);
            return;
          } else setVisibility(replaceElemIdStr, true);
          if (callbackFn) callbackFn(callbackObj);
        });
      } else if (callbackFn) callbackFn(callbackObj);
    },
    switchOffAutoHideShow: function() {
      autoHideShow = false;
    },
    ua: ua,
    getFlashPlayerVersion: function() {
      return {
        major: ua.pv[0],
        minor: ua.pv[1],
        release: ua.pv[2]
      };
    },
    hasFlashPlayerVersion: hasPlayerVersion,
    createSWF: function(attObj, parObj, replaceElemIdStr) {
      if (ua.w3) return createSWF(attObj, parObj, replaceElemIdStr);
      else return undefined;
    },
    showExpressInstall: function(att, par, replaceElemIdStr, callbackFn) {
      if (ua.w3 && canExpressInstall())
        showExpressInstall(att, par, replaceElemIdStr, callbackFn);
    },
    removeSWF: function(objElemIdStr) {
      if (ua.w3) removeSWF(objElemIdStr);
    },
    createCSS: function(selStr, declStr, mediaStr, newStyleBoolean) {
      if (ua.w3) createCSS(selStr, declStr, mediaStr, newStyleBoolean);
    },
    addDomLoadEvent: addDomLoadEvent,
    addLoadEvent: addLoadEvent,
    getQueryParamValue: function(param) {
      var q = doc.location.search || doc.location.hash;
      if (q) {
        if (/\?/.test(q)) q = q.split('?')[1];
        if (param == null) return urlEncodeIfNecessary(q);
        var pairs = q.split('&');
        for (var i = 0; i < pairs.length; i++)
          if (pairs[i].substring(0, pairs[i].indexOf('=')) == param)
            return urlEncodeIfNecessary(
              pairs[i].substring(pairs[i].indexOf('=') + 1)
            );
      }
      return '';
    },
    expressInstallCallback: function() {
      if (isExpressInstallActive) {
        var obj = getElementById(EXPRESS_INSTALL_ID);
        if (obj && storedAltContent) {
          obj.parentNode.replaceChild(storedAltContent, obj);
          if (storedAltContentId) {
            setVisibility(storedAltContentId, true);
            if (ua.ie && ua.win) storedAltContent.style.display = 'block';
          }
          if (storedCallbackFn) storedCallbackFn(storedCallbackObj);
        }
        isExpressInstallActive = false;
      }
    }
  };
})();

$(function() {
  function json2str(json) {
    try {
      return JSON.stringify(json);
    } catch (e) {
      return;
    }
  }
  $('#form-tabs li').on('click', function() {
    var holder = {
      name: '例如: 不要说话 陈奕迅',
      id: '例如: 25906124',
      url: '例如: http://music.163.com/#/song?id=25906124',
      'pattern-name': '^.+$',
      'pattern-id': '^[\\w\\/\\|]+$',
      'pattern-url': '^https?:\\/\\/\\S+$'
    };
    var filter = $(this).data('filter');
    $(this).addClass('am-active').siblings('li').removeClass('am-active');
    $('#music_input')
      .data('filter', filter)
      .attr({
        placeholder: holder[filter],
        pattern: holder['pattern-' + filter]
      })
      .removeClass('am-field-valid am-field-error am-active')
      .closest('.am-form-group')
      .removeClass('am-form-success am-form-error')
      .find('.am-alert')
      .hide();
    if (filter === 'url') {
      $('.music-type').hide();
    } else {
      $('.music-type').show();
    }
  });
  $('#form-vld').validator({
    onValid: function(validity) {
      $(validity.field).closest('.am-form-group').find('.am-alert').hide();
    },
    onInValid: function(validity) {
      var $field = $(validity.field);
      var $group = $field.closest('.am-form-group');
      var $alert = $group.find('.am-alert');
      var msgs = {
        name: '将 名称 和 作者 一起输入可提高匹配度',
        id: '输入错误，请查看下面的帮助',
        url: '输入错误，请查看下面的帮助'
      };
      var msg =
        msgs[$field.data('filter')] || this.getValidationMessage(validity);
      if (!$alert.length) {
        $alert = $(
          '<div class="am-alert am-alert-danger am-animation-shake"></div>'
        )
          .hide()
          .appendTo($group);
      }
      $alert.html(msg).show();
    },
    submit: function(validity) {
      validity.preventDefault();
      if (this.isFormValid()) {
        var post_data = {
          music_input: $.trim($('#music_input').val()),
          music_filter: $('#music_input').data('filter'),
          music_type: $('input[name="music_type"]:checked').val()
        };
        if ($('#music_input').data('filter') === 'url') {
          post_data.music_type = '_';
        }
        return $.ajax({
          type: 'POST',
          url: location.href.split('?')[0],
          timeout: 30000,
          data: post_data,
          dataType: 'json',
          beforeSend: function() {
            $('#music_input').attr('disabled', true);
            $('#submit').button('loading');
          },
          success: function(result) {
            if (result.code === 200 && result.data) {
              var mname = result.data[0].name ? result.data[0].name : '暂无';
              var mauthor = result.data[0].author
                ? result.data[0].author
                : '暂无';
              $('#form-vld').slideUp();
              $('.music-main').slideDown();
              $('#music-link').val(result.data[0].link);
              $('#music-src').val(result.data[0].music);
              $('#music-name').val(mname);
              $('#music-author').val(mauthor);
              var html =
                '<div id="player" class="audio-player"><div class="amazingaudioplayer-audios">';
              for (var i = 0; i < result.data.length; i++) {
                var rname = result.data[i].name ? result.data[i].name : '暂无';
                var rauthor = result.data[i].author
                  ? result.data[i].author
                  : '暂无';
                var rpic = result.data[i].pic
                  ? result.data[i].pic
                  : location.href + 'static/nopic.jpg';
                html +=
                  '<a data-artist="' +
                  rauthor +
                  '" data-title="' +
                  rname +
                  '" data-album="' +
                  rname +
                  '" data-info="" data-image="' +
                  rpic +
                  '" data-link="' +
                  result.data[i].link +
                  '" data-src="' +
                  result.data[i].music +
                  '" data-type="audio/mpeg"></a>';
              }
              html += '</div>';
              $('#music-show').html(html);
              $('#player').amazingaudioplayer();
              $(
                '.amazingaudioplayer-prev, .amazingaudioplayer-next, .amazingaudioplayer-track-item'
              ).on('click', function() {
                var index = $('.amazingaudioplayer-track-item-active').index();
                var mlink = $('.amazingaudioplayer-audios a')
                  .eq(index)
                  .data('link');
                var mmusic = $('.amazingaudioplayer-audios a')
                  .eq(index)
                  .data('src');
                var mname = $('.amazingaudioplayer-audios a')
                  .eq(index)
                  .data('title');
                var mauthor = $('.amazingaudioplayer-audios a')
                  .eq(index)
                  .data('artist');
                $('#music-link').val(mlink);
                $('#music-src').val(mmusic);
                $('#music-name').val(mname);
                $('#music-author').val(mauthor);
              });
            } else {
              $('#music_input')
                .closest('.am-form-group')
                .find('.am-alert')
                .html(result.error || '(°ー°〃) 服务器好像罢工了')
                .show();
            }
          },
          error: function(e, t) {
            var errtext = '(°ー°〃) 出了点小问题，请重试';
            if (t === 'timeout') {
              errtext = '(°ー°〃) 请求超时了，可能是您的网络慢';
            }
            $('#music_input')
              .closest('.am-form-group')
              .find('.am-alert')
              .html(errtext)
              .show();
          },
          complete: function() {
            $('#music_input').attr('disabled', false);
            $('#submit').button('reset');
          }
        });
      }
    }
  });
  $('.music-main input').focus(function() {
    $(this).select();
  });
  $('.music-tips .more').on('click', function() {
    $(this).hide();
    $('.music-tips p').show();
  });
  $('#getit').on('click', function() {
    $('audio')[0].pause();
    $('#form-vld').slideDown();
    $('.music-main').slideUp();
    $('.music-main input').val('');
    $('#music-show').html('');
  });
});
