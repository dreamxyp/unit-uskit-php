<?php
/**
 * Created by PhpStorm.
 * User: dreamxyp
 * Date: 2019/3/14
 * Time: 09:37
 */

namespace Baidu\Iov\Kit\Request;


class ParasService
{
    const LOG_ID_PREFIX = 'uskit_';

    /** @var string =2.0，当前api版本对应协议版本号为2.0，固定值 */
    protected $version = '2.0';

    /** @var string 机器人ID，service_id 与skill_ids不能同时缺失，至少一个有值。 */
    protected $service_id;

    /** @var array  list<string> 技能ID列表。我们允许开发者指定调起哪些技能。
     * 这个列表是有序的——排在越前面的技能，优先级越高。技能优先级体现在response的排序上。
     * 具体排序规则参见【应答参数说明】
     * service_id和skill_ids可以组合使用，详见【请求参数详细说明】  */
    protected $skill_ids = [];

    /** @var string 开发者需要在客户端生成的唯一id，用来定位请求，响应中会返回该字段。对话中每轮请求都需要一个log_id */
    protected $log_id;

    /** @var array [
     *     'service_id' => 机器人ID，标明该session由哪个机器人产生。
     *     'session_id' => session本身的ID，客户端可以使用session_id代替session，节约传输流量。
     *     'skill_sessions' =>  [ '技能ID' => '技能的session' ] 这里存储与当前对话相关的所有技能的session。key为技能ID，value为技能的session（同【UNIT对话API文档】中的bot_session)。
     *     'interactions'   => [
     *          [ 'interaction_id' => 第 i 次交互的唯一标识。
     *            'timestamp'      => interaction生成的时间（以interaction_id的生成时间为准）。格式：YYYY-MM-DD HH:MM:SS.fff （24小时制，精确到毫秒）
     *            'request'        => 第 i 次交互的 request，结构参考【请求参数说明】中的request
     *            'response_list'  => 第 i 次交互的 response列表，结构参考【应答参数说明】中的response_list
     *          ]
     *     ]
     * ]
     */
    protected $session = [];

    /** @var array [
     *     'skill_states' => [ '技能ID' => '技能的对话状态数据' ]
     *     'contexts'     => 希望在多技能对话过程中贯穿的全局性上下文. 这里预留了一个key用于控制各技能的session记忆。详见【请求参数详细说明】
     * ]
     */
    protected $dialog_state = [];

    /** @var array [
     *      'user_id'   => 与技能对话的用户id（如果客户端是用户未登录状态情况下对话的，也需要尽量通过其他标识（比如设备id）来唯一区分用户），方便今后在平台的日志分析模块定位分析问题、从用户维度统计分析相关对话情况。详情见【请求参数详细说明】
     *      'query'     => 本轮请求query（用户说的话），详情见【请求参数详细说明】
     *      'query_info'=> [     本轮请求query的附加信息
     *          'type'   => 请求信息类型，取值范围："TEXT","EVENT"。详情见【请求参数详细说明】
     *          'source' => 请求信息来源，可选值："ASR","KEYBOARD"。ASR为语音输入，KEYBOARD为键盘文本输入。针对ASR输入，UNIT平台内置了纠错机制，会尝试解决语音输入中的一些常见错误
     *          'asr_candidates' => [  请求信息来源若为ASR，该字段为ASR候选信息。（如果调用百度语音的API会有该信息，UNIT会参考该候选信息做综合判断处理。）
     *              [ 'text' => 语音输入候选文本
     *                'confidence' => 语音输入候选置信度
     *              ]
     *          ]
     *      ]
     *      'client_session' => client希望传给UNIT的本地信息，以一组K-V形式保存，具体内容见【请求参数详细说明】
     *      'hyper_params'   => [  key为技能id或机器人id（现在只实现技能id），value为控制相关技能/机器人内部行为的的超参数
     *          'bernard_level' =>  技能自动发现不置信意图/词槽，并据此主动发起澄清确认的频率。取值范围：0(关闭)、1(低频)、2(高频)。取值越高代表技能对不置信意图/词槽的敏感度就越高，默认值=1
     *          'slu_level'     =>  slu运行级别，值域1，2，3 默认值=1
     *          'slu_threshold' =>  slu阈值，值域0.0~1.0，值越高表示召回的阈值越高，避免误召回，默认值=0.5。
     *          'slu_tags'      =>  用于限定slu的解析范围，只在打上了指定tag的意图、词槽、问答对的范围内执行slu。目前这个功能暂时仅针对意图解析生效。
     *      ]
     *
     * ]
     */
    protected $request = [];

    public function __construct($user_id,$service_id,$skill_ids=[],$version='2.0')
    {
        $this->session            = [];
        $this->request            = [];
        $this->dialog_state       = [];
        $this->service_id         = $service_id;

        $this->request['user_id'] = $user_id;

        $version   || $this->version = $version;
        $skill_ids || $this->skill_ids;
    }

    public function get($query)
    {
        $log_id = self::LOG_ID_PREFIX . rand(100000,9999999);
        $paras  = [
            'version'    => $this->version,
            'service_id' => $this->service_id,
            'log_id'     => $log_id,
            'session'    => $this->session,
            'request'    => $this->request,
        ];
        if($this->dialog_state){
            $paras['dialog_state'] = $this->dialog_state;
        }
        return $paras;
    }


    public function set()
    {

    }

    public function user_id_get()
    {
        $this->request['user_id'];
    }
}

