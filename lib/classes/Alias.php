<?php

define('MAIL_ALIASES', '/etc/aliases');
define('AUTOGENERATED', '#!AUTOGENERATED -- Do not edit below this line!!');

class Alias {
    private static function write($data) {
        if(!is_writeable(MAIL_ALIASES)) {
            throw new Exception('can not write to alias database, permission problem?');
        }
        file_put_contents(MAIL_ALIASES, $data, LOCK_EX);

        Alias::commit();
    }

    public static function asList() {
        $aliases = file_get_contents(MAIL_ALIASES);
        
        if(false === strpos($aliases, AUTOGENERATED)) {
            $aliases .= PHP_EOL . AUTOGENERATED . PHP_EOL;
            Alias::write($aliases);
            $aliases = '';
        } else
            $aliases = substr($aliases, strpos($aliases, AUTOGENERATED) + strlen(PHP_EOL));
        
        preg_match_all('/^(?!#)(.+):\s*(.+(?!\S))/m', $aliases, $matches);
        
        $list = array();
        
        for($i=0; $i < count($matches[1]); $i++) {
            $list[$matches[1][$i]] = explode(' ', $matches[2][$i]);
        }
        
        return $list;
    }
    
    public static function addNew($name, $aliases) {
        if(!strlen($name) || empty($aliases))
            return;
    
        if(false !== strpos($name, '@'))
            throw new Exception('cannot alias non-local names');
        
        foreach($aliases as $ix => $alias) {
            if(!strlen($alias))
                unset($aliases[$ix]);
        }
        
        $filecontents = file_get_contents(MAIL_ALIASES);
        if(false === strpos($filecontents, AUTOGENERATED)) {
		$filecontents .= PHP_EOL . AUTOGENERATED . PHP_EOL;
        }
        
        $filecontents .= $name . ': ' . join(' ', $aliases) . PHP_EOL;
        
        Alias::write($filecontents);
    }
    
    public static function edit($name, $aliases) {
        $list = Alias::asList();
        
        foreach($aliases as $ix => $alias)
            if(!strlen($alias))
                unset($aliases[$ix]);
        
        $list[$name] = $aliases;
        
        $filecontents = file_get_contents(MAIL_ALIASES);
        
        $filecontents = substr($filecontents, 0, strpos($filecontents, AUTOGENERATED) + strlen(AUTOGENERATED . PHP_EOL));
        
        
        foreach($list as $name => $alias) {
            $filecontents .= $name . ': ' . join(' ', $alias) . PHP_EOL;
        }
        
        Alias::write($filecontents);
    }
    
    public static function remove($name) {
        $list = Alias::asList();
        
        unset($list[$name]);
        
        $filecontents = file_get_contents(MAIL_ALIASES);
        
        $filecontents = substr($filecontents, 0, strpos($filecontents, AUTOGENERATED) + strlen(AUTOGENERATED . PHP_EOL));
        
        foreach($list as $name => $alias) {
            $filecontents .= $name . ': ' . join(' ', $alias) . PHP_EOL;
        }
        
        Alias::write($filecontents);
    }
    
    public static function commit() {
        //system('../c/suidmakehash hash ' . MAIL_ALIASES . '.db < ' . MAIL_ALIASES, $ret);
        //system('./suidnewaliases', $ret);
        system("sudo newaliases", $ret);
        /*
        switch($ret) {
            case 64:
                throw new Exception('Kunde inte starta program, parameterfel');
            case 127:
                throw new Exception('Kunde inte starta program, filen finns inte');
            default:
        }*/
    }
}

?>
