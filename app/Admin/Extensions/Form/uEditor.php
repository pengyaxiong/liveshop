<?php
/**
 * Created by PhpStorm.
 * User: PengYaxiong
 * Date: 2020/3/5
 * Time: 17:57
 */

namespace App\Admin\Extensions\Form;

use Encore\Admin\Form\Field;

/**
 * 百度编辑器
 * Class uEditor
 * @package App\Admin\Extensions\Form
 */
class uEditor extends Field
{
    // 定义视图
    protected $view = 'admin.uEditor';

    // css资源
    protected static $css = [];

    // js资源
    protected static $js = [
        'laravel-u-editor/ueditor.config.js',
        'laravel-u-editor/ueditor.all.min.js',
        'laravel-u-editor/lang/zh-cn/zh-cn.js'
    ];

    /*
     *  var ue = UE.getEditor('ueditor', {
            // 自定义工具栏
            toolbars: [
                ['bold', 'italic', 'underline', 'strikethrough', 'blockquote', 'insertunorderedlist', 'insertorderedlist', 'justifyleft', 'justifycenter', 'justifyright', 'link', 'insertimage', 'source', 'fullscreen']
            ],
            elementPathEnabled: false,
            enableContextMenu: false,
            autoClearEmptyNode: true,
            wordCount: false,
            imagePopup: false,
            autotypeset: {indent: true, imageBlockLine: 'center'}
        });
     */
    public function render()
    {
        $this->script = <<<EOT
        //解决第二次进入加载不出来的问题 和 多个只加载一个问题
        UE.delEditor("{$this->column}");
        // 默认id是ueditor
        var ue = UE.getEditor('{$this->column}'); 
        ue.ready(function () {
            ue.execCommand('serverparam', '_token', '{{ csrf_token() }}');
        });

EOT;
        return parent::render();
    }
}
