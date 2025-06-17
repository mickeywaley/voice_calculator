import tkinter as tk
from tkinter import ttk, messagebox
import math
import platform
import threading
import logging
import sys
import os
from enum import Enum
import pyttsx3
from queue import Queue
import time

class AppState(Enum):
    RUNNING = 1
    CLOSING = 2
    ERROR = 3

class VoiceEngine:
    """语音引擎，负责文本转语音播报"""
    def __init__(self):
        self.logger = logging.getLogger("VoiceEngine")
        self.engine = None
        self.available = False
        self._initialize()
        self.queue = Queue()
        self._stop_event = threading.Event()
        self._thread = None
        self._last_text = None
        self._last_time = 0
        if self.available:
            self._start_speech_thread()
    
    def _initialize(self):
        """初始化语音引擎"""
        try:
            self.engine = pyttsx3.init()
            self._setup_voice()
            self.engine.setProperty('rate', 150)  # 语速
            self.engine.setProperty('volume', 1.0)  # 音量
            self.available = True
            self.logger.info("语音引擎初始化成功")
        except Exception as e:
            self.logger.error(f"语音引擎初始化失败: {e}")
            self.available = False
    
    def _setup_voice(self):
        """设置中文语音支持"""
        if not self.engine:
            return
            
        try:
            voices = self.engine.getProperty('voices')
            # 尝试查找中文语音
            for voice in voices:
                if 'chinese' in voice.id.lower() or 'china' in voice.id.lower():
                    self.engine.setProperty('voice', voice.id)
                    self.logger.info(f"已设置中文语音: {voice.name}")
                    return
            self.logger.warning("未找到中文语音，使用默认语音")
        except Exception as e:
            self.logger.error(f"设置语音失败: {e}")
    
    def _start_speech_thread(self):
        """启动语音播报线程"""
        self._thread = threading.Thread(target=self._speech_loop, daemon=True)
        self._thread.start()
    
    def _speech_loop(self):
        """语音播报循环"""
        while not self._stop_event.is_set():
            try:
                text = self.queue.get(timeout=0.1)
                if text is None:  # 退出信号
                    break
                    
                # 去重逻辑：如果和上次文本相同且时间间隔小于0.5秒，则跳过
                current_time = time.time()
                if text == self._last_text and current_time - self._last_time < 0.5:
                    self.logger.debug(f"去重：跳过重复文本 '{text}'")
                    self.queue.task_done()
                    continue
                    
                self._last_text = text
                self._last_time = current_time
                
                self._speak_internal(text)
                self.queue.task_done()
            except Exception as e:
                pass  # 忽略超时异常
    
    def _speak_internal(self, text):
        """内部语音播报方法，不处理线程安全"""
        try:
            # 检查并停止现有的运行循环
            if hasattr(self.engine, '_inLoop') and self.engine._inLoop:
                self.engine.endLoop()
                
            self.engine.say(text)
            self.engine.runAndWait()
        except Exception as e:
            self.logger.error(f"语音播报失败: {e}")
            raise
    
    def speak(self, text):
        """安全地语音播报文本"""
        if not self.available or not text:
            return
            
        self.queue.put(text)
    
    def shutdown(self):
        """关闭语音引擎"""
        self._stop_event.set()
        self.queue.put(None)  # 发送退出信号
        if self._thread:
            self._thread.join(timeout=1.0)
        if self.engine:
            try:
                self.engine.stop()
                self.engine = None
            except Exception as e:
                self.logger.error(f"关闭语音引擎失败: {e}")

class VoiceCalculator:
    def __init__(self, root):
        self.root = root
        self.root.title("语音计算器")
        self.root.geometry("300x450")
        self.root.minsize(300, 450)
        
        # 设置字体以支持中文显示
        self.system = platform.system()
        if self.system == "Windows":
            self.font_family = "SimHei"
        elif self.system == "Darwin":  # macOS
            self.font_family = "Heiti TC"
        else:  # Linux 等其他系统
            self.font_family = "WenQuanYi Micro Hei"
        
        # 配置日志记录
        self.setup_logging()
        self.logger = logging.getLogger("VoiceCalculator")
        self.logger.info("应用启动")
        
        # 应用状态
        self.state = AppState.RUNNING
        
        # 语音引擎（立即初始化）
        self.voice_engine = VoiceEngine()
        self.voice_active = False
        self.voice_available = self.voice_engine.available
        
        # 计算器状态
        self.current_expression = ""
        self.result = ""
        
        # 创建界面
        self.create_widgets()
        
        # 绑定键盘事件
        self.root.bind('<Key>', self.on_key_press)
        
        # 确保窗口关闭时正确清理资源
        self.root.protocol("WM_DELETE_WINDOW", self.on_close)
    
    def setup_logging(self):
        """配置日志记录"""
        try:
            # 创建日志目录（如果不存在）
            log_dir = "logs"
            if not os.path.exists(log_dir):
                os.makedirs(log_dir)
                
            logging.basicConfig(
                level=logging.INFO,
                format='%(asctime)s - %(name)s - %(levelname)s - %(message)s',
                filename=os.path.join(log_dir, 'voice_calculator.log')
            )
            
            # 添加控制台输出
            console_handler = logging.StreamHandler()
            console_handler.setLevel(logging.INFO)
            formatter = logging.Formatter('%(name)s - %(levelname)s - %(message)s')
            console_handler.setFormatter(formatter)
            logging.getLogger().addHandler(console_handler)
            
        except Exception as e:
            # 如果日志配置失败，使用默认配置
            self.logger.error(f"日志配置失败: {e}")
            logging.basicConfig(level=logging.INFO)
    
    def create_widgets(self):
        """创建计算器界面组件"""
        # 主框架
        self.main_frame = tk.Frame(self.root, bg="#f0f0f0")
        self.main_frame.pack(fill=tk.BOTH, expand=True, padx=10, pady=10)
        
        # 表达式显示框
        self.expression_frame = tk.Frame(self.main_frame, bg="#ffffff", bd=2, relief=tk.SUNKEN)
        self.expression_frame.pack(fill=tk.X, padx=5, pady=5)
        
        self.expression_var = tk.StringVar()
        self.expression_var.set("")
        self.expression_label = tk.Label(
            self.expression_frame, 
            textvariable=self.expression_var, 
            font=(self.font_family, 16),
            bg="#ffffff",
            anchor=tk.E
        )
        self.expression_label.pack(fill=tk.X, padx=5, pady=5)
        
        # 结果显示框
        self.result_frame = tk.Frame(self.main_frame, bg="#ffffff", bd=2, relief=tk.SUNKEN)
        self.result_frame.pack(fill=tk.X, padx=5, pady=5)
        
        self.result_var = tk.StringVar()
        self.result_var.set("0")
        self.result_label = tk.Label(
            self.result_frame, 
            textvariable=self.result_var, 
            font=(self.font_family, 24, "bold"),
            bg="#ffffff",
            anchor=tk.E
        )
        self.result_label.pack(fill=tk.X, padx=5, pady=5)
        
        # 按钮框架
        self.buttons_frame = tk.Frame(self.main_frame, bg="#f0f0f0")
        self.buttons_frame.pack(fill=tk.BOTH, expand=True, padx=5, pady=5)
        
        # 按钮网格 - 确保所有按钮都有语音文本
        buttons = [
            ("7", 1, 0, "七"), ("8", 1, 1, "八"), ("9", 1, 2, "九"), ("/", 1, 3, "除以"), ("C", 1, 4, "清除"),
            ("4", 2, 0, "四"), ("5", 2, 1, "五"), ("6", 2, 2, "六"), ("*", 2, 3, "乘以"), ("CE", 2, 4, "删除"),
            ("1", 3, 0, "一"), ("2", 3, 1, "二"), ("3", 3, 2, "三"), ("-", 3, 3, "减"), ("√", 3, 4, "平方根"),
            ("0", 4, 0, "零"), (".", 4, 1, "点"), ("=", 4, 2, "等于"), ("+", 4, 3, "加"), ("^", 4, 4, "次方")
        ]
        
        for button_text, row, col, voice_text in buttons:
            button = tk.Button(
                self.buttons_frame,
                text=button_text,
                font=(self.font_family, 14),
                command=lambda t=button_text, v=voice_text: self.button_click(t, v),
                bg="#e0e0e0",
                relief=tk.RAISED,
                padx=10,
                pady=10
            )
            button.grid(row=row, column=col, padx=2, pady=2, sticky="nsew")
            self.buttons_frame.grid_columnconfigure(col, weight=1)
            self.buttons_frame.grid_rowconfigure(row, weight=1)
        
        # 语音控制按钮
        self.voice_frame = tk.Frame(self.main_frame, bg="#f0f0f0")
        self.voice_frame.pack(fill=tk.X, padx=5, pady=5)
        
        if self.voice_available:
            self.voice_button = tk.Button(
                self.voice_frame,
                text="语音播报: 关闭",
                font=(self.font_family, 12),
                command=self.toggle_voice,
                bg="#ffcccc",
                fg="#ff0000",
                relief=tk.RAISED,
                padx=10,
                pady=5
            )
        else:
            self.voice_button = tk.Button(
                self.voice_frame,
                text="语音功能不可用",
                font=(self.font_family, 12),
                command=self.show_voice_error,
                bg="#dddddd",
                fg="#888888",
                relief=tk.RAISED,
                padx=10,
                pady=5,
                state=tk.DISABLED
            )
            
        self.voice_button.pack(side=tk.LEFT, padx=5)
        
        # 状态标签
        self.status_var = tk.StringVar()
        self.status_var.set("就绪")
        self.status_label = tk.Label(
            self.main_frame,
            textvariable=self.status_var,
            font=(self.font_family, 10),
            bg="#f0f0f0",
            fg="#666666"
        )
        self.status_label.pack(side=tk.BOTTOM, pady=5)
    
    def show_voice_error(self):
        """显示语音功能错误信息"""
        messagebox.showerror("错误", "语音功能不可用")
    
    def toggle_voice(self):
        """切换语音播报状态"""
        if not self.voice_available:
            self.show_voice_error()
            return
            
        self.voice_active = not self.voice_active
        
        if self.voice_active:
            self.voice_button.config(
                text="语音播报: 开启",
                bg="#ccffcc",
                fg="#008000"
            )
            self.status_var.set("语音播报: 开启")
            self.speak("语音播报已开启")
        else:
            self.voice_button.config(
                text="语音播报: 关闭",
                bg="#ffcccc",
                fg="#ff0000"
            )
            self.status_var.set("语音播报: 关闭")
    
    def speak(self, text):
        """安全地语音播报文本"""
        if not self.voice_available or not self.voice_active or self.state != AppState.RUNNING:
            return
            
        try:
            self.voice_engine.speak(text)
        except Exception as e:
            self.logger.error(f"语音播报失败: {e}")
    
    def on_key_press(self, event):
        """处理键盘按键事件"""
        if self.state != AppState.RUNNING:
            return
            
        key = event.char
        
        # 映射键盘按键到语音文本
        key_map = {
            '0': '零', '1': '一', '2': '二', '3': '三', '4': '四',
            '5': '五', '6': '六', '7': '七', '8': '八', '9': '九',
            '+': '加', '-': '减', '*': '乘以', '/': '除以', '.': '点',
            '=': '等于', '\r': '等于', '^': '次方', '\x08': '删除',
            '\x1b': '清除'  # ESC键对应清除
        }
        
        voice_text = key_map.get(key, key)
        
        if key.isdigit() or key in "+-*/.^":
            # 直接调用append_expression，避免重复播报
            self.append_expression(key, voice_text)
        elif key == "=" or key == "\r":  # Enter 键
            self.calculate()
        elif key == "\x08":  # Backspace
            self.clear_entry()
            self.speak("删除")
        elif key.lower() == "c" or key == "\x1b":  # C键或ESC键
            self.clear_all()
    
    def append_expression(self, value, voice_text):
        """向表达式添加内容"""
        if self.state != AppState.RUNNING:
            return
            
        # 防止无效输入
        if not self._is_valid_expression(self.current_expression + value):
            self.speak("无效输入")
            return
            
        self.current_expression += value
        self.expression_var.set(self.current_expression)
        
        # 直接调用语音播报
        self.speak(voice_text)
    
    def _is_valid_expression(self, expr):
        """检查表达式是否有效"""
        if not expr:
            return True
            
        # 简单检查：最后一个字符是否是运算符（允许最后一个字符是运算符）
        if expr[-1] in "+-*/^":
            # 但不能连续多个运算符
            if len(expr) >= 2 and expr[-2] in "+-*/^":
                return False
        
        # 更复杂的检查可以在这里添加
        return True
    
    def clear_all(self):
        """清除所有内容"""
        if self.state != AppState.RUNNING:
            return
            
        self.current_expression = ""
        self.result = ""
        self.expression_var.set("")
        self.result_var.set("0")
        self.status_var.set("就绪")
        self.speak("清除所有内容")
    
    def clear_entry(self):
        """清除当前输入"""
        if self.state != AppState.RUNNING or not self.current_expression:
            return
            
        self.current_expression = self.current_expression[:-1]
        self.expression_var.set(self.current_expression)
        self.speak("删除")
    
    def calculate(self):
        """计算表达式结果"""
        if self.state != AppState.RUNNING or not self.current_expression:
            return
            
        try:
            # 检查表达式是否以运算符结尾
            if self.current_expression and self.current_expression[-1] in "+-*/^":
                self.current_expression = self.current_expression[:-1]
                self.expression_var.set(self.current_expression)
            
            if not self.current_expression:
                return
                
            self.status_var.set("计算中...")
            self.result = eval(self.current_expression)
            self.result_var.set(str(self.result))
            self.status_var.set("计算完成")
            self.speak(f"计算结果是{self.result}")
            self.current_expression = str(self.result)
        except Exception as e:
            self.result_var.set("错误")
            self.status_var.set("计算错误")
            self.logger.error(f"计算错误: {e}, 表达式: {self.current_expression}")
            self.speak("计算错误")
    
    def calculate_sqrt(self):
        """计算平方根"""
        if self.state != AppState.RUNNING or not self.current_expression:
            return
            
        try:
            # 检查表达式是否有效
            if not self._is_valid_number(self.current_expression):
                self.result_var.set("错误")
                self.status_var.set("计算错误")
                self.speak("无效数字")
                return
                
            self.status_var.set("计算中...")
            value = float(self.current_expression)
            if value < 0:
                self.result_var.set("错误")
                self.status_var.set("计算错误")
                self.speak("负数不能开平方")
                return
                
            self.result = math.sqrt(value)
            self.result_var.set(str(self.result))
            self.status_var.set("计算完成")
            self.speak(f"{self.current_expression}的平方根是{self.result}")
            self.current_expression = str(self.result)
            
        except Exception as e:
            self.result_var.set("错误")
            self.status_var.set("计算错误")
            self.logger.error(f"平方根计算错误: {e}, 表达式: {self.current_expression}")
            self.speak("计算错误")
    
    def _is_valid_number(self, s):
        """检查字符串是否是有效的数字"""
        try:
            float(s)
            return True
        except ValueError:
            return False
    
    def button_click(self, button_text, voice_text):
        """统一处理按钮点击事件"""
        # 直接调用append_expression，避免重复播报
        if button_text.isdigit() or button_text in "+-*/.^":
            self.append_expression(button_text, voice_text)
        elif button_text == "=":
            self.calculate()
        elif button_text == "C":
            self.clear_all()
        elif button_text == "CE":
            self.clear_entry()
        elif button_text == "√":
            self.calculate_sqrt()
        elif button_text == "^":
            self.append_expression("**", voice_text)
    
    def on_close(self):
        """窗口关闭时的清理工作"""
        if self.state == AppState.CLOSING:
            return
            
        self.state = AppState.CLOSING
        self.status_var.set("正在关闭...")
        self.logger.info("应用关闭中")
        
        # 禁用语音功能
        self.voice_active = False
        
        # 释放语音引擎资源
        try:
            if self.voice_engine:
                self.voice_engine.shutdown()
                self.voice_engine = None
            self.logger.info("语音引擎资源已释放")
        except Exception as e:
            self.logger.error(f"释放语音引擎失败: {e}")
        
        self.status_var.set("应用已关闭")
        self.logger.info("应用已关闭")
        
        # 关闭主窗口
        self.root.destroy()

def handle_exception(exc_type, exc_value, exc_traceback):
    """全局异常处理"""
    if issubclass(exc_type, KeyboardInterrupt):
        sys.__excepthook__(exc_type, exc_value, exc_traceback)
        return
        
    logger = logging.getLogger("GlobalExceptionHandler")
    logger.critical("未处理的异常", exc_info=(exc_type, exc_value, exc_traceback))
    
    # 尝试显示错误消息
    try:
        if 'root' in globals() and root.winfo_exists():
            messagebox.showerror("致命错误", f"程序遇到未处理的异常: {exc_value}\n请查看日志获取详细信息")
    except Exception as e:
        logger.error(f"显示错误消息失败: {e}")
    
    sys.exit(1)

if __name__ == "__main__":
    # 设置全局异常处理器
    sys.excepthook = handle_exception
    
    # 确保中文显示正常
    try:
        import matplotlib
        matplotlib.rcParams["font.family"] = ["SimHei", "WenQuanYi Micro Hei", "Heiti TC"]
    except:
        pass
    
    root = tk.Tk()
    app = VoiceCalculator(root)
    root.mainloop()
