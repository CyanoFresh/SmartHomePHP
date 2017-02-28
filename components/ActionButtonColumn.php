<?php

namespace app\components;

use rmrevin\yii\fontawesome\FA;
use Yii;
use yii\grid\ActionColumn;
use yii\helpers\Html;

/**
 * ActionButtonGroupColumn is a ActionColumn for the [[GridView]] widget that displays buttons for viewing and manipulating the items
 * grouped into div.btn-group
 *
 * To add an ActionButtonGroupColumn to the gridview, add it to the [[GridView::columns|columns]] configuration as follows:
 *
 * ```php
 * 'columns' => [
 *     // ...
 *     [
 *         'class' => ActionButtonGroupColumn::className(),
 *         // you may configure additional properties here
 *     ],
 * ]
 * ```
 *
 * @author Alex Solomaha <cyanofresh@gmail.com>
 */
class ActionButtonColumn extends ActionColumn
{
    public $contentOptions = [
        'class' => 'action-column',
    ];

    protected function initDefaultButtons()
    {
        if (!isset($this->buttons['view'])) {
            $this->buttons['view'] = function ($url, $model, $key) {
                return Html::a(FA::i('eye'), $url, array_merge([
                    'title' => Yii::t('yii', 'View'),
                    'class' => 'btn btn-flat btn-xs btn-default',
                ], $this->buttonOptions));
            };
        }
        if (!isset($this->buttons['update'])) {
            $this->buttons['update'] = function ($url, $model, $key) {
                return Html::a(FA::i('pencil'), $url, array_merge([
                    'title' => Yii::t('yii', 'Update'),
                    'class' => 'btn btn-flat btn-xs btn-default',
                ], $this->buttonOptions));
            };
        }
        if (!isset($this->buttons['delete'])) {
            $this->buttons['delete'] = function ($url, $model, $key) {
                return Html::a(FA::i('trash'), $url, array_merge([
                    'title' => Yii::t('yii', 'Delete'),
                    'class' => 'btn btn-flat btn-xs btn-default',
                    'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                    'data-method' => 'post',
                ], $this->buttonOptions));
            };
        }
    }
}
