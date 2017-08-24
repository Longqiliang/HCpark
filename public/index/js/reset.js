/**
 * Created by rawraw on 2017/1/22.
 */
var wConfirm = window.confirm;
window.confirm = function (message) {
	try {
		var iframe = document.createElement("IFRAME");
		iframe.style.display = "none";
		iframe.setAttribute("src", 'data:text/plain,');
		document.documentElement.appendChild(iframe);
		var alertFrame = window.frames[0];
		var iwindow = alertFrame.window;
		if (iwindow == undefined) {
			iwindow = alertFrame.contentWindow;
		}
		iwindow.confirm(message);
		iframe.parentNode.removeChild(iframe);
	}
	catch (exc) {
		return wConfirm(message);
	}
};
var wAlert = window.alert;
window.alert = function (message) {
	try {
		var iframe = document.createElement("IFRAME");
		iframe.style.display = "none";
		iframe.setAttribute("src", 'data:text/plain,');
		document.documentElement.appendChild(iframe);
		var alertFrame = window.frames[0];
		var iwindow = alertFrame.window;
		if (iwindow == undefined) {
			iwindow = alertFrame.contentWindow;
		}
		iwindow.alert(message);
		iframe.parentNode.removeChild(iframe);
	}
	catch (exc) {
		return wAlert(message);
	}
};
function tabSwitch(a,b,fn,url){
	$(a).off('click').on('click',function(){
		var this_ = this ;
		var box = $(b ).parent();
		var index = $(this_ ).index();
		var ww = $(b ).parent().width();
		$(this_ ).addClass('active' );
		$(this_ ).siblings(a).removeClass('active');
		$(b).removeClass('hidden');
		ww = ww * index;
		box.stop().animate({left: -ww +'px'},300,function(){
			$(b).eq(index).siblings(b).addClass('hidden');
			setCookie( 'tab', index );
			if(fn){
				var tab = $('.active' ).index() + 1;
				fn(tab,url,7,5);
			}
		});

	})
}
function tabRecord(a,b){
	var tab = getCookie('tab');
	if(tab){
		var index = tab;
		var box = $(b).parent();
		var ww = $(b).parent().width();
		$(a).eq(index).addClass('active');
		$(a).eq(index).siblings(a).removeClass('active');
		$(b).removeClass('hidden');
		ww = ww * index;
		box.css({left: -ww +'px'});
		setTimeout(function(){
			$(b).eq(index).siblings(b).addClass('hidden');
		},100)
	}
	//清除tab值
	pushHistory();
	window.addEventListener( "popstate", function( e ){
		delCookie( 'tab' );
		window.history.go( -1 );
	}, false );
}
function pushHistory(){
	var sta = {
		title: "title"
	};
	if( window.history.state === null ){
		window.history.pushState( sta, "title" );
	}
}
function setCookie( name, value ){
	var Days = 30;
	var exp = new Date();
	exp.setTime( exp.getTime() + Days * 24 * 60 * 60 * 1000 );
	document.cookie = name + "=" + value + ";expires=" + exp.toGMTString();
}
function getCookie( name ){
	var arr, reg = new RegExp( "(^| )" + name + "=([^;]*)(;|$)" );
	if( arr = document.cookie.match( reg ) )
		return arr[ 2 ];
	else
		return null;
}
function delCookie( name ){
	var exp = new Date();
	exp.setTime( exp.getTime() - 1 );
	var cval = getCookie( name );
	if( cval != null )
		document.cookie = name + "=" + cval + ";expires=" + exp.toGMTString();
}
function moveanyway(){
	var oW,oH,touch_start,touch_end;
	var block = document.getElementById("publish");
	block.addEventListener("touchstart", function(e) {
		var touches = e.touches[0];
		touch_start = touches.clientX;
		oW = touches.clientX - block.offsetLeft;
		oH = touches.clientY - block.offsetTop;
		//阻止页面的滑动默认事件
		document.addEventListener("touchmove",defaultEvent,false);
	},false);
	block.addEventListener("touchmove", function(e) {
		var touches = e.touches[0];
		var oLeft = touches.clientX - oW;
		var oTop = touches.clientY - oH;
		touch_end = touches.clientX;
		if(oLeft < 0) {
			oLeft = 0;
		}else if(oLeft > document.documentElement.clientWidth - block.offsetWidth) {
			oLeft = (document.documentElement.clientWidth - block.offsetWidth);
		}
		if(oTop < 0) {
			oTop = 0;
		}else if(oTop > document.documentElement.clientHeight - block.offsetHeight) {
			oTop = (document.documentElement.clientHeight - block.offsetHeight);
		}
		block.style.left = oLeft+ "px";
		block.style.top = oTop + "px";
		e.preventDefault();
	},false);
	block.addEventListener("touchend",function() {
		document.removeEventListener("touchmove",defaultEvent,false);
		if(touch_start != touch_end && touch_end){
			var d = document.documentElement.clientWidth - block.offsetWidth;
			if(block.offsetLeft<(document.documentElement.clientWidth - block.offsetWidth)/2) {
				d = 0 ;
			}
			$(block).animate({
				left:d+'px'
			},300)
		}
	},false);
	function defaultEvent(e) {
		e.preventDefault();
	}
}
function imgresize(){
	setTimeout(function(){
		var img = $('.img img' );
		img.each(function(){
			if($(this).width() == $(this).height()){
				$(this).height('78px');
				$(this).width('78px');
			}else if($(this).width() > $(this).height()){
				$(this).height('78px' ).css({'left':-$(this).width()/2+78/2});
			}else{
				$(this).width('78px').css({'top':-$(this).height()/2+78/2});
			}
		});
	},100);
}
function tofixed(){
	var u = navigator.userAgent;
	var word = $('.myword input');
	var bottom =$('.bottom');
	var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
	if(isiOS){
		//兼容ios7
		$(document).scrollTop(1);
		word.focus(function(){
			var noInputViewHeight = $(window).height() - bottom.height();
			var contentHeight = $(document).height() - bottom.height();
			contentHeight = contentHeight > noInputViewHeight ? contentHeight : noInputViewHeight;
			setTimeout(function(){
				var inputTopHeight = bottom.offset().top - $(window).scrollTop()+1;
				var inputTopPos = bottom.offset().top + inputTopHeight+1;
				inputTopPos = inputTopPos > contentHeight ? contentHeight : inputTopPos;
				bottom.css({'position':'absolute', 'top':inputTopPos +1 });
				$(window).on('touchstart', function(){
					word.blur();
				});
			}, 100);
		});
		word.on('blur',function(){
			//输入框失焦后还原初始状态
			var a =$(window).height()-45;
			bottom.css({'position': 'fixed','top':'','bottom':'0'});
		})
	}
}
function numlimit(n){
	var textarea = $('textarea');
	var lock = false;
	textarea.on('compositionstart',function(){
		lock = true;
	});
	textarea.on('compositionend',function(){
		lock = false;
	});
	textarea.on('input',function(){
		if(!lock){
			//键盘输入
			var text = $(this).val();
			text = text.substring(0,n);
			var num = text.length;
			$(this).val(text);
			$('.contentnum span').text(num);
		}
	});
	textarea.bind("paste",function(){
		//黏贴输入
		var text = $(this).val();
		text = text.substring(0,n);
		var num = text.length;
		$(this).val(text);
		$('.contentnum span').text(num);
	}).css("ime-mode", "disabled"); //CSS设置输入法不可用
}
/*提示窗  mui.toast div模式源码修改*/
(function($, window) {
	var CLASS_ACTIVE = 'mui-active';
	/**
	 * 自动消失提示框
	 */
	$.toast = function(message,options) {
		var durations = {
			'long': 3500,
			'short': 1500
		};

		//计算显示时间
		options = $.extend({
			duration: 'short'
		}, options || {});

			if (typeof options.duration === 'number') {
				duration = options.duration>0 ? options.duration:durations['short'];
			} else {
				duration = durations[options.duration];
			}
			if (!duration) {
				duration = durations['short'];
			}
			var toast = document.createElement('div');
			toast.classList.add('mui-toast-container');
			toast.innerHTML = '<div class="' + 'mui-toast-message' + '">' + message + '</div>';
			toast.addEventListener('webkitTransitionEnd', function() {
				if (!toast.classList.contains(CLASS_ACTIVE)) {
					toast.parentNode.removeChild(toast);
					toast = null;
				}
			});
			//点击则自动消失
			toast.addEventListener('click', function() {
				toast.parentNode.removeChild(toast);
				toast = null;
			});
			document.body.appendChild(toast);
			toast.offsetHeight;
			toast.classList.add(CLASS_ACTIVE);
			setTimeout(function() {
				toast && toast.classList.remove(CLASS_ACTIVE);
			}, duration);
			return {
				isVisible: function() {return !!toast;}
			}
	};

})(jQuery, window);

//Polyfill object.assign()
if (!Object.assign) {
	// 定义assign方法
	Object.defineProperty(Object, 'assign', {
		enumerable: false,
		configurable: true,
		writable: true,
		value: function(target) { // assign方法的第一个参数
			'use strict';
			// 第一个参数为空，则抛错
			if (target === undefined || target === null) {
				throw new TypeError('Cannot convert first argument to object');
			}

			var to = Object(target);
			// 遍历剩余所有参数
			for (var i = 1; i < arguments.length; i++) {
				var nextSource = arguments[i];
				// 参数为空，则跳过，继续下一个
				if (nextSource === undefined || nextSource === null) {
					continue;
				}
				nextSource = Object(nextSource);

				// 获取改参数的所有key值，并遍历
				var keysArray = Object.keys(nextSource);
				for (var nextIndex = 0, len = keysArray.length; nextIndex < len; nextIndex++) {
					var nextKey = keysArray[nextIndex];
					var desc = Object.getOwnPropertyDescriptor(nextSource, nextKey);
					// 如果不为空且可枚举，则直接浅拷贝赋值
					if (desc !== undefined && desc.enumerable) {
						to[nextKey] = nextSource[nextKey];
					}
				}
			}
			return to;
		}
	});
}
// 手机号验证
function checkMobile(mobile){
	var reg = /^1[3|4|5|8][0-9]\d{4,8}$/;
	if(typeof mobile == "string" || (typeof  mobile == "number")){
		if(!(reg.test(mobile))){
			console.log("手机号格式错误！");
			return false;
		}else{
			console.log("验证成功");
			return true;
		}
	}
}
/*
 * 身份证15位编码规则：dddddd yymmdd xx p
 * dddddd：6位地区编码
 * yymmdd: 出生年(两位年)月日，如：910215
 * xx: 顺序编码，系统产生，无法确定
 * p: 性别，奇数为男，偶数为女
 *
 * 身份证18位编码规则：dddddd yyyymmdd xxx y
 * dddddd：6位地区编码
 * yyyymmdd: 出生年(四位年)月日，如：19910215
 * xxx：顺序编码，系统产生，无法确定，奇数为男，偶数为女
 * y: 校验码，该位数值可通过前17位计算获得
 *
 * 前17位号码加权因子为 Wi = [ 7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2 ]
 * 验证位 Y = [ 1, 0, 10, 9, 8, 7, 6, 5, 4, 3, 2 ]
 * 如果验证码恰好是10，为了保证身份证是十八位，那么第十八位将用X来代替
 * 校验位计算公式：Y_P = mod( ∑(Ai×Wi),11 )
 * i为身份证号码1...17 位; Y_P为校验码Y所在校验码数组位置
 * @param {Object} obj vue实例对象，用于调用toast弹窗
 */
function validateIdCard(idCard,obj){
	//15位和18位身份证号码的正则表达式
	var regIdCard=/^(^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$)|(^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])((\d{4})|\d{3}[Xx])$)$/;
	if(!obj){
		var obj = jQuery;
		obj.$toast = function (message,options) {
			jQuery.toast(message,options);
		}	
	}
	//如果通过该验证，说明身份证格式正确，但准确性还需计算
	if(regIdCard.test(idCard)){
		if(idCard.length==18){
			var idCardWi=new Array( 7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2 ); //将前17位加权因子保存在数组里
			var idCardY=new Array( 1, 0, 10, 9, 8, 7, 6, 5, 4, 3, 2 ); //这是除以11后，可能产生的11位余数、验证码，也保存成数组
			var idCardWiSum=0; //用来保存前17位各自乖以加权因子后的总和
			for(var i=0;i<17;i++){
				idCardWiSum+=idCard.substring(i,i+1)*idCardWi[i];
			}

			var idCardMod=idCardWiSum%11;//计算出校验码所在数组的位置
			var idCardLast=idCard.substring(17);//得到最后一位身份证号码

			//如果等于2，则说明校验码是10，身份证号码最后一位应该是X
			if(idCardMod==2){
				if(idCardLast=="X"||idCardLast=="x"){
					console.log("验证成功！");
					return true;
				}else{
					obj.$toast("身份证号码错误！");
					return false;
				}
			}else{
				//用计算出的验证码与最后一位身份证号码匹配，如果一致，说明通过，否则是无效的身份证号码
				if(idCardLast==idCardY[idCardMod]){
					console.log("验证成功！");
					return true;
				}else{
					obj.$toast("身份证号码错误！");
					return false;
				}
			}
		}
	}else{
		obj.$toast("身份证格式不正确!");
		return false;
	}
}
