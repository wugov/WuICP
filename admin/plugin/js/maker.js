// Ensure Blockly is loaded after the DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    const toolbox = {
        "kind": "flyoutToolbox",
        "contents": [
            {
                "kind": "block",
                "type": "controls_if"
            },
            {
                "kind": "block",
                "type": "controls_repeat_ext"
            },
            {
                "kind": "block",
                "type": "logic_compare"
            },
            {
                "kind": "block",
                "type": "math_number"
            },
            {
                "kind": "block",
                "type": "math_arithmetic"
            },
            {
                "kind": "block",
                "type": "text"
            },
            {
                "kind": "block",
                "type": "text_print"
            },
            // Add more blocks as needed
        ]
    };
    const workspace = Blockly.inject('blocklyDiv', {toolbox: toolbox});

    function updateCode(event) {
        const code = Blockly.PHP.workspaceToCode(workspace);
        document.getElementById('codeDisplay').value = code;
    }

    workspace.addChangeListener(updateCode);
});
// 获取按钮和菜单容器的引用
var menuButton = document.getElementById('menuButton');
var menuContainer = document.getElementById('menuContainer');

// 为按钮添加点击事件监听器
menuButton.addEventListener('click', function() {
    if (menuContainer.style.display === 'none') {
        menuContainer.style.display = 'block';
    } else {
        menuContainer.style.display = 'none';
    }
});