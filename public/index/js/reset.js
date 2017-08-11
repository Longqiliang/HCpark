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
