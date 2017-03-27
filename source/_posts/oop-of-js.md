---
title: js实现面向对象
date: 2017-01-21 14:57:06
updated: 2017-01-21
categories: [fe]
tags: [js, oop]
---

js实现面向对象的方法

1. 面向过程转化为面向对象的步骤（选项卡实例）
2. JS 里的继承方式
3. call（构造函数伪装） 和 prototype（原型链）
4. 引用类型的特点（引用相当于钥匙，存储空间相当于房子）
5. 原型继承的缺点及解决方案
6. instanceof 作用：查看某个对象是否是某个类的实例
7. 用继承来实现拖拽实例
8. 系统对象：宿主对象（BOM和DOM）、内置对象（静态对象：Global和Math）、本地对象
9. 继承的优势：修改父类bug，子类自动继承

## 选项卡 ##
普通方式
```js
var aBtn=null;
var aDiv=null;

window.onload=function ()
{
	var oDiv=document.getElementById('div1');
	aBtn=oDiv.getElementsByTagName('input');
	aDiv=oDiv.getElementsByTagName('div');
	var i=0;
	
	for(i=0;i<aBtn.length;i++)
	{
		aBtn[i].index=i;
		aBtn[i].onclick=tab;
	}
};

function tab()
{
	for(i=0;i<aBtn.length;i++)
	{
		aBtn[i].className='';
		aDiv[i].style.display='none';
	}
	this.className='active';
	aDiv[this.index].style.display='block';
}
```

转变为oop方式
```js
window.onload=function ()
{
	var oTab=new TabSwitch('div1');
};

function TabSwitch(id)
{
	var oDiv=document.getElementById(id);
	this.aBtn=oDiv.getElementsByTagName('input');
	this.aDiv=oDiv.getElementsByTagName('div');
	var i=0;
	
	var _this=this;
	
	for(i=0;i<this.aBtn.length;i++)
	{
		this.aBtn[i].index=i;
		this.aBtn[i].onclick=function ()
		{
			_this.tab(this);
		};
	}
}

TabSwitch.prototype.tab=function (oBtn)
{
	for(i=0;i<this.aBtn.length;i++)
	{
		this.aBtn[i].className='';
		this.aDiv[i].style.display='none';
	}
	oBtn.className='active';
	this.aDiv[oBtn.index].style.display='block';
};
```

## js继承 ##
```js
function Person(name, sex)
{
	this.name=name;
	this.sex=sex;
}

Person.prototype.showName=function ()
{
	alert(this.name);
};

Person.prototype.showSex=function ()
{
	alert(this.sex);
};

//-------------------------------------

function Worker(name, sex, job)
{
	//this->new出来的Worker对象
	//构造函数伪装		调用父级的构造函数——为了继承属性
	Person.call(this, name, sex);
	
	this.job=job;
}

//原型链		通过原型来继承父级的方法
//Worker.prototype=Person.prototype;

for(var i in Person.prototype)
{
	Worker.prototype[i]=Person.prototype[i];
}

Worker.prototype.showJob=function ()
{
	alert(this.job);
};

var oP=new Person('blue', '男');
var oW=new Worker('blue', '男', '打杂的');

oP.showName();
oP.showSex();

oW.showName();
oW.showSex();
oW.showJob();
```

## 继承实现拖拽 ##
能将一个物体拖拽,调用方式：	
- `new Drag('div1');` 普通拖拽类
- `new LimitDrag('div2');` 有限制的拖拽
```js
function Drag(id)
{
	var _this=this;
	
	this.disX=0;
	this.disY=0;
	this.oDiv=document.getElementById(id);
	
	this.oDiv.onmousedown=function (ev)
	{
		_this.fnDown(ev);
		
		return false;
	};
}

Drag.prototype.fnDown=function (ev)
{
	var _this=this;
	var oEvent=ev||event;
	this.disX=oEvent.clientX-this.oDiv.offsetLeft;
	this.disY=oEvent.clientY-this.oDiv.offsetTop;
	
	document.onmousemove=function (ev)
	{
		_this.fnMove(ev);
	};
	
	document.onmouseup=function ()
	{
		_this.fnUp();
	};
};

Drag.prototype.fnMove=function (ev)
{
	var oEvent=ev||event;
	
	this.oDiv.style.left=oEvent.clientX-this.disX+'px';
	this.oDiv.style.top=oEvent.clientY-this.disY+'px';
};

Drag.prototype.fnUp=function ()
{
	document.onmousemove=null;
	document.onmouseup=null;
};
```

继承自Drag类的LimitDrag类对拖拽范围有限制

```js
function LimitDrag(id)
{
	Drag.call(this, id);
}

//LimitDrag.prototype=Drag.prototype;

for(var i in Drag.prototype)
{
	LimitDrag.prototype[i]=Drag.prototype[i];
}

LimitDrag.prototype.fnMove=function (ev)
{
	var oEvent=ev||event;
	var l=oEvent.clientX-this.disX;
	var t=oEvent.clientY-this.disY;
	
	if(l<0)
	{
		l=0;
	}
	else if(l>document.documentElement.clientWidth-this.oDiv.offsetWidth)
	{
		l=document.documentElement.clientWidth-this.oDiv.offsetWidth;
	}
	
	if(t<0)
	{
		t=0;
	}
	else if(t>document.documentElement.clientHeight-this.oDiv.offsetHeight)
	{
		t=document.documentElement.clientHeight-this.oDiv.offsetHeight;
	}
	
	this.oDiv.style.left=l+'px';
	this.oDiv.style.top=t+'px';
};
```
