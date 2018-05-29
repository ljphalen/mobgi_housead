;(function() {
  $.fn.selectorplug = function(setting) {
    return this.each(function() {
      var defaults = {
        url: '',
        data: '',
        name: '',
        title: '请选择',
        object: { key: 'id', value: 'value' }
      }
      var opts = $.extend(defaults, setting)
      var that = $(this)
      that.html('')
      if (opts.url) {
        $.ajax({
          url: opts.url,
          async: true,
          data: opts.data,
          type: 'post',
          dataType: 'json',
          jsonp: 'callback',
          success: function(data) {
            if (data.success) {
              loadPage(true, data.data, that, opts)
            } else {
              loadPage(false, data.msg, that, opts)
            }
          },
          error: function(xhr, msg, e) {
            loadPage(false, msg, that, opts)
            //layer.msg(msg);
          }
        })
        //删除
        that.delegate('.remove', 'click', function() {
          var input = $(this)
            .parent()
            .find('input')
          var id = input.val()
          var name = input.attr('data-name')
          that
            .find('.selectorplug-unselected ul')
            .append('<li data-id="' + id + '">' + name + '</li>')
          $(this)
            .parent()
            .remove()
          var len = that.find('.selectorplug-selected ul li').length
          that.find('.selectorplug-selected .selected-title span').html(len)
        })
        // 点击选中
        that.delegate('.selectorplug-unselected ul li', 'click', function() {
          $(this).addClass('active')
        })
        // 添加
        that.delegate('.selectorplug-add p', 'click', function() {
          var activeHtml = ''
          that.find('.selectorplug-unselected .active').each(function() {
            var id = $(this).attr('data-id')
            var name = $(this).html()
            activeHtml =
              activeHtml +
              `<li>` +
              name +
              `<span class="remove">x</span>
            <input type="checkbox" name="` +
              opts.name +
              `" value="` +
              id +
              `" checked="checked" data-name="` +
              name +
              `"></li>`
            $(this).remove()
          })
          that.find('.selectorplug-selected ul').append(activeHtml)
          var len = that.find('.selectorplug-selected ul li').length
          that.find('.selectorplug-selected .selected-title span').html(len)
        })
        // 搜索
        that.delegate(
          '.selectorplug-search input',
          'input propertychange',
          function() {
            var text = $(this)
              .val()
              .replace(/\s/g, '')
              .toUpperCase()
            var isIndexOF = true
            that.find('.selectorplug-unselected ul li').each(function() {
              var searchText = $(this)
                .html()
                .toUpperCase()
              if (searchText.search(new RegExp(text, 'gi')) != -1) {
                isIndexOF = false
                $(this).show()
              } else {
                $(this).hide()
              }
            })
            if (isIndexOF) {
              that
                .find('.selectorplug-unselected ul')
                .append('<li class="nolist">无匹配数据</li>')
            } else {
              that.find('.selectorplug-unselected ul .nolist').remove()
            }
          }
        )
      }
    })
  }
  // 加载页面
  function loadPage(success, data, that, opts) {
    if (success) {
      var unselectedList = data.unselected
      var selectedList = data.selected
      var unselectedTHML = '',
        selectedHTML = '',
        selectedArr = selectedList
      // 加载未选中列表
      for (var i in unselectedList) {
        var active = ''
        if (selectedArr.length > 0) {
          for (var key in selectedArr) {
            if (
              selectedArr[key][opts.object.key] ==
              unselectedList[i][opts.object.key]
            ) {
              active = 'active'
              selectedArr.splice(key, 1)
              break
            }
          }
        }
        unselectedTHML =
          unselectedTHML +
          `<li class="` +
          active +
          `" data-id="` +
          unselectedList[i][opts.object.key] +
          `">` +
          unselectedList[i][opts.object.value] +
          `</li>`
      }
      // 加载选中列表
      for (var index in selectedList) {
        selectedHTML =
          selectedHTML +
          `<li>` +
          selectedList[index][opts.object.value] +
          `<span class="remove">x</span>
                   <input type="checkbox" name="` +
          opts.name +
          `" value="` +
          selectedList[index][opts.object.key] +
          `" checked="checked" data-name="` +
          selectedList[index][opts.object.value] +
          `">
            </li>`
      }
      var pagehtml =
        `<div class="selectorplug">
                    <div class="selectorplug-unselected">
                        <div class="selectorplug-title">` +
        opts.title +
        `</div>
        <div class="selectorplug-search"><input type="text" value="" placeholder="搜索" ></div>
                        <ul>` +
        unselectedTHML +
        `</ul>
                    </div>
                    <div class="selectorplug-add"><p>添加</p></div>
                    <div class="selectorplug-selected">
                        <div class="selected-title">已选择
                            <span>` +
        selectedList.length +
        `</span>个
                        </div>
                        <ul>
                            ` +
        selectedHTML +
        `
                        </ul> 
                    </div>  
                </div>    
                `
      that.html(pagehtml)
    } else {
      that.html(data)
    }
  }
})()
