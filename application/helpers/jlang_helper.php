<?

function jlang($str)
{                                        
    $mecab = new MeCab_Tagger();
    
    $feature='';
    $phare='';
    for($node=$mecab->parseToNode($str); $node; $node=$node->getNext())
    {
        if($node->getStat() != 2 && $node->getStat() != 3)
        {
            $feature=$node->getFeature();                
        }
    }

    
    if($feature)
    {
        $feature=split(',',$feature);
        
        if(isset($feature[6]))
        {
            $phrase  = $feature[6];
        }
    }

    
    if($phrase)
    {
        
	    $CI =& get_instance();
	    $CI->load->library('edict_lib');
	    
	    
	    

        $CI->edict_lib->start($str);
        
        return current($CI->edict_lib->_result);
    }
    return '';
	                              
}

?>