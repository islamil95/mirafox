/**
 * Mixin дает возможность анимировать элемент методом animate, в который передается ид элемента.
 * Полный список доступных анимация можно посмотреть здесь https://daneden.github.io/animate.css/
 */

export default {

  methods: {

    /**
     * Анимировать элемент с id анимацией animate
     * @param {string} id
     * @param {string} animation
     */
    animate: function (id, animation) {
      var animationEnd = (function(el) {
        var animations = {
          animation: "animationend",
          OAnimation: "oAnimationEnd",
          MozAnimation: "mozAnimationEnd",
          WebkitAnimation: "webkitAnimationEnd",
        };
        for (var t in animations) {
          if (el.style[t] !== undefined) {
            return animations[t];
          }
        }
      }) (document.createElement("div"));
      $('#' + id).addClass('animated ' + animation).one(animationEnd, function() {
        $(this).removeClass('animated ' + animation);
        if (typeof callback === 'function') {
          callback();
        }
      });
    },

  },

};