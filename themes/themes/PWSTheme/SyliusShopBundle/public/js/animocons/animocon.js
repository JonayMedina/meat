/**
 * Anicomon.js
 * http://www.codrops.com
 *
 * Licensed under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 *
 * Copyright 2016, Codrops
 * http://www.codrops.com
 */
;(function (window) {
  'use strict';

  // taken from mo.js demos
  function isIOSSafari() {
    var userAgent;
    userAgent = window.navigator.userAgent;
    return userAgent.match(/iPad/i) || userAgent.match(/iPhone/i);
  };

  // taken from mo.js demos
  function isTouch() {
    var isIETouch;
    isIETouch = navigator.maxTouchPoints > 0 || navigator.msMaxTouchPoints > 0;
    return [].indexOf.call(window, 'ontouchstart') >= 0 || isIETouch;
  };

  // taken from mo.js demos
  var isIOS = isIOSSafari(),
    clickHandler = isIOS || isTouch() ? 'touchstart' : 'click';

  function extend(a, b) {
    for (var key in b) {
      if (b.hasOwnProperty(key)) {
        a[key] = b[key];
      }
    }
    return a;
  }

  function Animocon(el, options) {
    this.el = el;
    this.options = extend({}, this.options);
    extend(this.options, options);

    this.checked = false;
    this.timeline = new mojs.Timeline();

    for (var i = 0, len = this.options.tweens.length; i < len; ++i) {
      this.timeline.add(this.options.tweens[i]);
    }

    var self = this;

    if (this.el) {
      var checked = (Boolean) ($(self.el[0]).find('input').attr('checked'));

      this.el[0].closest('a.add-true').addEventListener(clickHandler, function () {
        if (self.checked) {
          self.options.onUnCheck();
        } else {
          self.options.onCheck();
          self.timeline.replay();
        }

        self.checked = !self.checked;
      });

      $(this.el[0]).find('input').on('change', function () {
        self.checked = $(this).prop('checked');
      });
    }

    if (checked) {
      self.checked = checked;
      self.options.onCheck();
    }
  }

  Animocon.prototype.options = {
    tweens: [
      new mojs.Burst({})
    ],
    onCheck: function () {
      return false;
    },
    onUnCheck: function () {
      return false;
    }
  };

  function init() {
    if ($('body').find('.icobutton').length > 0) {
      /* Icon 6 */
      var el6 = $('.icobutton'), el6span = el6.find('i');
      var scaleCurve6 = mojs.easing.path('M0,100 L25,99.9999983 C26.2328835,75.0708847 19.7847843,0 100,0');

      new Animocon(el6, {
        tweens: [
          // burst animation
          new mojs.Burst({
            parent: el6,
            radius: { 10: 30 },
            count: 10,
            children: {
              shape: 'line',
              fill: 'white',
              radius: { 7: 0 },
              scale: 1,
              stroke: '#ab162b',
              strokeWidth: 2,
              duration: 1500,
              easing: mojs.easing.bezier(0.1, 1, 0.3, 1)
            },
          }),
          // icon scale animation
          new mojs.Tween({
            duration: 800,
            easing: mojs.easing.bezier(0.1, 1, 0.3, 1),
            onUpdate: function (progress) {
              var scaleProgress = scaleCurve6(progress);
              el6span.attr('style', 'transform: scale3d(' + progress + ',' + progress + ',1)');
            }
          })
        ],
        onCheck: function () {
          el6.attr('style', 'color:#ab162b !important');
          setTimeout(function () {
            el6.find('input[type="checkbox"]').prop('checked', true);
          }, 500);
        },
        onUnCheck: function () {
          el6.attr('style', 'color:#C0C1C3 !important');
          setTimeout(function () {
            el6.find('input[type="checkbox"]').prop('checked', false);
          });
        }
      });
      /* Icon 6 */
    }
  }

  init();
})(window);
