<?php


namespace resources;


use resources\states\StateBridgeDestroy;
use function utils\add_callback_request;
use function utils\get_callback_request;
use function utils\log;
use utils\TSingleton;
use function utils\redis_expire;

class Registry
{
    use TSingleton;

    private static $calls = [];

    public static function getCall($linkedid) : ?Call
    {
        if (isset(self::$calls[$linkedid]))
        {
            return self::$calls[$linkedid]['call'];
        }
        return null;
    }

    public static function addCallbackRequestCall(Call $call)
    {
        if (!get_callback_request($call->linkedid))
        {
            $serializedData = serialize($call);
            add_callback_request($call->linkedid, $serializedData);
            redis_expire($call->linkedid);
            self::removeCall($call->linkedid);
            log(INFO, "[$call->linkedid] Запрос отзвона ожидает отзвон или разрушения через " . CALL_ALIVE . " секунд");
        }
    }

    public static function addCall(Call $object)
    {
        if (!isset(self::$calls[$object->linkedid]['call'])) {
            self::$calls[$object->linkedid]['call'] = $object;
            log(OK, "[$object->linkedid]" . ' Создан новый звонок - ' . $object->linkedid);
            return true;
        }
        log(WARNING, "[$object->linkedid]" . ' Звонок уже существует в регистре');
        return false;
    }

    public static function removeCall($linkedid)
    {
        if (isset(self::$calls[$linkedid])) {
            log(INFO, "[" . self::$calls[$linkedid]['call']->linkedid . "] Звонок удален из реестра");
            unset(self::$calls[$linkedid]);
            return true;
        }
        log(WARNING, "Невозможно удалить несуществующий звонок с идентификатором - $linkedid");
        return false;
    }

    public static function getChannel($linkedid, $uniqueid) : ?Channel
    {
        if (isset(self::$calls[$linkedid]['channels'][$uniqueid]))
        {
            return self::$calls[$linkedid]['channels'][$uniqueid];
        }

        return null;
    }

    public static function addChannel(Channel $object, $linkedid, $uniqueid)
    {
        if (!isset(self::$calls[$linkedid]['channels'][$uniqueid])) {
            self::$calls[$linkedid]['channels'][$uniqueid] = $object;
            log(INFO, "[$linkedid]" . ' Создан новый канал - ' . $object->name . " | Uniqueid: $uniqueid");
            return true;
        }
        log(WARNING, "[$linkedid]" . 'Канал уже существует в регистре - ' . $object->name . " | Uniqueid: $uniqueid");
        return false;
    }

    public static function removeChannel($linkedid, $uniqueid)
    {
        if (isset(self::$calls[$linkedid]['channels'][$uniqueid]))
        {
            unset(self::$calls[$linkedid]['channels'][$uniqueid]);
            if (empty(self::$calls[$linkedid]['channels']))
            {
                self::$calls[$linkedid]['call']->stateNum = CALL_STATE['completed'];
                self::$calls[$linkedid]['call']->endtime = time();
                self::$calls[$linkedid]['call']->callDuration = self::$calls[$linkedid]['call']->endtime - self::$calls[$linkedid]['call']->createtime;
                self::$calls[$linkedid]['call']->proceedToNext();
            }
            return true;
        }
        log(WARNING, "[$linkedid]" . "Невозможно удалить несуществующий канал с идентификатором - $uniqueid");
        return false;
    }

    public static function getBridge($bridgeuniqueid)
    {
        if (isset(self::$calls['bridges'][$bridgeuniqueid]))
        {
            return self::$calls['bridges'][$bridgeuniqueid];
        }

        return null;
    }

    public static function getIdBridgeByUniqueId($uniqueid)
    {
        foreach (self::$calls['bridges'] as $key)
        {
            if (in_array($uniqueid, $key['channels'], true))
            {
                return $key['bridgeUniqueId'];
            }
        }

        return false;
    }

    public static function addBridge($linkedid, $bridgeuniqueid)
    {
        self::$calls['bridges'][$bridgeuniqueid] = [];
        self::$calls['bridges'][$bridgeuniqueid]['channels'] = [];
        self::$calls['bridges'][$bridgeuniqueid]['calls'] = [];
        self::$calls['bridges'][$bridgeuniqueid]['bridgeUniqueId'] = $bridgeuniqueid;
        log(INFO, "[$linkedid] Создан новый бридж. Идентификатор бриджа - $bridgeuniqueid");
        return true;
    }

    public static function destroyBridge($linkedid, $bridgeuniqueid, $endTime)
    {
        if (isset(self::$calls['bridges'][$bridgeuniqueid])) {
            if (!empty(self::$calls['bridges'][$bridgeuniqueid]['channels'])) {
                log(INFO, "[$linkedid] Бридж $bridgeuniqueid при звонке не пустой, в нем есть каналы. Нельзя разрушить");
                return false;
            }

            self::$calls['bridges'][$bridgeuniqueid]['endTime'] = $endTime;
            if (self::$calls['bridges'][$bridgeuniqueid]['createTime'] !== 0) {
                log(INFO, "[$linkedid] Конец разговора");
                self::$calls['bridges'][$bridgeuniqueid]['duration'] = self::$calls['bridges'][$bridgeuniqueid]['endTime'] - self::$calls['bridges'][$bridgeuniqueid]['createTime'];
            }
            self::getCall($linkedid)->setState(new StateBridgeDestroy(self::getCall($linkedid), self::$calls['bridges'][$bridgeuniqueid]));
            log(INFO, "[$linkedid] Бридж $bridgeuniqueid разрушен. Его длительность была - " . self::$calls['bridges'][$bridgeuniqueid]['duration']);
            unset(self::$calls['bridges'][$bridgeuniqueid]);
            return true;
        }
        log(WARNING, "[$linkedid] Бриджа $bridgeuniqueid - не существует. Невозможно разрушить");
        return false;
    }

    public static function bridgeEnter($linkedid, $bridgeuniqueid, $uniqueid, $startTime)
    {
        if (!isset(self::$calls['bridges'][$bridgeuniqueid]))
        {
            self::addBridge($linkedid, $bridgeuniqueid);
            self::$calls['bridges'][$bridgeuniqueid]['createTime'] = 0;
            self::$calls['bridges'][$bridgeuniqueid]['endTime'] = 0;
            self::$calls['bridges'][$bridgeuniqueid]['duration'] = 0;
            self::$calls['bridges'][$bridgeuniqueid]['type'] = 10;
        }
        if (in_array($uniqueid, self::$calls['bridges'][$bridgeuniqueid]['channels'], true))
        {
            log(WARNING, "[$linkedid] Канал $uniqueid уже находится в бридже $bridgeuniqueid");
            return false;
        }

        if (!empty(self::$calls['bridges'][$bridgeuniqueid]['channels']) && self::$calls['bridges'][$bridgeuniqueid]['createTime'] === 0)
        {
            log(INFO, "[$linkedid] Начало разговора");
            self::$calls['bridges'][$bridgeuniqueid]['createTime'] = $startTime;
        }
        self::$calls['bridges'][$bridgeuniqueid]['channels'][] = $uniqueid;
        $channel =  Registry::getChannel($linkedid, $uniqueid);
        if (self::$calls['bridges'][$bridgeuniqueid]['type'] > $channel->type)
        self::$calls['bridges'][$bridgeuniqueid]['type'] = $channel->type;
        log(INFO, "[$linkedid] Канал $uniqueid вошел в бридж $bridgeuniqueid");
        if (!in_array($linkedid, self::$calls['bridges'][$bridgeuniqueid]['calls'], true)){
            self::$calls['bridges'][$bridgeuniqueid]['calls'][] = $linkedid;
        }
        return true;
    }

    public static function whoHangUpInBridge($linkedid, $uniqueid)
    {
        if (isset(self::$calls['bridges']))
        {
            if (!empty(self::$calls['bridges']))
            {
                $bridgeId = self::getIdBridgeByUniqueId($uniqueid);

                if ($bridgeId)
                {
                    self::$calls['bridges'][$bridgeId]['whoHangUp'] = $uniqueid;
                    log(OK, "[$linkedid] Канал $uniqueid запросил завершение разговора");
                }
            }
        }
    }

    public static function bridgeLeave($linkedid, $bridgeuniqueid, $uniqueid, $endTime)
    {
        if (in_array($uniqueid, self::$calls['bridges'][$bridgeuniqueid]['channels'])) {
            unset(self::$calls['bridges'][$bridgeuniqueid]['channels'][array_search($uniqueid, self::$calls['bridges'][$bridgeuniqueid]['channels'], true)]);
            log(INFO, "[$linkedid] Канал $uniqueid покинул бридж $bridgeuniqueid");
            if (empty(self::$calls['bridges'][$bridgeuniqueid]['channels'])) {
                log(INFO, "[$linkedid] Каналов в бридже $bridgeuniqueid не осталось. Бридж разрушается...");
                self::destroyBridge($linkedid, $bridgeuniqueid, $endTime);
            }
            return true;
        }
        log(WARNING, "[$linkedid] Канал $uniqueid не может выйти из бриджа $bridgeuniqueid, так как его там несуществует");
        return false;
    }
}