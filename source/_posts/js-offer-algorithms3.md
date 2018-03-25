---
title: 剑指Offer笔记JS版 - 3 解题思路
categories: JavaScript
tags:
  - JavaScript
  - algorithms
  - 剑指Offer
date: 2018-03-25 18:25:19
updated: 2018-03-25 18:25:19
---

### 问题19 二叉树的镜像
**问题描述：操作给定的二叉树，将其变换为源二叉树的镜像。**

```
二叉树的镜像定义：源二叉树 
     8
    /  \
   6   10
  / \  / \
 5  7 9   11

镜像二叉树
     8
    /  \
   10   6
  / \  / \
 11 9 7   5
```

交换过程：先序遍历二叉树，若有子结点则交换子结点，当交换完子结点后就得到了树的镜像。

```js
function Mirror(root)
{
    // 若root不存在则直接返回
    if(!root){
        return null
    }
    // 交换
    let temp = Mirror(root.left);
    root.left = Mirror(root.right);
    root.right = temp;

    return root;
}
```

测试数据：
```js
var root = {
    val: 1,
    left: {
        val: 2,
        left: null,
        right: null,
    },
    right: {
        val: 1,
        left: {
            val: 2,
            left: null,
            right: null,
        },
        right: null
    }
}
```


相关题目：
- 若改为使用循环，该如何实现？

### 问题20 顺时针打印矩阵
**问题描述：输入一个矩阵，按照从外向里以顺时针的顺序依次打印出每一个数字，例如，如果输入如下矩阵： [[1,2],[3,4]] 则依次打印出数字 [1,2,4,3]**

关键是理清打印一圈行列的变化以及分清哪些情况下才会继续打印，那些情况不会继续打印。

```js
function printMatrix(matrix)
{

    // 每次打印从左上角开始
    let start = 0,
        rows = matrix.length,
        cols = matrix[0] && matrix[0].length,
        rst = [];

    // 特殊情况
    if(!rows || !cols){
        return null;
    }
    
    // 每次打印一圈
    while(rows > start*2 && cols > start*2){
        printMatrixCircle(matrix, cols, rows, start);
        ++start;
    }
    
    function printMatrixCircle(matrix, cols, rows, start){
        let endx = cols - 1 - start,
            endy = rows - 1 - start;

        // 从左到右
        for(let i = start; i <= endx; ++i){
            rst.push(matrix[start][i]);
        }

        // 从上到下
        if(start < endy){
            for(let i = start + 1; i <= endy; ++i){
                rst.push(matrix[i][endx]);
            }
        }

        // 从右到左
        if(start < endx && start < endy){
            for(let i = endx - 1; i >= start; --i){
                rst.push(matrix[endy][i]);
            }
        }

        // 从下到上
        if(start < endx && start < endy - 1){
            for(let i = endy - 1; i >= start + 1; --i){
                rst.push(matrix[i][start]);
            }
        }
    }

    return rst;
}

console.log(printMatrix([[1,2],[3,4]]))
```

### 问题21 包含min函数的栈
**问题描述：定义栈的数据结构，请在该类型中实现一个能够得到栈最小元素的min函数。**

准备两个栈，一个用于普通操作，一个专门用于保存当前最小值的栈。

```js
function push(node)
{
    // 检查最小值
    let minnum = min();
    // 若最小值不存在或当前push的值更小，则push到最小栈中
    if(!minnum || node <= minnum){
        stackmin.push(node);
    }else{
      // 否则压入当前的最小值到栈中
        stackmin.push(minnum);
    }

    stack.push(node);
}
function pop()
{
    // 出栈
    stackmin.pop();
    return stack.pop();
}
function top()
{
    // 获取当前栈顶元素
    return stack[stack.length - 1];
}
function min()
{
    // 获取当前栈中的最小值
    if(stackmin.length){
        return stackmin[stackmin.length - 1];
    }else{
        return null
    }
}

let stack = [],
    stackmin = [];
```

### 问题22 栈的压入、弹出序列
**问题描述：输入两个整数序列，第一个序列表示栈的压入顺序，请判断第二个序列是否为该栈的弹出顺序。假设压入栈的所有数字均不相等。例如序列1,2,3,4,5是某栈的压入顺序，序列4,5,3,2,1是该压栈序列对应的一个弹出序列，但4,3,5,1,2就不可能是该压栈序列的弹出序列。（注意：这两个序列的长度是相等的）**

以一个辅助栈，把输入的第一个序列依次压入栈，并按照第二个序列的顺序依次从栈中弹出，若过程能够完成，则说明正确。

```js
function IsPopOrder(pushV, popV)
{
    let check = false,
        n = pushV.length;
    // 检查输入
    if(pushV && popV && n > 0){
        // 准备2个数组队列，以及栈
        let nextPush = pushV,
            nextPop = popV,
            stack = [];

        // 只要nextPop内不空则继续循环
        while(nextPop.length){
            // 只要 nextPop头的值 与任意一个 头相等，就不用入栈
            if(nextPop[0] == stack[stack.length - 1]){
                nextPop.shift() && stack.pop();
            }
            if(nextPop[0] == nextPush[0]){
                nextPop.shift() && nextPush.shift();
            }

            // nextPush不空，并且
            // nextPop头的值 与 nextPush头值 不同，或 nextPop头的值 与 stack尾的值 不同
            // 则说明值可能在nextPush队列后面，
            while(nextPush.length && nextPop[0] != nextPush[0] && nextPop[0] != stack[stack.length - 1]){
                stack.push(nextPush.shift());
            }

            // 每次打印三个队列的值
            // console.log(stack, nextPush, nextPop)

            // 若出现nextPush已空，而nextPop头的值 与 stack尾的值 不同，则说明对比失败
            if(!nextPush.length && nextPop[0] != stack[stack.length - 1]){
                break;
            }
        }

        if(!nextPop.length){
            check = true;
        }
    }

    return check;
}

console.log(IsPopOrder([1,2,3,4,5], [4,5,3,2,1]))
console.log(IsPopOrder([1,2,3,4,5], [4,3,5,1,2]))
```

### 问题23 从上往下打印二叉树
**问题描述：从上往下打印出二叉树的每个节点，同层节点从左至右打印。**

用一个队列记录当前打印的结点，在打印之前将其子结点依次入队（先左后右）

```js
function PrintFromTopToBottom(root)
{
    let arr = [], // 记录打印顺序的队列
        rst = []; // 打印结果
    
    // 检查值
    if(root){
        arr.push(root); // 初始化将根结点加入打印队列
    }

    while(arr.length){
        let node = arr.shift();
        
        if(node.left){
            arr.push(node.left)
        }
        if(node.right){
            arr.push(node.right)
        }

        rst.push(node.val);
    }

    return rst;
}
```

### 问题24 二叉搜索树的后序遍历序列
**问题描述：输入一个整数数组，判断该数组是不是某二叉搜索树的后序遍历的结果。如果是则输出Yes,否则输出No。假设输入的数组的任意两个数字都互不相同。**

后续遍历根结点在最后，其前面的队列中左子树都小于根，右子树都大于根。先找到根节点，然后利用递归依次判断左右子树是否是搜索二叉树。

关键是考虑特殊情况，仅有左右子树时的情况。

```js
function VerifySquenceOfBST(sequence)
{
    // console.log(sequence);

    let len = sequence.length;
    if(!sequence || !len){
       return false;
    }
    // 若只有一个结点，则必然是有序的
    if(len == 1){
        return true;
    }
    
    let root = sequence[len - 1];

    // 从数组中找到右子树起始结点, 若没找到说明仅有左子树
    let endLeft = sequence.findIndex((key)=>{
        if(key > root){
            return true;
        }
    });

    // 若右子树有小于根的结点则返回false
    for(let j = endLeft; j >= 0 && j < len - 1; ++j){
        if(sequence[j] < root){
            return false;
        }
    }
    
    let left = true;
    if(endLeft > 0){ // 若存在左子树
        left = VerifySquenceOfBST(sequence.slice(0, endLeft));
    }else if(endLeft < 0){ // 仅有左子树
        left = VerifySquenceOfBST(sequence.slice(0, len - 1));
    }

    let right = true;
    if(endLeft >= 0){  // 若存在右子树
        right = VerifySquenceOfBST(sequence.slice(endLeft, len - 1));
    }
    
    return (left && right);
}

// console.log(VerifySquenceOfBST([4,8,6,12,16,14,10]));
// console.log(VerifySquenceOfBST([4,6,7,5]));
// console.log(VerifySquenceOfBST([6,7]));
console.log(VerifySquenceOfBST([7,4,6,5]));
```