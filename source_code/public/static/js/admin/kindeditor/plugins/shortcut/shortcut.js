KindEditor.lang({
    'shortcut' : '短链接生成功能',
    'shortcut.url' : 'URL',
	'shortcut.linkType' : '打开类型',
	'shortcut.newWindow' : '新窗口',
	'shortcut.selfWindow' : '当前窗口',
	'yes' : '确定',
	'no' : '取消',
	'close' : '关闭',
});

KindEditor.plugin('shortcut', function(K) {
	var self = this, name = 'shortcut';
	self.plugin.shortcut = {
		edit : function() {
			var lang = self.lang(name + '.'),
				html = '<div style="padding:10px 20px;">' +
					//url
					'<div class="ke-dialog-row">' +
					'<label for="keUrl">' + lang.url + '</label>' +
					'<input class="ke-input-text" type="text" id="keUrl" data-pro="linkUrlSel" name="url" value="" style="width:90%;" /></div>' +
					// 选择内容属性
					'<div class="ke-dialog-row"">' +
					'<label for="keType1">选择内容属性</label>' +
					'<select id="keType1" data-pro="contentTypeSel" name="contentType"></select>' +
					'</div>' +
					// 选择渠道商
					'<div class="ke-dialog-row"">' +
					'<label for="keType2">选择渠道商</label>' +
					'<select id="keType2" data-pro="channelTypeSel" name="channelType"></select>' +
					'</div>' +
					// 选择合作商家
					'<div class="ke-dialog-row"">' +
					'<label for="keType3">选择合作商家</label>' +
					'<select id="keType3" data-pro="parnterTypeSel" name="parnterType"></select>' +
					'</div>' +
					//type
					'<div class="ke-dialog-row"">' +
					'<label for="keType">' + lang.linkType + '</label>' +
					'<select id="keType" data-pro="linkTypeSel" name="type"></select>' +
					'</div>' +
					'</div>',
				dialog = self.createDialog({
					name : name,
					width : 400,
					title : self.lang(name),
					body : html,
					yesBtn : {
						name : self.lang('yes'),
						click : function(e) {
							var url = K.trim(urlBox.val());
							var contentTypeVal = K.trim(contentTypeBox.val());
							var channelTypeVal = K.trim(channelTypebox.val());
							var parnterTypeVal = K.trim(parnterTypeBox.val());
							//console.log(url,contentTypeVal,channelTypeVal,parnterTypeVal,selectTextVal);

							if (url == 'http://' || K.invalidUrl(url)) {
								alert(self.lang('invalidUrl'));
								urlBox[0].focus();
								return;
							}

							var str_all = self.cmd.sel.focusNode.data;
							var str_pre = self.cmd.sel.focusNode;
							var str_len1 = self.cmd.sel.baseOffset;
							var str_len2 = self.cmd.sel.extentOffset;
							var str_len3 = self.cmd.sel.focusOffset;
							var str_name = str_all.substr(str_len2,str_len1-str_len3);

							K.ajax(_shortcut_.ajaxUrl, function(data) {
								url = data.data.shortUrl;
								//console.log(url);
								self.exec('createlink', url, typeBox.val()).hideDialog().focus();
							}, 'POST', {
							    'contentType' : contentTypeVal,
							    'channel' : channelTypeVal,
							    'parnter_id' : parnterTypeVal,
							    'topic_id' : _shortcut_.topicId,
							    'url' : url,
							    'title': str_name,
							    'token': token
							});
						}
					}
				}),
				div = dialog.div,
				urlBox = K('input[data-pro="linkUrlSel"]', div),
				typeBox = K('select[data-pro="linkTypeSel"]', div),
				contentTypeBox = K('select[data-pro="contentTypeSel"]', div),
				channelTypebox = K('select[data-pro="channelTypeSel"]', div),
				parnterTypeBox = K('select[data-pro="parnterTypeSel"]', div);

				K.each(_shortcut_.contentType,function(key,val){
					contentTypeBox[0].options[key] = new Option(val,key);
				});

				K.each(_shortcut_.channelType,function(key,val){
					channelTypebox[0].options[key] = new Option(val['name'],val['ch']);
				});

				K.each(_shortcut_.parnterType,function(key,val){
					parnterTypeBox[0].options[key] = new Option(val['name'],val['id']);
				});

				urlBox.val('http://');
				typeBox[0].options[0] = new Option(lang.newWindow, '_blank');
				typeBox[0].options[1] = new Option(lang.selfWindow, '');
				var a = self.plugin.getSelectedLink();
				if (a) {
					self.cmd.range.selectNode(a[0]);
					self.cmd.select();
					urlBox.val(a.attr('data-ke-src'));
					typeBox.val(a.attr('target'));
				}
				urlBox[0].focus();
				urlBox[0].select();
		},
		'delete' : function() {
			self.exec('unlink', null);
		}
	};
	self.clickToolbar(name, self.plugin.shortcut.edit);
});
