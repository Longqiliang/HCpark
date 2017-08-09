// 接口URL
var apiBaseUrl = "http://wen.inleadcn.com:8086/";
var userAccount = 7741117;
var userNickName = '用户名';
var userheadPic = '../assets/images/profile_small.jpg';

mui.ready(function(){
    if(getItem('userId').isEmpty) {
        window.location.href = '../home/msg_error.html'
    }
});

function createParameter(actionUrl, parameter) {
    var url = apiBaseUrl + actionUrl + '?';
    var param = '';
    if (parameter) {
        for (var key in parameter) {
            param += '&' + key + '=' + parameter[key]
        }
        param = param.substr(1)
    }

    return url + param;
}

/**
 * 存储相关方法
 */
function setCookie(c_name,value,expiredays) {
    expiredays = expiredays ? expiredays : 365;
    var exdate = new Date();
    exdate.setDate(exdate.getDate()+expiredays);
    document.cookie = c_name+ "=" + encodeURIComponent(value) + "; path=/" +
        ((expiredays==null) ? "" : "; expires="+exdate.toGMTString())
}
function getCookie(c_name) {
    if (document.cookie.length > 0) {
        c_start = document.cookie.indexOf(c_name + "=")
        if (c_start != -1) {
            c_start = c_start + c_name.length + 1
            c_end = document.cookie.indexOf(";", c_start)
            if (c_end == -1) c_end = document.cookie.length
            return decodeURIComponent(document.cookie.substring(c_start, c_end))
        }
    }
    return "";
}
function setItem(name, value) {
    window.localStorage.setItem(name, JSON.stringify(value))
}
function getItem(name) {
    return JSON.parse(window.localStorage.getItem(name) || '[]')
}
// 检查手机号码格式
function checkPhone(phone){
    if(!(/^1[34578]\d{9}$/.test(phone))){
        // alert("手机号码有误，请重填");
        return false;
    } else {
        return true;
    }
}
// 复制
function copy(str){
    var save = function(e){
        e.clipboardData.setData('text/plain', str);
        e.preventDefault();
    };
    document.addEventListener('copy', save);
    document.execCommand('copy');
    document.removeEventListener('copy',save);
}
// 检验 undefined 和 null
Array.prototype.isEmpty = function(obj) {
    if(!obj && obj !== 0 && obj !== '') {
        return true;
    }
    if(Array.prototype.isPrototypeOf(obj) && obj.length === 0) {
        return true;　
    }

    if(Object.prototype.isPrototypeOf(obj) && Object.keys(obj).length === 0) {
        return true;
    }
    return false;
};
Array.prototype.contains = function (obj) {
    var i = this.length;
    while (i--) {
        if (this[i] === obj) {
            return true;
        }
    }
    return false;
};
Array.prototype.removeByValue = function(val) {
    for(var i=0; i<this.length; i++) {
        if(this[i] == val) {
            this.splice(i, 1);
            break;
        }
    }
};
Array.prototype.removeById = function(val) {
    for(var i=0; i<this.length; i++) {
        if(this[i].id == val) {
            this.splice(i, 1);
            break;
        }
    }
};
Date.prototype.format = function(format) {
    var date = {
        "M+": this.getMonth() + 1,
        "d+": this.getDate(),
        "h+": this.getHours(),
        "m+": this.getMinutes(),
        "s+": this.getSeconds(),
        "q+": Math.floor((this.getMonth() + 3) / 3),
        "S+": this.getMilliseconds()
    };
    if (/(y+)/i.test(format)) {
        format = format.replace(RegExp.$1, (this.getFullYear() + '').substr(4 - RegExp.$1.length));
    }
    for (var k in date) {
        if (new RegExp("(" + k + ")").test(format)) {
            format = format.replace(RegExp.$1, RegExp.$1.length == 1
                ? date[k] : ("00" + date[k]).substr(("" + date[k]).length));
        }
    }
    return format;
}
// 数组元素移动
var swapItems = function(arr, index1, index2) {
    arr[index1] = arr.splice(index2, 1, arr[index1])[0];
    return arr;
};
// 上移
var upRecord = function(arr, item) {
    for(var i=0; i<arr.length; i++) {
        if(item == arr[i].id) $index = i;
    }

    if($index == 0) {
        return;
    }
    swapItems(arr, $index, $index - 1);
};
// 下移
var downRecord = function(arr, item) {
    for(var i=0; i<arr.length; i++) {
        if(item == arr[i].id) $index = i;
    }

    if($index == arr.length -1) {
        return;
    }
    swapItems(arr, $index, $index + 1);
};

// 获取get参数
function getQueryString(name)
{
    var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if(r != null)
        return  unescape(r[2]);
    return null;
}
// 时间格式化
function getLocalTime(nS) {
    return new Date(nS).toLocaleString().replace(/年|月/g, "-").replace(/日/g, " ");
}
// 判断是否数字
function isNotANumber(inputData) {
    //isNaN(inputData)不能判断空串或一个空格
    //如果是一个空串或是一个空格，而isNaN是做为数字0进行处理的，而parseInt与parseFloat是返回一个错误消息，这个isNaN检查不严密而导致的。
    if (parseFloat(inputData).toString() == "NaN") {
        //alert("请输入数字……");注掉，放到调用时，由调用者弹出提示。
        return false;
    } else {
        return true;
    }
}
function getLength(str) {
    return str.replace(/[\u0391-\uFFE5]/g,"aa").length;
}

function getSuffix(filename) {
    pos = filename.lastIndexOf('.')
    suffix = ''
    if (pos != -1) {
        suffix = filename.substring(pos)
    }
    return suffix;
}
function randomString(len) {
    len = len || 32;
    var chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678';
    var maxPos = chars.length;
    var pwd = '';
    for (i = 0; i < len; i++) {
        pwd += chars.charAt(Math.floor(Math.random() * maxPos));
    }
    return pwd;
}
function getRandomName(filename) {
    var newDate = new Date();
    var dateStr = newDate.format('yyyyMMdd');
    return dateStr + '_' + randomString(10) + getSuffix(filename)
}

// 去掉开发模式警告
Vue.config.productionTip = false;
Vue.filter('time', function (value) {//value为13位的时间戳
    function add0(m) {
        return m < 10 ? '0' + m : m
    }
    var time = new Date(parseInt(value * 1000));
    var y = time.getFullYear();
    var m = time.getMonth() + 1;
    var d = time.getDate();
    var h = time.getHours();
    var min = time.getMinutes();

    return y + '-' + add0(m) + '-' + add0(d) + ' ' + add0(h) + ':' + add0(min);
});
Vue.filter('getLocalTime', function (value) {
    return getLocalTime(value);
});
Vue.filter('substr', function (value) {
    if (value != '' && value != null) {
        return value.substring(0, 6) + '...'
    } else {
        return ''
    }

});