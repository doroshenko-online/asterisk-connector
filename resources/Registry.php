<?php


namespace resources;


use resources\states\StateBridgeDestroy;
use utils\Logger;
use utils\TSingleton;

class Registry
{
    use TSingleton;

    public static $calls = [];

    public static function getCall($linkedid) : ?Call
    {
        if (isset(self::$calls[$linkedid]))
        {
            return self::$calls[$linkedid]['call'];
        }
        return null;
    }

    public static function addCall(Call $object)
    {
        if (!isset(self::$calls[$object->linkedid]['call'])) {
            self::$calls[$object->linkedid]['call'] = $object;
            Logger::log(INFO,  "[$object->linkedid]" . ' Создан новый звонок - ' . $object->linkedid);
            return true;
        } else {
            Logger::log(WARNING, "[$object->linkedid]" . 'Звонок уже существует в регистре');
            return false;
        }
    }

    public static function removeCall($linkedid)
    {
        if (isset(self::$calls[$linkedid]))
        {
            unset(self::$calls[$linkedid]);
            return true;
        } else {
            Logger::log(WARNING, "Невозможно удалить несуществующий звонок с идентификатором - $linkedid");
            return false;
        }
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
        if (!isset(self::$calls[$linkedid]['channels'][$uniqueid]))
        {
            self::$calls[$linkedid]['channels'][$uniqueid] = $object;
            Logger::log(INFO, "[$linkedid]" . ' Создан новый канал - ' . $object->name . " | Uniqueid: $uniqueid");
            return true;
        } else {
            Logger::log(WARNING, "[$linkedid]" . 'Канал уже существует в регистре - ' . $object->name . " | Uniqueid: $uniqueid");
            return false;
        }
    }

    public static function removeChannel($linkedid, $uniqueid)
    {
        if (isset(self::$calls[$linkedid]['channels'][$uniqueid]))
        {
            unset(self::$calls[$linkedid]['channels'][$uniqueid]);
            if (empty(self::$calls[$linkedid]['channels']))
            {
                self::$calls[$linkedid]['call']->stateNum = CALL_STATE['completed'];
                self::$calls[$linkedid]['call']->proceedToNext();
            }
            return true;
        } else {
            Logger::log(WARNING, "[$linkedid]" . "Невозможно удалить несуществующий канал с идентификатором - $uniqueid");
            return false;
        }
    }

    public static function getBridge($linkedid, $bridgeuniqueid)
    {
        if (isset(self::$calls[$linkedid]['bridges'][$bridgeuniqueid]))
        {
            return self::$calls[$linkedid]['bridges'][$bridgeuniqueid];
        }

        return null;
    }

    public static function getIdBridgeByUniqueId($linkedid, $uniqueid)
    {
        if (isset(self::$calls[$linkedid]['bridges']))
        {
            foreach (self::$calls[$linkedid]['bridges'] as $key)
            {
                if (in_array($uniqueid, $key['channels'], true))
                {
                    return $key['bridgeUniqueId'];
                }
            }
        }
        Logger::log(WARNING, "[$linkedid] Не найден бридж в звонке, в котором должен быть канал $uniqueid");
        return false;
    }

    public static function addBridge($linkedid, $bridgeuniqueid)
    {
        if (!isset(self::$calls[$linkedid]['bridges'][$bridgeuniqueid]))
        {
            self::$calls[$linkedid]['bridges'][$bridgeuniqueid] = [];
            self::$calls[$linkedid]['bridges'][$bridgeuniqueid]['channels'] = [];
            self::$calls[$linkedid]['bridges'][$bridgeuniqueid]['bridgeUniqueId'] = $bridgeuniqueid;
            Logger::log(INFO, "[$linkedid] Создан новый бридж. Идентификатор бриджа - $bridgeuniqueid");
            return true;
        } else {
            Logger::log(WARNING, "[$linkedid] Бридж с идентификатором $bridgeuniqueid уже существует в регистре");
            return false;
        }
    }

    public static function destroyBridge($linkedid, $bridgeuniqueid, $endTime)
    {
        if (isset(self::$calls[$linkedid]['bridges'][$bridgeuniqueid]))
        {
            if (!empty(self::$calls[$linkedid]['bridges'][$bridgeuniqueid]['channels']))
            {
                Logger::log(INFO, "[$linkedid] Бридж $bridgeuniqueid при звонке не пустой, в нем есть каналы. Нельзя разрушить");
                return false;
            }

            self::$calls[$linkedid]['bridges'][$bridgeuniqueid]['endTime'] = $endTime;
            self::$calls[$linkedid]['bridges'][$bridgeuniqueid]['duration'] = self::$calls[$linkedid]['bridges'][$bridgeuniqueid]['endTime'] - self::$calls[$linkedid]['bridges'][$bridgeuniqueid]['createTime'];
            self::getCall($linkedid)->setState(new StateBridgeDestroy(self::getCall($linkedid), self::$calls[$linkedid]['bridges'][$bridgeuniqueid]));
            Logger::log(INFO, "[$linkedid] Бридж $bridgeuniqueid разрушен. Его длительность была - " . self::$calls[$linkedid]['bridges'][$bridgeuniqueid]['duration']);
            unset(self::$calls[$linkedid]['bridges'][$bridgeuniqueid]);
            return true;
        } else {
            Logger::log(WARNING, "[$linkedid] Бриджа $bridgeuniqueid - не существует. Невозможно разрушить");
            return false;
        }
    }

    public static function bridgeEnter($linkedid, $bridgeuniqueid, $uniqueid, $startTime)
    {
        if (!isset(self::$calls[$linkedid]['bridges'][$bridgeuniqueid]))
        {
            self::addBridge($linkedid, $bridgeuniqueid);
            self::$calls[$linkedid]['bridges'][$bridgeuniqueid]['createTime'] = 0;
            self::$calls[$linkedid]['bridges'][$bridgeuniqueid]['endTime'] = 0;
            self::$calls[$linkedid]['bridges'][$bridgeuniqueid]['duration'] = 0;
        }
        if (in_array($uniqueid, self::$calls[$linkedid]['bridges'][$bridgeuniqueid]['channels']))
        {
            Logger::log(WARNING, "[$linkedid] Канал $uniqueid уже находится в бридже $bridgeuniqueid");
            return false;
        }

        if (!empty(self::$calls[$linkedid]['bridges'][$bridgeuniqueid]['channels']) && self::$calls[$linkedid]['bridges'][$bridgeuniqueid]['createTime'] === 0)
        {
            self::$calls[$linkedid]['bridges'][$bridgeuniqueid]['createTime'] = $startTime;
        }
        self::$calls[$linkedid]['bridges'][$bridgeuniqueid]['channels'][] = $uniqueid;
        Logger::log(INFO, "[$linkedid] Канал $uniqueid вошел в бридж $bridgeuniqueid");
        return true;
    }

    public static function whoHangUpInBridge($linkedid, $uniqueid)
    {
        if (isset(self::$calls[$linkedid]['bridges']))
        {
            $bridgeId = self::getIdBridgeByUniqueId($linkedid, $uniqueid);

            if ($bridgeId)
            {
                self::$calls[$linkedid]['bridges'][$bridgeId]['whoHangUp'] = $uniqueid;
                Logger::log(INFO, "[$linkedid] Канал $uniqueid запросил завершение разговора");
            }
        }
    }

    public static function bridgeLeave($linkedid, $bridgeuniqueid, $uniqueid, $endTime)
    {
        if (in_array($uniqueid, self::$calls[$linkedid]['bridges'][$bridgeuniqueid]['channels']))
        {
            unset(self::$calls[$linkedid]['bridges'][$bridgeuniqueid]['channels'][array_search($uniqueid, self::$calls[$linkedid]['bridges'][$bridgeuniqueid]['channels'], true)]);
            Logger::log(INFO, "[$linkedid] Канал $uniqueid покинул бридж $bridgeuniqueid");
            if (empty(self::$calls[$linkedid]['bridges'][$bridgeuniqueid]['channels']))
            {
                Logger::log(INFO, "[$linkedid] Каналов в бридже $bridgeuniqueid не осталось. Бридж разрушается...");
                self::destroyBridge($linkedid, $bridgeuniqueid, $endTime);
            }
            return true;
        } else {
            Logger::log(WARNING, "[$linkedid] Канал $uniqueid не может выйти из бриджа $bridgeuniqueid, так как его там несуществует");
            return false;
        }
    }
}