;
(function (window, $) {
  //
  function groupDropdown(opts, el) {
    this.$el = el
    this.$opts = opts
    this.state = opts.isUpdata
    this.dropdown = false
    this.init()
  }

  groupDropdown.prototype = {
    selectedData: [],
    // 初始化
    init: function () {
      this.renderTemp()
    },
    //  渲染模板
    renderTemp: function (data) {
      var $el = $(this.$el),
        data = [],
        checkedList = [],
        temp,
        copyData = [],
        selectedData = []
      var opts = this.$opts
      data = opts.data
      var copyData = this.dataFiltration(data)
      //console.log("copyData:", copyData)
      var isNolist = 'style="display:none"'
      if (copyData.length == 0) {
        isNolist = 'style="display:block"'
      }
      temp =
        `<div class="jq-groupDropdown-wrap">
                        <input class="ids" type="hidden" name="` +
        opts.name +
        `" value='' />
                        <div class ="jq-groupDropdown">
                            <a class="jq-gsb-a" title="" >` +
        opts.title +
        `</a>
                            <u class="jq-close">x</u>
                            <i class = "jq-edge"> </i> <span class="jq-num"></span>
                            <div class = "jq-groups-wrap" >
                            <input class="jq-groups-text" name="" value="" />
                            <div class="jq-group-add">
                                <select class="jq-group-pid">
                                </select>
                                <p class="jq-add-btn">添加</p>
                            </div>
                           
                            <div class="jq-group-list"></div>
                            <div class="jq-group" ` +
        isNolist +
        ` >
                                <div class="nolist">暂无数据</div>
                            </div>
                        </div>
                        </div>
                        <ul class = "jq-group-selectedData" ></ul>
                        <div class="clearfloat"></div>
                    </div>`
      $el.html(temp)
      this.updataTemp(data)
    },
    // 数据结构 拼装
    dataFiltration(listData) {
      var copyData = [],
        data = [],
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

    // 更新 渲染 select 和 组list
    updataTemp(data) {
      //console.log('updataTemp:', data)
      if (data.length > 0) {
        $(this.$el)
          .find('.nolist')
          .parent()
          .hide()
      }
      this.$opts.selected = []
      var ids = $(this.$el)
        .find('.ids')
        .val()
      this.$opts.selected = ids.split(',')
      //console.log(this.$opts.selected)
      this.unbindEvent() // 事件解绑
      //this.$opts.data = data
      var copyData = this.dataFiltration(data), // 选中数据更新 ，拼装
        selectedData = (this.selectedData = []),
        temp = '',
        names = [],
        tempLi = '',
        options = ''
      //console.log('copyData:', copyData)
      for (var i in copyData) {
        var childrenLi = ''
        var children = copyData[i]['children']
        for (var key in children) {
          var style = ''
          if (children[key]['checked']) {
            style = 'class="jq-g-checked"'
            selectedData.push(children[key])
            names.push(children[key]['name'])
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
        options =
          options +
          `<option value="` +
          copyData[i]['id'] +
          `">` +
          copyData[i]['name'] +
          `</option>`
      }
      $(this.$el)
        .find('.jq-group-pid')
        .html(options)
      $(this.$el)
        .find('.jq-group-list')
        .html(tempLi)
      $(this.$el)
        .find('.jq-num')
        .html(selectedData.length)
      $(this.$el)
        .find(".ids")
        .val(this.$opts.selected)
      $(this.$el)
        .find('.jq-gsb-a')
        .attr('title', names.toString())
      if (names.length > 0) {
        $(this.$el)
          .find('.jq-gsb-a')
          .html(names.toString())
      } else {
        $(this.$el)
          .find('.jq-gsb-a')
          .html(this.$opts.title)
      }

      this.renderSelected(selectedData) // 渲染(侧边)选中项
      this.bindEvent() // 事件绑定
      renderLayuiForm();
    },
    // 渲染 选中项（侧边）
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
          `</li>`
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
      $(this.$el).on('click', '.jq-group ul li', $.proxy(action.selected, this))
      $(this.$el).on(
        'input propertychange',
        '.jq-groups-text',
        $.proxy(action.search, this)
      )
      $(this.$el).on('click', '.jq-add-btn', $.proxy(action.add, this))
      $(this.$el).on('click', '.jq-close', $.proxy(action.clearSelected, this))
      $(this.$el).on(
        'click',
        '.jq-groupDropdown',
        $.proxy(action.dropdownShow, this)
      )
      $(document).on('click', $.proxy(action.dropdownHide, this)) //
    },
    // 事件解绑
    unbindEvent: function () {
      $(this.$el).off('click', '.jq-group ul li')
      $(this.$el).off('input propertychange', '.jq-groups-text')
      $(this.$el).off('click', '.jq-add-btn')
      $(this.$el).off('click', '.jq-close')
      $(this.$el).off('click', '.jq-groupDropdown-wrap')
      $(document).off('click')
    },
    // 销毁对象
    destroy: function () {
      this.unbindEvent()
      $(this).remove()
    },
    // 标签数据 请求
    allLabelAjaxRequest() {
      var callback = $.proxy(action.allLabel, this)
      this.ajaxRequest(this.$opts.requestData, callback)
    },

    // 添加二级标签 请求
    addLabelAjaxRequest: function (data) {
      var that = this,
        addLabelOpts = this.$opts.addLabelOpts
      var jsonData = $.extend(that.$opts.addLabelOpts.data, data)
      that.$opts.addLabelOpts.data = jsonData
      var callback = $.proxy(action.addLabel, this)
      this.ajaxRequest(that.$opts.addLabelOpts, callback)
    },

    // ajax 请求过滤
    ajaxRequest(setting, callback) {
      if (!setting.url) {
        callback({
          code: 400,
          data: {
            msg: '请求失败'
          }
        })
        return false
      }
      if (!setting.type) {
        setting.type = 'post'
      }
      $.ajax({
        data: setting.data,
        type: setting.type,
        url: setting.url,
        success: function (data) {
          callback({
            code: 200,
            data: data,
            setting: setting
          })
        },
        error: function (xhr, msg, err) {
          callback({
            code: 400,
            data: {
              xhr: xhr,
              msg: msg,
              err: err
            },
            setting: setting
          })
        }
      })
    }
  }

  // 事件逻辑
  var action = {
    // alldata updata
    allLabel: function (callData) {
      var that = this
      if (callData.code == 200) {
        var data = callData.data
        if (data.success) {
          var jsonData = data.data
          if (typeof jsonData == 'string') {
            jsonData = JSON.parse(jsonData)
          }
          that.$opts.data = jsonData
          that.updataTemp(that.$opts.data)
        }
      }
    },
    // 添加二级标签 回调函数
    addLabel: function (callData) {
      var that = this,
        setting = callData.setting
      if (callData.code === 200) {
        var data = callData.data
        if (data.success) {
          var obj = data.data
          that.$opts.selected.push(obj.id)
          $(that.$el)
            .find('.ids')
            .val(that.$opts.selected)

          that.$opts.data.push(obj)
          // console.log(' that.$opts.data:', that.$opts.data)
          that.updataTemp(that.$opts.data)
        }
      }
      this.$opts.addLabelCallback(callData.data) // 回调函数
    },
    // 添加二级标签
    add: function (e) {
      e = e || window.event
      e.stopPropagation()
      var pid = $(this.$el)
        .find('.jq-group-pid')
        .val()
      var name = $(this.$el)
        .find('.jq-groups-text')
        .val()
      if (name && pid) {
        var obj = {
          pid: pid,
          title: name
        }
        this.addLabelAjaxRequest(obj)
      } else {
        this.$opts.addLabelCallback({
          success: false,
          msg: '请选择填写标签名'
        })
      }
    },
    // 清除选中
    clearSelected: function () {
      //console.log('clearSelected')
      this.$opts.selected = [] // 清空选中
      this.selectedData = []
      $(this.$el)
        .find('.ids')
        .val('')
      this.updataTemp(this.$opts.data)
    },
    // 菜单显示
    dropdownShow: function (e) {
      // console.log(e.target)
      this.dropdown = true
      $(this.$el)
        .find('.jq-groups-wrap')
        .addClass('active')
      $(this.$el)
        .find('.jq-close')
        .show()
      $(this.$el)
        .find('.jq-edge')
        .addClass('jq-edgeed')
      if (this.$opts.isUpdata && this.state) {
        this.state = false
        this.allLabelAjaxRequest()
      }
      e.stopPropagation()
    },
    // 菜单隐藏
    dropdownHide: function (e) {
      var etarget = e.target || e.srcElement
      var that = $(this.$el)
      var _this = this
      that.find('.jq-groupDropdown').each(function (index, val) {
        var target = etarget
        //console.log(target)
        while (target != document && target != that) {
          target = target.parentNode
        }
        if (target == document) {
          $('.jq-groups-wrap').removeClass('active')
          $('.jq-edge').removeClass('jq-edgeed')
          $('.jq-close').hide()
          _this.dropdown = false
        }
      })
      if (this.$opts.isUpdata) {
        this.state = true
      }
    },
    // 选中
    selected: function (e) {
      var target = $(e.target),
        list,
        arr = [],
        names = [],
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
        names.push(name)
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
      if (names.length == 0) {
        $(this.$el)
          .find('.jq-gsb-a')
          .html(this.$opts.title)
      } else {
        $(this.$el)
          .find('.jq-gsb-a')
          .html(names.toString())
      }

      $(this.$el)
        .find('.jq-gsb-a')
        .attr('title', names.toString())
      this.selectedData = selected
      this.$opts.selected = arr
      this.renderSelected(selected)
      this.$opts.onSelect(arr)
      e.stopPropagation()
    },
    // 搜索
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
        data: [], // [{ pid:0 , id:1 ,name:'xx' }] , 默认渲染数据
        selected: [], // ids: [ 1 , 2 ,3] , 默认选中项
        selectedShow: true, // 是否显示 侧边选中项
        name: 'ids[]', // input 选中值名称（可用于form提交）
        title: '请选择', // 下拉菜单名 默认（请选择）
        onSelect: noop, // 选中值 回调函数
        isUpdata: false, // 是否每次打开，请求标签数据 默认（不请求更新）
        // 标签数据 请求参数
        requestData: {
          data: null, // 请求参数
          url: null, // 请求url
          type: 'post' // 请求类型
        },
        // 添加 组子集 请求参数
        addLabelOpts: {
          data: null,
          url: null, // 请求url
          type: 'post' // 请求类型
        },
        // 添加 组子集请求 回调函数
        addLabelCallback: noop
      }
      setting = setting || {}
      var opts = $.extend(defaults, setting)
      $(el).data('groupDropdown', new groupDropdown(opts, el))
    })
  }
})(window, $)