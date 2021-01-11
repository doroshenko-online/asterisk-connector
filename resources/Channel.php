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

    public function __construct($name, $channame, $callerid, $exten, $uniqueid, $linkedid, $createtime, $pbxNum = false)
    {
        $this->name = $name;
        $this->channame = $channame;
        $this->callerid = $callerid;
        $this->exten = $exten;
        $this->uniqueid = $uniqueid;
        $this->linkedid = $linkedid;
        $this->createtime = $createtime;
        if ($pbxNum)
        {
            $this->pbxNum = $pbxNum;
        }
        //LOGGING
        $vars = get_object_vars($this);
        foreach ($vars as $key => $value)
        {
            Logger::log(DEBUG, "$key: $value");
        }

        if (Registry::getCall($this->linkedid) === null)
        {
            new Call($this);
        }
        $call = getCallOrWarning($this->linkedid);
        if ($call)
        {
            if ($call->lastPbxNum)
            {
                $this->callerid = $call->lastPbxNum;
                $call->lastPbxNum = null;
            }
        }
        if ($call->call_type === CALL_TYPE['inbound'] || $call->call_type === CALL_TYPE['callback_request'])
        {
            if (!$this->pbxNum && preg_match('/\d{7,15}/s', $this->callerid) && preg_match('/\d{3,15}/s', $this->exten))
            {
                $this->pbxNum = $this->exten;
            }
        }

        Registry::addChannel($this, $this->linkedid, $this->uniqueid);
        Logger::log(INFO, "[$this->linkedid] Канал: $this->name | Имя канала: $this->channame | Номер канала: $this->callerid | Номер назначения: $this->exten | PBX NUM: $this->pbxNum | Ид канала: $this->uniqueid");

    }

}