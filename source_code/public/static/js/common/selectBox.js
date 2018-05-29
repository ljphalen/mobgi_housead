;(function(window, $) {
  // 点击其他区域隐藏
  $.fn.autoHide = function() {
    var that = $(this)
    $(document).bind('mousedown', function(e) {
      var etarget = e.target || e.srcElement
      $('.menuitem').each(function(index, val) {
        var target = etarget
        while (target != document && target != this) {
          target = target.parentNode
        }
        if (target == document) {
          $(this).hide()
        }
      })
    })
    return this
  }
  $.fn.selectBox = function(setting) {
    return this.each(function() {
      var defaults = {
        data: [],
        selected: [],
        name: '',
        title: '请选择'
      }
      var ch = document.body.clientHeight
      var cw = document.body.clientWidth
      var opts = $.extend(defaults, setting)
      var that = $(this)
      var ul = ''
      var topClass = ''
      if (ch - that.offset().top < 500) {
        topClass = 'top'
      } else {
        topClass = 'buttom'
      }
      for (var key in opts.data) {
        var checked = ''
        for (var i in opts.selected) {
          if (opts.selected[i] == key) {
            checked = 'checked'
          }
        }
        ul =
          ul +
          `<li><div><input type="checkbox" lay-skin="primary"  title="` +
          opts.data[key] +
          `" value="` +
          key +
          `" ` +
          checked +
          `/></div></li>`
      }
      var temp =
        `
            <div class="menuitem-wrap">
                <input class="selectBox" type="text" name="` +
        opts.name +
        `" value='' />
                <div class="menuitem-btn">` +
        opts.title +
        `</div>
                <div class="menuitem ` +
        topClass +
        `">
                    <div class="menuitem-search"><input class="mi-search" type="text" placeholder="搜索" value="" /></div>
                    <div class="menuitem-row">
                        <span><input class="layui-btn layui-btn-small" type="button" title="all" value="全选" /></span>
                        <span><input class="layui-btn layui-btn-small" type="button" title="noall" value="全不选"/></span>
                        <span><input class="layui-btn layui-btn-small" type="button" title="opposite" value="反选"/></span>
                    </div>
                    <ul class="menuitem-list">` +
        ul +
        `</ul>
                <div>
            </div>
            `

      that.html(temp)
      that.undelegate()
      that
        .delegate('.mi-search', 'input propertychange', function() {
          //绑定搜索
          var curval = $(this).val()
          var list = that.find(".menuitem-list input[type='checkbox']")
          var is = true
          that.find('.menuitem-list .nolist').remove()
          list.each(function() {
            if (
              $(this)
                .attr('title')
                .search(new RegExp(curval, 'gi')) != -1
            ) {
              $(this)
                .parent()
                .parent()
                .show()
              is = false
            } else {
              $(this)
                .parent()
                .parent()
                .hide()
            }
          })
          if (is) {
            that
              .find('.menuitem-list')
              .append("<li><p class='nolist'>无匹配记录</p></li>")
          }
        })
        .delegate('.menuitem-btn', 'click', function() {
          that.find('.menuitem-edga').addClass('edgarotate')
          that
            .find('.menuitem')
            .show()
            .autoHide()
        })
      var ischecked = []
      that.delegate('.layui-form-checkbox', 'click', function() {
        ischecked = []
        that.find("input[type='checkbox']").each(function() {
          if ($(this).prop('checked')) {
            ischecked.push($(this).val())
          }
        })
        $(that.find("input[name='" + opts.name + "']")).val(ischecked)
      })
      that.delegate('.layui-btn', 'click', function() {
        var isType = $(this).attr('title')
        ischecked = []
        that
          .find(".menuitem-list input[type='checkbox']")
          .each(function(index, el) {
            if (isType == 'all') {
              that.find('.layui-form-checkbox').addClass('layui-form-checked')
              $(this).prop('checked', true)
            } else if (isType == 'noall') {
              $(this).prop('checked', false)
              that
                .find('.layui-form-checkbox')
                .removeClass('layui-form-checked')
            } else if (isType == 'opposite') {
              $(this).prop('checked', !$(this).prop('checked'))
              if (
                $(this)
                  .parent()
                  .find('.layui-form-checkbox')
                  .hasClass('layui-form-checked')
              ) {
                $(this)
                  .parent()
                  .find('.layui-form-checkbox')
                  .removeClass('layui-form-checked')
              } else {
                $(this)
                  .parent()
                  .find('.layui-form-checkbox')
                  .addClass('layui-form-checked')
              }
            }
            if ($(this).prop('checked')) {
              ischecked.push($(this).val())
            }
          })
        $(that.find("input[name='" + opts.name + "']")).val(ischecked)
      })
    })
  }
})(window, jQuery)
