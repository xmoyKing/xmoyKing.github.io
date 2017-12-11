---
title: jquery.noConflict无冲突函数原理
categories: js
tags:
  - js
  - jquery
  - noConflict
date: 2017-12-09 23:08:08
updated:
---

无冲突处理也称为多库共存。许多框架都爱用$作为自己的命名空间。jQuery发明了noConflict函数，能解决多库共存问题。

关于noConflict方法的使用，可以参考[三分钟玩转jQuery.noConflict()](https://www.cnblogs.com/laoyu/p/5189750.html)

关键就是要理解，在页面加载jquery时，jquery自执行初始化对页面中的`jQuery`和`$`进行了缓存，无论这两个变量是否已经被占用，在使用noConflict方法后，都可以选择将其释放回缓存的内容。其实noConflict的布尔参数控制着是否释放`jQuery`变量，而`$`一定会被释放。

使用示例：
```html
<!-- jQuery and $ are undefined -->

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<!-- jQuery and $ now point to jQuery 1.10.2 -->

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js"></script>
<!-- jQuery and $ now point to jQuery 1.7.0 -->

<script>jQuery.noConflict();</script>
<!-- jQuery still points to jQuery 1.7.0; $ now points to jQuery 1.10.2 -->

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>
<!-- jQuery and $ now point to jQuery 1.6.4 -->

<script>var jquery164 = jQuery.noConflict( true );</script>
<!-- jQuery now points to jQuery 1.7.0; $ now points to jQuery 1.10.2; jquery164 points to jQuery 1.6.4 -->
```

源码解析：
```js
var
       window = this,
       undefined,
       _jQuery = window.jQuery,
       _$ = window.$,
       //把window存入闭包中的同名变量，方便内部函数在调用window时不用费大力气查找它
       //_jQuery与_$用于以后重写
       jQuery = window.jQuery = window.$ = function(selector, context) {
    //用于返回一个jQuery对象
    return new jQuery.fn.init(selector, context);
}

jQuery.extend({
    noConflict: function(deep) {
        //引入jQuery类库后，闭包外面的window.$与window.jQuery都储存着一个函数
        //它是用来生成jQuery对象或在domReady后执行其中的函数
        //回顾最上面的代码，在还没有把function赋给它们时，_jQuery与_$已经被赋值了
        //因此它们俩的值必然是undefined
        //因此这种放弃控制权的技术很简单，就是用undefined把window.$里面的jQuery系的函数清除
        //这时Prototype或mootools的$就可以了
        window.$ = _$;//相当于window.$ = undefined
        //这时就要为noConflict添加一个布尔值，为true
        if (deep)
            //但我们必须使用一个接纳jQuery对象与jQuery的入口函数
            //闭包里面的内容除非被window等宿主对象引用，否则就是不可见的
            //因此我们把闭包里面的jQuery return出去，外面用一个变量接纳就可以
            window.jQuery = _jQuery;//相当window.jQuery = undefined
        return jQuery;
    }
});
```

