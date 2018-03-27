---
title: 剑指Offer笔记-JS语法 - 5 综合能力
categories: JavaScript
tags:
  - JavaScript
  - algorithms
  - 剑指Offer
date: 2018-03-27 19:37:57
updated: 2018-03-27 19:37:57
---

### 问题38 数字在排序数组中出现的次数
**问题描述：统计一个数字在排序数组中出现的次数。**

由于数组有序，所以利用JavaScript提供的indexOf和last'IndexOf可以直接获得结果，第一个和最后一个出现的索引，然后直接求其差就可以得到次数了。

```js
function GetNumberOfK(data, k)
{
    // 检查输入
    if(!data){
        return 0;
    }

    let firstIdx = data.indexOf(k);
    if(firstIdx >= 0){
        let lastIdx = data.lastIndexOf(k);
        
        return lastIdx - firstIdx + 1;
    }else{
        return 0;
    }
}
```

若不使用内置函数，则可以考虑用二分查找分别找到firstIdx和lastIdx。其中需要注意的是找到k后还需要对比当前位置前后是否还有k，若有，说明当前位置不是第一个k的位置或最后一个k的位置。此时还需要继续二分查找。

### 问题39 二叉树的深度
**问题描述：输入一棵二叉树，求该树的深度。从根结点到叶结点依次经过的结点（含根、叶结点）形成树的一条路径，最长路径的长度为树的深度。**

可以考虑使用递归，一棵树的深度就是其左右子树深度中的最大值+1，这样的话就能容易写出递归的代码。

```js
function TreeDepth(pRoot)
{
    // 检查输入
    if(!pRoot){
      return 0
    }

    let leftDepth = TreeDepth(pRoot.left);
    let rightDepth = TreeDepth(pRoot.right);

    return Math.max(leftDepth, rightDepth) + 1;
}
```

#### 问题39.2 平衡二叉树
**问题描述：输入一棵二叉树，判断该二叉树是否是平衡二叉树。**

套用上面的解法，所以平衡二叉树就是左右子树的高度差不超过1，所以用递归的方式非常简单，但需要注意的是从父结点看是平衡的但是到子结点就不一定是平衡的了，所以递归其实是向下的。
```js
function IsBalanced_Solution(pRoot)
{
    // 检查输入
    if(!pRoot){
      return true
    }

    let leftDepth = TreeDepth(pRoot.left);
    let rightDepth = TreeDepth(pRoot.right);
    if(Math.abs(leftDepth - rightDepth) > 1){
        return false;
    }
    // 父结点平衡不代表子结点也平衡
    return IsBalanced_Solution(pRoot.left) && IsBalanced_Solution(pRoot.right);
}
```

但这种方式其实做了很多次无用的判断，因为这相当于是在前序遍历，在在计算深度的时候就遍历过子结点了，而在后面判断平衡时又遍历了一遍。

所以更好的方式时采用后序遍历，因为后序遍历的话则不会有这个问题，因为是先从子树开始判断是否是平衡。
```js
function IsBalanced_Solution(pRoot)
{
    return lastOrder(pRoot).isBalance;
}

function lastOrder(pRoot)
{
    // 检查输入
    if(!pRoot){
        return {
            depth: 0,
            isBalance: true
        };
    }
    
    let left = lastOrder(pRoot.left);
    let right = lastOrder(pRoot.right);
    
    if(left.isBalance && right.isBalance){
        if(Math.abs(left.depth - right.depth) <= 1){
            return {
                depth: Math.max(right.depth, left.depth) + 1,
                isBalance: true
            };
        }
    }
    
    return {
       depth: Math.max(right.depth, left.depth) + 1,
       isBalance: false
   }
}
```