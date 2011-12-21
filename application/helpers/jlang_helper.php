<?

function jlang($str)
{   
	//$str = mb_convert_encoding($str,"EUC-JP","UTF-8");
                                     
    $mecab = new MeCab_Tagger();
    $html='';
    $split_nodes=array();    
    for($node=$mecab->parseToNode($str); $node; $node=$node->getNext())
    {
        if($node->getStat() != 2 && $node->getStat() != 3)
        {
            $split_nodes[$node->getId()]=array('feature'=>$node->getFeature(), 'surface'=>$node->getSurface());
        }
    }

    
    
    if($split_nodes)
    {
		foreach($split_nodes as $k=>$snode)
		{
			$f=split(',',$snode['feature']);
        
        
			if(isset($f[6]))
			{
                $split_nodes[$k]['phrase']=$f[6];                
			}
		}
    }
    
    if($split_nodes)
    {
        
	    $CI =& get_instance();
	    $CI->load->library('edict_lib');
	    
	    
	    
        foreach($split_nodes as $k=>$snode)
        {   
            $CI->edict_lib->start($snode['phrase']);
            
            if($translated=@current($CI->edict_lib->_result))
            {   
                $translated=preg_replace('/\(\d+\)/','<br/>', $translated);
                
                // '-' - char is tiltle dilimiter for tooltip plugin
                $tooltip="{$snode['surface']} - {$snode['phrase']} {$translated}";            
                
                $html.=("<font id='jt_$k' title='$tooltip'>{$snode['surface']}</font>");
            }
            else//there is no translations
            {
                $html.= '<font>'.$snode['surface'].'</font>';
            }
        }
        
        $html.="<script>";
        foreach($split_nodes as $k=>$snode)
        {
            $html.="\$('#jt_$k').tooltip({showBody: ' - '});";
        }
        $html.="</script>";
                
        
    }
    
    
    //$html = mb_convert_encoding($html,"EUC-JP","UTF-8");
    return $html;
	                              
}

?>