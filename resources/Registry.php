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
            Logger::log(INFO, 'Создан новый звонок - ' . $object->linkedid);
            return true;
        } else {
            Logger::log(WARNING, 'Звонок уже существует в регистре - ' . $object->linkedid);
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
            Logger::log(INFO, 'Создан новый канал - ' . $object->name . " | Uniqueid: $uniqueid | Linkedid: $linkedid");
            return true;
        } else {
            Logger::log(WARNING, 'Канал уже существует в регистре - ' . $object->name . " | Uniqueid: $uniqueid | Linkedid: $linkedid");
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
                self::$calls[$linkedid]['call']->status = CALL_STATUS['completed'];
                self::$calls[$linkedid]['call']->proceedToNext();
            }
            return true;
        } else {
            Logger::log(WARNING, "Невозможно удалить несуществующий канал с идентификатором - $uniqueid в звонке с идентификатором - $linkedid");
            return false;
        }
    }

    public static function getBridge($linkedid, $bridgeuniqueid)
    {
        return self::$calls[$linkedid]['bridges'][$bridgeuniqueid] ?: null;
    }

    public static function addBridge($linkedid, $bridgeuniqueid)
    {
        if (!isset(self::$calls[$linkedid]['bridges'][$bridgeuniqueid]))
        {
            self::$calls[$linkedid]['bridges'][$bridgeuniqueid] = [];
            Logger::log(INFO, "Создан новый бридж на звонке - $linkedid. Идентификатор бриджа - $bridgeuniqueid");
            return true;
        } else {
            Logger::log(WARNING, "Бридж с идентификатором $bridgeuniqueid уже существует в регистре");
            return false;
        }
    }

    public static function destroyBridge($linkedid, $bridgeuniqueid)
    {
        if (isset(self::$calls[$linkedid]['bridges'][$bridgeuniqueid]))
        {
            if (!empty(self::$calls[$linkedid]['bridges'][$bridgeuniqueid]['channelsInBridge']))
            {
                Logger::log(INFO, "Бридж $bridgeuniqueid при звонке $linkedid не пустой, в нем есть каналы. Нельзя разрушить");
                return false;
            } else {
                self::getCall($linkedid)->setState(new StateBridgeDestroy(self::getCall($linkedid)));
                unset(self::$calls[$linkedid]['bridges'][$bridgeuniqueid]);
                Logger::log(INFO, "Бридж $bridgeuniqueid от звонка $linkedid разрушен");
                return true;
            }
        } else {
            Logger::log(WARNING, "Бриджа $bridgeuniqueid от звонка $linkedid - не существует. Невозможно разрушить");
            return false;
        }
    }

    public static function bridgeEnter($linkedid, $bridgeuniqueid, $uniqueid)
    {
        if (!isset(self::$calls[$linkedid]['bridges'][$bridgeuniqueid]))
        {
            self::addBridge($linkedid, $bridgeuniqueid);
        }
        if (in_array($uniqueid, self::$calls[$linkedid]['bridges'][$bridgeuniqueid]['channels']))
        {
            Logger::log(WARNING, "Канал $uniqueid от звонка $linkedid уже находится в бридже $bridgeuniqueid");
            return false;
        } else {
            self::$calls[$linkedid]['bridges'][$bridgeuniqueid]['channels'][] = $uniqueid;
            Logger::log(INFO, "Канал $uniqueid от звонка $linkedid вошел в бридж $bridgeuniqueid");
            return true;
        }
    }

    public static function bridgeLeave($linkedid, $bridgeuniqueid, $uniqueid)
    {
        if (in_array($uniqueid, self::$calls[$linkedid]['bridges'][$bridgeuniqueid]['channels']))
        {
            unset(self::$calls[$linkedid]['bridges'][$bridgeuniqueid]['channels'][array_search($uniqueid, self::$calls[$linkedid]['bridges'][$bridgeuniqueid]['channels'], true)]);
            Logger::log(INFO, "Канал $uniqueid от звонка $linkedid покинул бридж $bridgeuniqueid");
            if (empty(self::$calls[$linkedid]['bridges'][$bridgeuniqueid]['channels']))
            {
                Logger::log(INFO, "Каналов в бридже $bridgeuniqueid от звонка $linkedid не осталось. Бридж разрушается...");
                self::destroyBridge($linkedid, $uniqueid);
            }
            return true;
        } else {
            Logger::log(WARNING, "Данный канал $uniqueid, от звонка $linkedid, не может выйти из бриджа $bridgeuniqueid, так как его там несуществует");
            return false;
        }
    }
}