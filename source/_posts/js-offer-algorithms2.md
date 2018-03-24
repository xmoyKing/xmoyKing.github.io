---
title: 剑指Offer笔记JS版 2
categories: JavaScript
tags:
  - JavaScript
  - algorithms
  - 剑指Offer
date: 2018-03-24 21:08:44
updated: 2018-03-24 21:08:44
---


### 问题11 数值的整数次方
**问题描述：给定一个double类型的浮点数base和int类型的整数exponent。求base的exponent次方。**

若不允许直接使用内置的`Math.pow(base, exponent)`计算，则可以考虑使用二分法，

$$ 
a^n = a^{(n/2)} * a^{(n/2)}
$$

同时，需要特别注意exponent为负数、1、0三种情况。
```js
function Power(base, exponent)
{
  if(exponent == 0){
      return 1;
  }else if(exponent == 1){
      return base;
  }else if(exponent < 0){
      let rst = 1/Power(base, -exponent >> 1);
      rst *= rst;

      // 指数为奇数
      if(-exponent & 1 == 1){
          rst *= 1/base;
      }
      return rst;
  }else {
      let rst = Power(base, exponent >> 1);
      rst *= rst;

      // 指数为奇数
      if(exponent & 1 == 1){
          rst *= base;
      }
      return rst;
      
  }
}
```

### 问题12 打印1到最大的n位数
**问题描述：输入数字n，按顺序打印从1到最大的n位十进制数，比如输入3，则从1打印到999**

本体的关键在于大数问题，所以需要在字符数组上模拟数字的加法。

```js
function printMaxNbit(n){
  if(n < 0) return;

  let num = []; // 使用字符数组模拟

  for(let i = 0; i < 10; i++){
    num[0] = i; //保存当前位
    printRecur(num, n, 0);
  }

  // 循环打印
  function printRecur(num, len, idx){
    if(idx == len - 1){
      print(num);
      return;
    }

    for(let i = 0; i < 10; i++){
      num[idx+1] = i;
      printRecur(num, len, idx+1);
    }
  }

  // 打印字符数组，注意前导零
  print(num){
    console.log(num.join('').replace(/^0/,''));
  }
}
```

相关题目：
- 定义一个函数，在该函数上实现任意两个整数的加法。

### 问题13 在O(1)时间内删除链表结点
**问题描述：给定单向链表的头指针和一个结点指针，定义一个函数在O(1)时间删除该结点**

此问题的关键在于，正常单链表删除结点必须知道前一个结点。

所以解决的方法就是，将后一个结点的内容复制到本结点，然后删除本结点即可。，同时需要注意删除头尾结点、以及空结点的问题。

<script src="//cdn.bootcss.com/mathjax/2.7.0/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>