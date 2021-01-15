<?php


namespace resources;


use utils\Logger;
use function utils\getCallOrWarning;

class Channel
{

    public $name;
    public $channame;
    public $pbxNum;
    public $callerid;
    public $exten;
    public $uniqueid;
    public $linkedid;
    public $createtime;
    public $type;

    public function __construct($name, $channame, $callerid, $exten, $uniqueid, $linkedid, $createtime, $type, $pbxNum = false)
    {
        $this->name = $name;
        $this->channame = $channame;
        $this->callerid = $callerid;
        $this->exten = $exten;
        $this->uniqueid = $uniqueid;
        $this->linkedid = $linkedid;
        $this->createtime = $createtime;
        $this->type = $type;
        if ($pbxNum)
        {
            $this->pbxNum = $pbxNum;
        }

        if (Registry::getCall($this->linkedid) === null)
        {
            new Call($this);
        }
        $call = getCallOrWarning($this->linkedid);

        if ($call)
        {
            if ($call->call_type === CALL_TYPE['inbound'] || $call->call_type === CALL_TYPE['callback_request'])
            {
                if (!$this->pbxNum && preg_match('/\d{7,15}/s', $this->callerid) && preg_match('/\d{3,15}/s', $this->exten))
                {
                    $this->pbxNum = $this->exten;
                }
            }
            //LOGGING
            $vars = get_object_vars($this);
            foreach ($vars as $key => $value)
            {
                Logger::log(DEBUG, "[$this->linkedid] $key: $value");
            }
            Logger::log(DEBUG, "");

            Registry::addChannel($this, $this->linkedid, $this->uniqueid);
            Logger::log(INFO, "[$this->linkedid] Канал: $this->name | Имя канала: $this->channame | Номер канала: $this->callerid"
                . " | Номер назначения: $this->exten | PBX NUM: $this->pbxNum | Ид канала: $this->uniqueid | Тип канала: " . array_search($type, CHANNEL_TYPE, true));
        }
    }

    public function setCallerId($callerid)
    {
        if ($this->callerid === null)
        {
            $this->callerid = $callerid;
            Logger::log(INFO, "[$this->linkedid] У канала $this->uniqueid установлен callerid: $this->callerid");
        }
    }
}