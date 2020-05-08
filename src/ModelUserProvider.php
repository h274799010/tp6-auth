<?php
/**
 * Created by PhpStorm
 * @author HuangSen
 * Time: 2019/8/16 11:34
 */

namespace hsen\auth;


use hsen\auth\Contracts\AuthIdCard;
use hsen\auth\Contracts\UserProvider;
use think\contract\Arrayable;

class ModelUserProvider implements UserProvider
{
    /**
     * 用户的模型信息
     * @var
     */
    protected $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * 根据配置的信息创建用户的model
     * @return mixed
     * @author HuangSen
     * Time 2019/8/16 11:40
     */
    public function createModel()
    {
        $class = '\\' . ltrim($this->model, '\\');
        return new $class;
    }

    /**
     * 根据用户标识查询用户信息
     * @param Contracts\标识 $identifier
     * @return mixed
     * @author HuangSen
     * Time 2019/8/16 13:02
     */
    public function getUserById($identifier)
    {
        $model = $this->createModel();
        return $model->where($model->getAuthFieldName(), $identifier)->field(true)->findOrEmpty();
    }

    /**
     * 通过给定的条件来获取相应的用户信息
     * @param array $credentials
     * @return mixed|void
     * @author HuangSen
     * Time 2019/8/16 13:31
     */
    public function getUserByCredentials(array $credentials)
    {
        $model = $this->createModel();
        //判断当前给定的条件是否找到相应的用户信息
        foreach ($credentials as $key => $value) {
            if (is_array($value) || $value instanceof Arrayable) {
                $model = $model->whereIn($key, $value);
            } else {
                $model = $model->where($key, $value);
            }
        }
        return $model->findOrEmpty();
    }

    public function validateCredentials(AuthIdCard $user, array $credentials)
    {
        if (array_key_exists('password', $credentials)) {
            $plain = $credentials['password'];
            return $this->check($plain, $user->getAuthPassword());
        }
        return false;
    }

    /**
     * 通过他们的唯一标识寻找用户和记住我的领牌
     * @param Contracts 标识 $identifier
     * @param Contracts 令牌 $token
     * @return mixed|null
     * @author HuangSen
     * Time 2019/10/9 9:31
     */
    public function getUserByToken($identifier, $token)
    {
        $model = $this->createModel();

        $model = $model->where($model->getAuthFieldName(), $identifier)->findOrEmpty();

        if (!$model) {
            return null;
        }
        //获取领牌值
        $rememberToken = $model->getRememberToken();

        return $rememberToken && hash_equals($rememberToken, $token) ? $model : null;
    }

    /**
     * 根据哈希检查给定的普通值
     * @param $value
     * @param $hashedValue
     * @param array $options
     * @return bool
     * @author HuangSen
     * Time 2019/8/16 13:54
     */
    public function check($value, $hashedValue, array $options = []): bool
    {
        if ($hashedValue === '') {
            return false;
        }

        return password_verify($value, $hashedValue);
    }

    /**
     * 在存储的介质中更新记住我的领牌
     * @param AuthIdCard $user
     * @param $token
     * @return mixed
     * @author HuangSen
     * Time 2019/8/16 10:59
     */
    public function updateRememberToken(AuthIdCard $user, $token)
    {
        $user->setRememberToken($token);
        $user->save();
    }
}