---
title: 前端面试题-1-各大公司前端面试题集
date: 2017-02-11 10:10:56
tags: [interview, fe]
---

[2017年年初前端面试总结](http://www.jianshu.com/p/30965d1fe6a6)



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


