webpackJsonp([16],{"9KDH":function(t,e,i){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var a={components:{"v-target":i("xHFA").a},created:function(){this.account_id&&this.getTargetList()},watch:{account_id:function(){this.getTargetList()}},computed:{account_id:function(){return this.$store.getters.get_account_action.account_id}},data:function(){return{currentChange:1,dialogTitle:"创建新定向包",dialogVisible:!1,targeting_id:"",filtering:"",targetData:[],pagination:[],target:{}}},methods:{changePage:function(t){this.currentChange=t,this.getTargetList()},handleClose:function(t){t()},newTarget:function(){this.target={targeting_id:0,targeting_name:"",description:"",targeting:{}},this.dialogVisible=!0},editTarget:function(t){this.dialogTitle="编辑定向包",this.target=t,this.dialogVisible=!0},changeTarget:function(t){t&&(this.target.targeting_name=t.targeting_name,this.target.description=t.description,this.target.targeting=t.targeting,0==this.target.targeting_id&&(this.target.targeting_id=t.targeting_id,this.targetData.unshift(this.target))),this.dialogVisible=!1},formatter:function(t,e){return t.address},delTarget:function(t,e){var i=this,a={targeting_id:t.targeting_id};a.account_id=this.account_id,this.$axios.get("/Admin/Marketing_Targetings/delete",{params:a}).then(function(t){var a=t.data;0==a.code?(console.log("delete"),i.targetData.splice(e,1)):i.$message(a.msg)}).catch(function(t){i.$message("网络繁忙，请稍后再试！"),console.log(t)})},getTargetList:function(){var t=this,e={};this.targeting_id>0&&(e.targeting_id=this.targeting_id),""!=this.filtering&&(e.filtering=this.filtering),this.currentChange>1&&(e.page=this.currentChange),e.account_id=this.account_id,this.$axios.get("/Admin/Marketing_Targetings/get",{params:e}).then(function(e){var i=e.data;0==i.code?(t.targetData=i.data.list,t.pagination=i.data.page_info):t.$message(i.msg)}).catch(function(e){t.$message("网络繁忙，请稍后再试！"),console.log(e)})}}},n={render:function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{staticClass:"template-container"},[i("div",{staticClass:"template-item-wrap"},[i("div",{staticClass:"temp-item-inlineblock"},[i("el-input",{attrs:{placeholder:"定向名称",maxlength:120,clearable:""},nativeOn:{keyup:function(e){if(!("button"in e)&&t._k(e.keyCode,"enter",13,e.key))return null;t.getTargetList()}},model:{value:t.filtering,callback:function(e){t.filtering=e},expression:"filtering"}})],1),t._v(" "),i("div",{staticClass:"temp-item-inlineblock"},[i("el-button",{attrs:{type:"primary"},on:{click:function(e){t.getTargetList()}}},[t._v("查询")])],1),t._v(" "),i("div",{staticClass:"temp-item-inlineblock temp-item-right"},[i("el-button",{attrs:{type:"primary"},on:{click:function(e){t.newTarget()}}},[t._v("新建定向")])],1)]),t._v(" "),i("div",{staticClass:"table-wrap"},[i("el-table",{staticStyle:{width:"100%"},attrs:{data:t.targetData}},[i("el-table-column",{attrs:{prop:"targeting_name",label:"定向名称",sortable:"",width:"200"}}),t._v(" "),i("el-table-column",{attrs:{prop:"description",label:"定向描述"}}),t._v(" "),i("el-table-column",{attrs:{prop:"last_modified_time",label:"最后更新时间",width:"200"}}),t._v(" "),i("el-table-column",{attrs:{fixed:"right",label:"操作",width:"180"},scopedSlots:t._u([{key:"default",fn:function(e){return[i("el-button",{attrs:{type:"text",size:"small"},on:{click:function(i){t.editTarget(e.row)}}},[t._v("编辑")]),t._v(" "),i("el-button",{attrs:{type:"text",size:"small"},on:{click:function(i){t.delTarget(e.row,e.$index)}}},[t._v("删除")])]}}])})],1)],1),t._v(" "),i("el-pagination",{attrs:{background:"",layout:"prev, pager, next",total:t.pagination.total_number},on:{"current-change":t.changePage}}),t._v(" "),i("el-dialog",{attrs:{title:t.dialogTitle,visible:t.dialogVisible,width:"50%",modal:!1,"before-close":t.handleClose},on:{"update:visible":function(e){t.dialogVisible=e}}},[i("v-target",{attrs:{target:t.target},on:{change:t.changeTarget}})],1)],1)},staticRenderFns:[]};var r=i("VU/8")(a,n,!1,function(t){i("9W/4")},null,null);e.default=r.exports},"9W/4":function(t,e){}});
//# sourceMappingURL=16.7e3bc07694b202eac437.js.map