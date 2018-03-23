---
title: 剑指Offer笔记JS版
categories: js
tags:
  - js
  - algorithms
  - 剑指Offer
date: 2018-03-23 15:35:38
updated: 2018-03-23 15:35:38
---

本系列为《剑指Offer》中问题的js版解答。

其他资料：
- [学习JavaScript数据结构与算法笔记](/2017/02/21/js-data-structure/)
- [JavaScript版剑指offer](https://blog.csdn.net/column/details/16574.html?&page=1)
- [牛客网剑指在线测试](https://www.nowcoder.com/ta/coding-interviews?page=1) *在线编程、测试*

### 问题3 二维数组中的查找
**问题描述：在二维数组中找到给定数是否存在，二维数组的行和列都是有序增大的。**

简单的方法，依次遍历，如下解法是采用数组内置方法：
```js
function findNative(arr2d, key) {
    let found = false;
    arr2d.map(function (arr) {
        if(arr.includes(key)){
            found = true;
        }
    });
    return found;
}
```

参考方法，每次选取对比的指为当前范围右上角的数，则每次循环将按行或列剔除：
```js
// arr2d 是一个二维数组，横纵增大，
// rows表示行数，cols表示列数，key表示搜索关键字
// 返回布尔值，查询key是否在arr2d中
function findNum(arr2d, rows, cols, key) {
    let found = false;
    if(arr2d && rows > 0 && cols > 0){
        let row = 0,
            col = cols - 1;

        while(row < rows && col >= 0){
            if(arr2d[row][col] == key){
                found = true;
                break;
            }else if(arr2d[row][col] > key){
                col--;
            }else{
                row++;
            }
        }
    }

    return found;
}

let arrQ = [[1,2,8,9],[2,4,9,12],[4,7,10,13],[6,8,11,15]];

console.log(findNum(arrQ, 4, 4, 7));
```

由于二维数组在内存中实际是连续的空间，所以有可能给出的是一维数组，此时需要计算当前比较数的位置（`row * col + col`）

本题的考点就是能否找出从某个数组内的数（右上角、左上角、任何角落的位置都可以），从其位置开始分析，找到查找的规律。

### 问题4 替换空格
**问题描述：将字符串`'we are happy'`替换为`we%20are%20happy`。**

由于js的字符串比较简单，而原书是C++描述的，所以若采用内置的replace函数是非常方便的，而若不用内置的情况下，则O(n)解法思想如下：

相当于一个字符数组，第一遍遍历获取所有空格数量m，然后第二遍从后向前遍历，没遇到空格就将当前向后移动`m*3`位，遇到空格就替换为%20。

合并两个数组（包括字符串），若从前向后复制需要重复移动数字或字符多次，那么可以考虑从后向前复制，这样能减少移动次数

相关题目：
- 有两个排序的数组A1和A2，内存在A1的末尾有足够的空间容纳A2，请将A2中所有数字插入到A1中并排序。
  解法：从后向前对比A1和A2，将较大的数字复制到A1的合适位置上

### 问题5 从尾到头打印链表
**问题描述：输入一个链表的头结点、从尾到头反过来打印出每个节点的指**

链表节点定义如下：
```js
function Node(value){
  this.value = value;
  this.next = null;
}
```

解法思想：由于链表只能从头到尾遍历，而却需要从尾到头打印，所以可以采用递归的方法，也可以采用辅助栈的方法（其实递归在大多数语言的内部实现就是栈，所以用递归能实现的用栈一定也能实现）。

而关于递归，主要的问题在于，若栈太长，递归调用太深，性能不高。（PS: 与递归相关的一个话题是“尾递归”优化）。

### 问题6 重建二叉树
**问题描述：输入某二叉树的前序遍历和中序遍历结果，重建出该二叉树，假设输入序列不含重复数字**
例如：前序[1,2,4,7,3,5,6,8],中序[4,7,2,1,5,3,8,6]
树节点定义如下：
```js
function BinaryTreeNode(value){
  this.value = value;
  this.leftNode = null;
  this.rightNode = null;
}
```

思想：在前序中，第一个数字一定是树根节点，后面依次是连续的左子树的序列和连续的右子树序列；中序遍历中根节点在序列中间，左子树在左边，右子树在右边。

所以从给出的中序中可以得到左右子树的序列。然后对应到前序，分别找到对应的子树，这样就相当于找到了一个节点的位置，同时找到了其左右子树的根节点，如此再对每个子树递归即可找到所有节点的位置。
```js
// preOrder为前序数组，inOrder为中序数组，返回根节点
function buildBinaryTree(preOrder, inOrder) {
    // 对参数进行检查 ...
    let startPre = preOrder[0];

    let root = new BinaryTreeNode(startPre);

    // 若传入的是单个节点
    if(preOrder.length == 1 && inOrder.length == 1){
        return root;
    }

    // 在中序中找到根节点
    let rootIdxIn = inOrder.indexOf(startPre),
        newInOrderLeft =  inOrder.slice(0, rootIdxIn),
        newInOrderRight = inOrder.slice(rootIdxIn+1),
        newPreOrderLeft = preOrder.slice(1, rootIdxIn+1), // 此处传入rootIdxIn的索引其实就是左子树的节点个数
        newPreOrderRight = preOrder.slice(rootIdxIn+1);

    // 利用递归构造当前root的左右子树
    if(newPreOrderLeft.length > 0){
        root.leftNode = buildBinaryTree(newPreOrderLeft, newInOrderLeft);
    }
    if(newPreOrderRight.length > 0){
        root.rightNode =  buildBinaryTree(newPreOrderRight, newInOrderRight);
    }
    return root;
}

let root = buildBinaryTree([1,2,4,7,3,5,6,8], [4,7,2,1,5,3,8,6]);

// 前序遍历测试：
function preOrder(root) {
    console.log(root.value);
    if(root.leftNode){
        preOrder(root.leftNode)
    }
    if(root.rightNode){
        preOrder(root.rightNode)
    }
}
preOrder(root);
```

需要有对二叉树不同遍历算法基本的理解，以及如何在遍历中划分去左右子树，发现问题的本质：子树也是一颗二叉树，因此可采用递归方式。

### 问题7 用两个栈实现队列
**问题描述：用两个栈实现一个队列，队列的声明如下，请实现它的两个函数 appendTail 和 deleteHead，分别完成在队列尾部插入和队列头部删除**
```js
// 以数组模拟栈，仅对栈使用push和pop方法和length属性
let stack1 = [],
    stack2 = [];
 
function push(node)
{
    // write code here
    stack1.push(node);
}
function pop()
{
    // write code here
    if(stack2.length == 0){
        while(stack1.length > 0){
            stack2.push(stack1.pop());
        }
    }
    return stack2.pop();
}
```

关键是弄明白3种情况：
1. 忘空队列里添加、删除元素
1. 忘非空队列里添加、删除元素
1. 连续删除元素直至队列为空

思路：插入元素是仅插入到stack1中，每次需要删除队头元素时检查stack2，若stack2非空则直接弹出stack2中栈顶元素，否则先将stack1中全部元素弹出到stack2中，然后再弹出stack2栈顶元素。

相关题目：
  - 用两个队列实现一个栈
    解法：插入时始终只在一个队列queue1操作，另一个队列queue2为空，弹出时将当前queue1内除最后一个元素外的其他元素依次弹入queue2，然后弹出最后一个元素即可，完成一次弹出操作后，queue1和queue2的角色互换即可复原原来的操作顺序了。

### 问题8 旋转数组最小值
**问题描述：把一个数组最开始的若干个元素搬到数组的末尾，我们称之为数组的旋转。 输入一个非递减排序的数组的一个旋转，输出旋转数组的最小元素。 例如数组{3,4,5,1,2}为{1,2,3,4,5}的一个旋转，该数组的最小值为1。 NOTE：给出的所有元素都大于0，若数组大小为0，请返回0。**

若套用js数组原生的Math.min则非常简单
```js
function minNumberInRotateArray(rotateArray)
{
    // write code here
    if(rotateArray.length == 0){
        return 0
    }else{
        return Math.min.apply(null,rotateArray)
    }
}
```

但若不使用原生提供的函数，则可以通过顺序遍历或二分法找到这个最小值。

使用二分法的时候需要注意，数组不是完全有序的，而是一个类似凹形，所以最小值一定在中间（只要检查一下数组头和数组尾的大小即可），需要注意的是“非递减”的意思是有可能会有相等的元素。同时需要注意数组可能本身就是有序的（即数组头小于数组尾）。

对普通情况，找到中间值，无论是任何的情况，中间值大于数组头，说明最小值在右边，中间值小于数组尾，说明最小值在左边，一定可以将数组化为一个只有一半规模的数组，此时重复查找即可。当前后两个指针相遇时说明找到了最小值。
