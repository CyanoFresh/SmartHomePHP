<?php

namespace app\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $email
 * @property string $auth_key
 * @property string $auth_token
 * @property string $api_key
 * @property string $name
 * @property string $avatar
 * @property integer $room_id
 * @property integer $status
 * @property integer $group
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property boolean $isAdmin
 *
 * @property string $password write-only password
 *
 * @property Room $room
 */
class User extends ActiveRecord implements IdentityInterface
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';

    const STATUS_DISABLED = 0;
    const STATUS_ACTIVE = 10;

    const GROUP_ADMIN = 10;
    const GROUP_USER = 20;

    public $password;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'email'], 'required'],
            [['api_key', 'auth_token', 'name', 'avatar'], 'string'],
            [['group', 'room_id'], 'integer'],
            [['password'], 'required', 'on' => self::SCENARIO_CREATE],
            [['password'], 'safe', 'on' => self::SCENARIO_UPDATE],
            [['auth_token'], 'default', 'value' => null],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['status'], 'in', 'range' => self::getStatusesArray()],
            [['group'], 'in', 'range' => self::getGroupsArray()],
            [['room_id'], 'exist', 'skipOnError' => true, 'targetClass' => Room::className(), 'targetAttribute' => ['room_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Логин',
            'password' => 'Пароль',
            'email' => 'Email',
            'status' => 'Статус',
            'group' => 'Группа',
            'name' => 'Имя',
            'avatar' => 'Аватар',
            'room_id' => 'Комната',
            'api_key' => 'API ключ',
            'auth_token' => 'Auth токен',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата изменения',
        ];
    }

    /**
     * @return array
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_ACTIVE => 'Активен',
            self::STATUS_DISABLED => 'Не активен',
        ];
    }

    /**
     * @return string
     */
    public function getStatusLabel()
    {
        return self::getStatuses()[$this->status];
    }

    /**
     * @return array
     */
    public static function getStatusesArray()
    {
        return array_keys(self::getStatuses());
    }

    /**
     * @return array
     */
    public static function getGroups()
    {
        return [
            self::GROUP_USER => 'Пользователь',
            self::GROUP_ADMIN => 'Администратор',
        ];
    }

    /**
     * @return array
     */
    public static function getGroupsArray()
    {
        return array_keys(self::getGroups());
    }

    /**
     * @return string
     */
    public function getGroupLabel()
    {
        return self::getGroups()[$this->group];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['api_key' => $token]);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * @return string
     */
    public function getAuthToken()
    {
        if (is_null($this->auth_token)) {
            $this->reGenerateAuthToken();
        }

        return $this->auth_token;
    }

    /**
     * Generates authentication token
     */
    public function generateAuthToken()
    {
        $this->auth_token = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates authentication token
     */
    public function reGenerateAuthToken()
    {
        $this->generateAuthToken();

        return $this->save(false);
    }

    /**
     * @param int $size
     * @return string
     */
    public function getAvatar($size = 45)
    {
        $hash = md5($this->email);
        return 'https://www.gravatar.com/avatar/' . $hash . '?s=' . $size;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHistories()
    {
        return $this->hasMany(History::className(), ['user_id' => 'id'])->inverseOf('user');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRoom()
    {
        return $this->hasOne(Room::className(), ['id' => 'room_id']);
    }

    /**
     * @return bool
     */
    public function getIsAdmin()
    {
        return $this->group === self::GROUP_ADMIN;
    }
}
