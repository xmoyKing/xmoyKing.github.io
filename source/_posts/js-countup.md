---
title: js插件CountUp源码解析
categories: JavaScript
tags:
  - JavaScript
  - countup
date: 2017-12-13 18:50:19
updated: 2017-12-13 18:50:19
---

该插件Git地址：[CountUp.js](http://inorganik.github.io/countUp.js/)

```js
// root表示插件所依附的命名空间，一般为全局对象
// factory表示工厂方法，即生成CountUp插件类的工厂
(function(root, factory) {
  if (typeof define === 'function' && define.amd) { // AMD规范的包定义
    define(factory);
  } else if (typeof exports === 'object') { // CommonJS规范的包定义
    module.exports = factory(require, exports, module);
  } else { // 否则直接在浏览器的环境内执行，在window全局变量上添加CountUp方法
    root.CountUp = factory();
  }
}(this, function(require, exports, module) {

/*

	countUp.js
	by @inorganik

*/

// target = id of html element or var of previously selected html element where counting occurs
// 			目标元素的id或变量
// startVal = the value you want to begin at
//			开始数值
// endVal = the value you want to arrive at
//			结束数值
// decimals = number of decimal places, default 0
//			精确到的小数的位数，默认为0，即没有小数
// duration = duration of animation in seconds, default 2
//			动画持续时间，默认2s
// options = optional object of options (see below)
//			其他配置

// CountUp类
var CountUp = function(target, startVal, endVal, decimals, duration, options) {

	var self = this; // 保存this变量的引用，后续闭包中this的指向需要用到，也是每个实例化CountUp后的实例对象
	self.version = function () { return '1.9.3'; };

	// default options 默认配置
	self.options = {
		useEasing: true, // toggle easing 开启缓动效果
		useGrouping: true, // 1,000,000 vs 1000000 开启分组，以千为分隔
		separator: ',', // character to use as a separator 分组默认以,为分隔符
		decimal: '.', // character to use as a decimal 小数点默认以.分隔
		easingFn: easeOutExpo, // optional custom easing function, default is Robert Penner's easeOutExpo 自定义缓动效果函数，默认为easeOutExpo效果
		formattingFn: formatNumber, // optional custom formatting function, default is formatNumber above 文本格式化，默认使用内部的formatNumber方法
		prefix: '', // optional text before the result 结果前缀
		suffix: '', // optional text after the result 结果后缀
		numerals: [] // optionally pass an array of custom numerals for 0-9 默认为数组0-9，可以传入一个字符数组
	};

	// extend default options with passed options object 将默认配置对应项修改为传入的配置项
	if (options && typeof options === 'object') {
		for (var key in self.options) {
			if (options.hasOwnProperty(key) && options[key] !== null) {
				self.options[key] = options[key];
			}
		}
	}
	// 若配置的分组分隔符为空字符串，则不开启分组
	if (self.options.separator === '') {
		self.options.useGrouping = false;
	}else {
		// ensure the separator is a string (formatNumber assumes this) 确保分隔符为字符类型
		self.options.separator = '' + self.options.separator;
	}

	// make sure requestAnimationFrame and cancelAnimationFrame are defined
	// polyfill for browsers without native support
	// by Opera engineer Erik Möller
	// 全局环境下requestAnimationFrame和cancelAnimationFrame方法需要存在
	// 若没有原生实现，则使用垫片自定义该方法，
	// 关于requestAnimationFrame，可以参考： http://www.zhangxinxu.com/wordpress/2013/09/css3-animation-requestanimationframe-tween-%E5%8A%A8%E7%94%BB%E7%AE%97%E6%B3%95/

	var lastTime = 0;
	var vendors = ['webkit', 'moz', 'ms', 'o'];
	for(var x = 0; x < vendors.length && !window.requestAnimationFrame; ++x) {
		window.requestAnimationFrame = window[vendors[x]+'RequestAnimationFrame'];
		window.cancelAnimationFrame = window[vendors[x]+'CancelAnimationFrame'] || window[vendors[x]+'CancelRequestAnimationFrame'];
	}
	if (!window.requestAnimationFrame) {
		window.requestAnimationFrame = function(callback, element) {
			var currTime = new Date().getTime();
			var timeToCall = Math.max(0, 16 - (currTime - lastTime)); // 为了得出当前宿主浏览器能够支持的最大帧率
			var id = window.setTimeout(function() { callback(currTime + timeToCall); }, timeToCall); // 在调用回调函数时传入调用时的时间
			lastTime = currTime + timeToCall;
			return id;
		};
	}
	if (!window.cancelAnimationFrame) {
		window.cancelAnimationFrame = function(id) {
			clearTimeout(id);
		};
	}
	// 内部使用格式化数字方法，若有自定义的格式化方法则会被覆盖
	function formatNumber(num) {
		var neg = (num < 0), // 负号
        	x, x1, x2, x3, i, len;
		num = Math.abs(num).toFixed(self.decimals);
		num += ''; // 将num转换为字符串
		x = num.split('.'); // 用split将num分为整数和小数部分，字符串类型
		x1 = x[0]; // 整数部分
		x2 = x.length > 1 ? self.options.decimal + x[1] : ''; // 小数部分
		if (self.options.useGrouping) { // 若开启分组，则对整数部分进行分隔
			x3 = '';
			for (i = 0, len = x1.length; i < len; ++i) { // 依次（从右至左）对整数每一位进行处理，每3位添加分隔符
				if (i !== 0 && ((i % 3) === 0)) {
					x3 = self.options.separator + x3;
				}
				x3 = x1[len - i - 1] + x3;
			}
			x1 = x3;
		}
		// optional numeral substitution 若配置了替代字符，则对每一个数字进行替换
		if (self.options.numerals.length) {
			x1 = x1.replace(/[0-9]/g, function(w) {
				return self.options.numerals[+w];
			})
			x2 = x2.replace(/[0-9]/g, function(w) {
				return self.options.numerals[+w];
			})
		}
		return (neg ? '-' : '') + self.options.prefix + x1 + x2 + self.options.suffix;
	}
	// Robert Penner's easeOutExpo 缓动效果方法
	// * t: current time（当前时间）
	// * b: beginning value（初始值）
	// * c: change in value（变化量）
	// * d: duration（持续时间）
	// * 返回当前时间点对应的值
	function easeOutExpo(t, b, c, d) {
		return c * (-Math.pow(2, -10 * t / d) + 1) * 1024 / 1023 + b;
	}
	function ensureNumber(n) { // 检测n是否为数字类型
		return (typeof n === 'number' && !isNaN(n));
	}
	// 初始化
	self.initialize = function() {
		if (self.initialized) return true; // 初始化标志

		self.error = '';
		self.d = (typeof target === 'string') ? document.getElementById(target) : target; // 获取目标，支持ID或原生元素对象
		if (!self.d) {
			self.error = '[CountUp] target is null or undefined'
			return false;
		}
		self.startVal = Number(startVal);
		self.endVal = Number(endVal);
		// error checks 检查开始值和结束值是否为数字类型
		if (ensureNumber(self.startVal) && ensureNumber(self.endVal)) {
			self.decimals = Math.max(0, decimals || 0); // 最大精度
			self.dec = Math.pow(10, self.decimals); // 获取最大精度的整数形式，避免小数计算
			self.duration = Number(duration) * 1000 || 2000; // 持续时间
			self.countDown = (self.startVal > self.endVal); // 是否为倒数
			self.frameVal = self.startVal; // 将动画值设置为开始值
			self.initialized = true;
			return true;
		}else {
			self.error = '[CountUp] startVal ('+startVal+') or endVal ('+endVal+') is not a number';
			return false;
		}
	};

	// Print value to target 设置值到目标元素
	self.printValue = function(value) {
		var result = self.options.formattingFn(value); // 将当前的值传入并获取结果格式化后的数字

		// 依据不同的目标标签类型，设置方法不同
		if (self.d.tagName === 'INPUT') { // 文本框
			this.d.value = result;
		}
		else if (self.d.tagName === 'text' || self.d.tagName === 'tspan') {
			this.d.textContent = result;
		}
		else {
			this.d.innerHTML = result;
		}
	};
	// 计算方法，传入当前的时间戳，
	self.count = function(timestamp) {
		 // 保存第一次的时间戳为开始时间
		if (!self.startTime) { self.startTime = timestamp; }

		self.timestamp = timestamp;
		var progress = timestamp - self.startTime; // 计算当前的进度
		self.remaining = self.duration - progress; // 计算剩余时间

		// to ease or not to ease 根据配置决定是否采用缓动效果，同时需要注意是否为倒数，最后设置当前帧的值
		if (self.options.useEasing) {
			if (self.countDown) {
				self.frameVal = self.startVal - self.options.easingFn(progress, 0, self.startVal - self.endVal, self.duration);
			} else {
				self.frameVal = self.options.easingFn(progress, self.startVal, self.endVal - self.startVal, self.duration);
			}
		} else { // 不采用缓动时，即默认为线性改变
			if (self.countDown) {
				self.frameVal = self.startVal - ((self.startVal - self.endVal) * (progress / self.duration));
			} else {
				self.frameVal = self.startVal + (self.endVal - self.startVal) * (progress / self.duration);
			}
		}

		// don't go past endVal since progress can exceed duration in the last frame
		// 在最后一帧的动画会超过设置的结束值，所以需要验证是否超值
		if (self.countDown) {
			self.frameVal = (self.frameVal < self.endVal) ? self.endVal : self.frameVal;
		} else {
			self.frameVal = (self.frameVal > self.endVal) ? self.endVal : self.frameVal;
		}

		// decimal 由于计算时会产生小数误差，此处作用为消除误差：小数-》整数-》小数
		self.frameVal = Math.round(self.frameVal*self.dec)/self.dec;

		// format and print value 打印值
		self.printValue(self.frameVal);

		// whether to continue 动画没结束，则递归调用自身，用requestAnimationFrame代替setTimeout
		if (progress < self.duration) {
			self.rAF = requestAnimationFrame(self.count); // 每次执行将定时器保存下来，主要用于在reset方法中提前取消动画
		} else { // 若动画结束，则调用回调函数
			if (self.callback) self.callback();
		}
	};
	// start your animation 开始执行动画方法
	// 同时注意，回调函数不是在配置中指定的，而是在start方法中指定
	self.start = function(callback) {
		if (!self.initialize()) return; // 检查是否初始化，防止初始化前就调用此实例方法
		self.callback = callback;
		self.rAF = requestAnimationFrame(self.count);
	};
	// toggles pause/resume animation 支持暂停和恢复方法，通过内部的paused标志检查状态
	// 但暂停仅仅只是取消了定时器，并没有重置已经完成的动画进度
	self.pauseResume = function() {
		if (!self.paused) { // 粘贴动画
			self.paused = true;
			cancelAnimationFrame(self.rAF);
		} else { // 恢复动画时，将剩余时间设置为持续时间，同时将当前的帧值保存为开始时间
			self.paused = false;
			delete self.startTime;
			self.duration = self.remaining;
			self.startVal = self.frameVal;
			requestAnimationFrame(self.count);
		}
	};
	// reset to startVal so animation can be run again 重置动画
	// 与暂停动画方法相比，重置方法将开始时间删除，同时重置初始化标识
	self.reset = function() {
		self.paused = false;
		delete self.startTime;
		self.initialized = false;
		if (self.initialize()) { // 接着直接开始新一轮动画
			cancelAnimationFrame(self.rAF);
			self.printValue(self.startVal);
		}
	};
	// pass a new endVal and start animation 在动画未结束前调用时可以动态更新结束值
	self.update = function (newEndVal) {
		if (!self.initialize()) return; // 若未初始化则直接返回
		newEndVal = Number(newEndVal);
		if (!ensureNumber(newEndVal)) {
			self.error = '[CountUp] update() - new endVal is not a number: '+newEndVal;
			return;
		}
		self.error = '';
		if (newEndVal === self.frameVal) return; // 若更新的值为当前帧的值，则直接返回
		cancelAnimationFrame(self.rAF); // 每次更新值则需重置内部的属性，此时流程与暂停后立即恢复的操作类似，但不完全一致
		self.paused = false;
		delete self.startTime;
		self.startVal = self.frameVal;
		self.endVal = newEndVal;
		self.countDown = (self.startVal > self.endVal);
		self.rAF = requestAnimationFrame(self.count);
	};

	// format startVal on initialization
	if (self.initialize()) self.printValue(self.startVal);
};

return CountUp;

}));
```