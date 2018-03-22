---
title: JavaScript框架设计笔记-1-种子模块
categories: js
tags:
  - js
  - js-framework
date: 2016-12-16 16:15:31
updated: 
---

PS:本系列笔记来源于《JavaScript框架设计》一书，但是非常尴尬的是，我买的不是第二版的，= =！[《JavaScript框架设计（第2版）》](http://www.epubit.com.cn/book/details/4849)一书，学习JS框架底层库知识，一个框架应该提供那些功能，应该如何区分这些模块等等，第二版前2章在网上有公开的，可以免费阅读。

注：作者提到的很多都是以[Avalon](http://avalonjs.coding.me/)作为示例的，而本系列笔记仅提取个人记录之处，同时，书中的模块基于CommonJS规范（即Node模块的定义方式）

#### 种子模块介绍
种子模块也叫核心模块，是框架的最先执行的部分。即便像jQuery那样的单文件函数库，它的内部也分许多模块，必然有一些模块冲在前面立即执行；有一些模块只有用到才执行；也有一些模块（补丁模块）可有可无，存在感比较弱，只在特定浏览器下才运行。

既然是最先执行的模块，那么就要求其里面的方法是历经考验、千锤百炼的，并且能将这个模块变得极具扩展性、高可用、稳定性。

（1）扩展性，是指方便将其他模块的方法或属性加入进来，让种子迅速成长为“一棵大树”。

（2）高可用，是指这里的方法是极其常用的，其他模块不用重复定义它们。

（3）稳定性，是指不能轻易在以后版本中删除，要信守承诺。

参照许多框架与库的实现，作者认为种子模块应该包含如下功能：对象扩展、数组化、类型判定、无冲突处理、domReady。

#### 对象扩展
我们需要一种机制，将新功能添加到我们的命名空间上。命名空间，是指我们这个框架在全局作用域暴露的唯一变量，它多是一个对象或一个函数。命名空间通常也就是框架名字。我们可以看一下别人是如何为框架起名字的。 https://www.zhihu.com/question/46804815

回到主题，对象扩展这种机制，我们一般做成一个方法，叫做extend或mixin。JavaScript对象在属性描述符[3]（Property Descriptor）没有诞生之前，是可以随意添加、更改、删除其成员的，因此扩展一个对象非常便捷。由于此功能这么常用，到后来ES6就干脆支持它了，于是有了 Object.assgin。如果要低端浏览器直接用它，可以使用以下polyfill

关于polyfill：*Polyfilling是由RemySharp提出的一个术语，它是用来描述复制缺少的API和API功能的行为。你可以使用它编写单独应用的代码，而不用担心其他浏览器原生是不是支持。实际上，polyfills并不是新技术，也不是和HTML5捆绑到一起的。*

```js
function ToObject(val) {
    if (val == null) {
        throw new TypeError('Object.assign cannot be called with null or undefined');
    }

    return Object(val);
}
module.exports = Object.assign || function (target, source) {
    var from;
    var keys;
    var to = ToObject(target);

    for (var s = 1; s < arguments.length; s++) {
        from = arguments[s];
        keys = Object.keys(Object(from));

        for (var i = 0; i < keys.length; i++) {
            to[keys[i]] = from[keys[i]];
        }
    }

    return to;
};
```

#### 数组化
浏览器下存在许多类数组对象，如function内的arguments，通过document.forms、form.elements、doucment.links、select.options、document.getElementsByName、document.getElementsBy TagName、childNodes、children等方式获取的节点集合（HTMLCollection、NodeList），或依照某些特殊写法的自定义对象。

通常来说，使用Array.prototype.slice.call就能转换我们的类数组对象了，但旧版本IE下的HTMLCollection、NodeList不是Object的子类，采用如上方法将导致IE执行异常。设法让IE下的Array.prototype.slice能切割节点集合就一帆风顺了。
```js
//https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Array/slice
/**
* Shim for "fixing" IE's lack of support (IE < 9) for applying slice
* on host objects like NamedNodeMap, NodeList, and HTMLCollection
* (technically, since host objects have been implementation-dependent,
* at least before ES6, IE hasn't needed to work this way).
* Also works on strings, fixes IE < 9 to allow an explicit undefined
* for the 2nd argument (as in Firefox), and prevents errors when
* called on other DOM objects.
*/

var _slice = Array.prototype.slice
try {
    // Can't be used with DOM elements in IE < 9
    _slice.call(document.documentElement)
} catch (e) { // Fails in IE < 9
    // This will work for genuine arrays, array-like objects,
    // NamedNodeMap (attributes, entities, notations),
    // NodeList (e.g., getElementsByTagName), HTMLCollection (e.g., childNodes),
    // and will not fail on other DOM objects (as do DOM elements in IE < 9)
    Array.prototype.slice = function (begin, end) {
        // IE < 9 gets unhappy with an undefined end argument
        end = (typeof end !== 'undefined') ? end : this.length

        // For native Array objects, we use the native slice function
        if (Array.isArray(this) ) {
            return _slice.call(this, begin, end)
        }

        // For array like object we handle it ourselves.
        var i, cloned = [],
               size, len = this.length

        // Handle negative value for "begin"
        var start = begin || 0
        start = (start >= 0) ? start : len + start

        // Handle negative value for "end"
        var upTo = (end) ? end : len
        if (end < 0) {
           upTo = len + end
        }

        // Actual expected size of the slice
        size = upTo - start

        if (size > 0) {
            cloned = new Array(size)
            if (this.charAt) {
                for (i = 0; i < size; i++) {
                    cloned[i] = this.charAt(start + i)
                }
            } else {
                for (i = 0; i < size; i++) {
                    cloned[i] = this[start + i]
                }
            }
        }

        return cloned
    }
}

avalon.slice = function (nodes, start, end) {
    return _slice.call(nodes, start, end)
}
```
上面的Array.prototype.slice polyfill可以放到另一个补丁模块，这样确保我们的框架在升级时非常轻松地抛弃这些历史包袱。

#### 类型的判定
JavaScript存在两套类型系统：一套是基本数据类型，另一套是对象类型系统。基本数据类型在ES5中包括6种，分别是undefined、string、null、boolean、function和object。基本数据类型是通过typeof来检测的。对象类型系统是以基础类型系统为基础的，通过instanceof来检测。然而，JavaScript自带的这两套识别机制非常不靠谱，于是催生了isXXX系列。就拿typeof来说，它只能粗略识别出string、number、boolean、function、undefined和object这6种数据类型，无法识别Null、RegExp和Argument等细分对象类型。

这里有很多坑:
```js
typeof null// "object"
typeof document.childNodes //safari "function"
typeof document.createElement('embed')//ff3-10 "function"
typeof document.createElement('object')//ff3-10 "function"
typeof document.createElement('applet')//ff3-10 "function"
typeof /\d/i //在实现了ecma262v4的浏览器返回 "function"
typeof window.alert //IE678 "object""
var iframe = document.createElement('iframe');
document.body.appendChild(iframe);
xArray = window.frames[window.frames.length - 1].Array;
var arr = new xArray(1, 2, 3); // [1,2,3]
arr instanceof Array; // false
arr.constructor === Array; // false

window.onload = function() {
    alert(window.constructor);// IE67 undefined
    alert(document.constructor);// IE67 undefined
    alert(document.body.constructor);// IE67 undefined
    alert((new ActiveXObject('Microsoft.XMLHTTP')).constructor);// IE6789 undefined
}
isNaN("aaa") //true
```
上面分4组，第一组是typeof的坑。第二组是instanceof的陷阱，只要原型上存在此对象的构造器它就返回true，但如果跨文档比较，iframe里面的数组实例就不是父窗口的Array的实例。第三组是有关constructor的陷阱，在旧版本IE下，DOM与BOM对象的constructor属性是没有暴露出来的。最后有关NaN，NaN对象与null、undefined一样，在序列化时是原样输出的，但isNaN这方法非常不靠谱，把字符串、对象放进去也返回true，这对我们序列化非常不利。

jQuery发明type方法，这个方法就囊括了isBoolean、isNumber、isString、isFunction、isArray、isDate、isRegExp、isObject及isError。
```js
//jquery2.0
var class2type
// Populate the class2type map
jQuery.each("Boolean Number String Function Array Date RegExp Object Error".split(" "), function(i, name) {
    class2type[ "[object " + name + "]" ] = name.toLowerCase();
});

jQuery.type = function( obj ) {
    if ( obj == null ) {
        return String( obj );
    }
   // Support: Safari <= 5.1 (functionish RegExp)
    return typeof obj === "object" || typeof obj === "function" ?
            class2type[ core_toString.call(obj) ] || "object" :
            typeof obj;
}
```

isPlainObject则是用来判定是否为纯净的JavaScript对象，既不是DOM、BOM对象，也不是自定义“类”的实例对象，制造它的最初目的是用于深拷贝，避开像window那样自己引用自己的对象。在avalon中有一个更精简的版本，由于它只支持IE10等非常新的浏览器及不支持跨iframe，就没有干扰因素了，可以大胆使用ecma262v5的新API。
```js
avalon.isPlainObject = function(obj) {
    return typeof obj === "object" && Object.getPrototypeOf(obj) === Object.prototype
}
```

isWindow
```js
avalon.isWindow = function (obj) {
    if (!obj)
        return false
    // 利用IE6、IE7、IE8 window == document为true,document == window竟然为false的神奇特性
    // 标准浏览器及IE9、IE10等使用正则检测
    return obj == obj.document && obj.document != obj
}

var rwindow = /^\[object (?:Window|DOMWindow|global)\]$/
function isWindow(obj) {//现代浏览器使用这个实现
    return rwindow.test(toString.call(obj))
}

if (isWindow(window)) {
    avalon.isWindow = isWindow
}
```

#### domReady
domReady其实是一种名为DOMContentLoaded事件的别称。不过由于框架的需要，它与真正的DOMContentLoaded有一点区别。在许多JavaScript书籍中，它们都会教导我们把JavaScript逻辑写在window.onload回调中，以防DOM树还没有建完就开始对节点进行操作，导致出错。而对于框架来说，越早介入对DOM的干涉就越好，例如要进行特征侦测之类的。domReady还可以满足用户提前绑定事件的需求。因为有时网页的图片等资源过多，window.onload就迟迟不能触发，这时若还没有绑定事件，用户点击哪个按钮都没反应（除了跳转页面）。因此主流框架都引入domReady机制，并且费了很大劲兼容所有浏览器，具体策略如下。

（1）对于支持DOMContentLoaded事件的使用DOMContentLoaded事件。

（2）旧版本IE使用Diego Perini发现的著名hack!
```js
//http://javascript.nwbox.com/IEContentLoaded/
//by Diego Perini 2007.10.5
function IEContentLoaded(w, fn) {
    var d = w.document, done = false,
            init = function() {
        if (!done) {//只执行一次
            done = true;
            fn();
        }
    };
    (function() {
        try {//在DOM未建完之前调用元素doScroll抛出错误
            d.documentElement.doScroll('left');
        } catch (e) {//延迟再试
            setTimeout(arguments.callee, 50);
            return;
        }
        init();//没有错误则执行用户回调
    })();
    // 如果用户是在domReady之后绑定这个函数，则立即执行它
    d.onreadystatechange = function() {
        if (d.readyState == 'complete') {
            d.onreadystatechange = null;
            init();
        }
    };
}
```
不过有个问题，如果我们的种子模块是动态加载的，在它插入DOM树时，DOM树已经建完了，这该怎么触发我们的ready回调呢？jQuery给出的方案是，onload也一起被监听。但是如果用户的脚本是onload之后才加载进来呢？那么只好判定一下document.readyState是否等于complete，如果是，则说明页面早就domReady，可以执行用户的回调。
```js
var readyList = [];
avalon.ready = function(fn) {
    if (readyList) {
        readyList.push(fn);
    } else {
        fn();
    }
}
var readyFn, ready = W3C ? "DOMContentLoaded" : "readystatechange";
function fireReady() {
    for (var i = 0, fn; fn = readyList[i++]; ) {
        fn();
    }
    readyList = null;
    fireReady = avalon.noop; //惰性函数，防止IE9二次调用_checkDeps
}

function doScrollCheck() {
    try { //IE下通过doScrollCheck检测DOM树是否建完
        html.doScroll("left");
        fireReady();
    } catch (e) {
        setTimeout(doScrollCheck);
    }
}

//在Firefox 3.6之前，不存在readyState属性
//http://www.cnblogs.com/rubylouvre/archive/2012/12/18/2822912.html
if (!DOC.readyState) {
    var readyState = DOC.readyState = DOC.body ? "complete" : "loading";
}
if (DOC.readyState === "complete") {
    fireReady(); //如果在domReady之外加载
} else {
    avalon.bind(DOC, ready, readyFn = function() {
        if (W3C || DOC.readyState === "complete") {
            fireReady();
            if (readyState) { //IE下不能改写DOC.readyState
                DOC.readyState = "complete";
            }
        }
    });
    if (html.doScroll) {
        try { //如果跨域会报错，那时肯定证明是存在两个窗口的
            if (self.eval === parent.eval) {
                doScrollCheck();
            }
        } catch (e) {
            doScrollCheck();
        }
    }
}
```