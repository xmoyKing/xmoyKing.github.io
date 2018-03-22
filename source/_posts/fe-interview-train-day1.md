---
title: 前端面试题-1-各大公司前端面试题集
date: 2017-02-11 10:10:56
updated: 2017-02-11
categories: mixed
tags: [interview]
---

[2017年年初前端面试总结](http://www.jianshu.com/p/30965d1fe6a6)

[80% 应聘者都不及格的 JS 面试题](https://juejin.im/post/58cf180b0ce4630057d6727c)

----

from [奇虎360Web前端开发工程师面试题](http://yanhaijing.com/work/2015/06/26/find-job-of-360/)

1. `alert(1&&2);`的输出是什么？
    - 错误：弹出true
    - 弹出 2
2. 正则表达式匹配，开头为11N，12N或1NNN，后面7-8个数字的电话号码
3. 写出下面代码的输出值：
    ```js
    var obj = {
        a: 1,
        b: function () {console.log(this.a)}
    };

    var a = 2;
    var objb = obj.b;

    obj.b();
    objb();
    obj.b.call(window);
    ```
    - 输出 1 2 2
4. 写出下列代码的输出值：
    ```js
    function A() {

    }
    function B(a) {
        this.a = a;
    }
    function C(a) {
        if (a) {
            this.a = a;
        }
    }

    A.prototype.a = 1;
    B.prototype.a = 1;
    C.prototype.a = 1;

    console.log(new A());
    console.log(new B());
    console.log(new C(2));
    ```
    - 错误：输出三个[object]
    - ![题目4输出结果][1.png] 分辨对象本身属性以及其原型链上的属性
5. 写出下列代码的输出值：
    ```js
    var a = 1;
    function b() {
        var a = 2;
        function c() {
            console.log(a);
        }

        return c;
    }

    b()();
    ```
    - 错误：输出 1
    - 输出 2， 因为闭包，第一个b()执行输出内部的函数c，然后第二个()执行c函数，而此时的执行环境还是b内，所以输出2

## HTML&CSS ##
1. 写出下列代码在各个浏览器中的颜色值?
    ```css
    background: red;
    _background: green;
    *background: blue;
    background: black\9;
    ```
    ```css
    style{ /* css hack系列 */
    color:#000000;                  /* FF,OP支持 */
    color:#0000FF\9;       /* 所有浏览器IE浏览器(ie6+)支持 ；但是IE8不能识别“*”和“_”的css hack；所以我们可以这样写hack */
    [color:#000000;color:#00FF00;      /* SF,CH支持 */
    *color:#FFFF00;                 /* IE7支持 */
    _color:#FF0000;                 /* IE6支持 */
    }
    ```
2. 添加些css让其水平垂直居中。
    ```html
    <div style="____________________________">
        颜海镜
    </div>
    ```
    - [centering-in-the-unknown](https://css-tricks.com/centering-in-the-unknown/)
    - 最佳方案：使用css3的弹性盒模型即可快速设置匿名，同时需要给定一个高度：`display:flex;align-items: center;justify-content:center;height: 500px;`
    - 使用表格显示模式：
    ```html
    <div class="something-semantic">
        <div class="something-else-semantic">
            Unknown stuff to be centered.
        </div>
    </div>

    .something-semantic {
        display: table;
        width: 100%;
    }
    .something-else-semantic {
        display: table-cell;
        text-align: center;
        vertical-align: middle;
    }
    ```
3. 如下代码，在空白处填写代码，使其点击时，前景色为白色，背景色为黑色。
    `<div onclick="_________________">颜海镜</div>`
    - 前景色：`color:#fff`,背景色：`background-color:#000;`
    - `this.style.color='#fff';this.style.backgroundColor='#000';`
4. 书写代码，点击时从1分钟开始，每秒递减到0。
    `<div onclick="test();">颜海镜</div>`
    ```js
    function test(){        /* 无法多次点击，否则会出现多个计时器 */
        var _self = this;
        _self.innerHTML = '1分钟';
        var s = 59;
        var timer = setInterval(function(){
            if(s>=0)_self.innerHTML = s--;
            else clearInterval(timer);
        },1000);
    }
    ```
5. 简述在IE下mouseover和mouseenter的区别？
    - 不论鼠标指针穿过被选元素或其子元素，都会触发 mouseover 事件。对应mouseout
    - 只有在鼠标指针穿过被选元素时，才会触发 mouseenter 事件。对应mouseleave

[为什么 [ ] 是 false 而 !![ ] 是 true](https://www.h5jun.com/post/why-false-why-true.html)

1. 已知圆心(x,y)，求圆上任一点(x1,y1)的坐标
    - 利用圆的方程，圆的方程：（x-x1)^2+(y-x2)^2=r^2
    把其中的一个x，y化成x2,y2就是切线方程
    (x2-x1)(x-x1)+(y2-y1)(y-y1)=r^2
2. 随机抛五枚硬币，求三枚及以上朝上的概率


[技术之瞳 阿里巴巴技术笔试心得](技术之瞳 阿里巴巴技术笔试心得-2016.11-p260.pdf)

## 第四章 Web前端开发
### HTTP协议
> 4.4 请描述HTTP协议与HTTPS协议，分析两者的相同点和不同点


### CSS
> 4.15 请阐述CSS实现三角形的原理


### JavaScript
> 4.20 对比jsonp和document.domain+iframe做跨域的异同，分别指出其优缺点

> 4.22 补充代码空白部分，用JavaScript实现一个栈，实现基本的push、pop、top、length接口。
```js
...
var stack = new Stack();
stack.push(1);
stack.pop();
```

### 正则
> 4.24 给定如下的一段字符串和JavaScript对象字面量，需要将字符串中所有使用花括号括起来的关键字，统一替换为对象字面量中对应的键值，请使用正则表达式实现字符串的模版替换操作。
字符串：`<a href="{href}">{text}</a>`。
对象字面量：`{href: '//www.taobao.com/', text: '淘宝网'}`。
要求：
    1. 请给出用于匹配所有花括号关键词的正则表达式。
    2. 请尽可能考虑正则表达式的兼容性、匹配的效率问题。
    3. 使用原生JavaScript语言，不依赖任何框架或类库。


### Node.js
> 4.25 编写一个简单的包，要求能够通过npm进行发布，发布成功后，能在另一个项目中进行调用。

> 4.26 如果出于公司的安全需要，不允许将代码上传到npm仓库中，但又处于模块化的考虑，需要提供给公司内的其他同事使用，那么怎样才是最佳方案？

> 4.27 如何拼接多个Buffer为一个Buffer？

> 4.28 如何实现一个Writable、Readable、Duplex流？

> 4.29 如何保证Session的安全？

> 4.30 如何实现在父进程退出时子进程跟着退出？

> 4.31 实现一个能自动重启的HTTP服务集群

### 前端框架
> 4.32 简述React组件的生命周期

> 4.33 如果组件初始化时需要Ajax获取数据，代码应该写在哪里？

### 前端工程化
> 4.34 请说出你所知道的前端项目构建工具，以及每种构建工具的优缺点

> 4.35 为了保证页面性能，需要对JS和CSS文件进行压缩与合并，请Node.js实现一个命令行工具实现这一功能。
假定：
    - 在CSS中引入另一个CSS的语法为`@import url("b.css");`
    - JS通过`import"my-module.js";`语法引入另一个模块。
    - CSS的入口文件为app.css, JS的入口文件为app.js。
    - 产出物存放在dist目录中。
要求：
    - 为每个JS和CSS文件按照原始目录结构生成压缩后的文件。
    - app.js、app.css需要包含所依赖的全部文件。

> 4.36 请描述你参与的项目中使用的代码开发的分支策略。

### 数据可视化
> 4.38 浏览器兼容性是前端工作中必须考虑的一个问题，浏览器使用占比是进行技术决策的重要依据，下面是一个网站的浏览器周访问数据：
```js
{
    "2015.11.11": {"IE": 135248, "Chrome": 356567, "Firefox": 69350, "Safri": "32876"},
    "2015.11.12": {"IE": 125248, "Chrome": 356567, "Firefox": 69350, "Safri": "32876"},
    "2015.11.13": {"IE": 115698, "Chrome": 356567, "Firefox": 69350, "Safri": "32876"},
    "2015.11.14": {"IE": 145894, "Chrome": 356567, "Firefox": 69350, "Safri": "32876"},
    "2015.11.15": {"IE": 103458, "Chrome": 356567, "Firefox": 69350, "Safri": "32876"},
    "2015.11.16": {"IE": 110672, "Chrome": 356567, "Firefox": 69350, "Safri": "32876"},
    "2015.11.17": {"IE": 146503, "Chrome": 356567, "Firefox": 69350, "Safri": "32876"}
}
```
请基于这些数据绘制两张图：
    - 基于一周数据的浏览器分布比例图。
    - 每天的浏览器占比变化趋势图。
要求:
    - 不能采用第三方绘图类库，仅允许使用浏览器原生API实现。
    - 可不兼容IE及手机浏览器。

##安全
### Web安全
> 6.1 什么是fuzz？

> 6.2 如何伪造发件人发送欺诈邮件？

> 6.3 产生安全漏洞的原因是什么？

> 6.4 下面代码有什么安全问题？请指出并修正。
```java
String msg = request.getParameter("msg");
if(msg.indexOf('<') > -1 || msg.indexOf('>') > -1){
    out.println("<span>error</span>");
}else{
    out.println("<input type=hidden name=msg value='"+ msg +"' />");
}
```

> 6.4 小明目前是淘宝某开发团队的实习生，今天的任务是负责用PHP编写一个动态页面，小明很快完成了代码，并附上了详细的注释。过了几天，他收到一封来自淘宝安全的邮件，邮件中说他写的代码存在安全问题。小明很纳闷，他不明白自己开发的代码为什么会有安全问题。各位同学，你能帮他看一下究竟在哪些地方存在什么样的安全问题吗？另外有哪些方法可以防范？
![php代码](2.png)
