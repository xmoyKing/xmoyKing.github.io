---
title: jQuery技术内幕笔记-4-jQuery.extend 源码解读
categories: jQuery
tags:
  - jQuery
  - jQuery技术内幕
  - jQuery.extend
date: 2016-04-10 10:57:27
updated: 2016-04-10 10:57:27
---

方法 jQuery.extend() 和 jQuery.fn.extend() 常用于编写插件和处理函数的参数。

方法 jQuery.extend() 和 jQuery.fn.extend() 用于合并两个或多个对象的属性到第一个对象，它们的语法如下：
- jQuery.extend( [deep], target, object1 [, objectN] )
- jQuery.fn.extend( [deep], target, object1 [, objectN] )

其中，参数 deep 是可选的布尔值，表示是否进行深度合并（即递归合并）。合并行为默认是不递归的，如果第一个参数的属性本身是一个对象或数组，它会被第二个或后面的其他参数的同名属性完全覆盖。如果为 true，表示进行深度合并，合并过程是递归的。

参数 target 是目标对象；参数 object1 和 objectN 是源对象，包含了待合并的属性。如果提供了两个或更多的对象，所有源对象的属性将会合并到目标对象；如果仅提供一个对象，意味着参数 target 被忽略，jQuery 或 jQuery.fn 被当作目标对象，通过这种方式可以在 jQuery或 jQuery.fn 上添加新的属性和方法，jQuery 的其他模块大都是这么实现的。

方法 jQuery.extend() 和 jQuery.fn.extend() 执行的关键步骤如下所示：
1. 修正参数 deep、target、源对象的起始下标。
2. 逐个遍历源对象：
  1. 遍历源对象的属性。
  2. 覆盖目标对象的同名属性；如果是深度合并，则先递归调用 jQuery.extend()。

jQuery.extend() 和 jQuery.fn.extend() 的源码如下：

### 定义 jQuery.extend() 和 jQuery.fn.extend()
```js
324 jQuery.extend = jQuery.fn.extend = function() {
```
第 324 行：因为参数的个数是不确定的，可以有任意多个，所以没有列出可接受的参数。

### 定义局部变量
```js
325 var options, name, src, copy, copyIsArray, clone,
326 target = arguments[0] || {},
327 i = 1,
328 length = arguments.length,
329 deep = false;
```
第 325 ～ 329 行：定义一组局部变量，它们的含义和用途如下：
- 变量 options：指向某个源对象。
- 变量 name：表示某个源对象的某个属性名。
- 变量 src：表示目标对象的某个属性的原始值。
- 变量 copy：表示某个源对象的某个属性的值。
- 变量 copyIsArray：指示变量 copy 是否是数组。
- 变量 clone：表示深度复制时原始值的修正值。
- 变量 target：指向目标对象。
- 变量 i：表示源对象的起始下标。
- 变量 length：表示参数的个数，用于修正变量 target。
- 变量 deep：指示是否执行深度复制，默认为 false。

### 修正目标对象 target、源对象起始下标 i
```js
331 // Handle a deep copy situation
332 if ( typeof target === "boolean" ) {
333 deep = target;
334 target = arguments[1] || {};
335 // skip the boolean and the target
336 i = 2;
337 }
338
339 // Handle case when target is a string or something (possible in deep copy)
340 if ( typeof target !== "object" && !jQuery.isFunction(target) ) {
341 target = {};
342 }
343
344 // extend jQuery itself if only one argument is passed
345 if ( length === i ) {
346 target = this;
347 --i;
348 }
```
第 331~337 行：如果第一个参数是布尔值，则修正第一个参数为 deep，修正第二个参数为目标对象 target，并且期望源对象从第三个元素开始。

变量 i 的初始值为 1，表示期望源对象从第 2 个元素开始；当第一个参数为布尔型时，变量 i 变为 2，表示期望源对象从第 3 个元素开始。

第 339 ～ 342 行：如果目标对象 target 不是对象、不是函数，而是一个字符串或其他的基本类型，则统一替换为空对象 {}，因为在基本类型上设置非原生属性是无效的。

第 344 ～ 348 行：变量 i 表示源对象开始的下标，变量 length 表示参数个数，如果二者相等，表示期望的源对象没有传入，则把 jQuery 或 jQuery.fn 作为目标对象，并且把源对象的开始下标减一，从而使得传入的对象被当作源对象。变量 length 等于 i 可能有两种情况：
- extend( object )，只传入了一个参数。
- extend( deep, object )，传入了两个参数，第一个参数是布尔值。

### 逐个遍历源对象
```js
350 for ( ; i < length; i++ ) {
351 // Only deal with non-null/undefined values
352 if ( (options = arguments[ i ]) != null ) {
353 // Extend the base object
354 for ( name in options ) {
```
第 352 行：arguments 是一个类似数组的对象，包含了传入的参数，可以通过整型下标访问指定位置的参数。这行代码把获取源对象和对源对象的判断合并为一条语句，只有源对象不是 null、undefined 时才会继续执行。

第 353 行：开始遍历单个源对象的属性。

### 覆盖目标对象的同名属性
```js
355 src = target[ name ];
356 copy = options[ name ];
357
358 // Prevent never-ending loop
359 if ( target === copy ) {
360 continue;
361 }
362
363 // Recurse if we're merging plain objects or arrays
364 if ( deep && copy && ( jQuery.isPlainObject(copy) || (copyIsArray = jQuery.isArray(copy)) ) ) {
365 if ( copyIsArray ) {
366 copyIsArray = false;
367 clone = src && jQuery.isArray(src) ? src : [];
368
369 } else {
370 clone = src && jQuery.isPlainObject(src) ? src : {};
371 }
372
373 // Never move original objects, clone them
374 target[ name ] = jQuery.extend( deep, clone, copy );
375
376 // Don't bring in undefined values
377 } else if ( copy !== undefined ) {
378 target[ name ] = copy;
379 }
```
第 355 ～ 361 行：变量 src 是原始值，变量 copy 是复制值。如果复制值 copy 与目标对象 target 相等，为了避免深度遍历时死循环，因此不会覆盖目标对象的同名属性。

第 364 ～ 374 行：如果是深度合并，且复制值 copy 是普通 JavaScript 对象或数组，则递归合并。

第 365 ～ 371 行：复制值 copy 是数组时，如果原始值 src 不是数组，则修正为空数组；复制值 copy 是普通 JavaScript 对象时，如果原始值 src 不是普通 JavaScript 对象，则修正为空对象 {}。把原始值 src 或修正后的值赋值给原始值副本 clone。

通过调用方法 jQuery.isPlainObject( copy ) 判断复制值 copy 是否是“纯粹”的 JavaScript对象，只有通过对象直接量 {} 或 new Object() 创建的对象，才会返回 true。

第 374 行：先把复制值 copy 递归合并到原始值副本 clone 中，然后覆盖目标对象的同名属性。

第 376 ～ 379 行：如果不是深度合并，并且复制值 copy 不是 undefined，则直接覆盖目标对象的同名属性。

---

本系列笔记来自《jQuery技术内幕》一书中的部分内容。注意：其内的**jQuery版本为1.7.1**，后面的版本也许在一些方法和功能模块上有修改，了解其思想即可，但不可想当然的直接认定。