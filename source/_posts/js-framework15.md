---
title: JavaScript框架设计笔记-15-插件化
categories: js
tags:
  - js
  - js-framework
date: 2017-01-10 15:43:40
updated: 2017-01-10 15:43:40
---

插件化和模块化看起来很像，都是在一个系统上添加新功能。而实际上，模块化是从开发流程上讲的，插件是从功能上讲的。模块化是把一堆相同的接口打包在一起，它们可能会依赖其他模块，但总的而言会返回一个对象或函数供其他调用，而调用者只需要关注它是如何被加载就行了。插件化需要系统和插件件有一套规范，让插件集中放到某个位置，比如一个对象或一个数组，方便统计整理。

#### jQuery插件的一般写法
经过多年发展，jquery有非常多的插件，而且已成为一个不变的模式了。其中extend方法非常关键，extend同时存在与命名空间与原型中，而jquery的原型有一个别名`fn`,因此为jquery扩展一个新的原型方法可以直接以`$.fn.xxx`或`$.fn.extend({a:fun})`的形式实现。同时由于IIFE（立即调用函数表达式），可以减少全局污染。

```js
(function($){
  // 扩展方法到jQuery上
  var Plugin = function(){};
  Plugin.prototype = {};

  $.fn.extend({
    // 插件名字为pluginName
    pluginName: function(options){ // 统一配置对象或方法名
      // 遍历匹配元素的集合
      var args = [].slice.call(arguments, 1);
      return this.each(function(){
        // 在这里编写相应代码进行处理
        var ui = $._data(this, pluginName);

        if(!ui){
          var opts = $.entend(true, {}, $.fn.pluginName.defaults,
            typeof options === 'objects' ? options : {});
          ui = new Plugin(opts, this);
          $._data(this, pluginName, ui);
        }
        if(typeof options === 'string' && typeof ui[options] == 'function'){
          ui[options].apply(ui, args); // 执行插件方法
        }
      });
    }
  });

  $.fn.pluginName.default = { /*默认配置对象*/ }

})(jQuery);  // 传递jQuery到内层作用域
```

在Bootstrap流行起来后，jQuery插件开始模拟它那种只需引用JS就能用的编写方式，原理是，这些插件最后几行都是一些事件代理，当用户触发某些事件，就会自动实例化它们。因此用户不用写js代码，只要引入js文件，html按照规定的模式写，标签上有指定的类名即可。

比如[Bootstrap3的Dropdown插件](https://github.com/twbs/bootstrap/blob/v3.3.7/js/dropdown.js)的主体骨架如下：
```js
+function ($) {
  'use strict';

  // DROPDOWN CLASS DEFINITION 类定义
  // =========================

  var backdrop = '.dropdown-backdrop'
  var toggle   = '[data-toggle="dropdown"]'
  var Dropdown = function (element) {
    $(element).on('click.bs.dropdown', this.toggle)
  }

  Dropdown.prototype.toggle = function (e) {
    // ...
  }
  // ...其他原型方法

  // DROPDOWN PLUGIN DEFINITION 插件定义
  // ==========================

  function Plugin(option) {
    return this.each(function () {
      var $this = $(this)
      var data  = $this.data('bs.dropdown')

      if (!data) $this.data('bs.dropdown', (data = new Dropdown(this)))
      if (typeof option == 'string') data[option].call($this)
    })
  }
  var old = $.fn.dropdown

  $.fn.dropdown             = Plugin
  $.fn.dropdown.Constructor = Dropdown


  // DROPDOWN NO CONFLICT 无冲突处理
  // ====================

  $.fn.dropdown.noConflict = function () {
    $.fn.dropdown = old
    return this
  }

  // APPLY TO STANDARD DROPDOWN ELEMENTS 事件代理，自动初始化
  // ===================================

  $(document)
    .on('click.bs.dropdown.data-api', clearMenus)
    .on('click.bs.dropdown.data-api', '.dropdown form', function (e) { e.stopPropagation() })
    .on('click.bs.dropdown.data-api', toggle, Dropdown.prototype.toggle)
    .on('keydown.bs.dropdown.data-api', toggle, Dropdown.prototype.keydown)
    .on('keydown.bs.dropdown.data-api', '.dropdown-menu', Dropdown.prototype.keydown)

}(jQuery);
```

#### jQuery easy UI的智能加载与个别化指定
UI库通常是非常庞大的，jquery UI由于依赖关系太复杂，所以推广不太顺利，使用者少。jQuery easy UI只要引入核心库和parse.js即可智能加载。parse.js会在domReady之后会扫描DOM树，把带有特定类名的元素全部找出来，并且根据这些类名来加载对应的UI插件的js文件，最后初始化它们，有关加载的实现和依赖关系全部写在easyloader中。
```js
(function($){
  $.parse = {
    auto: true, // 由于加载与初始化是在domReady之后才开始，因此可早早将auto改为false，或者不加载此js文件
    onComplete: function(context){},

    plugins: ['draggable','droppable',/*插件名集合...*/],

    parse: function(context){
      var aa = [];
      for(var i = 0; i < $.parser.plugins.length; i++){
        var name = $.parser.plugins[i];
        // 搜索DOM树
        var r = $('.easyui-'+name, context);
        if(r.length){
          if(r[name]){ // 若jQuery原型有此插件方法，即实例化
            r[name]();
          }else{ // 没有就加载
            aa.push({name: name, jq: r});
          }
        }
      }

      if(aa.length && window.easyloader){
        var names = [];
        for(var i = 0; i < aa.length; i++){
          names.push(aa[i].name);
        }
        easyloader.load(names, function(){ // 加载好了初始化
          for(var i = 0; i < aa.length; i++){
            var name = aa[i].name;
            var jq = aa[i].jq;
            jq[name]();
          }
          $.parser.onComplete.call($.parser, context);
        });
      }else{
        $.parser.onComplete.call($.parser, context);
      }
    },

    parseOptions: function(traget, properties){}
  };

  $(function(){
    if(!window.easyloader && $.parser.auto){
      $.parser.parse();
    }
  });

})(jQuery);
```

关于个性化定制，由于jQuery是集合操作，`$('.selector')`可得到多个匹配的元素，而`$('.selector').tabs(opts)`这样的操作，其实是对所有匹配的元素应用相同的配置。而有的时候需要根据元素本身的情况做出特殊调整，easyUI的方法就是对自定义属性data-options的值两边加上括号，然后通过new Function转换为一个配置对象，同时还支持传入数组，通过style或attr取得指定的目标值，若这个数组元素也同时是一个对象，那么就直接混入。
```js
$.parser.parseOptions = function(target, properties){
  var t = $(target);
  var options = {};
  var s = $.trim(t.attr('data-options'));
  if(s){
    var first = s.substring(0,1);
    var last = s.substring(s.length - 1, 1);

    if(first != '{')
      s = '{' + s;
    if(last != '}')
      s = s + '}';

    options = (new Function('return' + s))();
  }

  if(properties){
    var opts = {};
    for(var i = 0; i < properties.length; i++){
      var pp = properties[i];
      if(typeof pp == 'string'){
        if(pp == 'width' || pp == 'height' || pp == 'left' || pp == 'top'){
          opts[pp] == parseInt(target.style[pp]) || undefined;
        }else{
          opts[pp] == t.attr(pp);
        }
      }else{
        for(var name in pp){
          var type = pp[name];
          if(type == 'boolean'){
            opts[name] = t.attr(name) ? (t.attr(name) == 'true') : undefined;
          }else if(type == 'number'){
            opts[name] = t.attr(name) === '0' ? 0 : parseInt(t.attr(name)) || undefined;
          }
        }
      }
    }
    $.extend(options, opts);
  }

  return options;
}
```
如此，针对每一个元素，得到的配置对象为：
```js
newOptions = $.extend({}, $.fn.pluginName.defaults, $.parser.parseOptions(el), options);
```