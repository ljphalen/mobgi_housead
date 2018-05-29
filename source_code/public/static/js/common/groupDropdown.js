;
(function (window, $) {
  //
  function groupDropdown(opts, el) {
    this.$el = el
    this.$opts = opts
    this.init()
    // console.log(el)
  }

  groupDropdown.prototype = {
    selectedData: [],
    // 初始化
    init: function () {
      this.renderTemp()
    },
    //  渲染模板
    renderTemp: function () {
      var $el = $(this.$el),
        data = [],
        checkedList = [],
        temp,
        copyData = [],
        selectedData = []
      var opts = this.$opts
      data = opts.data
      checkedList = opts.selected
      selectedData = this.selectedData
      var copyData = this.dataFiltration(data)
      var tempLi = ''
      for (var i in copyData) {
        var childrenLi = ''
        var children = copyData[i]['children']
        for (var key in children) {
          var style = ''
          if (children[key]['checked']) {
            style = 'class="jq-g-checked"'
            selectedData.push(children[key])
          }
          childrenLi =
            childrenLi +
            `<li ` +
            style +
            ` data-id="` +
            children[key]['id'] +
            `" data-pid="` +
            children[key]['pid'] +
            `" >` +
            children[key]['name'] +
            `</li>`
        }

        tempLi =
          tempLi +
          `<div class="jq-group">
                <p data-id="` +
          copyData[i]['id'] +
          `" data-pid="` +
          copyData[i]['pid'] +
          `" class="jq-g-title">` +
          copyData[i]['name'] +
          `</p>
                <ul>` +
          childrenLi +
          `</ul></div>`
      }

      var isNolist = 'style="display:none"'
      if (copyData.length == 0) {
        isNolist = 'style="display:block"'
      }
      temp =
        `<div class="jq-groupDropdown-wrap">
                        <input class="ids" type="hidden" name="` +
        opts.name +
        `" value='' />
                        <div class = "jq-groupDropdown">
                            <input class = "jq-gsb-text" type = "text" value = "" placeholder = "请选择标签" / >
                            <i class = "jq-edge"> </i> <span class="jq-num">` +
        selectedData.length +
        `</span>
                        <div class="jq-groups-tier">
                         <p class="jq-groups-btn">全不选</p>
                        <div class = "jq-groups-wrap" >
                            ` + tempLi + `
                            <div class="jq-group" ` + isNolist + ` >
                                <div class="nolist">暂无数据</div>
                            </div>
                        </div>
                        </div>
                        </div>
                        <ul class = "jq-group-selectedData" ></ul>
                        <div class="clearfloat"></div> 
                    </div>`
      $el.html(temp)
      this.renderSelected(selectedData)
      this.bindEvent()
    },

    // 数据结构 拼装
    dataFiltration(listData) {
      //console.log('dataFiltration:', data)
      var data = [],
        copyData = [],
        checkedList = this.$opts.selected

      for (var i in listData) {
        var obj = {
          name: listData[i]['title'],
          id: listData[i]['id'],
          pid: listData[i]['pid']
        }
        data.push(obj)
      }

      for (var key in data) {
        if (data[key]['pid'] == 0) {
          if (!copyData[data[key]['id']]) {
            copyData[data[key]['id']] = {
              name: data[key]['name'],
              id: data[key]['id'],
              pid: data[key]['pid'],
              children: []
            }
          } else {
            copyData[data[key]['id']] = {
              name: data[key]['name'],
              id: data[key]['id'],
              pid: data[key]['pid']
            }
          }
        } else {
          data[key]['checked'] = false
          for (var i in checkedList) {
            if (data[key]['id'] == checkedList[i]) {
              data[key]['checked'] = true
              break
            }
          }
          if (copyData[data[key]['pid']]) {
            copyData[data[key]['pid']].children.push(data[key])
          } else {
            copyData[data[key]['pid']] = {
              name: data[key]['name'],
              id: data[key]['id'],
              pid: data[key]['pid'],
              children: [data[key]]
            }
          }
        }
      }
      return copyData
    },

    // 渲染 选中项
    renderSelected: function (selectedData) {
      if (!this.$opts.selectedShow) {
        return false
      }
      if (selectedData.length == 0) {
        $(this.$el)
          .find('.jq-group-selectedData')
          .hide()
        return false
      }
      var s_li = ``
      for (var k in selectedData) {
        s_li =
          s_li +
          `<li data-id="` +
          selectedData[k]['id'] +
          `" data-pid="` +
          selectedData[k]['pid'] +
          `" >` +
          selectedData[k]['name'] +
          `<span style="padding:0 5px">x</span></li>`
      }
      $(this.$el)
        .find('.jq-group-selectedData')
        .html(s_li)
      $(this.$el)
        .find('.jq-group-selectedData')
        .show()
    },
    //

    // 事件绑定
    bindEvent: function () {
      $(document).on('click', $.proxy(action.show, this))
      $(this.$el).on(
        'click',
        '.jq-groupDropdown-wrap',
        $.proxy(action.groupDropdown, this)
      )
      $(this.$el).on('click', '.jq-group ul li', $.proxy(action.selected, this))
      $(this.$el).on(
        'input propertychange',
        '.jq-gsb-text',
        $.proxy(action.search, this)
      )
      $(this.$el).delegate(
        '.jq-group-selectedData li',
        'click',
        $.proxy(action.sideSelected, this)
      )
      $(this.$el).on('click', '.jq-groups-btn', $.proxy(action.operation, this))
    },
    // 事件解绑
    unbindEvent: function () {
      $(document).off('click')
      $(this.$el).off('click', '.jq-groupDropdown-wrap')
      $(this.$el).off('click', '.jq-group ul li')
      $(this.$el).undelegate('.jq-group-selectedData li', 'click')
      $(this.$el).off('input propertychange', '.jq-gsb-text')
      $(this.$el).off('click', '.jq-groups-btn')
    },
    // 销毁对象
    destroy: function () {
      this.unbindEvent()
    }
  }

  // 事件逻辑
  var action = {
    operation: function (e) {
      var target = e.currentTarget || e.target || e.srcElement
      var checked = $(target).attr('data-checked')
      var arr = [];
      $(this.$el).find(".jq-group li").removeClass("jq-g-checked");
      $(this.$el)
        .find('.jq-num')
        .html(arr.length)
      $(this.$el)
        .find('.ids')
        .val('')
      $(this.$el).find(".jq-group-selectedData").empty();
      this.$opts.onSelect(arr)
      e.stopPropagation()
    },
    sideSelected: function (e) {
      var target = e.currentTarget || e.target || e.srcElement
      var id = $(target).attr('data-id')
      var pid = $(target).attr('data-pid')
      $(this.$el)
        .find('.jq-group li')
        .each(function () {
          var _id = $(this).attr('data-id')
          var _pid = $(this).attr('data-pid')
          if (id == _id && _pid == pid) {
            $(this).removeClass('jq-g-checked')
            return
          }
        })
      var selected = this.$opts.selected
      for (var i in selected) {
        if (selected[i] == id) {
          selected.splice(i, 1)
          break
        }
      }
      $(target).remove()

      $(this.$el)
        .find('.jq-num')
        .html(selected.length)
      $(this.$el)
        .find('.ids')
        .val(selected)
      this.$opts.onSelect(selected)
      e.stopPropagation()
      //console.log(this.$opts.selected)
    },
    groupDropdown: function (e) {
      // console.log(e.target)
      $(this.$el)
        .find('.jq-groups-tier')
        .show()
      $(this.$el)
        .find('.jq-edge')
        .addClass('jq-edgeed')
    },
    show: function (e) {
      var etarget = e.target || e.srcElement
      var that = $(this.$el)
      that.find('.jq-groupDropdown').each(function (index, val) {
        var target = etarget
        while (target != document && target != this) {
          target = target.parentNode
        }
        if (target == document) {
          that.find('.jq-groups-tier').hide()
          that.find('.jq-edge').removeClass('jq-edgeed')
        }
      })
      e.stopPropagation()
    },
    selected: function (e) {
      var target = $(e.target),
        list,
        arr = [],
        selected = []
      if (target.hasClass('jq-g-checked')) {
        target.removeClass('jq-g-checked')
        this.selectedData.push()
      } else {
        target.addClass('jq-g-checked')
      }
      list = $(this.$el).find('.jq-g-checked')
      list.each(function () {
        // console.log('id', $(this).attr("data-id"));
        var id = $(this).attr('data-id')
        var pid = $(this).attr('data-pid')
        var name = $(this).html()
        arr.push(id)
        selected.push({
          id: id,
          pid: pid,
          name: name
        })
      })
      $(this.$el)
        .find("input[name='" + this.$opts.name + "']")
        .val(arr)
      $(this.$el)
        .find('.jq-num')
        .html(arr.length)
      this.selectedData = selected
      this.$opts.selected = arr
      this.renderSelected(selected)
      this.$opts.onSelect(arr)
      e.stopPropagation()
    },
    search: function (e) {
      var target = $(e.target),
        val = '',
        list,
        isAll = false
      val = target.val()
      list = $(this.$el).find('.jq-group ul li')
      $(this.$el)
        .find('.jq-group')
        .hide()
      list.each(function () {
        var name = $(this)
          .html()
          .toLowerCase()
        if (name.search(new RegExp(val, 'gi')) != -1) {
          $(this)
            .parent()
            .parent()
            .show()
          isAll = true
        }
      })
      if (!isAll) {
        $(this.$el)
          .find('.nolist')
          .parent()
          .show()
      } else {
        $(this.$el)
          .find('.nolist')
          .parent()
          .hide()
      }
    }
  }

  //  命名空间
  $.fn.groupDropdown = function (setting) {
    var noop = function () {}
    return this.each(function (index, el) {
      var defaults = {
        data: [], // [{ pid:0 , id:1 ,name:'xx' }]
        selected: [], // ids: [ 1 , 2 ,3]
        name: 'ids[]',
        title: '请选择标签',
        selectedShow: true,
        onSubmit: noop,
        onSelect: noop
      }
      setting = setting || {}
      var opts = $.extend(defaults, setting)
      $(el).data('groupDropdown', new groupDropdown(opts, el))
    })
  }
})(window, $)