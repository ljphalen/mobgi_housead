/**
 * 基于jquery的级联菜单插件
 * 
 * @date 2014-10-10
 * @author stylering
 */

$(function($) {
	$.fn.chainSelect = function(options) {
		// 是否远程请求数据
		var remote = !!options.remote;
		var chains = options.chains;

		// 更新select菜单
		var updateSelect = function(data, next) {
			var sel = $(next.dom);
			var option = [];
			var i=0, len;

			if (len = data.length) {
				for (; i<len; i++) {
					option.push('<option value="'+ data[i][next.id] +'">' + data[i][next.name] + '</option>')
				}
			} else {
				option.push('<option>-</option>');
			}
			sel.html(option.join(''));
		}

		// 非远程请求时，显示对应数据
		var filterSelect = function(val, next) {

		}

		// 请求远程数据
		var loadData = function(val, next) {
			$.ajax({
				url: next.url + '?id=' + val,
				type: 'GET',
				success: function(result) {
					result = JSON.parse(result);
					if (result.success) {
						updateSelect(result.data, next);
						// if (next.callback) next.callback(result.data);
					}
				}
			})
		}

		// 清空下级select
		var emptySelect = function(chains) {
			$(chains.dom).html('<option>-</option>');
			if (chains.next) emptySelect(chains.next);
		}

		// 绑定select的change事件
		var bindEvent = function(chains) {
			var val, next = chains.next;
			$(chains.dom).change(function() {
				if (val = $(this).find('option:selected').val()) {
					emptySelect(next);
					remote ? loadData(val, next) : filterSelect(val, next);
				}
			})
			if (chains.next.next) bindEvent(next);
		}

		return this.each(function() {
			bindEvent(chains);
			//$(this).trigger('change');
		})
	}
}(jQuery))
