---
title: JavaScript高级程序设计-21-表单
categories: JavaScript
tags:
  - JavaScript
  - JavaScript高级程序设计
  - 表单
date: 2016-09-04 16:57:13
updated:
---

js最初的应用就是分担服务器处理表单的责任，打破处处依赖服务器的局面。we表单没有为许多常见任务提供解决手段，所以一般需要在前端通过js增强表单功能，比如验证表单。

### 表单基础知识
在HTML中，表单是由form元素表示的，js中对应的则是HTMLFormElement类型，HTMLFormElement类型继承自HTMLElement，与其他HTML有相同的默认属性，但其也有特殊的属性和方法：
- acceptCharset，服务器能够处理的字符集，等价于HTML中的accept-charset属性
- action，请求的URL，等价于HTML中的action属性
- elements，表单中所有控件的集合（HTMLCollection）
- enctype，请求的编码类型，等价于HTML中的enctype属性
- length，表单中控件数量
- method，要发生的HTTP请求类型，通常为GET/POST,等价于HTML的method属性
- name，表单的名称，等价于HTML的name属性
- reset(), 将所有表单域重置为默认值
- submit(), 提交表单
- target，用于发生请求和接收响应的窗口名，等价于HTML的target属性
除了getElementById取得表单，可以先通过document.forms取得所有表单，然后再通过索引或name值来取得特定表单。
```js
document.forms[0]; // 页面中第一个表单
document.forms['formName'];  // 页面中name为formName的表单
```

#### 提交表单
用户单击提交按钮或图像按钮时，就能提交表单，使用input/button元素都可以定义提交按钮，只要将type属性设置为submit即可，而图片按钮则可将type属性设置为image。

只要表单中存在任何上述的一种按钮，那么在相应表单空间拥有焦点的情况下，回车即可提交表单（textarea中回车会换行），若表单内没有提交按钮，则回车不会提交。

以此种方式提交表单时，浏览器在请求发生到服务器之前会触发submit事件，如此就能验证表单数据了，阻止submit事件可以取消表单提交。

通过js获取到表单对象后可以调用`form.submit()`方法，也能提交表单。此种方式无需表单包含提交按钮，在任何时候都能提交。但这种方式不会触发shubmit事件，所以，需要验证表单数据。

提交表单时很可能出现重复提交问题，解决此问题的办法：第一次提交后就禁用提交按钮，或，利用onsubmit事件处理程序取消后续的表单提交操作。

#### 重置表单
与submit一样，表单的重置也有好几种方式。

#### 表单字段
可以像访问页面中其他元素一样，使用原生DOM方法访问表单元素，此外，每个表单都有elements属性，该属性是表单所有元素的集合，这个elements集合是一个有序列表，其中包含着表单中所有字段（表单控件），每个表单字段在elements集合中的顺序与它们出现在HTML代码中的顺序相同，可以依索引和name属性来访问。

若多个表单空间都使用同一个name（如radio按钮），那么就会返回以该name命名的一个NodeList。

其实也可以通过表单控件的name属性来访问控件，但此种方式是为了与旧浏览器兼容，尽量使用elements的方式。

##### 共有的表单字段属性
除了fieldset元素外，所有表单字段都有相同的一组属性和方法：（input类型可以表示多种表单字段，有些属性仅适用于某些字段）
- disabled，布尔值，表示当前字段是否被禁用
- form，指向当前字段所属表单的指针，只读
- name，当前字段的名称
- readOnly，布尔值，表示当前字段是否只读
- tabIndex，表示当前字段的切换序号（tab的顺序）
- type，当前字段的类型，如radio、text等
- value，当前字段提交给服务器的值，（对file类型来说是只读的，表示文件路径）
除了form属性之外，可以通过js动态修改属性。
```js
var field = form.elements[0];
field.value = 'king';
field.focus(); // 聚焦到field字段上
field.disabled = true; // 禁用当前字段
field.type = 'checkbox'; // 对input元素可用，改变类型，
```

禁止重复提交可以在第一次提交表单后将按钮禁用掉，但需要注意，这种方式只能在submit事件内禁用，而不能在click事件内禁用，因为不同浏览器之间onclick和submit事件之间存在时差。

关于type属性，不同元素具有的type属性值不同，有些能动态修改有些无法修改

 | 说明 | HTML代码 | type值 |
 | - | - | - |
 | 单选列表 | `<select> </select>` | select-one, 只读 |
 | 多选列表 | `<select multiple> </select>` | select-multiple, 只读 |
 | 按钮 | `<button> </button>` | 默认submit,也可以显示设置 |
 | 普通按钮 | `<button type="button"> </button>` | button |
 | 重置按钮 | `<button type="reset"> </button>` | reset |

##### 共有的表单字段方法
每个表单字段都有两个方法，focus/blur,focus方法用于将浏览器的焦点设置到表单字段，即激活表单字段，使其可以响应键盘事件。

H5为表单字段新增了一个autofocus属性，在支持的浏览器中，不用js就能自动把焦点移动到相应字段。

与focus对应的blur方法是从元素上移走焦点，仅仅只是移走焦点，不会将焦点转移到其他控件上。

##### 共有的表单字段事件
所有表单字段都支持如下3个事件：
- blur， 当前字段失去焦点时触发
- focus，当前字段获得焦点时触发
- change，对input和textarea元素，在它们失去焦点且value值改变时触发，对于select元素，在其选项改变时触发。

### 文本框脚本
在HTML中，有2种方式表现文本框，一种是使用input元素的单行文本框，另一种是使用textarea的多行文本框。
单行文本框通过size属性可以指定文本框中能够显示的字符数，maxlength属性能指定文本框的最大字符数，value元素属性能设置初始值。
多行文本框设置rows指定行数，cols指定列数，其初始值在元素之间。

两种文本框都可以通过value对象属性读取或设置，不建议用DOM的方法，即不要修改文本框的DOM值，因为对value属性的修改不一定反映到DOM中。

#### 选择文本
文本框都支持selet方法，用于选择文本框中的所有文本，调用此方法时浏览器（Opera除外）会将焦点设置到文本框上。

在选择了文本框中的文本时就会触发select事件，H5添加了两个属性selectionStart/selectionEnd，表示所选文本的范围（即开头和结尾的偏移量）。

IE8-提供另外一个版本document.selection对象，该对象保存整个文档范围内选择的文本信息，配合select事件即可获取用户选择的文本。

H5为选择文本框内的部分文本提供了setSelectionRange方法，该方法接收2个参数，要选择的第一个字符的索引，要选择的最后一个字符之后的字符索引，（类似substring的2个参数）。

IE8-则可使用createTextRange方法创建一个范围，然后使用collase方法将范围折叠，最后调用moveStart、moveEnd方法并传入字符索引。

#### 过滤输入
一个常见的场景就是过滤用户的输入，比如输入特定格式的数据。

##### 屏蔽字符
当需要输入的文本中包含或不包含某些字符时，可以通过监听keypress事件，阻止特定的字符编码,比如只允许输入数值：
```js
EventUtil.addHandler(textbox, 'keypress', function(event){
  event = EventUtil.getEvent(event);
  var target = EventUtil.getTarget(event);
  var charCode = EventUtil.getcharCode(event);
  // 所有非数字的字符，以及Ctrl符合键
  if( !/\d/.test(String.fromCharCode(charCode)) && charCode > 9 && !event.ctrlKey){
    EventUtil.preventDefault(event);
  }
});
```

##### 操作剪贴板
H5规范中有6个剪贴板事件：
- beforecopy, 在发生复制操作前触发
- copy
- beforecut，在发生剪切操作前触发
- cut
- beforepaste，在发生粘贴操作前触发
- paste
可以通过clipboardData对象访问剪贴板中的数据，在IE中该对象是window对象的属性，其他浏览器中该对象是event对象的属性。clipboardData对象有3个方法，getData、setData、clearData。

getData接收一个参数表示要取得的数据的格式，IE中可填'text'或'URL',其他浏览器填写MIME类型，用'text/plain'。

setData接收2个参数，一个数据类型，一个是文本内容。

#### 自动切换焦点
配合文本框的maxlength属性，监听keyup事件，每当文本框内容达到最大字符数后就自动聚焦到下一个文本框。

#### H5约束验证API
H5新增了一些功能为了在表单提交前验证数据，但目前此功能由各浏览器自行实现，所以差异很大。
1. required属性，必填
2. type="email"，type="url"， type="number"
3. 其他如range、datetime、date、month、week、time等支持程度不一
4. pattern属性，用正则匹配文本
5. 使用checkValidity方法检测表单某字段是否有效，该方法返回布尔值
6. 控件的validity属性则返回一个对象，对象中每个属性都表示了对应字段为何有效/无效
7. novalidate属性，表示禁用验证，可以通过表单对象上对应的属性`form.noValidate = true/false`设置。

### 选择框脚本
选择框是通过selcet和option元素创建的。在获取选择框的信息时，推荐通过原生DOM访问获取，而不通过getAttribute、nodeValue等方法。
[HTML DOM Select 对象](http://www.w3school.com.cn/jsref/dom_obj_select.asp)、[HTML DOM Option 对象](http://www.w3school.com.cn/jsref/dom_obj_option.asp)

**但在实际使用中，由于选择框无法设置样式，所以多采用自定义的选择框，原生选择框使用较少。**

### 富文本编辑
富文本编辑（WYSIWYG），在网页中编辑富文本内容，由IE最先引入而且成为事实标准。

本质就是在页面中嵌入一个包含空HTML的iframe，通过设置iframe的document.designMode属性，这个空白的HTML页面可以被编辑，而编辑对象则是该页面body元素的HTML代码，designMode属性有2个可能的值：'off'默认值，'on'，设置为off时，整个文档都可以编辑，然后就可以像文字处理软件一样编辑了。

而且可以为空白页面应用CSS样式，修改可编辑区字段的外观。

#### 使用contenteditable属性
另一种编辑富文本内容的方式是使用名为contenteditable的特殊属性，也由IE最先引入，它比iframe方法更好是因为其可以应用给页面中的任意元素，而且不需要iframe和js的配合。
`<div id="richedit" contentEditable>`
可以通过元素的contentEditable属性打开/关闭编辑模式，值为字符串，'true'表示打开，'false'表示关闭，'inherit'表示从父元素继承（在contentEditable元素内可创建元素或删除元素）
```js
richedit.contentEditable = 'true';
```

#### 操作富文本
与iframe富文本编辑器交互的主要方式，就是使用document.execCommand方法，该方法可以对文档执行预定义的命令，而且可以应用大多数格式，接收3个参数：字符串（要执行命令名）、布尔值（表示浏览器是否应该为当前命令提供一个用户界面）、值（表示执行命令需要的值，若不必则为null，若需要则为特定类型即可）。

详细查看：[document.execCommand](https://developer.mozilla.org/zh-CN/docs/Web/API/Document/execCommand)

在富文本编辑器中，使用iframe的getSelection方法可以确定实际选择的文本，该方法是window对象的document对象，调用会返回当前选择文本的Selection对象。[Selection对象](https://developer.mozilla.org/zh-CN/docs/Web/API/Selection)

注：由于富文本编辑器其实并不是一个表单控件，所以需要显式获取所有HTML内容。