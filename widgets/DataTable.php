<?php

namespace app\widgets;

use yii\grid\GridView;

class DataTable extends GridView
{
    public $tableOptions = ['class' => 'table table-hover material-table'];
    public $summary = '<b>{begin}-{end}</b> из <b>{totalCount}</b>';
    public $pager = [
        'maxButtonCount' => 0,
        'nextPageLabel' => '&#xf054;',
        'prevPageLabel' => '&#xf053;',
    ];
    public $layout = "<div class=\"table-responsive\">{items}<div class='table-footer'><div class='table-footer-summary'>{summary}</div>{pager}</div></div>";
}
