---
title: 类似jQuery的简单自制库
date: 2017-01-22 11:51:08
updated: 2017-01-22
categories: [fe]
tags: [js, lib]
---


vQuery基础一

1. vQuery 简介
2. jQuery 操作简介
3. "$" 功能介绍
4. vQuery 选择器实现过程：ID\class\tagName
5. click() 方法实现过程
6. 把 new vQuery() 改成 $()
7. show() 和 hide() 方法实现过程
8. hover() 方法实现过程
9. css() 方法实现过程
10. this 在 IE 下使用绑定事件的指向问题

vQuery基础二

1. show() 与 show.call() 的区别，apply() 与 call() 的区别
2. 用 call() 解决 this 在 IE 下使用绑定事件的指向问题
3. toggle() 方法简介
4. 闭包特性及其几种怪异的闭包写法
5. 累加计数的实现
6. toggle() 方法实现过程
7. attr() 方法实现过程
8. eq() 方法实现过程
9. find() 方法实现过程（上半部分）

vQuery基础三

1. find() 方法实现过程
2. index() 方法实现过程
3. 运用 vQuery 制作选项卡实例


vQuery高级

1. jQuery 链式操作
2. css() 函数的改进：支持多属性操作与链式操作
3. css() 函数多属性操作的实质：传递 json
4. 在 css() 函数内添加判断与循环 json
5. 使用 css() 函数对 div 进行操作
6. 函数链式操作：返回函数自身
7. 给 css() 函数加上：return this;
8. vQuery 的链式操作实例
9. jQuery 里阻止默认事件、阻止冒泡
10. 添加 bind() 方法
11. 添加 vQuery 阻止默认事件与阻止冒泡的功能
12. jQuery 里的 animate()
13. vQuery 的插件机制：extend()
14. 运用插件机制为 vQuery 添加 size() 方法
15. vQuery  里 animate() 方法实现过程
16. 运用 vQuery 制作淘宝幻灯片实例
17. 运用插件机制为 vQuery 添加 drag() 方法

```js
function myAddEvent(obj, sEv, fn)
{
	if(obj.attachEvent)
	{
		obj.attachEvent('on'+sEv, function (){
			if(false==fn.call(obj))
			{
				event.cancelBubble=true;
				return false;
			}
		});
	}
	else
	{
		obj.addEventListener(sEv, function (ev){
			if(false==fn.call(obj))
			{
				ev.cancelBubble=true;
				ev.preventDefault();
			}
		}, false);
	}
}

function getByClass(oParent, sClass)
{
	var aEle=oParent.getElementsByTagName('*');
	var aResult=[];
	var i=0;
	
	for(i=0;i<aEle.length;i++)
	{
		if(aEle[i].className==sClass)
		{
			aResult.push(aEle[i]);
		}
	}
	
	return aResult;
}

function getStyle(obj, attr)
{
	if(obj.currentStyle)
	{
		return obj.currentStyle[attr];
	}
	else
	{
		return getComputedStyle(obj, false)[attr];
	}
}

function VQuery(vArg)
{
	//用来保存选中的元素
	this.elements=[];
	
	switch(typeof vArg)
	{
		case 'function':
			//window.onload=vArg;
			myAddEvent(window, 'load', vArg);
			break;
		case 'string':
			switch(vArg.charAt(0))
			{
				case '#':	//ID
					var obj=document.getElementById(vArg.substring(1));
					
					this.elements.push(obj);
					break;
				case '.':	//class
					this.elements=getByClass(document, vArg.substring(1));
					break;
				default:	//tagName
					this.elements=document.getElementsByTagName(vArg);
			}
			break;
		case 'object':
			this.elements.push(vArg);
	}
}

VQuery.prototype.click=function (fn)
{
	var i=0;
	
	for(i=0;i<this.elements.length;i++)
	{
		myAddEvent(this.elements[i], 'click', fn);
	}
	
	return this;
};

VQuery.prototype.show=function ()
{
	var i=0;
	
	for(i=0;i<this.elements.length;i++)
	{
		this.elements[i].style.display='block';
	}
	
	return this;
};

VQuery.prototype.hide=function ()
{
	var i=0;
	
	for(i=0;i<this.elements.length;i++)
	{
		this.elements[i].style.display='none';
	}
	
	return this;
};

VQuery.prototype.hover=function (fnOver, fnOut)
{
	var i=0;
	
	for(i=0;i<this.elements.length;i++)
	{
		myAddEvent(this.elements[i], 'mouseover', fnOver);
		myAddEvent(this.elements[i], 'mouseout', fnOut);
	}
	
	return this;
};

VQuery.prototype.css=function (attr, value)
{
	if(arguments.length==2)	//设置样式
	{
		var i=0;
		
		for(i=0;i<this.elements.length;i++)
		{
			this.elements[i].style[attr]=value;
		}
	}
	else	//获取样式
	{
		if(typeof attr=='string')
		{
		//return this.elements[0].style[attr];
			return getStyle(this.elements[0], attr);
		}
		else
		{
			for(i=0;i<this.elements.length;i++)
			{
				var k='';
				
				for(k in attr)
				{
					this.elements[i].style[k]=attr[k];
				}
			}
		}
	}
	
	return this;
};

VQuery.prototype.attr=function (attr, value)
{
	if(arguments.length==2)
	{
		var i=0;
		
		for(i=0;i<this.elements.length;i++)
		{
			this.elements[i][attr]=value;
		}
	}
	else
	{
		return this.elements[0][attr];
	}
	
	return this;
};

VQuery.prototype.toggle=function ()
{
	var i=0;
	var _arguments=arguments;
	
	for(i=0;i<this.elements.length;i++)
	{
		addToggle(this.elements[i]);
	}
	
	function addToggle(obj)
	{
		var count=0;
		myAddEvent(obj, 'click', function (){
			_arguments[count++%_arguments.length].call(obj);
		});
	}
	
	return this;
};

VQuery.prototype.eq=function (n)
{
	return $(this.elements[n]);
};

function appendArr(arr1, arr2)
{
	var i=0;
	
	for(i=0;i<arr2.length;i++)
	{
		arr1.push(arr2[i]);
	}
}

VQuery.prototype.find=function (str)
{
	var i=0;
	var aResult=[];
	
	for(i=0;i<this.elements.length;i++)
	{
		switch(str.charAt(0))
		{
			case '.':	//class
				var aEle=getByClass(this.elements[i], str.substring(1));
				
				aResult=aResult.concat(aEle);
				break;
			default:	//标签
				var aEle=this.elements[i].getElementsByTagName(str);
				
				//aResult=aResult.concat(aEle);
				appendArr(aResult, aEle);
		}
	}
	
	var newVquery=$();
	
	newVquery.elements=aResult;
	
	return newVquery;
};

function getIndex(obj)
{
	var aBrother=obj.parentNode.children;
	var i=0;
	
	for(i=0;i<aBrother.length;i++)
	{
		if(aBrother[i]==obj)
		{
			return i;
		}
	}
}

VQuery.prototype.index=function ()
{
	return getIndex(this.elements[0]);
};

VQuery.prototype.bind=function (sEv, fn)
{
	var i=0;
	
	for(i=0;i<this.elements.length;i++)
	{
		myAddEvent(this.elements[i], sEv, fn);
	}
};

VQuery.prototype.extend=function (name, fn)
{
	VQuery.prototype[name]=fn;
};

function $(vArg)
{
	return new VQuery(vArg);
}
```

```js
$().extend('animate', function (json){
	var i=0;
	
	for(i=0;i<this.elements.length;i++)
	{
		startMove(this.elements[i], json);
	}
	
	function getStyle(obj, attr)
	{
		if(obj.currentStyle)
		{
			return obj.currentStyle[attr];
		}
		else
		{
			return getComputedStyle(obj, false)[attr];
		}
	}
	
	function startMove(obj, json, fn)
	{
		clearInterval(obj.timer);
		obj.timer=setInterval(function (){
			var bStop=true;		//这一次运动就结束了——所有的值都到达了
			for(var attr in json)
			{
				//1.取当前的值
				var iCur=0;
				
				if(attr=='opacity')
				{
					iCur=parseInt(parseFloat(getStyle(obj, attr))*100);
				}
				else
				{
					iCur=parseInt(getStyle(obj, attr));
				}
				
				//2.算速度
				var iSpeed=(json[attr]-iCur)/8;
				iSpeed=iSpeed>0?Math.ceil(iSpeed):Math.floor(iSpeed);
				
				//3.检测停止
				if(iCur!=json[attr])
				{
					bStop=false;
				}
				
				if(attr=='opacity')
				{
					obj.style.filter='alpha(opacity:'+(iCur+iSpeed)+')';
					obj.style.opacity=(iCur+iSpeed)/100;
				}
				else
				{
					obj.style[attr]=iCur+iSpeed+'px';
				}
			}
			
			if(bStop)
			{
				clearInterval(obj.timer);
				
				if(fn)
				{
					fn();
				}
			}
		}, 30)
	}
});
```

```js
$().extend('drag', function (){
	var i=0;
	
	for(i=0;i<this.elements.length;i++)
	{
		drag(this.elements[i]);
	}
	
	function drag(oDiv)
	{
		oDiv.onmousedown=function (ev)
		{
			var oEvent=ev||event;
			var disX=oEvent.clientX-oDiv.offsetLeft;
			var disY=oEvent.clientY-oDiv.offsetTop;
			
			document.onmousemove=function (ev)
			{
				var oEvent=ev||event;
				
				oDiv.style.left=oEvent.clientX-disX+'px';
				oDiv.style.top=oEvent.clientY-disY+'px';
			};
			
			document.onmouseup=function ()
			{
				document.onmousemove=null;
				document.onmouseup=null;
			};
		};
	}
});
```







