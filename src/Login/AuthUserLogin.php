<?php
/**
 * Created by PhpStorm
 * @author HuangSen
 * Time: 2019/9/18 18:46
 */

namespace hsen\auth\Login;


use hsen\auth\Exceptions\AuthenticationException;
use app\Request;
use think\response\View;

/**
 * session 登陆方法
 * Trait AuthUserLogin
 * @package hsen\auth\login
 * @author HuangSen
 * Time: 2019/10/26 15:37
 */
trait AuthUserLogin
{


    /**
     * 显示登陆页面
     * @return View
     * @author HuangSen
     * Time 2019/9/18 18:50
     */
    public function showLogin()
    {
//        dump(Auth::user());
//        $a = Auth::builder(['uid'=>1]);
//        dump($a);
//        dump(Auth::loginUsingId(1));
//        $payload = [
//            'iss' => 'gosPlus', //签发者
//            'iat' => time(), //什么时候签发的
//            'uid' => 1,
//        ];
//        dump($token = Auth::builder($payload));
//        Auth::setToken($token);
//        dump(Auth::getPayload());
        return view('login');
    }

    /**
     * 登陆页面
     * @param Request $request
     * @return mixed
     * @author HuangSen
     * Time 2019/10/26 15:34
     */
    public function login(Request $request)
    {
        //合并参数到请求中
        $request->merge([$this->username() => $request->param('login')]);
        $this->validateLogin($request);
        $userInfo = $this->getUserInfo($request);
        //用户登陆前的信息是否正确
        event('LoginBerfore',$userInfo);
        //然后做登陆的后续呢操作
        halt('登陆完成');
    }

    /**
     * 根据用户提交字段查询用户相关信息
     * @param Request $request
     * @return mixed
     * @author HuangSen
     * Time 2019/10/27 22:52
     */
    public function getUserInfo(Request $request)
    {
        $credentials = $this->credentials($request);
        //判断password字段是否存在 如果不存在新增一个字段充数
        if (!array_key_exists('password', $credentials)) {
            $credentials['password'] = '';
        }
        return $this->guard()->getProvider()->getUserByCredentials($credentials);
    }

    /**
     * 使用验证器验证登录名称
     * @param Request $request
     * @author HuangSen
     * Time 2019/9/18 19:13
     */
    public function validateLogin(Request $request)
    {
        $this->validate($request->param(), LoginValidate::class);
    }

    /**
     * 获取登陆的字段
     * @return string
     * @author HuangSen
     * Time 2019/9/18 18:58
     */
    public function username(): string
    {
        return 'id';
    }

    /**
     * 获取已经登陆的认证守卫
     * @return mixed
     * @author HuangSen
     * Time 2019/10/24 18:14
     */
    protected function guard()
    {
        return Auth::guard();
    }

    /**
     * 尝试登陆
     * @param Request $request
     * @return mixed
     * @author HuangSen
     * Time 2019/10/25 15:45
     */
    protected function attemptLogin(Request $request)
    {
        $credentials = $this->credentials($request);
        //判断password字段是否存在
        if (!array_key_exists('password', $credentials)) {
            $credentials['password'] = '';
        }
        return $this->guard()->attempt($credentials, $request->isEmptyString('remember'));
    }

    /**
     * 获取登陆需要的信息
     * @param Request $request
     * @return array
     * @author HuangSen
     * Time 2019/10/25 15:48
     */
    protected function credentials(Request $request)
    {
        return $request->only([$this->username(), 'password']);
    }

    /**
     * 退出登录
     * @author HuangSen
     * Time 2019/10/26 9:56
     */
    public function logout()
    {
        $this->guard()->logout();
        throw new AuthenticationException('退出成功');
    }
}