/*!
 *@mode 三级级联
 *@Author wangwenlong@ttop.com
 *@Date 2012.02.20
 */

(function($){
	function _mix(r, s, ov){
		if(!s || !r) return r;
		if(ov===undefined) ov = true;

		for(var p in s) {
			if(ov || !(p in r)){
				r[p] = s[p];
			}
		}
		return r;
	}

	function isString(obj){
		return Object.prototype.toString.call(obj).slice(8, -1) === 'String';
	}

	var _getSelected = function(d, v){
			var node = {node1:null, node2:null, node3:null};
			if(!v && v!=0) return node;
			
			for (var o in d) {
				for (var m in d[o].items) {
					for (var s in d[o].items[m].items) {
						node.node3 = d[o].items[m].items[s];
						if (node.node3==v){
							node.node1 = d[o].val;
							node.node2 = d[o].items[m].val;
							return node;
						} else {
							node.node3 = null;
						}
					}
				}
			}
			return node;
		},
		
		_insertOption = function(oS, k, v, n3){
			/*console.log(k, v);
			if(v==='') return;*/
			//if(!v) k = '请选择';
			var op = $('<option>').text(k).val(k+'|'+v).attr('title', v);
			if(v==n3) op.attr('selected','selected');
			op.appendTo(oS);
		},
		
		_threeSelect = function(cfg){
			var oS1 = isString(cfg.s1)? $(cfg.s1) : cfg.s1,
				oS2 = isString(cfg.s2)? $(cfg.s2) : cfg.s2,
				oS3 = isString(cfg.s3)? $(cfg.s3) : cfg.s3,
				v1 = cfg.v1 || null,
				v2 = cfg.v2 || null,
				v3 = cfg.v3 || null;
			
			$.each(cfg.d, function(k,v){
				_insertOption(oS1, k, v.val, v1);
			});
			
			oS1.change(function(){
				oS2.html(''); oS3.html('');
				if(this.selectedIndex==-1) return;
				
				var s1_currVal = this.options[this.selectedIndex].value;
				$.each(cfg.d, function (k, v){
					if (s1_currVal.replace(/^.*\|/g,'')==v.val) {
						if (v.items) {
							$.each(v.items, function (k, v) {
								_insertOption(oS2, k, v.val, v2);
							});
						}
					}
				});
				
				if (oS2[0].options.length==0) _insertOption(oS2, "...", '', v2);
			});
			
			oS2.change(function () {
				oS3.html('');
				var s1_currVal = oS1[0].options[oS1[0].selectedIndex].value;
				if (this.selectedIndex==-1) return;

				var s2_currVal = this.options[this.selectedIndex].value;
				$.each(cfg.d, function (k, v) {
					if (s1_currVal.replace(/^.*\|/g,'') == v.val) {
						if (v.items) {
							$.each(v.items, function (k, v) {
								if (s2_currVal.replace(/^.*\|/g,'') == v.val) {
									$.each(v.items, function (k, v) {
										_insertOption(oS3, k, v, v3);
									});
								}
							});
							if (oS3[0].options.length==0) {//当没有区县时，隐藏下拉框
								_insertOption(oS3, " ", "", v3);
								oS3.hide();
							}
						}
					}
				});
			});

			if(v1){
				oS1.trigger('change');
				oS2.trigger('change');
			}
		};
	
	var linkage = {
		init: function(cfg){
			
			if(cfg.isArea){
				if(!cfg.areaWrap.length) return;
				linkage.areaLinkage(CitySelectData, cfg);
			}
			
			if(cfg.isIndustry){
				if(!cfg.industryWrap.length) return;
				linkage.industryLinkage(IndustrySelectData, cfg);
			}
		},
		
		areaLinkage: function(d, cfg){
			var sltArea = _getSelected(d, cfg.aNode),
				node = {
					d: d,
					v1: sltArea.node1,
					v2: sltArea.node2,
					v3: sltArea.node3
				},
				config = _mix(cfg, node);
			
			_threeSelect(config);
		},
		
		industryLinkage: function(d, cfg){
			var sltIndustry =  _getSelected(d, cfg.iNode),
				node = {
					d: d,
					v1: sltIndustry.node1,
					v2: sltIndustry.node2,
					v3: sltIndustry.node3
				},
				config = _mix(cfg, node);
			
			_threeSelect(config);
		}
	};
	
	$.linkage = linkage.init;
})(jQuery);