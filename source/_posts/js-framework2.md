---
title: JavaScript框架设计笔记-2-语言模块
categories: js
tags:
  - js
  - js-framework
date: 2017-12-17 17:52:05
updated: 2017-12-17 17:52:05
---

浏览器提供的原生API总是不够的，因此各个框架都创造了许多方法来弥补这缺陷。这就是语言模块的来源，即添加许多对字符串、数组、函数...的方法来修复或增强语言的能力。主要介绍了一些非常底层的知识点，让我们更熟悉这门语言。

#### 字符串的扩展与修复
脚本语言都对字符串特别关注，有关它的方法特别多。
![String扩展](1.png)
wbr来自Tangram，用于软换行，

contains 方法：判定一个字符串是否包含另一个字符串。常规思维是使用正则表达式。但每次都要用new RegExp来构造，性能太差，转而使用原生字符串方法，如indexOf、lastIndexOf、search。
```js
function contains(target, it) {
   //indexOf改成search，lastIndexOf也行得通
   return target.indexOf(it) != -1; 
}
```
startsWith方法：判定目标字符串是否位于原字符串的开始之处，可以说是contains方法的变种。endsWith方法：与startsWith方法相反。
```js
//最后一个参数是忽略大小写
function startsWith(target, str, ignorecase) {
    var start_str = target.substr(0, str.length);
    return ignorecase ? start_str.toLowerCase() === str.toLowerCase() :
            start_str === str;
}

//最后一个参数是忽略大小写
function endsWith(target, str, ignorecase) {
    var end_str = target.substring(target.length - str.length);
    return ignorecase ? end_str.toLowerCase() === str.toLowerCase() :
            end_str === str;
}
```
repeat方法：将一个字符串重复自身N次，如repeat（"ruby", 2）得到rubyruby。
其中有很多方法（具体实现就不写了，给出思想）：
版本1：利用空数组的join方法。
版本2：版本1的改良版。创建一个对象，使其拥有length属性，然后利用call方法去调用数组原型的join方法，省去创建数组这一步，性能大为提高。重复次数越多，两者对比越明显。另外，之所以要创建一个带length属性的对象，是因为要调用数组的原型方法，需要指定call的第一个参数为类数组对象，而类数组对象的必要条件是其length属性的值为非负整数。
版本3：版本2的改良版。利用闭包将类数组对象与数组原型的join方法缓存起来，避免每次都重复创建与寻找方法。
版本 4：从算法上着手，使用二分法，比如我们将ruby重复5次，其实我们在第二次已得到rubyruby，那么第3次直接用rubyruby进行操作，而不是用ruby。
版本5：版本4的变种，免去创建数组与使用jion方法。它的短处在于它在循环中创建的字符串比要求的还长，需要回减一下。
版本6：版本4的改良版。
版本7：与版本6相近。不过在浏览器下递归好像都做了优化（包括IE6），与其他版本相比，属于上乘方案之一。
版本8：可以说是一个反例，很慢，不过实际上它还是可行的，因为实际上没有人将n设成上百成千。

版本6在各浏览器的得分是最高的：
```js
function repeat(target, n) {
    var s = target, total = "";
    while (n > 0) {
        if (n % 2 == 1)
            total += s;
        if (n == 1)
            break;
        s += s;
        n = n >> 1;
    }
    return total;
}
```

byteLen方法：取得一个字符串所有字节的长度。这是一个后端过来的方法，如果将一个英文字符插入数据库char、varchar、text类型的字段时占用一个字节，而将一个中文字符插入时占用两个字节。为了避免插入溢出，就需要事先判断字符串的字节长度。在前端，如果我们要用户填写文本，限制字节上的长短，比如发短信，也要用到此方法。随着浏览器普及对二进制的操作，该方法也越来越常用。

truncate方法：用于对字符串进行截断处理。当超过限定长度，默认添加3个点号。
```js
function truncate(target, length, truncation) {
    length = length || 30;
    truncation = truncation === void(0) ? '...' : truncation;
    return target.length > length ?
            target.slice(0, length - truncation.length) + truncation : String(target);
}
```

capitalize方法：首字母大写。
```js
function capitalize(target) {
    return target.charAt(0).toUpperCase() + target.substring(1).toLowerCase();
}
```

stripTags 方法：移除字符串中的html标签。比如，我们需要实现一个HTMLParser，这时就要处理option元素的innerText问题。此元素的内部只能接受文本节点，如果用户在里面添加了span、strong等标签，我们就需要用此方法将这些标签移除。在Prototype.js中，它与strip、stripScripts是一组方法。
```js
var rtag = /<\w+(\s+("[^"]*"|'[^']*'|[^>])+)?>|<\/\w+>/gi
function stripTags(target) {
    return String(target || "").replace(rtag, '');
}
```

escapeHTML 方法：将字符串经过html转义得到适合在页面中显示的内容，如将“<”替换为“&lt;”`。此方法用于防止XSS攻击。
```js
function escapeHTML(target) {
    return target.replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;");
}
function unescapeHTML(target) {
    return String(target)
    .replace(/&#39;/g, '\'')
    .replace(/&quot;/g, '"')
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>')
    .replace(/&amp;/g, '&')
}
```
escapeHTML和unescapeHTML这两个方法，它们不但在replace的参数是反过来的，replace的顺序也是反过来的。它们在做html parser非常有用的。

pad方法：与trim方法相反，pad可以为字符串的某一端添加字符串。常见的用法如日历在月份前补零，因此也被称之为fillZero。


#### 数组的扩展与修复

#### 数值的扩展与修复

#### 函数的扩展与修复

#### 日期的扩展与修复