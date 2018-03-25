---
title: 剑指Offer笔记JS版 - 2 高质量代码（完整性和Robust）
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


### 问题14 调整数组顺序使奇数位于偶数前面
**问题描述：输入一个整数数组，实现一个函数来调整该数组中数字的顺序，使得所有的奇数位于数组的前半部分，所有的偶数位于位于数组的后半部分（牛客网的要求：并保证奇数和奇数，偶数和偶数之间的相对位置不变。）**

若不需要保证相对位置，则采用快速排序中的划分思想即可，头尾分别两个指针，分别将奇偶交换。

需要注意一些特殊情况：全为奇，偶

```js
function reOrderArray(array)
{
    // 需要保证奇数和奇数，偶数和偶数之间的相对位置不变
    let oddArray = [],
        evenArray = [];
    array.map((k)=>{
        k%2 ? oddArray.push(k) : evenArray.push(k);
    });
    // 注意特殊情况：全为奇，偶
    
    return oddArray.length == 0 ? evenArray : 
            evenArray.length == 0 ? oddArray : 
            [oddArray, ...evenArray];
}
```

### 问题15 链表中倒数第k个结点
**问题描述：输入一个链表，输出该链表中倒数第k个结点。**

解法1：用快、慢两个相差k步指针分别遍历，当快指针走到链表尾时，慢指针就是倒数第k位了。
解法2：既然无论如何都要遍历一遍，那么直接将所有结点都保存到一个数组内，然后需要第几个结点直接取即可。

特殊情况：链表头结点为null，k为0，k大于结点数等。

解法1：
```js
/*function ListNode(x){
    this.val = x;
    this.next = null;
}*/
function FindKthToTail(head, k)
{
    // head为null或k为0
    if(!head || k == 0){
        return null;
    }
    
    let quick = head,
        slow = head;
    // 快结点先走k步
    for(let i = 1; i < k; ++i){
        if(quick && quick.next){
            quick = quick.next;
        }else{ // k大于结点总数
            return null;
        }
    }
    
    // 依次遍历剩下所有结点
    while(quick && quick.next){
        slow = slow.next;
        quick = quick.next;
    }
    
    return slow;
}
```
解法2：
```js
function FindKthToTail(head, k)
{
    // head为null或k为0
    if(!head || k == 0){
        return null;
    }
    
    let arr = [];
    while(head){
        arr.push(head);
        head = head.next;
    }
    
    return arr[arr.length - k
}
```

相关题目：
- 求链表的中间结点，若链表结点总数为偶数，则返回中间任意一个，也可以用快慢指针解决，快指针每次走2步
- 判断一个单向链表是否形成环，同前，若快指针能追到慢指针则表示形成了环，若快指针直到null都没追上，则无环

### 问题16 反转链表
**问题描述：输入一个链表，反转链表后，输出链表的所有元素。**

解法1：
使用三个指针i,j,k，分别是前一个结点、当前结点、下一个结点。反转步骤如下：
1. j.next = i
1. i = j
1. j = k
1. k = k.next

结束循环条件为`k == null`，注意输入指针头为null、链表仅有1个结点的特殊情况。
```js
function ReverseList(pHead)
{
    // 特殊情况：pHead为null、或仅有一个结点
    if(!pHead || !pHead.next){
        return pHead
    }
    
    let i = pHead,
        j = pHead.next,
        k = j.next;
    
    while(j){
        j.next = i;
        i = j;
        j = k;
        if(k){
            k = k.next;
        }
    }
    // 最后需要输出i，因为到链尾j已经指向null,同时注意pHead.next的引用未消除
    pHead.next = null;
    return i;
}
```

解法2：先遍历，将所有的值都存在一个数组内，然后遍历第二遍将所有结点的值修改为数组内pop出来的值。
```js
function ReverseList(pHead)
{
    // 特殊情况：pHead为null、或仅有一个结点
    if(!pHead || !pHead.next){
        return pHead
    }
    let arr = [],
        pointer = pHead;
    while(pointer){
        arr.push(pointer.val);
        pointer = pointer.next;
    }
    
    pointer = pHead;
    while(pointer){
        pointer.val = arr.pop();
        pointer = pointer.next;
    }
    
    return pHead;
}
```

### 问题17 合并两个排序的链表
**问题描述：输入两个单调递增的链表，输出两个链表合成后的链表，当然我们需要合成后的链表满足单调不减规则。**

注意链表头为null的情况即可，思想就是依次对比两个链表的头，谁小取谁。
```js
function Merge(pHead1, pHead2)
{
    // 每次输出一个pointer
    function buildOne(pHead1, pHead2) {
        let pointer = pHead1 == null ? pHead2 : pHead2 == null ? pHead1 : null;

        if(pHead1 && pHead2){ // 若都存在，则选择2个链表中最小的结点
            if(pHead1.val <= pHead2.val){ // 取pHead1
                pointer =  pHead1;
                pointer.next = buildOne(pHead1.next, pHead2);
            }else{ // 取pHead2
                pointer =  pHead2;
                pointer.next = buildOne(pHead1, pHead2.next);
            }    
        }

        return pointer;
    }

    return buildOne(pHead1, pHead2);
}
```

### 问题18 树的子结构
**问题描述：输入两棵二叉树A，B，判断B是不是A的子结构。（ps：我们约定空树不是任意一个树的子结构）**

只要用遍历的方式依次对比即可，二叉树的指针操作比链表多，所以一定要注意各种特殊情况，尤其是空结点或者空树的情况了。

判断是否为子树需要确定两个步骤，说明时候需要递归，说明时候不需要递归，以及递归的终止条件
```js
function HasSubtree(pRoot1, pRoot2)
{
    let rst = false;

    if(pRoot1 && pRoot2){
        // 仅当值相等时才递归比较后面的子树
        if(pRoot1.val == pRoot2.val){
            rst = hasTree2(pRoot1, pRoot2);
        }

        // 同时，若左右子树只要出现匹配就判断成功，无需再次判断。
        if(!rst){
            rst = HasSubtree(pRoot1.left, pRoot2);
        }

        if(!rst){
            rst = HasSubtree(pRoot1.right, pRoot2);
        }
    }

    return rst;
    
    // 递归比较pRoot1, pRoot2， 若pRoot2为空说明此条pRoot2的路径已经比较到最后了，返回true
    function hasTree2(pRoot1, pRoot2){
        if(!pRoot2){
            return true;
        }
        // 若pRoot2不为空的情况下而pRoot1为空，说明子树不匹配
        if(!pRoot1){
            return false;
        }
    
        if(pRoot1.val != pRoot2.val){
            return false;
        }
    
        return hasTree2(pRoot1.left, pRoot2.left) && hasTree2(pRoot1.right, pRoot2.right)
    }
}
```


<script src="//cdn.bootcss.com/mathjax/2.7.0/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>