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

    private function sortOids()
    {
        $keys = array_keys($this->snmp_actions);
        usort($keys, function ($a, $b){
            $a_s = explode(".", $a);
            $b_s = explode(".", $b);
            $i = 0;
            while(1){
                if($i> count($a_s)){
                    if($i > count($b_s)){
                        return 0;
                    } else {
                        return -1;
                    }
                }else if($i > count($a_s)){
                    return 1;
                }

                if ($a_s[$i] < $b_s[$i]) {
                    return -1;
                } else if ($a_s[$i] > $b_s[$i]) {
                    return 1;
                }
                $i++;
            }
            return str_replace(".", "", $a) > str_replace(".", "", $b);
        });
        return $keys;
    }

    public function run()
    {
        $this->sortedOids = $this->sortOids();
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
        $keys = $this->sortedOids;
        if($oid == $this->base_oid){
            $next_oid = @$keys[0];
        } else if ($oid == end($keys)) {
            $next_oid = 'ENDOFOID';
        }else {
            $index = 0;
            foreach($keys as $key){
                if($key == $oid){
                    $index++;
                    break;
                }
                $index++;
            }
            if($index == count($keys)){
                $next_oid = @$keys[0];
            }else {
                $next_oid = @$keys[$index];
            }
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
