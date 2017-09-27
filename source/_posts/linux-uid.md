---
title: Linux Uid
categories:
  - linux
tags:
  - linux 
  - uid
date: 2017-06-12 23:47:02
updated: 2017-06-12 23:47:02
---

这里主要讨论三种uid，真实ruid(real user id), 有效euid(effective user id), 被保存的uid(saved user id)。

在UNIX系统中，能否访问文件的特权是基于用户id和组id的。当用户需要增加特权或者降低特权的时候，就需要更换用户ID或者组ID。一般的情况是使用过最小特权，以防止恶意用户攻击我们的程序获得不应当的特权。

1.实际用户ID和实际组ID标示我们实际上是谁；这两个字段是在登录时读取口令文件中的登录项。一般情况下，在一个会话期间，实际用户和实际组用户不会改变；但超级用户的进程可能改变它们。
2.有效用户ID，有效组ID以及附加组ID决定了文件访问权限；
3.保存的设置用户ID在执行程序时包含了有效用户ID的副本。

每个文件都有所有者和组所有者；当执行一个文件时，进程的有效用户ID通常就是实际用户ID，有效组ID通常是实际组ID。但是可以设置特殊标志，含义是“当执行此文件时，将进程的有效用户ID设置为文件所有者的ID”。因此，当文件设置了setuid之后，实际用户ID和有效用户ID可能是不同的，此时的有效用户ID是文件所有者的ID。

（APUE）
关于文件访问权限：
1.对文件的读写权限决定我们是否能够打开和读写该文件；
2.为了在目录中创建新文件，需要对该目录有写和执行权限；
3.为了删除目录中的文件，需要对目录有写和执行权限，但是对文件本身不需要具有读写权限；

进程每次打开、创建或者删除文件时，内核进行文件访问权限测试，这种测试设计文件的所有者，进程的有效ID。基本规则是：
1.如进程的有效ID是0， 则允许访问；
2.如果进程的有效ID等于文件的所有者ID，那么如果设置了文件的所有者权限位，则允许访问。
3.如果进程的有效组ID是文件的组ID，则根据组权限判断。
4.如果设置了其它用户的访问权限，则根据其它用户访问权限进行判断。

关于文件权限：
1.根据mode，
2.根据进程的effective uid。

我们通过一个例子来理解以上的描述。
myecho程序，功能是向文件中写入一个字符串。
以三个用户分别创建三个文件root.txt,user1.txt和user2.txt。登录用户为user1，即myecho的owner是user1.myecho可执行文件可以改变与自己的owner相同的文件；但是对另外两个文件没有读写权限。
通过修改myecho的owner和set-uid，则不管哪个用户执行myecho，可以实现对另外两个文件的读写。
譬如，将myecho的owner设置为root，并设置u+s，则普通用户user1和user2也可以使用myecho修改root.txt，因为此时的user1和user2的有效权限是root。
当然，如果myecho的owner是root，并且设置了setuid，可以对三个文件进行读写。

Myecho程序，其它用户拥有执行权限，因此，当由其它用户执行时，也可以改变自己的文件。
如果user1是myecho的owner，而myecho的执行权限是755，那么user2执行myecho时，可以改变user2.txt。

现在的问题是：user2首先想要修改user1.txt，所以myecho需要设置s位。另一方面，此时它还能修改user2.txt吗？（它的effective uid已经是user1）



关于setuid和seteuid。
1.setuid(uid)首先请求内核将本进程的[真实uid],[有效uid]和[被保存的uid]都设置成函数指定的uid, 若权限不够则请求只将effective uid设置成uid, 再不行则调用失败.
2.seteuid(uid)仅请求内核将本进程的[有效uid]设置成函数指定的uid.
再具体来说setuid函数的话是这样的规则:
1.当用户具有超级用户权限的时候,setuid 函数设置的id对三者都起效.[规则一]
2.否则,仅当该id为real user ID 或者saved user ID时,该id对effective user ID起效.[规则二]
3.否则,setuid函数调用失败.

区分setuid和seteuid可以结合放弃root权限来做例子。
1.本来具有root权限，暂时性地降低权限到普通用户，然后返回root权限；以及
2.本来具有root权限，然后永久地放弃root权限，再也返回不了root权限。

x.c，x1.c和x2.c三个程序，响应的可执行文件都已经设置为是root:root，4755.
x.c中使用了seteuid，暂时地改变了effective uid，但是saved uid依然是root；因此之后可以通过setuid或者seteuid将它变回root；
x1.c使用了setuid，因为此时的effective uid是0，所以同时改变了ruid，euid和suid三者。

关于saved uid。

最后关于chmod 7755，或者1755。
如果一个文件夹是公共文件夹，那么它的权限可能是777，也即所有人可读写执行。所有人都可以在这个目录下创建自己的文件，但同时，任何人也可以删除其他人创建的文件，虽然不能写。
为了防止在公共文件夹下删除他人的文件，可以对文件夹设置sticky位。Chmod 1777来设置。
此时，用户只能删除自己的文件。
（如果某个公共文件夹是某个普通用户，则该普通用户可以删除其它用户的文件，即使设置了粘滞位）
