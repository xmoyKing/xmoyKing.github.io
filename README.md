# xmoyKing.github.io
HEXO dir with all raw files.

1. git全局设置，如ssh，user.name/email等，若已经设置则跳过  
    `git config --global user.email "you@example.com"`  
    `git config --global user.name "Your Name"`
2. `git clone --depth=1 -b hexo git@github.com:xmoyKing/xmoyKing.github.io.git` 下载本分支至本地 `--dpeth=1`表示仅获取最近一次commit
3. 进行4之前，可以考虑更换npm源，用`npm config get registry`查看源，默认为 https://registry.npmjs.org/   
    设置为淘宝源`npm config set registry https://registry.npm.taobao.org`可以比较快的安装node包
4. 安装各种依赖`npm install`
5. 全局安装hexo`npm install -g hexo`后可以使用各种hexo的命令
