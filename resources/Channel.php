<?php


namespace resources;


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
        if (Registry::getCall($this->linkedid) === null)
        {
            new Call($this);
        }
        Registry::addChannel($this, $this->linkedid, $this->uniqueid);
    }

}