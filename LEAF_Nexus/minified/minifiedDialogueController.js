function dialogController(t,i,o,n,r){this.containerID=t,this.contentID=i,this.indicatorID=o,this.btnSaveID=n,this.btnCancelID=r,this.dialogControllerXhrEvent=null,this.prefixID="dialog"+Math.floor(1e3*Math.random())+"_",this.validators=new Object,this.validatorErrors=new Object,this.validatorOks=new Object,this.invalid=0,$("#"+this.containerID).dialog({autoOpen:!1,modal:!0,height:"auto",width:"auto"}),this.clearDialog();var l=this;$("#"+this.btnCancelID).on("click",function(){l.hide()})}dialogController.prototype.clearDialog=function(){$("#"+this.contentID).empty(),$("#"+this.containerID).dialog("option","title","Org. Chart Editor"),$("#"+this.btnSaveID).off()},dialogController.prototype.setTitle=function(t){$("#"+this.containerID).dialog("option","title",t)},dialogController.prototype.hide=function(){$("#"+this.containerID).dialog("close"),this.clearDialog()},dialogController.prototype.show=function(){""==$("#"+this.contentID).html()&&$("#"+this.indicatorID).css("visibility","visible"),$("#"+this.containerID).dialog("open"),$("#"+this.containerID).css("visibility","visible"),$("input:visible:first, select:visible:first").focus()},dialogController.prototype.setContent=function(t){this.clearValidators(),$("#"+this.contentID).empty().html(t),$("#"+this.indicatorID).css("visibility","hidden")},dialogController.prototype.indicateBusy=function(){$("#"+this.indicatorID).css("visibility","visible")},dialogController.prototype.indicateIdle=function(t){$("#"+this.indicatorID).css("visibility","hidden")},dialogController.prototype.enableLiveValidation=function(){var t=this;$('input[type="text"]').on("keyup",function(){t.isValid()})},dialogController.prototype.isValid=function(){this.invalid=0;for(var t in this.validators)this.validators[t]()?void 0!=this.validatorOks[t]&&this.validatorOks[t]():(this.invalid=1,void 0!=this.validatorErrors[t]?this.validatorErrors[t]():alert("Data entry error. Please check your input."));return 1==this.invalid?0:1},dialogController.prototype.setSaveHandler=function(t){$("#"+this.btnSaveID).off();var i=this;this.dialogControllerXhrEvent=$("#"+this.btnSaveID).on("click",function(){1==i.isValid()&&t()})},dialogController.prototype.setCancelHandler=function(t){$("#"+this.containerID).off(),$("#"+this.containerID).on("dialogclose",function(){t()})},dialogController.prototype.setJqueryButtons=function(t){$("#"+this.containerID).dialog("option","buttons",t)},dialogController.prototype.clickSave=function(){$("#"+this.btnSaveID).click()},dialogController.prototype.setValidator=function(t,i){this.validators[t]=i},dialogController.prototype.clearValidators=function(){this.validators=new Object,this.validatorErrors=new Object},dialogController.prototype.setValidatorError=function(t,i){this.validatorErrors[t]=i},dialogController.prototype.setValidatorOk=function(t,i){this.validatorOks[t]=i};