//  渲染table
function renderTable(table, setting, callback) {
  var defaults = {
    method: 'post',
    response: {
      statusName: 'success', //数据状态的字段名称，默认：code success
      statusCode: 0, //成功的状态码，默认：0
      msgName: 'msg', //状态信息的字段名称，默认：msg
      countName: 'count', //数据总数的字段名称，默认：count
      dataName: 'data' //数据列表的字段名称，默认：data
    },
    initSort: {},
    request: {
      pageName: 'page', //页码的参数名称，默认：page
      limitName: 'limit' //每页数据量的参数名，默认：limit
    },
    where: '',
    limit: '10',
    page: true //开启分页
  }
  var opts = $.extend(defaults, setting)
  var callBakcData = {}
  //第一个实例
  table.render({
    elem: opts.elem,
    url: opts.url, //数据接口
    method: opts.method,
    response: {
      statusName: opts.response.statusName, //数据状态的字段名称，默认：code success
      statusCode: opts.response.statusCode, //成功的状态码，默认：0
      msgName: opts.response.msgName, //状态信息的字段名称，默认：msg
      countName: opts.response.countName, //数据总数的字段名称，默认：count
      dataName: opts.response.dataName //数据列表的字段名称，默认：data
    },
    //initSort: opts.initSort,
    where: opts.where,
    limit: opts.limit,
    page: opts.page, //开启分页
    cols: opts.cols,
    done: function(res, curr, count) {
      callBakcData.where = opts.where
      callBakcData.page = curr
      callBakcData.res = res
      callBakcData.count = count
      var searchData = { page: { curr: curr }, where: opts.where }
      searchData.url = window.location.href
      localStorage.searchData = JSON.stringify(searchData) //  缓存 page和查询条件
      renderLayuiFormAndTab()
      if (callback) {
        try {
          callback.done(callBakcData)
        } catch (e) {}
      }
    }
  })
}
// 设置 cookies
function setCookie(name, value) {
  var Days = 1
  var exp = new Date()
  exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000)
  document.cookie =
    name + '=' + escape(value) + ';path=/; expires=' + exp.toGMTString()
}

//重新渲染
function renderLayuiFormAndTab() {
  layui.use(['element', 'form'], function() {
    layui.element.init()
    layui.form.render()
  })
}

function getQueryString(name) {
  var reg = new RegExp('(^|&)' + name + '=([^&]*)(&|$)', 'i')
  var r = window.location.search.substr(1).match(reg)
  if (r != null) return unescape(r[2])
  return ''
}
