---
title: JavaScript框架设计笔记-9-属性模块
categories: js
tags:
  - js
  - js-framework
date: 2017-01-05 14:53:31
updated: 2017-01-05 14:53:31
---

通常将对象的非函数成员叫属性，对于元素节点而言，其属性分为两类，固有属性和自定义属性（特性）。固有属性一般遵循驼峰命名，有默认值，并且无法删除。自定义属性是用户添加的键值对，由于元素节点也是一个普通的JS对象，并没有严格的访问限制，所以命名风格和值的类型都很随意，很有可能会引起循环引用或其他问题，所以浏览器为了规范这些自定义属性，提供了一组API，即setAttribute、getAttribute、removeAttribute，这3个通常就是DOM属性系统了，DOM属性系统对属性名进行小写话处理，属性值统一转为字符串。IE6、7会有兼容性问题（非常尴尬，前端的世界到处是兼容问题）

多年的发展，谁也不知道某个元素节点到底有多少个属性，for...in循环对不可遍历属性无用。而H5对属性进行了更多的分类，比如dataset对象包括所有以data-开头的自定义属性，classList包括所有的类名，且有对应的API操作，formData包括所有要提交到后端的数据...
而且值的类型也是五花八门，有整数、0/1、on/off、yes/no，布尔值等。

#### 如何区分固有属性与自定义属性
探索的方法很多，但是很多都失败了，最后留下了一个方法, 通过API访问和下标访问的差异：
```js
// 有些属性是特殊元素才有的，需要用到第二个参数
function isAttribute(attr, host){
  host = host || document.createElement('div');
  return host.getAttribute(attr) ==== null && host[attr] === void 0;
}
```

#### 如何判断浏览器是否区分固有属性与自定义属性
除了IE6、7不区分固有属性与自定义属性，其他现代浏览器都区分，基本可以不用考虑这个话题了。

#### className的操作
通常操作一个属性只有3个选择，设置、读取、删除，但className比较特殊，它的值是用空格隔开的，分为多个类名，因此对类名的操作就有：读取、添加、删减。总结下来就是：classNames、hasClassName、addClassName、removeClassName、toggleClassName。基本和H5的classList(有add、remove、toggle、contains方法)类似。

简化一下，将这些方法作为工具函数，在不引入任何框架的时候可以非常有用：
```js
var getClass = function(ele){
  return ele.className.replace(/\s+/,' ').split('');
}
var hasClass = function(ele, cls){
  return -1 < (' '+ele.className+' ').indexOf(' '+cls+' ');
}
var addClass = function(ele, cls){
  if(!this.hasClass(ele, cls))
    ele.className += ' '+cls;
}
var removeClass = function(ele, cls){
  if(hasClass(ele, cls)){
    var reg = new RegExp('(\\s|^)'+cls+'(\\s|$)');
    ele.className = ele.className.replace(reg, ' ');
  }
}
var clearClass = function(ele, cls){
  ele.className = '';
}
```

jquery实现如下：

其addClass方法关键在去重,其参数可以为函数，而且通过indexOf来回避已有的类名，添加新类名后需要trim操作,所以非常长：
```js
addClass: function(value){
  var classNames, i, l, elem, setClass, c, cl;
  if(jQuery.isFunction(value)){
    return this.each(function(j){
      jQuery(this).addClass(value.call(this, j, this.className));
    });
  }

  if(value && typeof value === 'string'){
    classNames = value.split(/\s+/);
    for(i = 0, l = this.length; i < l; i++){
      elem = this[i];
      if(elem.nodeType == 1){
        if(!elem.className && classNames.length === 1){
          elem.className = value;
        }else{
          setClass = ' '+elem.className+' ';
          for(c = 0, cl = className.lenght; c < cl; c++){
            if(setClass.indexOf(' '+ classNames[c]+' ') < 0){
              setClass += classNames[c]+' ';
            }
          }
          elem.className = jQuery.trim(setClass);
        }
      }
    }
  }

  return this;
}
```

hash去重法的特点就是快：
```js
addClass: function(item){
  if(typeof item === 'string'){
    for(var i = 0, el; el = this[i++];){
      if(el.nodeType === 1){
        if(!el.className){
          el.className = item;
        }else{
          var obj = {}, set = '';
          (el.className+' '+cls).replace(/\S+/g, function(w){
            if(!obj['@'+w]){ // 对付旧版本IE的toString
              set += w+' ';
              obj['@'+w] = 1;
            }
          });
          el.className = set.slice(0, set.length - 1);
        }
      }
    }
  }
  return this;
}
```

将hash去重改为数组去重，则性能不错，长度也短：
```js
addClass: function(item){
  if(typeof item === 'string'){
    for(var i = 0, el; el = this[i++];){
      if(el.nodeType === 1){
        if(!el.className){
          el.className = item;
        }else{
          var a = (el.className+' '+cls).match(/\S+/g);
          a.sort();
          for(var i = a.length - 1; i > 0; --i)
            if(a[i] == a[i-1])
              a.splice(i, 1);
          el.className = a.join(' ');
        }
      }
    }
  }
  return this;
}
```

removeClass能同时删除多个类名，不传参时清除所有类名：
```js
removeClass: function(item){
  if((item&&typeof item === 'string') || item === void 0){
    // var rnospaces = /\S+/g;
    var classNames = (item || '').match(rnospaces), cl = classNames.length;
    for(var i = 0, node; node = this[i++];){
      if(node.nodeType === 1 && node.className){
        if(item){
          var set = ' '+node.className.match(rnospaces).join(' ') + ' ';
          for(var c = 0; c < cl; c++){
            set = set.replace(' '+ classNames[c] +' ', ' ');
          }
          node.className = set.slice(1, set.length - 1);
        }else{
          node.className = '';
        }
      }
    }
  }
  return this;
}
```

hasClass方法，当第二个参数为true，要求所有匹配的元素都拥有此类名才返回true,若H5的classList可用则直接用原生
```js
hasClass: function(item, every){
  var method = every === true ? 'every' : 'some',
    rclass = new RegExp('(\\s|^)'+item+'(\\s|$)'); // 判断多个元素、正则比indexOf快
    return $.slice(this)[method](function(el){
      // 先转换wield数组
      return 'classList' in el ? el.classList.contains(item): (el.className || '').match(rclass);
    })
}
```

toggleClass，常用于下拉菜单的展开收起,可接收一个布尔值，true表示addClass，false表示removeClass,依靠数据缓存系统，还可以在删除之前把它们存储起来，那么下次加上类名时直接从缓存系统中获取：
```js
toggleClass: function(value){
  var type = typeof value, className, i;
  className = type === 'string' && value.match(/\S+/g) || [];
  return this.each(function(el){
    i = 0;
    if(el.nodeType === 1){
      var self = $(el);
      if(type == 'string'){
        while((className = className[i++])){
          self[self.hasClass(className) ? 'removeClass' : 'addClass'](className);
        }
      }else if(type === 'undefined' || type === 'boolean'){
        if(el.className){
          $._data(el,'__className__', el.className);
        }
        el.className = el.className || value === false ? '' : $._data(el, '__className__') || '';
      }
    }
  });
}
```

#### jquery的属性系统
jquery的属性系统是经年累月，量变引发质变的结果。太过早期就不用关注了，直接看从[jquery1.8.3](https://github.com/jquery/jquery/blob/1.8.3/src/attributes.js)看起：
```js
	prop: function( elem, name, value ) {
		var ret, hooks, notxml,
			nType = elem.nodeType;

    // 跳过注释、文本、特性节点
		// don't get/set properties on text, comment and attribute nodes
		if ( !elem || nType === 3 || nType === 8 || nType === 2 ) {
			return;
		}

		notxml = nType !== 1 || !jQuery.isXMLDoc( elem );

		if ( notxml ) {
      // 若是HTML文档的元素节点
			// Fix name and attach hooks
			name = jQuery.propFix[ name ] || name;
			hooks = jQuery.propHooks[ name ];
		}

    // 写方法
		if ( value !== undefined ) {
			if ( hooks && "set" in hooks && (ret = hooks.set( elem, value, name )) !== undefined ) {
				return ret; // 处理特殊情况

			} else { // 通用情况
				return ( elem[ name ] = value );
			}

		} else { // 读方法
			if ( hooks && "get" in hooks && (ret = hooks.get( elem, name )) !== null ) {
				return ret;

			} else {
				return elem[ name ];
			}
		}
	},

  // ...

  attr: function( elem, name, value, pass ) {
		var ret, hooks, notxml,
			nType = elem.nodeType;

		// don't get/set attributes on text, comment and attribute nodes
		if ( !elem || nType === 3 || nType === 8 || nType === 2 ) {
			return;
		}

		if ( pass && jQuery.isFunction( jQuery.fn[ name ] ) ) {
			return jQuery( elem )[ name ]( value );
		}

		// Fallback to prop when attributes are not supported
		if ( typeof elem.getAttribute === "undefined" ) {
			return jQuery.prop( elem, name, value );
		}

		notxml = nType !== 1 || !jQuery.isXMLDoc( elem );

		// All attributes are lowercase
		// Grab necessary hook if one is defined
		if ( notxml ) {
			name = name.toLowerCase();
			hooks = jQuery.attrHooks[ name ] || ( rboolean.test( name ) ? boolHook : nodeHook );
		}

		if ( value !== undefined ) {

			if ( value === null ) {
				jQuery.removeAttr( elem, name );
				return;

			} else if ( hooks && "set" in hooks && notxml && (ret = hooks.set( elem, value, name )) !== undefined ) {
				return ret;

			} else {
				elem.setAttribute( name, value + "" );
				return value;
			}

		} else if ( hooks && "get" in hooks && notxml && (ret = hooks.get( elem, name )) !== null ) {
			return ret;

		} else {

			ret = elem.getAttribute( name );

			// Non-existent attributes return null, we normalize to undefined
			return ret === null ?
				undefined :
				ret;
		}
	},
```
除此之外，还有很多钩子函数，且每个钩子的结构都不太一样，比如propHooks、attrHooks里都是以属性命名的对象，里面或存在get/set方法。

jquery对属性系统的主要贡献是发现更多的兼容性问题和解决方法，具体如下：
1. tabindex的取值问题，tabindex默认情况下只对表单元素和链接有效，对于这些元素没有显示设置会返回0，对于div这样的普通元素返回-1，但IE都返回0，jquery做了统一处理
1. Safari下，option元素的selected取值问题，必须向上访问一下select元素才得到结果
1. 表单元素的value属性的操作，由于表单元素种类繁多，存在严重兼容性问题，jquery做了很多处理

但其缺点也有，如下：
1. 名字映射是穷举机制，attrFix和propFix待完善
1. 对布尔属性的判断存在硬编码，准确率低
1. 添加了一个与removeAttr对称的removeProp方法，但里面实现用到了delete操作符，在chrome中会有将固有属性从原型删除的风险
1. nodeHooks是使用getAttributeNode实现的，虽然能应对所有自定义属性，但判断某些固有属性是否为显示属性时，需要用fixSpecified补漏洞，但其是穷举机制
1. 由于旧版本IE7不支持修改表单元素的type属性，导致在所有浏览器修改type属性，这个是不太好的处理方式

#### mass Framework的属性系统
jquery之所以叫钩子，是由于它只会对特定属性进行回调（同步回调），最后根据结果是否直接返回还是默认处理，在prop方法，默认处理就是对目标进行数组法取赋值，在attr方法就是直接调用setAttribute或getAttribute。

[mass Framework 1.4](https://github.com/RubyLouvre/mass-Framework/blob/1.4/attr.js)使用适配器，在抵达适配器之前，就已经做好判断，是写还是读、是特殊处理还是默认处理，布尔属性也作为特殊处理的适配函数，在prop方法中，默认处理是@default:get/set方法，在attr方法中，默认处理是@w3c:get/set，@ie:get/set方法。只要直接返回适配方法的结果即可，流程非常简单清晰。

attr只处理元素节点，若是XML元素或其他对象类型，转交prop处理，若isXML为false，就试用propMap取得其js属性名，然后在attrHooks或propHooks取得钩子函数处理，在attr中，如值为false或null，需要做移除操作。
```js
prop: function(node, name, value) {
    if($["@bind"] in node) {
        if(node.nodeType === 1 && !$.isXML(node)) {
            name = $.propMap[name.toLowerCase()] || name;
        }
        var access = value === void 0 ? "get" : "set";
        return($.propHooks[name + ":" + access] || $.propHooks["@default:" + access])(node, name, value);
    }
},
attr: function(node, name, value) {
    if($["@bind"] in node) {
        if(typeof node.getAttribute === "undefined") {
            return $.prop(node, name, value);
        }
        //这里只剩下元素节点
        var noxml = !$.isXML(node),
            type = "@w3c";
        if(noxml) {
            name = name.toLowerCase();
            var prop = $.propMap[name] || name;
            if(!support.attrInnateName) {
                type = "@ie";
            }
            var isBool = typeof node[prop] === "boolean" && typeof defaultProp(node, prop) === "boolean"; //判定是否为布尔属性
        }
        //移除操作
        if(noxml) {
            if(value === null || value === false && isBool) {
                return $.removeAttr(node, name);
            }
        } else if(value === null) {
            return node.removeAttribute(name);
        }
        //读写操作
        var access = value === void 0 ? "get" : "set";
        if(isBool) {
            type = "@bool";
            name = prop;
        };
        return(noxml && $.attrHooks[name + ":" + access] || $.attrHooks[type + ":" + access])(node, name, value);
    }
},
```

#### value的操作
一般而言，只有表单的value才有价值，而且涉及与后端交互，但表单的元素种类非常多，读写方式都不一样，所以需要在内部使用一个适配器来实现它。

对每个表单元素的情况需要分开处理：
- select元素，其value值为被选中的option孩子的value值，需要考虑select-one/multiple的情况，
- option元素，它的value值可以是value属性的值，也可以是其文本值，当没有显示设置value时就是innerText，若显示设置了value，则在元素节点的attribute属性对象中（类数组对象，里面全是对象，每个对象拥有value、name、specified、ownerElement等很多属性），判断specified是否为true即可，在IE8或其他现代浏览器还可以用hasAttribute方法来判断。
- button元素，它的value与option元素类似，但在标准浏览器下，button标签只有当其作为提交按钮时，才会提交自身的value值，此时统一返回value值。
- checkbox、radio在设置value是需要考虑到checked属性的修改。

最后
```js
var valHooks = {
    "option:get": function(node) {
        var val = node.attributes.value;
        //黑莓手机4.7下val会返回undefined,但我们依然可用node.value取值
        return !val || val.specified ? node.value : node.text;
    },
    "select:get": function(node, value, getter) {
        var option, options = node.options,
            index = node.selectedIndex,
            one = node.type === "select-one" || index < 0,
            values = one ? null : [],
            max = one ? index + 1 : options.length,
            i = index < 0 ? max : one ? index : 0;
        for(; i < max; i++) {
            option = options[i];
            //旧式IE在reset后不会改变selected，需要改用i === index判定
            //我们过滤所有disabled的option元素，但在safari5下，如果设置select为disable，那么其所有孩子都disable
            //因此当一个元素为disable，需要检测其是否显式设置了disable及其父节点的disable情况
            if((option.selected || i === index) && !(support.optDisabled ? option.disabled : / disabled=/.test(option.outerHTML.replace(option.innerHTML, "")))) {
                value = getter(option);
                if(one) {
                    return value;
                }
                //收集所有selected值组成数组返回
                values.push(value);
            }
        }
        return values;
    },
    "select:set": function(node, name, values, getter) {
        values = [].concat(values); //强制转换为数组
        for(var i = 0, el; el = node.options[i++];) {
            el.selected = !! ~values.indexOf(getter(el));
        }
        if(!values.length) {
            node.selectedIndex = -1;
        }
    }
}
//checkbox的value默认为on，唯有chrome 返回空字符串
if(!support.checkOn) {
    valHooks["checked:get"] = function(node) {
        return node.getAttribute("value") === null ? "on" : node.value;
    };
}
//处理单选框，复选框在设值后checked的值
valHooks["checked:set"] = function(node, name, value) {
    if(Array.isArray(value)) {
        return node.checked = !! ~value.indexOf(node.value);
    }
}
```

由getValType方法决定分配到那个适配器
```js
function getValType(el) {
    var ret = el.tagName.toLowerCase();
    return ret === "input" && /checkbox|radio/.test(el.type) ? "checked" : ret;
}
```

最后的val方法只是代理，唯一要做的就是将参数转换为字符串或字符串数组（针对select元素），然后让狗子函数执行：
```js
//用于取得表单元素的value值
val: function(item) {
    var getter = valHooks["option:get"];
    if(arguments.length) {
        if(Array.isArray(item)) {
            item = item.map(function(item) {
                return item == null ? "" : item + "";
            });
        } else if(isFinite(item)) {
            item += "";
        } else {
            item = item || ""; //我们确保传参为字符串数组或字符串，null/undefined强制转换为"", number变为字符串
        }
    }
    return $.access(this, function(el) {
        if(this === $) { //getter
            var ret = (valHooks[getValType(el) + ":get"] || $.propHooks["@default:get"])(el, "value", getter);
            return typeof ret === "string" ? ret.replace(rreturn, "") : ret == null ? "" : ret;
        } else { //setter
            if(el.nodeType === 1) {
                (valHooks[getValType(el) + ":set"] || $.propHooks["@default:set"])(el, "value", item, getter);
            }
        }
    }, 0, arguments);
}
```