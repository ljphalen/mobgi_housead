/**
 * 刷新
 * 
 * 
 * @param e
 */
function frameRefresh(e) {
	var id = $('#B_history .current').attr('id').substr(4);
	document.getElementById(id).contentWindow.location.reload();
}

/**
 * 鼠标移过对象
 * 
 * @param e
 */
function mouseOverHandle(e) {
	var target = e.target;
	if (target != 'LI') {
		target = $.parent(target);
	}
	e.currentTarget.addClass('hover');
}
/**
 * 鼠标移出对象
 * 
 * @param e
 */
function mouseOutHandle(e) {
	var target = e.target;
	while (target != 'LI') {
		target = $.parent(target);
	}
	target.removeClass('hover');
}
/**
 * 刷新
 * 
 * @returns {Boolean}
 */
function fullscreen() {
	var scr = $('#full_screen_style');
	if (scr.length > 0 && !scr.attr('disabled')) {
		scr.attr('disabled', true);
		$('#fullScreen').removeClass('admin_unfull').addClass('admin_full').text('全屏');
		return;
	}
	if (scr.length == 0) {
		scr = $('<link type="text/css" rel="stylesheet" href="' + FULL_CSS
				+ '" id="full_screen_style"/>');
		$(document.body).append(scr);
	}
	scr.attr('disabled', false);
	$('#fullScreen').removeClass('admin_full').addClass('admin_unfull').text('退出全屏');
	return false;
}
/**
 * 提交页面搜索信息
 * 
 * @param data
 */
function descsubmit(data) {
	if (data.code !== 0) {
		showError(data.msg);
	};
	$('#descrip_form').dialog('close');
}
/**
 * 初始化函数
 */
function init() {
	// 绑定事件
	$('#B_main_block a').each(function(index) {
		var name = $(this).attr('href').substr($(this).attr('href').indexOf('#') + 1);

		if (name == 'modemanage') {
			$(this).mouseover(showModes)
			$(this).mouseout(hideModes);
		} else {
			showMenu2($('#B_main_block a').first());
			$(this).click(function(){
				showMenu2($(this));
			});
		}
	});

	$('#admenu').mouseover(cancelHideModes).mouseout(hideModes);
	$('#admenu a').click(function() {
				showMenu2($(this));
			});
	$('#fullScreen').click(function() {
				fullscreen($(this));
			});
	$('#fullScreen2').click(function() {
			fullscreen($(this));
		});
	// 后台地图
	$('#pagedesc').click(function() {
				pagedesc();
			});
	$('#shortcutHandle').click(function() {
				shortcut();
			});
	// 上一页
	$('#menu_next a').click(scrollMenu);
	$('#toppage').click(function(e) {
				$('#pagesetting').css('display', '').mouseover(function(e) {
							e.stopPropagation();
						});
				document.body.onmouseover = pageHide;
				e.stopPropagation();
			});

	if (document.addEventListener) {
		document.addEventListener('DOMMouseScroll', scrollWheel, false);
	} else {
		window.onmousescroll = document.onmousewheel = scrollWheel;
	}
	// $(document).scroll(scrollWheel);
	// 加个标签
	$('#B_history');
	$('#pagesetting li').click(function(e) {
				var target = e.target, input = e.target;
				while (input.tagName != 'LI') {
					input = input.parentNode;
				}
				input = $('input', input);
				var ckey = input.attr('id').substr(0, 8);
				var v = (input.checked) ? 0 : 1;
				if (target.tagName != 'INPUT') {
					var x = !v;
					input.checked = x;
					v = v ? 0 : 1;
				}
				if (v) {
					document.cookie = ckey + '=' + v;
				} else {
					Cookie.del(ckey);
				}
				initTips();
				e.stopPropagation();
			});

	var span = $('<li id="tab_default" class="current" onmouseover="$(\'#tab_default\').addClass(\'hover\')" onmouseout="$(\'#tab_default\').removeClass(\'hover\')"><span><a href="javascript:;" hidefocus="true">后台首页</a></span></li>'), a = $(
			'a', span);
	a.data('name', 'default');
	a.click(function() {
				shiftTag($(this));
			});
	$('.del', span).click(function() {
				delSpan($(this));
			});
	$('#B_history .current').removeClass('current');
	$('#B_history').append(span);
	$(window).resize(resizeWin);
	$('#showfuncinput').checked = !Cookie.get('showfunc');
	$('#showtipsinput').checked = !Cookie.get('showtips');
	ajaxForm('descrip_form', descsubmit);
	$('input[name=menukey]').val("default");
}
/**
 * 添加页面描述函数
 */
function pagedesc() {
	menukey = $('input[name=menukey]').val();
	url = $('input[name=url]').val();
	$("textarea[name='descrip']").val("");
	if ('' == menukey || undefined == menukey || 'default' == menukey)
		return;
	$.ajax({
	  type: 'POST',
	  url: getdescurl,
	  data: "menukey="+menukey+"&url=" + encodeURIComponent(url)+"_token="+token,
	  dataType: 'json',
	  success: function(response) {
		if (response.code !== 0) {
			showError("提示", response.msg);
		}
		if (response.data && response.data[0]
				&& "" != response.data[0]['descrip'])
			$("textarea[name='descrip']")
					.val(response.data[0]['descrip']);
		$('.ui-dialog-titlebar-close').show();
		$('#descrip_form').dialog({
					'title' : '编辑页面描述信息',
					width : 590 + 'px'
				});
	}});
}
function pageHide() {
	$('#pagesetting').css('display', 'none');
	document.body.onmouseover = '';
}
var popCount;
function showModes(e) {
	cancelHideModes(e);
	$('#admenu').css('display', '');
	$('#modemanage a').addClass('current');
}
function hideModes(e) {
	popCount = setTimeout(function() {
				$('#admenu').css('display', 'none');
				$('#modemanage a').removeClass('current');
			}, 100);
	$.removeEvent(document.body, 'mouseout', hideModes);
}
function blockModes(e) {
	showModes(e);
	$.removeEvent(e.target, 'mouseout', hideModes);
	document.body.mousedown(hideModes);
}
function cancelHideModes(e) {
	popCount && clearTimeout(popCount);
}
/**
 * 显示二级菜单
 * 
 * @param target
 * @returns {Boolean}
 */
function showMenu2(target) {
	var name = target.attr('href').substr(target.attr('href').indexOf('#') + 1), dl = $('#B_menubar'), ttl = $('#menu_title'), data = {}, title = '';
	dl.css('marginTop', 0);
	$('#B_main_block .current').removeClass('current');
	$('#admenu .current').removeClass('current');
	target.parent() && target.parent().addClass('current');
	if (name == 'common') {
		title = '常用功能';
		data.items = USUALL;
	} else {
		data = SUBMENU_CONFIG[name];
		title = target.html();
	}
	ttl.html(title);
	dl.html('');
	$.each(data.items, function(index, o) {
				if (o.items && 0 == o.items.length)
					return;
				if (o == '-') {
					dl.append($('<div class="hr"></div>'));
					return;
				}
				// 建立dt
				var a, dt = $('<dt></dt>');
				if (o.url) {
					a = $('<a href="' + o.url + '">' + o.name + '</a>');
					a.click(function() {
								openWinHandle($(this));
								return false;
							});
					a.data('name', name + '-' + o.id);
				} else {
					a = $('<a href="#' + name + '-' + o.id + '">' + o.name
							+ '</a>');
					if (o.disabled) {
						dt.css('disabled');
						dt.html(o.name);
						dl.append(dt);
						return;
					} else {
						a.click(toggleSubMenu);
						dt.addClass('expand');
					}
				}
				dt.append(a);
				dl.append(dt);
				if (o.items) {
					var dd = $('<dd style=""></dd>'), ul = $('<ul></ul>');
					dd.append(ul);
					$.each(o.items, function(index, n) {
								var li = $('<li></li>');
								var lk = $('<a href="' + n.url + '">' + n.name + '</a>');
								lk.data('name', name + '-' + o.id + '-' + n.id);
								li.append(lk);
								ul.append(li);
							});
					dl.append(dd);
					$('#B_menubar dd a').click(function() {
								openWinHandle($(this));
								return false;
							});
				}
			});
	resizeWin();
	return false;
}
/**
 * 窗口重置大小
 */
function resizeWin() {
	$('#menu_next').css('visibility', '');
	var m = parseInt($('#menu_next').css('top'))
			|| $('#menu_next').offset().top;
	n = parseInt($('.menubar').height()), p = parseInt($('#B_menubar')
			.css('top'))
			|| 0, q = Math.min(m - n - 85, 0);
	if (q < 0) {
		$('#menu_next').css('visibility', '');
	} else {
		$('#menu_next').css('visibility', 'hidden');
	}
	var menu_nav = $('#B_menunav');
	menu_nav.css('height', $(window).height() - 150 + 'px');
}
/**
 * 显示下拉菜单
 * 
 * @param target
 */
function showBreadList(target) {
	if (target.attr('tagName') != 'SPAN') {
		target = target.parentNode;
	}
	var mid = target.attr('data-id'), cls = mid ? mid.split('-') : []
	main = false;
	switch (cls.length) {
		case 0 :
			ls = MAIN_BLOCK;
			main = true;
			break;
		case 1 :
			ls = SUBMENU_CONFIG[cls[0]].items;
			break;
		case 2 :
			ls = findItem(SUBMENU_CONFIG[cls[0]].items, cls[1]).items;
			break;
		case 3 :
			ls = findItem(findItem(SUBMENU_CONFIG[cls[0]].items, cls[1]).items,
					cls[2]).items;
			break;
	}
	var sHtml = '<div class="admenu_bg"><h2 class="treename">' + target.html()
			+ '</h2><ul>';
	if (main) {
		$.each(ls, function(index, v) {
					var nid = mid ? mid + '-' + v.id : v.id, nam = v.name;
					o = SUBMENU_CONFIG[v.id];
					if (o && o.items && o.items.length) {
						var r = o.items[0];
						nid += '-' + r.id;
						if (r.items && r.items.length) {
							nid += '-' + r.items[0].id;
						}
						sHtml += '<li><a href="#" onclick="return openWin(\''
								+ nid + '\');">' + nam + '</a></li>';
					}
				});
	} else {
		$.each(ls, function(index, o) {
					var nid = mid ? mid + '-' + o.id : o.id;
					if (o.items && o.items.length) {
						var r = o.items[0];
						nid += '-' + r.id;
						if (r.items && r.items.length) {
							nid += '-' + r.items[0].id;
						}
					}
					sHtml += '<li><a href="#" onclick="return openWin(\'' + nid
							+ '\');">' + o.name + '</a></li>';
				});
	}
	sHtml += '</ul>';
	if($('#breadTip').length>0) $('#breadTip').remove();
	var offset = target.offset(), top = (offset.top + 1) + 'px', left = (offset.left + 1)
			+ 'px';
	var div = $('<div id="breadTip" style="position:absolute;z-index:9999;visibility:visible;display:block"></div>')
			.css({
						top : top,
						left : left
					});
	div.mouseup(function() {
				$(this).hide();
			});
	$(document.body).append(div);
	$("#breadTip").html(sHtml);
	$("#breadTip").show();

}
/**
 * 点击子菜单事件
 * 
 * @param e
 */
function toggleSubMenu(e) {
	var node = e.target.parentNode.nextSibling;
	if (node && node.tagName == 'DD') {
		if (node.style.display == "none") {
			node.style.display = '';
			e.target.parentNode.className = 'current';
		} else {
			node.style.display = 'none';
			e.target.parentNode.className = 'expand';
		}
	}
	resizeWin();
}
/**
 * 打开窗口
 * 
 * @param target
 * @returns {Boolean}
 */
function openWinHandle(target) {
	if (target[0].tagName != 'A') {
		target = target.parent();
	}
	$("input[name='menukey']").val(target.data('name'));
	$('input[name="url]').val(target.href);
	openWin(target.data('name'));
	return false;
}
/**
 * 查找对象
 * 
 * @param ar
 * @param id
 * @returns
 */
function findItem(ar, id) {
	for (key in ar) {
		if (ar[key].id == id) {
			return ar[key];
		}
	}
	return null;
}
function shortcut() {
	var arBread = $('#breadCrumb'), mid = 'shortcut', name = '后台地图', frame = $('#'
			+ mid);
	arBread.html('<span>当前位置</span><em>&gt;</em><span>后台地图</span>');
	if (frame.length > 0) {
		if (frame[0].style.display == 'none') {
			$('#B_frame iframe').hide();
			frame.show();
			$('#B_history .current').removeClass('current');
			$('#tab_' + mid).addClass('current');
		}
		return false;
	}
	var span = $('<li id="tab_'
			+ mid
			+ '" class="current"  onmouseover="$(\'#tab_'
			+ mid
			+ '\').addClass(\'hover\')" onmouseout="$(\'#tab_'
			+ mid
			+ '\').removeClass(\'hover\')"><span><a href="javascript:void(0);" hidefocus="true">'
			+ name
			+ '</a><a href="javascript:;" class="del">关闭</a></span></li>'), a = $(
			'a', span);
	a.data('name', mid);
	a.click(function() {
				shiftTag($(this));
			});
	$('.del', span).click(function() {
				delSpan($(this));
			});
	$('#B_history .current').removeClass('current');
	$('#B_history').append(span);
	$('#B_frame iframe').css('display', 'none');
	var iframe = '<iframe id="'
			+ mid
			+ '" src="$default" style="height:100%;width:100%;" scrolling="0" frameborder="0"></iframe>';
	$('#B_frame').append(iframe);
	// span.scrollIntoView();
	return false;
}
function openWin(mid, url, nickname) {
	$('#breadTip').remove();
	var arBread = $('#breadCrumb');
	if (mid) {
		// 获取Name
		var cls = mid.split('-'), ob = SUBMENU_CONFIG, name, frame = $('#'
				+ mid);
		if (cls[0] == 'common') {
			openWin(findParent(cls[1]));
			return false;
		}
		if (cls[0] == 'search') {
			arBread
					.html('<span>当前位置</span><em>&gt;</em><span data-id="">搜索结果</span>');
		}
		if (cls.length > 1) {
			html = '<span>当前位置</span><em>&gt;</em><span class="admenu_down" data-id="">'
					+ findItem(MAIN_BLOCK, cls[0]).name + '</span>';
			arBread.html(html);
			ob = findItem(ob[cls[0]].items, cls[1]);
			$('input[name=name]').val(ob.name);
			html += '<em>&gt;</em><span class="admenu_down" data-id="' + cls[0]
					+ '">' + ob.name + '</span>';
			arBread.html(html);
		}
		if (ob.items) {
			ob = findItem(ob.items, cls[2]);
			$('input[name=name]').val(ob.name);
			html += '<em>&gt;</em><span class="admenu_down" data-id="' + cls[0]
					+ '-' + cls[1] + '">' + ob.name + '</span>';
			arBread.html(html);
		}
		name = nickname ? nickname : ob.name;
		$('#breadCrumb span').click(function() {
					showBreadList($(this));
				});
		if (!$('#tab_' + mid).length) {
			var span = $('<li id="tab_'
					+ mid
					+ '" name="tab_'
					+ mid
					+ '" class="current"  onmouseover="$(\'#tab_'
					+ mid
					+ '\').addClass(\'hover\')" onmouseout="$(\'#tab_'
					+ mid
					+ '\').removeClass(\'hover\')"><span><a href="javascript:;" hidefocus="true">'
					+ name
					+ '</a><a href="javascript:;" class="del">关闭</a></span></li>'), a = $(
					'a', span);
			a.data('name', mid);
			a.click(function() {
						shiftTag($(this));
					});
			$('.del', span).click(function() {
						delSpan($(this));
					});
			$('#B_history .current').removeClass('current');
			$('#B_history').append(span);
			$('#B_history').scrollTop = span.offset().top;
		}
		if (frame.length > 0) {
			if (frame[0].style.display == 'none') {
				$('#B_frame iframe').css('display', 'none');
				frame.show();
				$('#B_history .current').removeClass('current');
				$('#tab_' + mid).addClass('current');
				if(url) frame.attr('src', url);
				return false;
			}
			src = url || ob.url || frame[0].attr('src');
			frame.attr('src', src);
			// setTimeout(initTips,500);
			return false;
		}
	} else {
		mid = 'wrong';
		name = nickname;
		arBread.html('<span>当前位置</span><em>&gt;</em><span data-id="">'
				+ nickname + '</span>');
	}
	$('#B_frame iframe').css('display', 'none');
	var iframe = $('<iframe id="'
			+ mid
			+ '" src="'
			+ (url || ob.url)
			+ '" style="height:100%;width:100%;" scrolling="0" frameborder="0"></iframe>');
	$('#B_frame').append(iframe);
	document.getElementById(mid).onload = function() {
		onframeload(document.getElementById(mid).contentWindow);
	};
	// setTimeout(initTips, 500);
	return false;
}

function onframeload(w) {

	$("textarea[name='descrip']").html("");

	// 获取当前选中的标签
	subname = '';
	navs = getElementsByClassName('nav3', w.document);
	if (navs.length == 1) {
		li = getElementsByClassName('current', navs[0]);
		if (li.length == 1) {
			a = li[0].getElementsByTagName('A');
			if (a.length == 1) {
				subname = a[0].innerText;
			}
		}
	}
	// 获取当前页面的描述信息
	helps = getElementsByClassName('help_a', w.document);
	descrip = $("textarea[name='descrip']");
	if (helps.length > 0) {
		$.each(helps, function(index, n) {
					helpstr = $(n).html();
					helpstr = helpstr.replace(/(^\s*)|(\s*$)/g, "").replace(
							/&gt;/g, ">").replace(/&lt;/g, "<").replace(
							/&amp;/g, "&").replace(/\<[^>]*>/g, "").replace(
							/\n/g, "");
					if ("" == descrip.html()) {
						descrip.html(helpstr + ";\n");
					} else {
						descrip.html(helpstr + ";\n");
					}
				});
	}

	$('input[name=subname]').val(subname);
	$('input[name=url]').val(w.document.baseURI);
}

function shiftTag(target) {
	var mid = target.parent().parent().attr('id').substr(4), frame = $('#'
			+ mid), cls = mid.split('-'), ob = SUBMENU_CONFIG, arBread = $('#breadCrumb');
	if (frame.length > 0 && frame[0].style.display == 'none') {
		$('#B_frame iframe').css('display', 'none');
		frame.show();
		$('#B_history .current').removeClass('current');
		$('#tab_' + mid).addClass('current');
	}
	$('input[name=menukey]').val(mid);
	$('input[name=url]').val($('#' + mid).src);
	if (mid == 'default') {
		$('#breadCrumb')
				.html('<span>当前位置</span><em>&gt;</em><span>后台首页</span>');
	} else {
		if (cls.length > 1) {
			html = '<span>当前位置</span><em>&gt;</em><span class="admenu_down" data-id="">'
					+ findItem(MAIN_BLOCK, cls[0]).name + '</span>';
			arBread.html(html);
			ob = findItem(ob[cls[0]].items, cls[1]);
			html += '<em>&gt;</em><span class="admenu_down" data-id="' + cls[0]
					+ '">' + ob.name + '</span>';
			arBread.html(html);
		}
		if (ob.items) {
			ob = findItem(ob.items, cls[2]);
			html += '<em>&gt;</em><span class="admenu_down" data-id="' + cls[0]
					+ '-' + cls[1] + '">' + ob.name + '</span>';
			arBread.html(html);
		}
		$('#breadCrumb span').click(function() {
					showBreadList($(this));
				});
	}
}
function delSpan(target) {
	delSpan2(target.parent().parent());
}
function delSpan2(li) {
	// 切换焦点
	var newli = li.prev() || li.next();
	if (li.hasClass('current')) {
		if (newli.length == 0) {
			if (li.id == 'tab_default') {
				return false;
			}
			var span = $('<li id="tab_default" class="current" onmouseover="$(\'#tab_'
					+ mid
					+ '\').addClass(\'hover\')" onmouseout="$(\'#tab_'
					+ mid
					+ '\').removeClass(\'hover\')"><span><a href="javascript:;" hidefocus="true">后台首页</a></span></li>'), a = $(
					'a', span);
			a.data('name', 'default');
			a.click(function() {
						shiftTag($(this));
					});
			$('.del', span).click(function() {
						delSpan($(this));
					});
			$('#B_history .current').removeClass('current');
			$('#B_history').append(span);
			$('#breadCrumb')
					.html('<span>当前位置</span><em>&gt;</em><span>后台首页</span>');
			var iframe = '<iframe id="default" src="$default" style="height:100%;width:100%;" scrolling="0" frameborder="0"></iframe>';
			$('#B_frame').append(iframe);

		} else {
			var mid = newli.attr('id').substr(4);
			if (mid == 'default') {
				$('#breadCrumb')
						.html('<span>当前位置</span><em>&gt;</em><span>后台首页</span>');
			}
			openWin(mid);
		}
	}
	li.remove();
	$('#' + $('a', li).data('name')).remove();
}
function next(e) {
	var a, node = $('#B_history .current').next();
	if (node.length == 0) {
		return;
	}
	a = $('a', node);
	$('#B_history .current').removeClass('current');
	node.addClass('current');
	$('#B_frame iframe').hide();
	$('#B_history').scrollTop = node.offset().top;
	$('#' + a.data('name')).show();
}
function prev(e) {
	var a, node = $('#B_history .current').prev();
	if (node.length == 0) {
		return;
	}
	a = $('a', node);
	$('#B_history .current').removeClass('current');
	node.addClass('current');
	$('#B_history').scrollTop = node.offset().top;
	$('#B_frame iframe').hide();
	$('#' + a.data('name')).show();
}
var adminSearchClass = {
	obj : null,
	defaultValue : "后台搜索",
	init : function() {
		this.obj = document.getElementById('keyword');
	},
	focus : function() {
		this.init();
		if (this.obj.value == this.defaultValue) {
			this.obj.value = "";
		}
		this.obj.className = "s-input";
	},
	blur : function() {
		this.init();
		if (this.obj.value == "") {

			this.obj.className = "s-input gray";
			this.obj.value = this.defaultValue;
		}
	},
	keyup : function(evt) {
		var keycode = window.event ? window.event.keyCode : evt.which;
		if (keycode == 13) {
			this.search();
		}
	},
	search : function() {
		if (times == 2) {
			times = 0;
		}
		var keyword = $('#keyword').val();
		if (keyword.length > 1) {
			var searchFrame = $('#search');
			if (searchFrame.length > 0) {
				searchFrame.attr('src', searchurl + '&keyword='
								+ encodeURI(keyword));
				if (searchFrame.hide()) {
					openWin('search');
				}
				return;
			}
			var span = $('<li id="tab_search" class="current" onmouseover="$(\'#tab_search\').addClass(\'hover\')" onmouseout="$(\'#tab_search\').removeClass(\'hover\')"><span><a href="javascript:;" hidefocus="true">搜索结果</a><a href="javascript:;" class="del">关闭</a></span></li>'), a = $(
					'a', span), mid = 'search';
			a.data('name', mid);
			a.click(function() {
						shiftTag($(this));
					});
			$('.del', span).click(function() {
						delSpan($(this));
					});
			$('#B_history .current').removeClass('current');
			$('#B_history').append(span);
			$('#B_frame iframe').css('display', 'none');
			var iframe = '<iframe id="'
					+ mid
					+ '" src="'
					+ searchurl
					+ '&keyword='
					+ encodeURI(keyword)
					+ '" style="height:100%;width:100%;" scrolling="0" frameborder="0"></iframe>';
			$('#B_frame').append(iframe);
			return false;
		} else {
			if (times < 1) {
				alert('至少输入两个字节');
			}
			times++;
		}
	}
}
function findParent(id) {
	var l = MAIN_BLOCK.length;
	for (var i = 0; i < l; i++) {
		var tmp_id_1 = MAIN_BLOCK[i].id;
		if (tmp_id_1 == id) {
			return id;
		}
		var params = SUBMENU_CONFIG[MAIN_BLOCK[i].id].items, m = params.length;
		for (var j = 0; j < m; j++) {
			var tmp_id_2 = params[j].id;
			if (tmp_id_2 == id) {
				return tmp_id_1 + '-' + id;
			}
			// 三级
			var params2 = params[j].items;
			if (params2) {
				var n = params2.length;
				for (var k = 0; k < n; k++) {
					var tmp_id_3 = params2[k].id;
					if (tmp_id_3 == id) {
						return tmp_id_1 + '-' + tmp_id_2 + '-' + tmp_id_3;
					}
				}
			}

		}
	}
	return '';
}
function scrollMenu(e) {
	var m = parseInt($('#menu_next').css('top'))
			|| $('#menu_next').offset().top;
	p = parseInt($('#B_menubar').css('marginTop')) || 0, n = parseInt($('.menubar')
			.height())
			- ($.browser.msie ? 0 : p), q = Math.min(m - n - 95, 0);
	if (e.target.className.indexOf('pre') > -1) {
		$('#B_menubar').css('marginTop', Math.min(0, p + 25));
	} else if (e.target.className.indexOf('next') > -1) {
		$('#B_menubar').css('marginTop', Math.max(q, p - 25));
	}
}

function scrollWheel(e) {
	var direct = 0;
	e = e || window.event;
	if (e.wheelDelta) {// IE/Opera/Chrome
		direct = -e.wheelDelta;
	} else if (e.detail) {// Firefox
		direct = e.detail;
	}
	var m = parseInt($('#menu_next').css('top'))
			|| $('#menu_next').offset().top;
	p = parseInt($('#B_menubar').css('marginTop')) || 0, n = parseInt($('.menubar')
			.height())
			- ($.browser.msie ? 0 : p), q = Math.min(m - n - 95, 0);
	$('#B_menubar').css('marginTop', Math.min(0, Math.max(q, p - 25 * direct)));
}
function getElementsByClassName(className, parentElement) {
	if (typeof(parentElement) == 'object') {
		var elems = parentElement.getElementsByTagName("*");
	} else {
		var elems = (document.getElementById(parentElement) || document.body)
				.getElementsByTagName("*");
	}
	var result = [];
	for (i = 0; j = elems[i]; i++) {
		if ((" " + j.className + " ").indexOf(" " + className + " ") != -1) {
			result.push(j);
		}
	}
	return result;
}
/* 初始化信息 */
function initTips() {
	var tips = Cookie.get('showtips') ? 0 : 1;
	_showDesc(tips);
	var desc = Cookie.get('showfunc') ? 0 : 1;
	_showTips(desc);
}
function _showTips(isopen) {
	var iframes = $('iframe');
	var infos = [];
	$.each(iframes, function(index, n) {
				infos = infos.concat(getElementsByClassName('admin_info',
						n.contentWindow.document));
			});
	var v = (isopen) ? "block" : "none";
	if (infos) {
		for (var i = 0; i < infos.length; i++) {
			infos[i].style.display = v;
		}
	}
}
function _showDesc(isopen) {
	var iframes = $('iframe');
	var infos = [];
	$.each(iframes, function(index, n) {
				infos = infos.concat(getElementsByClassName('help_a',
						n.contentWindow.document));
			});
	var v = (isopen) ? "block" : "none";
	if (infos) {
		for (var i = 0; i < infos.length; i++) {
			infos[i].style.display = v;
		}
	}
}
function closeAdminTab(win) {
	if (win.frameElement) {
		var mid = win.frameElement.id;
		delSpan2($('#tab_' + mid));
	}
}
var PW = {}, adminNavClass = {};
PW.Dialog = function(cfg) {
	id = cfg.allid ? cfg.allid : findParent(cfg.id);
	openWin(id, cfg.url, cfg.name);
	return {
		loadIframe : function(url) {
		}
	}
}





