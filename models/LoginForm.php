<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'username' => 'Логин',
            'password' => 'Пароль',
            'rememberMe' => 'Запомнить',
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $session = Yii::$app->session;

            // Set timeout for first time if counter exceed
            if ($session->get('failedLoginCount') >= Yii::$app->params['maxLoginFailCount'] and !$session->has('loginAgainAt')) {
                $session->set('loginAgainAt', time() + Yii::$app->params['loginFailTimeout']);
                $session->set('failedLoginCount', 0);
            }

            if ($session->has('loginAgainAt') and $session->get('loginAgainAt') >= time()) {
                return $this->addError($attribute, 'Слишком много неудачных попыток. Попробуйте позже');
            }

            if ($session->has('loginAgainAt') and $session->get('loginAgainAt') <= time()) {
                $session->remove('loginAgainAt');
            }

            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Неверный логин или пароль');
                $session->set('failedLoginCount', $session->get('failedLoginCount', 0) + 1);
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            // Reset login failure counter
            Yii::$app->session->set('failedLoginCount', 0);

            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        }

        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }
}
