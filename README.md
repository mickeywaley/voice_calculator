# voice_calculator

语音计算器PC

<img src="https://raw.githubusercontent.com/mickeywaley/voice_calculator/refs/heads/main/1.1.1-1.png"  />

目前已知问题，语音播报有点慢

----------------------

这个语音计算器是一个基于 Python 的图形界面应用程序，它不仅能够完成基本的数学计算功能，还能通过语音播报的方式反馈操作结果，非常适合视力障碍人士或需要双手忙碌时使用。

----------------------

主要功能

基本计算功能：支持加、减、乘、除四则运算，以及乘方和平方根计算。

语音播报：可开启或关闭语音功能，操作按钮和计算结果都会通过语音反馈。

表达式输入：顶部显示当前输入的表达式，底部显示计算结果。

清除功能：提供 "CE" 清除当前输入和 "C" 清除所有内容两个清除按钮。

错误处理：对无效输入和计算错误会有语音提示和界面反馈。

键盘支持：可以使用键盘输入数字和符号，支持 Enter 键计算，Backspace 删除。

--------------------

# 运行环境

操作系统：支持 Windows、macOS 和 Linux

Python 版本：Python 3.6 及以上

必要依赖库：

tkinter（Python 标准库，无需额外安装）

pyttsx3（用于语音合成，需通过 pip 安装）

math（Python 标准库，用于数学计算）

platform, threading, logging 等（Python 标准库）

# 安装和使用方法

安装依赖：

bash

pip install pyttsx3

运行程序：

将代码保存为voice_calculator.py，然后在终端中运行：

bash

python voice_calculator.py

------------------------

# 使用方法：

点击数字和运算符按钮进行计算

点击 "=" 按钮或按 Enter 键计算结果

点击 "语音播报：关闭" 按钮可切换语音功能

使用 "CE" 按钮清除当前输入，使用 "C" 按钮清除所有内容

# 代码结构

VoiceEngine 类：负责语音合成和播报，处理语音队列和去重

VoiceCalculator 类：计算器的主类，处理界面和计算逻辑

AppState 枚举：定义应用的运行状态

全局异常处理：捕获和处理程序中的异常

# 注意事项

语音功能依赖系统的语音引擎，首次运行可能需要系统安装语音包

如果语音没有中文支持，会使用默认语音，可以通过系统设置添加中文语音

程序会在同级目录创建 logs 文件夹，记录运行日志，方便排查问题

对于复杂表达式，可以使用键盘输入，支持 ^ 表示乘方

这个计算器设计简洁，功能实用，通过语音反馈提供了更加友好的交互体验。


----------------------

# 在线版语音播放器

<img src="https://raw.githubusercontent.com/mickeywaley/voice_calculator/refs/heads/main/%E5%9C%A8%E7%BA%BF%E7%89%88%E8%AF%AD%E9%9F%B3%E8%AE%A1%E7%AE%97%E5%99%A8.png"  />

----------------------
