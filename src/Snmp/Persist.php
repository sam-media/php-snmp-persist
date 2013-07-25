<?php
namespace Snmp;

class Persist
{
    public function setInput($input=STDIN)
    {
        $this->input = $input;
    }

    public function __construct($base_oid='.1.3.6.1.4.1.2022.1')
    {
        $this->base_oid = $base_oid;
        $this->snmp_actions = array();
        $this->setInput();
        $unit = new Unit();
    }

    private function writeln($text)
    {
        printf("%s\n", $text);
    }

    public function registerOid($oid, $type = null, $func = null)
    {
        $this->snmp_actions[$this->base_oid . '.' . $oid] = array(
            'func' => $func,
            'type' => $type
        );
    }

    public function fork()
    {
        $pid = pcntl_fork();
        if ($pid == -1) {
            die("Could not fork the snmp");
        } else if ($pid) {
             pcntl_wait($status); 
        } else {
            $this->run();
        }
    }

    public function run()
    {
        while($cmd = fgets($this->input)){
            $cmd = strtoupper(trim($cmd));
            switch ($cmd){
                case 'PING':
                    $this->ping();
                    break;
                case 'GET':
                    $this->get();
                    break;
                case 'GETNEXT':
                    $this->getNext();
                default:
                    break;
            }
        }
    }

    private function getNext()
    {
        $next_oid = null;
        $oid = trim(fgets($this->input));
        if($oid == $this->base_oid){
            $next_oid = $this->base_oid . '.1';
        }else {
            $keys = array_keys($this->snmp_actions);
            asort($keys);
            $index = 0;
            foreach($keys as $key){
                if($key == $oid){
                    $index++;
                    break;
                }
                $index++;
            }
            $next_oid = @$keys[$index];
        }

        $this->getOid($next_oid);
    }

    private function get()
    {
        $oid = fgets($this->input);
        $oid = trim($oid);
        $this->getOid($oid);
    }

    private function getOid($oid)
    {
        if(array_key_exists($oid, $this->snmp_actions)){
            $result = '';
            if($func = $this->snmp_actions[$oid]['func']){
                $result = $func();
            } else {
                $this->writeln("NONE");
                return;
            }
            $this->writeln($oid);
            $this->writeln($this->snmp_actions[$oid]['type']);
            $this->writeln($result);
        } else {
            $this->writeln("NONE");
        }
    }

    private function ping()
    {
        $this->writeln('PONG');
    }
}
