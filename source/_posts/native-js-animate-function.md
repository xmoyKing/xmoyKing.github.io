---
title: 原生js动画函数
date: 2017-01-20 20:02:18
updated: 2017-01-20
categories: js
tags: [js, animate]
---


事件高级（一）

1. 给元素添加事件的问题
2. 事件绑定的意义
3. 事件绑定：IE - attachEvent、W3C - addEventListener
4. 兼容性处理
5. 封装事件绑定函数
6. IE下事件绑定this指向的问题
7. 解除事件绑定、匿名函数的特性
8. 拖拽原理回顾
9. 封装可重用的拖拽实例

事件高级（二）

1. 可重用的选项卡实例
2. 限制范围的拖拽实例
3. 磁性吸附的拖拽实例
4. 解决拖拽问题一：选中文字
5. 事件捕获：setCapture()与releaseCapture()
6. 合并代码，共用函数
7. 碰撞检测原理
8. 与DOM配合，制作带框的拖拽实例
9. 处理带框拖拽的细节部分
10. 用拖拽改变DIV大小实例



### 属性动画： ###

> 1. 支持回调函数
> 2. 支持多属性同时变化

需要注意：
> 1. offsetWidth类的属性包括border和width，所以需要使用计算后的属性值
> 此时可以使用currentStyle或getComputedStyle两种方法
> 2. 需要注意是否所有属性都完成了才取消定时器，否则第一个完成属性后其他属性无法变化

```js
//获取当前o元素的attr属性c
function getStyle(o, attr){
    if(o.currentStyle)
        return o.currentStyle[attr];
    else
        return getComputedStyle(o, false)[attr];
}

//动画函数 o为目标元素，json为一个包含变化属性和预期值的对象
//  fn为完成此动画后的回调，可以继续使用animate形成延时动画
function animate(o, json, fn){
    var itime = 30; //定时器时间
    var iS = 8; //动画速度基准，越大越慢

    clearInterval(o.timer); //清除上次在o元素上使用的定时器，防止动画重复

    o.timer = setInterval(function(){
        var bStop = true; //当所有值都变化为预期值后，标识此次动画结束

        for(var attr in json){
            // 1. 获取当前该attr的值
            var iCur = 0;
            if(attr == 'opacity') //防止小数与误差
                iCur = parseInt(parseFloat(getStyle(o, attr))*100);
            else
                iCur = parseInt(getStyle(o, attr));
            
            // 2. 算速度，防止小数误差积累，需要使用取整
            var iSpeed = (json[attr]-iCur)/iS;
            iSpeed = iSpeed>0 ? Math.ceil(iSpeed) : Math.floor(iSpeed); //正反速度区别对待

            // 3. 检测停止，每一轮定时，只要有一个属性未到预期值则设置为false
            if(iCur != json[attr])
                bStop = false;
            
            // 4. 透明度和其他属性单位不同
            if(attr == 'opacity')
                o.style.opacity = (iCur + iSpeed)/100;
            else
                o.style[attr] = iCur + iSpeed + 'px';

        }

        // 5. 检测所有属性是否完成
        if(bStop){
            clearInterval(o.timer);
            if(fn) 
                fn();
        }
    }, itime);
}
```

<iframe  src="animate.html" frameborder="0" width="100%" height="300"></iframe>


### 弹性运动： ###

1. 加速运动：步长越来越大
2. 减速运动：步长越来越小，直到负值
5. 引入摩擦力系数，变成弹性公式
6. 应用弹性公式：滑动菜单实例
7. 解决数值精度问题：引入变量累加消除小数
8. 弹性运动的停止条件：目标与速度为零
9. 弹性运动的适用范围：不能运用于物体超过原大小的动画

```js
        var iSpeed = 0;
        var left = 0;

        function startMove(obj, iTarget) {
            clearInterval(obj.timer); //防止一元素多次触发

            obj.timer = setInterval(function() {
                //用预期位值和当前位置（offsetLeft）的差作为速度，系数5，越大则速度越小
                iSpeed += (iTarget - obj.offsetLeft) / 5;
                iSpeed *= 0.7; //0.7模拟摩擦力系数，越大则摩擦力越小

                left += iSpeed;
                //使用绝对值，将速度和距离差小于1像素当作0处理
                if (Math.abs(iSpeed) < 1 && Math.abs(left - iTarget) < 1) {
                    clearInterval(obj.timer);
                    obj.style.left = iTarget + 'px'; //防止出现1像素的误差，结束动画时强制设置为预期值
                } else {
                    obj.style.left = left + 'px';
                }
            }, 30);
        }
```

<iframe src="slideMenu.html" frameborder="0" width="100%"></iframe>


### 碰撞运动： ###

2. 碰撞运动：无重力情况下的变化
3. 消除运动过界时窗口出现滚动条
4. 带重力的碰撞运动：向下的力不断递减
5. 正小数与负小数对样式的影响
7. 生成拖拽轨迹，计算拖拽速度
8. 拖拽+碰撞+重力消除bug，最终演示实例
9. 碰撞运动停止条件

```js
window.onload = function() {
    var oDiv = document.getElementById('div1');

    var lastX = 0;
    var lastY = 0;
    //拖拽需要监听鼠标按下事件以及传入event对象用于计算拖拽丢出的初速度
    oDiv.onmousedown = function(ev) {
        var oEvent = ev || event;

        var disX = oEvent.clientX - oDiv.offsetLeft;
        var disY = oEvent.clientY - oDiv.offsetTop;

        document.onmousemove = function(ev) {
            var oEvent = ev || event;

            var l = oEvent.clientX - disX;
            var t = oEvent.clientY - disY;

            oDiv.style.left = l + 'px';
            oDiv.style.top = t + 'px';

            iSpeedX = l - lastX;
            iSpeedY = t - lastY;

            lastX = l;
            lastY = t;

            document.title = 'x:' + iSpeedX + ', y:' + iSpeedY;
        };

        document.onmouseup = function() {
            document.onmousemove = null;
            document.onmouseup = null;

            startMove();
        };

        clearInterval(timer);
    };
};

var timer = null;

var iSpeedX = 0;
var iSpeedY = 0;

function startMove() {
    clearInterval(timer);

    timer = setInterval(function() {
        var oDiv = document.getElementById('div1');

        iSpeedY += 3;

        var l = oDiv.offsetLeft + iSpeedX;
        var t = oDiv.offsetTop + iSpeedY;

        if (t >= document.documentElement.clientHeight - oDiv.offsetHeight) {
            iSpeedY *= -0.8;
            iSpeedX *= 0.8;
            t = document.documentElement.clientHeight - oDiv.offsetHeight;
        } else if (t <= 0) {
            iSpeedY *= -1;
            iSpeedX *= 0.8;
            t = 0;
        }

        if (l >= document.documentElement.clientWidth - oDiv.offsetWidth) {
            iSpeedX *= -0.8;
            l = document.documentElement.clientWidth - oDiv.offsetWidth;
        } else if (l <= 0) {
            iSpeedX *= -0.8;
            l = 0;
        }

        //当速度足够小时（绝对值小于1），直接设为0
        if (Math.abs(iSpeedX) < 1) {
            iSpeedX = 0;
        }

        if (Math.abs(iSpeedY) < 1) {
            iSpeedY = 0;
        }
        //终止条件为：x，y方向上的速度都为0，且到达目标点
        if (iSpeedX == 0 && iSpeedY == 0 && t == document.documentElement.clientHeight - oDiv.offsetHeight) {
            clearInterval(timer);
        } else {
            oDiv.style.left = l + 'px';
            oDiv.style.top = t + 'px';
        }

        document.title = iSpeedX;
    }, 30);
}
```

<iframe src="collision.html" frameborder="0" width="100%" height="400"></iframe>
