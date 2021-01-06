<?php


namespace resources;


use utils\Logger;
use function utils\getCallOrWarning;

class Channel
{

    public $name;
    public $channame;
    public $callerid;
    public $exten;
    public $uniqueid;
    public $linkedid;
    public $createtime;

    public function __construct($name, $channame, $callerid, $exten, $uniqueid, $linkedid, $createtime)
    {
        $this->name = $name;
        $this->channame = $channame;
        $this->callerid = $callerid;
        $this->exten = $exten;
        $this->uniqueid = $uniqueid;
        $this->linkedid = $linkedid;
        $this->createtime = $createtime;
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

        Registry::addChannel($this, $this->linkedid, $this->uniqueid);
        Logger::log(INFO, "Канал - $this->name | Имя канала - $this->channame | Номер канала - $this->callerid | Номер назначения - $this->exten | Ид канала - $this->uniqueid | Ид звонка - $this->linkedid");

    }

}