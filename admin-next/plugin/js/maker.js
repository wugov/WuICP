// Ensure Blockly is loaded after the DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    const add_action = {
        init: function() {
            this.appendValueInput('hook_name')
                .setCheck('String')
                .appendField('钩子名称');
            this.appendStatementInput('callback')
                .appendField('钩子要执行的函数');
            this.setTooltip('111');
            this.setHelpUrl('https://www.biliwind.com');
            this.setColour(330);
        }
    };
    Blockly.common.defineBlocks({add_action: add_action});
    php.phpGenerator.forBlock['add_action'] = function(block, generator) {
        // TODO: change Order.ATOMIC to the correct operator precedence strength
        const value_hook_name = generator.valueToCode(block, 'hook_name', php.Order.ATOMIC);

        const statement_callback = generator.statementToCode(block, 'callback');

        // TODO: Assemble php into the code variable.
        const code = `add_action(${value_hook_name}, ${statement_callback});\n`;
        return code;
    }
    const toolbox = {
        kind: 'categoryToolbox',
        contents: [
            {
                kind: 'category',
                name: '逻辑',
                categorystyle: 'logic_category',
                contents: [
                    {
                        kind: 'block',
                        type: 'controls_if',
                    },
                    {
                        kind: 'block',
                        type: 'logic_compare',
                    },
                    {
                        kind: 'block',
                        type: 'logic_operation',
                    },
                    {
                        kind: 'block',
                        type: 'logic_negate',
                    },
                    {
                        kind: 'block',
                        type: 'logic_boolean',
                    },
                ],
            },
            {
                kind: 'category',
                name: '循环',
                categorystyle: 'loop_category',
                contents: [
                    {
                        kind: 'block',
                        type: 'controls_repeat_ext',
                        inputs: {
                            TIMES: {
                                shadow: {
                                    type: 'math_number',
                                    fields: {
                                        NUM: 10,
                                    },
                                },
                            },
                        },
                    },
                    {
                        kind: 'block',
                        type: 'controls_flow_statements',
                    },
                ],
            },
            {
                kind: 'category',
                name: '数学',
                categorystyle: 'math_category',
                contents: [
                    {
                        kind: 'block',
                        type: 'math_number',
                        fields: {
                            NUM: 123,
                        },
                    },
                    {
                        kind: 'block',
                        type: 'math_arithmetic',
                        inputs: {
                            A: {
                                shadow: {
                                    type: 'math_number',
                                    fields: {
                                        NUM: 1,
                                    },
                                },
                            },
                            B: {
                                shadow: {
                                    type: 'math_number',
                                    fields: {
                                        NUM: 1,
                                    },
                                },
                            },
                        },
                    },
                    {
                        kind: 'block',
                        type: 'math_single',
                        inputs: {
                            NUM: {
                                shadow: {
                                    type: 'math_number',
                                    fields: {
                                        NUM: 9,
                                    },
                                },
                            },
                        },
                    },
                    {
                        kind: 'block',
                        type: 'math_number_property',
                        inputs: {
                            NUMBER_TO_CHECK: {
                                shadow: {
                                    type: 'math_number',
                                    fields: {
                                        NUM: 0,
                                    },
                                },
                            },
                        },
                    },
                ],
            },
            {
                kind: 'category',
                name: '文本',
                categorystyle: 'text_category',
                contents: [
                    {
                        kind: 'block',
                        type: 'text',
                    },
                    {
                        'kind': 'label',
                        'text': '输入和输出：',
                        'web-class': 'ioLabel',
                    },
                    {
                        kind: 'block',
                        type: 'text_print',
                        inputs: {
                            TEXT: {
                                shadow: {
                                    type: 'text',
                                    fields: {
                                        TEXT: 'abc',
                                    },
                                },
                            },
                        },
                    },
                    {
                        kind: 'block',
                        type: 'text_prompt_ext',
                        inputs: {
                            TEXT: {
                                shadow: {
                                    type: 'text',
                                    fields: {
                                        TEXT: 'abc',
                                    },
                                },
                            },
                        },
                    },
                ],
            },
            {
                kind: 'category',
                name: '变量',
                categorystyle: 'variable_category',
                custom: 'VARIABLE',
            },
            {
                kind: 'category',
                name: '函数',
                categorystyle: 'procedure_category',
                custom: 'PROCEDURE',
            },
            {
                kind: 'category',
                name: 'TuanPlug',
                categorystyle: 'procedure_category',
                contents: [
                    {
                        kind: 'block',
                        type: 'add_action',
                    },
                ],
            },
        ],
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