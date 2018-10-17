function nationalEmployeeSelector(t){this.apiPath="./api/?a=",this.rootPath="",this.useJSONP=0,this.selection="",this.containerID=t,this.prefixID="empSel"+Math.floor(1e3*Math.random())+"_",this.timer=0,this.q="",this.qDomain="",this.isBusy=1,this.backgroundImage="images/indicator.gif",this.intervalID=null,this.selectHandler=null,this.resultHandler=null,this.selectLink=null,this.selectionData=new Object,this.optionNoLimit=0,this.currRequest=null,this.outputStyle="standard",this.emailHref=!1,this.numResults=0}function groupSelector(t){this.basePath="",this.apiPath="./api/?a=",this.selection="",this.containerID=t,this.prefixID="grpSel"+Math.floor(1e3*Math.random())+"_",this.timer=0,this.q="",this.isBusy=1,this.backgroundImage="images/indicator.gif",this.intervalID=null,this.tag="",this.selectHandler=null,this.resultHandler=null,this.selectLink=null,this.selectionData=new Object,this.inputID="#"+this.prefixID+"input",this.optionNoLimit=0,this.currRequest=null,this.jsonResponse=null,this.numResults=0}nationalEmployeeSelector.prototype.initialize=function(){var e=this,t="",i=["aac.dva.va.gov","cem.va.gov","dva.va.gov","r01.med.va.gov","r02.med.va.gov","r03.med.va.gov","r04.med.va.gov","vba.va.gov","vha.med.va.gov","VHA01","VHA02","VHA03","VHA04","VHA05","VHA06","VHA07","VHA08","VHA09","VHA10","VHA11","VHA12","VHA15","VHA16","VHA17","VHA18","VHA19","VHA20","VHA21","VHA22","VHA23"];for(var s in i)t+='<option value="'+i[s]+'">'+i[s]+"</option>";$("#"+this.containerID).html('<div id="'+this.prefixID+'border" class="employeeSelectorBorder">\t\t\t<select id="'+this.prefixID+'domain" class="employeeSelectorInput" style="width: 100px; display: none">\t\t\t<option value="">All Domains</option>\t\t\t'+t+'\t\t\t</select>\t\t\t<img id="'+this.prefixID+'icon" src="'+e.rootPath+'../libs/dynicons/?img=search.svg&w=16" class="employeeSelectorIcon" alt="search" />\t\t\t<img id="'+this.prefixID+'iconBusy" src="'+e.rootPath+'images/indicator.gif" style="display: none" class="employeeSelectorIcon" alt="busy" />\t\t\t<input id="'+this.prefixID+'input" type="search" class="employeeSelectorInput" style="width: calc(90% - 100px)" aria-label="search input"></input></div>\t\t\t<div id="'+this.prefixID+'result" aria-label="search results"></div>'),$("#"+this.prefixID+"input").on("keydown",function(t){e.showBusy(),e.timer=0,13==t.keyCode&&e.search()}),this.showNotBusy(),this.intervalID=setInterval(function(){e.search()},200)},nationalEmployeeSelector.prototype.showNotBusy=function(){1==this.isBusy&&($("#"+this.prefixID+"icon").css("display","inline"),$("#"+this.prefixID+"iconBusy").css("display","none"),this.isBusy=0)},nationalEmployeeSelector.prototype.showBusy=function(){$("#"+this.prefixID+"icon").css("display","none"),$("#"+this.prefixID+"iconBusy").css("display","inline"),this.isBusy=1},nationalEmployeeSelector.prototype.select=function(t){this.selection=t,$.each($("#"+this.containerID+" .employeeSelected"),function(t,e){$("#"+e.id).removeClass("employeeSelected"),$("#"+e.id).addClass("employeeSelector")}),$("#"+this.prefixID+"emp"+t).removeClass("employeeSelector"),$("#"+this.prefixID+"emp"+t).addClass("employeeSelected"),null!=this.selectHandler&&this.selectHandler()},nationalEmployeeSelector.prototype.setSelectHandler=function(t){this.selectHandler=t},nationalEmployeeSelector.prototype.setResultHandler=function(t){this.resultHandler=t},nationalEmployeeSelector.prototype.setSelectLink=function(t){this.selectLink=t},nationalEmployeeSelector.prototype.clearSearch=function(){$("#"+this.prefixID+"input").val("")},nationalEmployeeSelector.prototype.forceSearch=function(t){$("#"+this.prefixID+"input").val(t.replace(/<[^>]*>/g,""))},nationalEmployeeSelector.prototype.hideInput=function(){$("#"+this.prefixID+"border").css("display","none")},nationalEmployeeSelector.prototype.showInput=function(){$("#"+this.prefixID+"border").css("display","block")},nationalEmployeeSelector.prototype.hideResults=function(){$("#"+this.prefixID+"result").css("display","none")},nationalEmployeeSelector.prototype.showResults=function(){$("#"+this.prefixID+"result").css("display","inline")},nationalEmployeeSelector.prototype.getSelectorFunction=function(t){var e=this;return function(){e.select(t)}},nationalEmployeeSelector.prototype.enableNoLimit=function(){this.optionNoLimit=1},nationalEmployeeSelector.prototype.setDomain=function(t){$("#"+this.prefixID+"domain").val(t)},nationalEmployeeSelector.prototype.search=function(){if(null==$("#"+this.prefixID+"input").val()||null==$("#"+this.prefixID+"input"))return clearInterval(this.intervalID),!1;if(this.timer+=5e3<this.timer?0:200,300<this.timer){var u=$("#"+this.prefixID+"input").val().replace(/<[^>]*>/g,""),t=$("#"+this.prefixID+"domain").val().replace(/<[^>]*>/g,"");if(""==u||null==u||u==this.q&&t==this.qDomain)""==u&&(this.q=u,$("#"+this.prefixID+"result").html(""),this.numResults=0,this.selection="",null!=this.resultHandler&&this.resultHandler()),this.showNotBusy();else{this.q=u,this.qDomain=t,null!=this.currRequest&&this.currRequest.abort();var e="national/employee/search";"#"==this.q.substr(0,1)&&(e="employee/search");var i={url:this.apiPath+e,dataType:"json",data:{q:this.q,noLimit:this.optionNoLimit,domain:t},success:function(t){h.currRequest=null,h.numResults=0,h.selection="",$("#"+h.prefixID+"result").html("");var e="";for(var i in e="micro"==h.outputStyle?'<table class="employeeSelectorTable"><thead><tr><th>Name</th><th>Contact</th></tr></thead><tbody id="'+h.prefixID+'result_table"></tbody></table>':'<table class="employeeSelectorTable"><thead><tr><th>Name</th><th>Location</th><th>Contact</th></tr></thead><tbody id="'+h.prefixID+'result_table"></tbody></table>',$("#"+h.prefixID+"result").html(e),0==t.length&&$("#"+h.prefixID+"result_table").append('<tr id="'+h.prefixID+'emp0"><td style="font-size: 120%; background-color: white; text-align: center" colspan=3>No results for &quot;<span style="color: red">'+u+"</span>&quot;</td></tr>"),h.selectionData=new Object,t){h.selectionData[t[i].empUID]=t[i];var s=null!=t[i].data[1]&&""!=t[i].data[1].data?'<img class="employeeSelectorPhoto" src="'+h.rootPath+"image.php?categoryID=1&amp;UID="+t[i].empUID+'&amp;indicatorID=1" alt="photo" />':"",o=null!=t[i].positionData?t[i].positionData.positionTitle:"";o=""==o&&void 0!==t[i].data[23]?t[i].data[23].data:o;var l="";if(null!=t[i].serviceData&&null!=t[i].serviceData[0].groupTitle){var r=0,a="";for(var n in t[i].serviceData)0<r&&(a=" - "),l+=a+(null==t[i].serviceData[n].groupAbbreviation?t[i].serviceData[n].groupTitle:t[i].serviceData[n].groupAbbreviation)+"<br />",r++}room="",null!=t[i].data[8]&&""!=t[i].data[8].data&&(room=t[i].data[8].data);var p="";p=h.emailHref?null!=t[i].data[6]?'<b>Email:</b> <a href="mailto:'+t[i].data[6].data+'" onclick="event.stopPropagation();">'+t[i].data[6].data+"</a><br />":"":null!=t[i].data[6]?"<b>Email:</b> "+t[i].data[6].data+"<br />":"",phone=null!=t[i].data[5]?"<b>Phone:</b> "+t[i].data[5].data+"<br />":"",midName=""==t[i].middleName?"":"&nbsp;"+t[i].middleName+".",linkText=t[i].lastName+", "+t[i].firstName+midName,null!=h.selectLink&&(linkText='<a href="'+h.selectLink+"&empUID="+t[i].empUID+'">'+linkText+"</a>"),"micro"==h.outputStyle?$("#"+h.prefixID+"result_table").append('<tr id="'+h.prefixID+"emp"+t[i].empUID+'">\t\t\t                \t\t\t<td class="employeeSelectorName" title="'+t[i].empUID+" - "+t[i].userName+'">'+s+linkText+'<br /><span class="employeeSelectorTitle">'+o+'</span></td>\t\t\t                \t\t\t<td class="employeeSelectorContact">'+p+phone+"</td>\t\t\t                \t\t\t</tr>"):$("#"+h.prefixID+"result_table").append('<tr id="'+h.prefixID+"emp"+t[i].empUID+'">\t\t\t                \t\t\t<td class="employeeSelectorName" title="'+t[i].empUID+" - "+t[i].userName+'">'+s+linkText+'<br /><span class="employeeSelectorTitle">'+o+'</span></td>\t\t                    \t\t\t<td class="employeeSelectorService">'+l+"<span>"+room+'</span></td>\t\t                    \t\t\t<td class="employeeSelectorContact">'+p+phone+"</td>\t\t\t                \t\t\t</tr>"),$("#"+h.prefixID+"emp"+t[i].empUID).addClass("employeeSelector"),$("#"+h.prefixID+"emp"+t[i].empUID).on("click",h.getSelectorFunction(t[i].empUID)),h.numResults++}if(1==h.numResults&&(h.selection=t[i].empUID),5<=h.numResults){var c=3;"micro"==h.outputStyle&&(c=2),$("#"+h.prefixID+"result_table").append('<tr id="'+h.prefixID+'tip">\t\t                \t\t\t<td class="employeeSelectorName" colspan="'+c+'" style="background-color: white; text-align: center; font-weight: normal">&#x1f4a1; Can&apos;t find someone? Trying searching their Email address</td>\t\t                \t\t\t</tr>')}null!=h.resultHandler&&h.resultHandler(),h.showNotBusy()},cache:!1},h=this;1==this.useJSONP&&(i.url+="&format=jsonp",i.dataType="jsonp"),this.currRequest=$.ajax(i)}}},groupSelector.prototype.initialize=function(){var e=this;$("#"+this.containerID).html('<div id="'+this.prefixID+'border" class="groupSelectorBorder">\t\t\t<div style="float: left"><img id="'+this.prefixID+'icon" src="'+this.basePath+'../libs/dynicons/?img=search.svg&w=16" class="groupSelectorIcon" alt="search" />\t\t\t<img id="'+this.prefixID+'iconBusy" src="'+this.basePath+'images/indicator.gif" style="display: none" class="groupSelectorIcon" alt="search" /></div>\t\t\t<input id="'+this.prefixID+'input" type="search" class="groupSelectorInput" aria-label="search"></input></div>\t\t\t<div id="'+this.prefixID+'result"></div>'),$(this.inputID).on("keydown",function(t){e.showBusy(),e.timer=0,""==$(e.inputID).val()&&(e.q=""),13==t.keyCode&&(e.q="",e.search())}),this.showNotBusy(),this.intervalID=setInterval(function(){e.search()},200)},groupSelector.prototype.showNotBusy=function(){1==this.isBusy&&($("#"+this.prefixID+"icon").css("display","inline"),$("#"+this.prefixID+"iconBusy").css("display","none"),this.isBusy=0)},groupSelector.prototype.showBusy=function(){$("#"+this.prefixID+"icon").css("display","none"),$("#"+this.prefixID+"iconBusy").css("display","inline"),this.isBusy=1},groupSelector.prototype.select=function(t){for(var e in this.selection=t,nodes=$("#"+this.containerID+" .groupSelected"),nodes)null!=nodes[e].id&&($("#"+nodes[e].id).removeClass("groupSelected"),$("#"+nodes[e].id).addClass("groupSelector"));$("#"+this.prefixID+"grp"+t).addClass("groupSelected"),$("#"+this.prefixID+"grp"+t).removeClass("groupSelector"),null!=this.selectHandler&&this.selectHandler()},groupSelector.prototype.searchTag=function(t){this.tag=t},groupSelector.prototype.setSelectHandler=function(t){this.selectHandler=t},groupSelector.prototype.setResultHandler=function(t){this.resultHandler=t},groupSelector.prototype.setSelectLink=function(t){this.selectLink=t},groupSelector.prototype.clearSearch=function(){$("#"+this.prefixID+"input").val("")},groupSelector.prototype.forceSearch=function(t){$("#"+this.prefixID+"input").val(t.replace(/<[^>]*>/g,""))},groupSelector.prototype.hideInput=function(){$("#"+this.prefixID+"border").css("display","none")},groupSelector.prototype.hideResults=function(){$("#"+this.prefixID+"result").css("display","none")},groupSelector.prototype.showResults=function(){$("#"+this.prefixID+"result").css("display","inline")},groupSelector.prototype.enableNoLimit=function(){this.optionNoLimit=1},groupSelector.prototype.configInputID=function(t){this.inputID=t},groupSelector.prototype.search=function(){if(null==$("#"+this.prefixID+"input").val()||null==$("#"+this.prefixID+"input"))return clearInterval(this.intervalID),!1;if(this.timer+=5e3<this.timer?0:200,300<this.timer){var t=0,i=$("#"+this.prefixID+"input").val().replace(/<[^>]*>/g,"");if(null==i)return clearInterval(this.intervalID),!1;if(0!=this.q.length&&this.q.length<i.length&&0==this.numResults&&(t=1),""!=i&&i!=this.q&&0==t){this.q=i,null!=this.currRequest&&this.currRequest.abort();var s=this;this.currRequest=$.ajax({url:this.apiPath+"group/search",dataType:"json",data:{q:this.q,tag:this.tag,noLimit:this.optionNoLimit},success:function(t){s.currRequest=null,s.selection="",s.numResults=0,s.jsonResponse=t,$("#"+s.prefixID+"result").html("");var e='<table class="groupSelectorTable"><tr><th>Group Title</th></tr><tbody id="'+s.prefixID+'result_table"></tbody></table>';$("#"+s.prefixID+"result").html(e+$("#"+s.prefixID+"result").html()),0==t.length&&$("#"+s.prefixID+"result_table").append('<tr id="'+s.prefixID+'emp0"><td style="font-size: 120%; background-color: white; text-align: center">No results for &quot;<span style="color: red">'+i+"</span>&quot;</td></tr>"),s.selectionData=new Object,$.each(t,function(t,e){s.selectionData[e.groupID]=e,linkText=e.groupTitle,null!=s.selectLink&&(linkText='<a href="'+s.selectLink+"&groupID="+e.groupID+'">'+linkText+"</a>"),$("#"+s.prefixID+"result_table").append('<tr id="'+s.prefixID+"grp"+e.groupID+'"><td class="groupSelectorTitle" title="'+e.groupID+'">'+linkText+"</td></tr>"),$("#"+s.prefixID+"grp"+e.groupID).addClass("groupSelector"),$("#"+s.prefixID+"grp"+e.groupID).on("click",function(){s.select(e.groupID)}),s.numResults++}),1==s.numResults&&(s.selection=t[0].groupID),null!=s.resultHandler&&s.resultHandler(),s.showNotBusy()},cache:!1})}else""==i&&(this.q=i,$("#"+this.prefixID+"result").html(""),this.numResults=0,this.selection="",null!=this.resultHandler&&this.resultHandler()),this.showNotBusy()}};