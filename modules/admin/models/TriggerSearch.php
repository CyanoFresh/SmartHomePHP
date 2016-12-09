<?php

namespace app\modules\admin\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Trigger;

/**
 * TriggerSearch represents the model behind the search form about `app\models\Trigger`.
 */
class TriggerSearch extends Trigger
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'type', 'trig_item_id'], 'integer'],
            [['trig_date', 'trig_time', 'trig_time_wdays', 'trig_item_value', 'name'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Trigger::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'type' => $this->type,
            'trig_item_id' => $this->trig_item_id,
        ]);

        $query->andFilterWhere(['like', 'trig_date', $this->trig_date])
            ->andFilterWhere(['like', 'trig_time', $this->trig_time])
            ->andFilterWhere(['like', 'trig_time_wdays', $this->trig_time_wdays])
            ->andFilterWhere(['like', 'trig_item_value', $this->trig_item_value])
            ->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}
