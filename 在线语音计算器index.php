<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>智能语音计算器</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" rel="stylesheet">
    
    <!-- Tailwind配置 -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3B82F6',
                        secondary: '#10B981',
                        accent: '#F59E0B',
                        dark: '#1E293B',
                        light: '#F8FAFC',
                        error: '#EF4444'
                    },
                    fontFamily: {
                        inter: ['Inter', 'system-ui', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    
    <style type="text/tailwindcss">
        @layer utilities {
            .content-auto {
                content-visibility: auto;
            }
            .calc-btn {
                @apply flex items-center justify-center h-16 rounded-lg transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 text-lg font-medium;
            }
            .calc-btn-number {
                @apply bg-gray-800 text-white hover:bg-gray-700;
            }
            .calc-btn-operation {
                @apply bg-primary text-white hover:bg-primary/80;
            }
            .calc-btn-equal {
                @apply bg-secondary text-white hover:bg-secondary/80;
            }
            .calc-btn-clear {
                @apply bg-error text-white hover:bg-error/80;
            }
            .calc-btn-speech {
                @apply bg-accent text-white hover:bg-accent/80;
            }
            .display {
                @apply h-20 w-full bg-gray-900 text-white rounded-lg p-4 text-right text-3xl font-semibold overflow-x-auto;
            }
            .history-item {
                @apply py-2 px-4 border-b border-gray-700 hover:bg-gray-800/50 transition-colors;
            }
            .btn-press {
                @apply scale-95 opacity-80;
            }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-dark to-gray-900 min-h-screen font-inter text-light flex items-center justify-center p-4">
    <div class="max-w-md w-full mx-auto bg-gray-900/80 backdrop-blur-md rounded-2xl shadow-2xl overflow-hidden border border-gray-800">
        <!-- 计算器头部 -->
        <div class="p-4 bg-gray-800/50 border-b border-gray-700">
            <h1 class="text-xl font-bold text-center">智能语音计算器</h1>
            <p class="text-sm text-gray-400 text-center mt-1">支持语音输入和结果播报</p>
        </div>
        
        <!-- 显示区域 -->
        <div class="p-4">
            <div class="display" id="calculator-display">0</div>
            <div class="h-24 overflow-y-auto bg-gray-800/30 rounded-lg mt-2 p-2 text-sm text-gray-400" id="calculator-history">
                <p class="text-gray-500 italic">计算历史将显示在这里</p>
            </div>
        </div>
        
        <!-- 按钮区域 -->
        <div class="p-4 grid grid-cols-4 gap-3">
            <!-- 清除按钮 -->
            <button class="calc-btn calc-btn-clear col-span-2" id="btn-clear">
                <span class="flex items-center justify-center">
                    <i class="fa fa-eraser mr-2"></i>清除
                </span>
            </button>
            
            <!-- 删除按钮 -->
            <button class="calc-btn bg-gray-800 text-white hover:bg-gray-700" id="btn-delete">
                <span class="flex items-center justify-center">
                    <i class="fa fa-backspace"></i>
                </span>
            </button>
            
            <!-- 除法按钮 -->
            <button class="calc-btn calc-btn-operation" id="btn-divide">
                <span>÷</span>
            </button>
            
            <!-- 数字按钮 -->
            <button class="calc-btn calc-btn-number" id="btn-7">
                <span>7</span>
            </button>
            <button class="calc-btn calc-btn-number" id="btn-8">
                <span>8</span>
            </button>
            <button class="calc-btn calc-btn-number" id="btn-9">
                <span>9</span>
            </button>
            
            <!-- 乘法按钮 -->
            <button class="calc-btn calc-btn-operation" id="btn-multiply">
                <span>×</span>
            </button>
            
            <button class="calc-btn calc-btn-number" id="btn-4">
                <span>4</span>
            </button>
            <button class="calc-btn calc-btn-number" id="btn-5">
                <span>5</span>
            </button>
            <button class="calc-btn calc-btn-number" id="btn-6">
                <span>6</span>
            </button>
            
            <!-- 减法按钮 -->
            <button class="calc-btn calc-btn-operation" id="btn-subtract">
                <span>−</span>
            </button>
            
            <button class="calc-btn calc-btn-number" id="btn-1">
                <span>1</span>
            </button>
            <button class="calc-btn calc-btn-number" id="btn-2">
                <span>2</span>
            </button>
            <button class="calc-btn calc-btn-number" id="btn-3">
                <span>3</span>
            </button>
            
            <!-- 加法按钮 -->
            <button class="calc-btn calc-btn-operation" id="btn-add">
                <span>+</span>
            </button>
            
            <!-- 正负号按钮 -->
            <button class="calc-btn bg-gray-800 text-white hover:bg-gray-700" id="btn-sign">
                <span class="flex items-center justify-center">
                    <i class="fa fa-plus-minus"></i>
                </span>
            </button>
            
            <button class="calc-btn calc-btn-number" id="btn-0">
                <span>0</span>
            </button>
            
            <!-- 小数点按钮 -->
            <button class="calc-btn bg-gray-800 text-white hover:bg-gray-700" id="btn-decimal">
                <span>.</span>
            </button>
            
            <!-- 等号按钮 -->
            <button class="calc-btn calc-btn-equal col-span-2" id="btn-equal">
                <span class="flex items-center justify-center">
                    <i class="fa fa-equals mr-2"></i>等于
                </span>
            </button>
            
            <!-- 语音输入按钮 -->
            <button class="calc-btn calc-btn-speech col-span-4" id="btn-speech">
                <span class="flex items-center justify-center">
                    <i class="fa fa-microphone mr-2"></i>语音输入
                </span>
            </button>
        </div>
        
        <!-- 底部信息 -->
        <div class="p-3 bg-gray-800/50 text-center text-xs text-gray-400 border-t border-gray-700">
            <p>支持的语音指令: "加", "减", "乘", "除", "等于", "清除", "点"</p>
        </div>
    </div>
    
    <script>
        // 计算器状态
        const calculator = {
            displayValue: '0',
            firstOperand: null,
            waitingForSecondOperand: false,
            operator: null,
            history: []
        };
        
        // DOM元素
        const display = document.getElementById('calculator-display');
        const historyElement = document.getElementById('calculator-history');
        const speechButton = document.getElementById('btn-speech');
        
        // 更新显示
        function updateDisplay() {
            // 格式化显示数字，添加千位分隔符
            let displayValue = calculator.displayValue;
            
            // 如果是数字且不是错误
            if (!isNaN(parseFloat(displayValue)) && displayValue !== 'Error') {
                // 处理小数点
                const parts = displayValue.split('.');
                parts[0] = parseFloat(parts[0]).toLocaleString('en-US');
                displayValue = parts.length > 1 ? `${parts[0]}.${parts[1]}` : parts[0];
            }
            
            display.textContent = displayValue;
        }
        
        // 更新历史记录
        function updateHistory() {
            if (calculator.history.length === 0) {
                historyElement.innerHTML = '<p class="text-gray-500 italic">计算历史将显示在这里</p>';
                return;
            }
            
            historyElement.innerHTML = '';
            calculator.history.forEach(item => {
                const historyItem = document.createElement('div');
                historyItem.className = 'history-item';
                historyItem.innerHTML = `
                    <div class="text-gray-300">${item.expression}</div>
                    <div class="text-secondary">${item.result}</div>
                `;
                historyElement.appendChild(historyItem);
            });
            
            // 滚动到底部
            historyElement.scrollTop = historyElement.scrollHeight;
        }
        
        // 数字输入处理
        function inputDigit(digit) {
            const { displayValue, waitingForSecondOperand } = calculator;
            
            if (waitingForSecondOperand) {
                calculator.displayValue = digit;
                calculator.waitingForSecondOperand = false;
            } else {
                // 如果当前显示为0，则替换为新数字，否则追加
                calculator.displayValue = displayValue === '0' ? digit : displayValue + digit;
            }
            
            updateDisplay();
            speakButtonClick(digit);
        }
        
        // 小数点输入处理
        function inputDecimal() {
            if (calculator.waitingForSecondOperand) {
                calculator.displayValue = '0.';
                calculator.waitingForSecondOperand = false;
            } else if (!calculator.displayValue.includes('.')) {
                // 只有当当前值不包含小数点时才添加
                calculator.displayValue += '.';
            }
            
            updateDisplay();
            speakButtonClick('点');
        }
        
        // 处理运算符
        function handleOperator(nextOperator) {
            const { firstOperand, displayValue, operator } = calculator;
            const inputValue = parseFloat(displayValue);
            
            // 如果已经有一个运算符，并且正在等待第二个操作数，则更新运算符
            if (operator && calculator.waitingForSecondOperand) {
                calculator.operator = nextOperator;
                updateDisplay();
                speakButtonClick(getOperatorSymbol(nextOperator));
                return;
            }
            
            // 如果第一个操作数为空，且当前输入是一个有效数字
            if (firstOperand === null && !isNaN(inputValue)) {
                calculator.firstOperand = inputValue;
            } else if (operator) {
                // 如果已经有运算符和第一个操作数，则执行计算
                const result = calculate(firstOperand, inputValue, operator);
                calculator.displayValue = String(result);
                calculator.firstOperand = result;
                
                // 添加到历史记录
                addToHistory(`${formatNumber(firstOperand)} ${getOperatorSymbol(operator)} ${formatNumber(inputValue)}`, formatNumber(result));
            }
            
            calculator.waitingForSecondOperand = true;
            calculator.operator = nextOperator;
            updateDisplay();
            speakButtonClick(getOperatorSymbol(nextOperator));
        }
        
        // 格式化数字（添加千位分隔符）
        function formatNumber(number) {
            if (number === 'Error') return number;
            
            // 处理整数和小数
            const parts = number.toString().split('.');
            parts[0] = parseFloat(parts[0]).toLocaleString('en-US');
            return parts.length > 1 ? `${parts[0]}.${parts[1]}` : parts[0];
        }
        
        // 计算结果
        function calculate(firstOperand, secondOperand, operator) {
            if (operator === '+') {
                return firstOperand + secondOperand;
            } else if (operator === '-') {
                return firstOperand - secondOperand;
            } else if (operator === '*') {
                return firstOperand * secondOperand;
            } else if (operator === '/') {
                if (secondOperand === 0) {
                    return 'Error';
                }
                return firstOperand / secondOperand;
            }
            
            return secondOperand;
        }
        
        // 获取运算符符号
        function getOperatorSymbol(operator) {
            switch (operator) {
                case '+': return '加';
                case '-': return '减';
                case '*': return '乘';
                case '/': return '除';
                default: return '';
            }
        }
        
        // 重置计算器
        function resetCalculator() {
            calculator.displayValue = '0';
            calculator.firstOperand = null;
            calculator.waitingForSecondOperand = false;
            calculator.operator = null;
            
            updateDisplay();
            speakButtonClick('清除');
        }
        
        // 添加到历史记录
        function addToHistory(expression, result) {
            calculator.history.push({
                expression,
                result
            });
            
            // 限制历史记录数量
            if (calculator.history.length > 5) {
                calculator.history.shift();
            }
            
            updateHistory();
            
            // 语音播报结果
            speakResult(result);
        }
        
        // 语音播报结果
        function speakResult(result) {
            if (!('speechSynthesis' in window)) return;
            
            const speech = new SpeechSynthesisUtterance();
            speech.lang = 'zh-CN';
            
            // 处理特殊结果
            if (result === 'Error') {
                speech.text = '错误：除数不能为零';
            } else {
                // 格式化数字并转换为语音友好格式
                const formattedResult = formatNumber(result);
                const resultText = formattedResult
                    .replace(/,/g, '千')  // 简化中文播报，实际应用中可能需要更复杂的转换
                    .replace('.', '点');
                
                speech.text = `计算结果是${resultText}`;
            }
            
            speech.volume = 1;
            speech.rate = 1;
            speech.pitch = 1;
            
            window.speechSynthesis.speak(speech);
        }
        
        // 按钮点击语音反馈
        function speakButtonClick(buttonText) {
            if (!('speechSynthesis' in window)) return;
            
            // 创建语音实例
            const speech = new SpeechSynthesisUtterance();
            speech.lang = 'zh-CN';
            
            // 根据按钮文本设置语音内容
            switch (buttonText) {
                case '+':
                    speech.text = '加';
                    break;
                case '−':
                    speech.text = '减';
                    break;
                case '×':
                    speech.text = '乘';
                    break;
                case '÷':
                    speech.text = '除';
                    break;
                case '=':
                    speech.text = '等于';
                    break;
                case '.':
                    speech.text = '点';
                    break;
                default:
                    speech.text = buttonText;
            }
            
            // 设置语音参数
            speech.volume = 1;
            speech.rate = 1.2;
            speech.pitch = 1;
            
            // 播放语音
            window.speechSynthesis.speak(speech);
        }
        
        // 处理语音命令
        function handleSpeechCommand(command) {
            command = command.trim().toLowerCase();
            console.log('识别的语音命令:', command);
            
            // 清除命令
            if (command.includes('清除') || command.includes('重置')) {
                resetCalculator();
                return;
            }
            
            // 处理数字和运算符
            const numberMatch = command.match(/\d+(\.\d+)?/g);
            const operatorMatch = command.match(/[加|减|乘|除|plus|minus|multiply|divide]/);
            const equalMatch = command.match(/等于|等于|计算结果/);
            const decimalMatch = command.match(/点/);
            
            // 如果包含小数点但没有数字，忽略
            if (decimalMatch && !numberMatch) return;
            
            // 处理数字
            if (numberMatch) {
                numberMatch.forEach((number, index) => {
                    // 如果是第一个数字且当前显示为0，则直接替换
                    if (index === 0 && calculator.displayValue === '0') {
                        calculator.displayValue = number;
                    } else {
                        // 否则追加
                        calculator.displayValue += number;
                    }
                    
                    updateDisplay();
                    
                    // 如果后面有运算符，处理运算符
                    if (operatorMatch && index === numberMatch.length - 1) {
                        const operator = getOperatorFromText(operatorMatch[0]);
                        if (operator) {
                            handleOperator(operator);
                        }
                    }
                });
            }
            
            // 处理小数点
            if (decimalMatch && !calculator.displayValue.includes('.')) {
                calculator.displayValue += '.';
                updateDisplay();
            }
            
            // 处理等号
            if (equalMatch && calculator.firstOperand !== null && calculator.operator !== null) {
                const inputValue = parseFloat(calculator.displayValue);
                const result = calculate(calculator.firstOperand, inputValue, calculator.operator);
                calculator.displayValue = String(result);
                
                // 添加到历史记录
                addToHistory(`${formatNumber(calculator.firstOperand)} ${getOperatorSymbol(calculator.operator)} ${formatNumber(inputValue)}`, formatNumber(result));
                
                calculator.firstOperand = result;
                calculator.waitingForSecondOperand = true;
                
                updateDisplay();
            }
        }
        
        // 从文本获取运算符
        function getOperatorFromText(text) {
            switch (text) {
                case '加':
                case 'plus':
                    return '+';
                case '减':
                case 'minus':
                    return '-';
                case '乘':
                case 'multiply':
                    return '*';
                case '除':
                case 'divide':
                    return '/';
                default:
                    return null;
            }
        }
        
        // 设置语音识别
        function setupSpeechRecognition() {
            if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
                speechButton.disabled = true;
                speechButton.innerHTML = '<span class="flex items-center justify-center"><i class="fa fa-microphone-slash mr-2"></i>语音输入不可用</span>';
                speechButton.classList.remove('calc-btn-speech');
                speechButton.classList.add('bg-gray-700', 'text-gray-400');
                return;
            }
            
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            const recognition = new SpeechRecognition();
            
            recognition.lang = 'zh-CN';
            recognition.interimResults = false;
            recognition.maxAlternatives = 1;
            
            speechButton.addEventListener('click', () => {
                // 改变按钮状态
                speechButton.innerHTML = '<span class="flex items-center justify-center"><i class="fa fa-microphone-slash mr-2"></i>正在聆听...</span>';
                speechButton.classList.remove('calc-btn-speech');
                speechButton.classList.add('bg-red-500');
                
                recognition.start();
            });
            
            recognition.onresult = (event) => {
                const speechResult = event.results[0][0].transcript;
                handleSpeechCommand(speechResult);
                
                // 恢复按钮状态
                speechButton.innerHTML = '<span class="flex items-center justify-center"><i class="fa fa-microphone mr-2"></i>语音输入</span>';
                speechButton.classList.remove('bg-red-500');
                speechButton.classList.add('calc-btn-speech');
            };
            
            recognition.onerror = (event) => {
                console.error('语音识别错误:', event.error);
                
                // 恢复按钮状态
                speechButton.innerHTML = '<span class="flex items-center justify-center"><i class="fa fa-microphone mr-2"></i>语音输入</span>';
                speechButton.classList.remove('bg-red-500');
                speechButton.classList.add('calc-btn-speech');
            };
            
            recognition.onend = () => {
                // 恢复按钮状态
                speechButton.innerHTML = '<span class="flex items-center justify-center"><i class="fa fa-microphone mr-2"></i>语音输入</span>';
                speechButton.classList.remove('bg-red-500');
                speechButton.classList.add('calc-btn-speech');
            };
        }
        
        // 设置按钮事件监听
        function setupButtonListeners() {
            // 数字按钮
            document.getElementById('btn-0').addEventListener('click', () => inputDigit('0'));
            document.getElementById('btn-1').addEventListener('click', () => inputDigit('1'));
            document.getElementById('btn-2').addEventListener('click', () => inputDigit('2'));
            document.getElementById('btn-3').addEventListener('click', () => inputDigit('3'));
            document.getElementById('btn-4').addEventListener('click', () => inputDigit('4'));
            document.getElementById('btn-5').addEventListener('click', () => inputDigit('5'));
            document.getElementById('btn-6').addEventListener('click', () => inputDigit('6'));
            document.getElementById('btn-7').addEventListener('click', () => inputDigit('7'));
            document.getElementById('btn-8').addEventListener('click', () => inputDigit('8'));
            document.getElementById('btn-9').addEventListener('click', () => inputDigit('9'));
            
            // 小数点按钮
            document.getElementById('btn-decimal').addEventListener('click', inputDecimal);
            
            // 运算符按钮
            document.getElementById('btn-add').addEventListener('click', () => handleOperator('+'));
            document.getElementById('btn-subtract').addEventListener('click', () => handleOperator('-'));
            document.getElementById('btn-multiply').addEventListener('click', () => handleOperator('*'));
            document.getElementById('btn-divide').addEventListener('click', () => handleOperator('/'));
            
            // 等号按钮
            document.getElementById('btn-equal').addEventListener('click', () => {
                if (calculator.firstOperand !== null && calculator.operator !== null) {
                    const inputValue = parseFloat(calculator.displayValue);
                    const result = calculate(calculator.firstOperand, inputValue, calculator.operator);
                    calculator.displayValue = String(result);
                    
                    // 添加到历史记录
                    addToHistory(`${formatNumber(calculator.firstOperand)} ${getOperatorSymbol(calculator.operator)} ${formatNumber(inputValue)}`, formatNumber(result));
                    
                    calculator.firstOperand = result;
                    calculator.waitingForSecondOperand = true;
                    updateDisplay();
                }
            });
            
            // 清除按钮
            document.getElementById('btn-clear').addEventListener('click', resetCalculator);
            
            // 删除按钮
            document.getElementById('btn-delete').addEventListener('click', () => {
                if (calculator.displayValue.length > 1) {
                    calculator.displayValue = calculator.displayValue.slice(0, -1);
                } else {
                    calculator.displayValue = '0';
                }
                
                updateDisplay();
                speakButtonClick('删除');
            });
            
            // 正负号按钮
            document.getElementById('btn-sign').addEventListener('click', () => {
                const value = parseFloat(calculator.displayValue);
                calculator.displayValue = String(-value);
                updateDisplay();
                speakButtonClick('正负号');
            });
            
            // 添加按钮点击动画
            const allButtons = document.querySelectorAll('.calc-btn');
            allButtons.forEach(button => {
                button.addEventListener('mousedown', () => {
                    button.classList.add('btn-press');
                });
                
                button.addEventListener('mouseup', () => {
                    button.classList.remove('btn-press');
                });
                
                button.addEventListener('mouseleave', () => {
                    button.classList.remove('btn-press');
                });
            });
        }
        
        // 初始化计算器
        function initCalculator() {
            updateDisplay();
            setupButtonListeners();
            setupSpeechRecognition();
            
            // 添加键盘支持
            document.addEventListener('keydown', (event) => {
                const key = event.key;
                
                // 数字键
                if (/[0-9]/.test(key)) {
                    event.preventDefault();
                    inputDigit(key);
                }
                
                // 运算符
                switch (key) {
                    case '+':
                        event.preventDefault();
                        handleOperator('+');
                        break;
                    case '-':
                        event.preventDefault();
                        handleOperator('-');
                        break;
                    case '*':
                        event.preventDefault();
                        handleOperator('*');
                        break;
                    case '/':
                        event.preventDefault();
                        handleOperator('/');
                        break;
                    case '=':
                    case 'Enter':
                        event.preventDefault();
                        document.getElementById('btn-equal').click();
                        break;
                    case '.':
                    case ',':
                        event.preventDefault();
                        inputDecimal();
                        break;
                    case 'Escape':
                        event.preventDefault();
                        resetCalculator();
                        break;
                    case 'Backspace':
                        event.preventDefault();
                        document.getElementById('btn-delete').click();
                        break;
                }
            });
        }
        
        // 页面加载完成后初始化
        document.addEventListener('DOMContentLoaded', initCalculator);
    </script>
</body>
</html>    
