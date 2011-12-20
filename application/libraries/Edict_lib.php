<?php
    #!/usr/local/bin/php
    #character code UTF-8.この行から32行はライセンスにより無許可では書き換えてはいけません。
    #PHP版 鍋田辞書 Ver 0.1
    #Nabeta Jisho for PHP Ver 0.1 (2010/8/3)
    #PHP版 鍋田辞書(NabetaJisho for PHP) Copyright (C) 2010 大場正輝(Oba Masaki) All Rights Reserved.";
    #http://wwww.nabeta.tk
    #admin@nabeta.tk;
    #Windows版 鍋田辞書 Ver 4.2以降からCGI辞書用に出力した辞書データを検索するPHPスクリプトです。
    #Windows版/Linux版の鍋田辞書はデータの書き込みや編集ができますがPHP版は読み込み専用です。
    #Windows版/Linux版は機能が豊富ですがPHP版は基本的な機能しか装備していません。
    #PHP版鍋田辞書 Ver 0.3は、Perl版鍋田辞書CGI Ver 0.3と同等の機能です。
    #モジュール版PHPのセーフモードに対応しています。
    #モジュール版PHPのセーフモードでない方法で動作させる場合は、スクリプト行頭に「#!/usr/local/bin/php」を追加し、スクリプト名の拡張子をcgiに変える必要があるかも知れません。(サーバーの設定で違うと思います。)
    #鍋田辞書Windows版から出力したnabeta.cgidatという、ひとつのファイルがデータファイルです。
    #単語のインデックスファイルを持っています。
    #先頭一致または完全一致且つ大文字小文字の区別がある場合はバイナリサーチで検索できます。
    #
    #ライセンス license
    #このPHPスクリプトはフリーソフトですが、著作権を放棄しておりません。
    #無料で使えます。
    #寄付は受け付けます。「鍋田辞書 Windows版/Linux版」のヘルプのライセンス説明に銀行口座番号が書いてあります。
    #改変の有無を問わずこのPHPスクリプトの再配布は無許可ではできません。
    #このPHPスクリプトを使って辞書サイト、検索サイトを一般WEB公開することは可能です。
    #このPHPスクリプトを改変して辞書サイト、検索サイトを一般WEB公開することは可能です。
    #一般WEB公開する場合はこのスクリプトのソースが見えないようにする必要があります。
    #このPHPスクリプトは改変していはいけない部分があり、それ以外は改変が可能です。
    #行頭のコメントの説明表示と、このファイルの末尾あたりにあるHTMLに出力するライセンス表示部分とその説明のコメントの三行は書き換えてはいけません。
    #データのライセンスは別ライセンスになっておりますので各データの各著作権者の決めたルールに従ってください。
    #鍋田辞書オリジナルのデータ(作者が鍋田辞書と同一のデータ)は、無許可での一般WEB公開は禁止しています。
    #ローカル環境で、IP 127.0.0.1からのみアクセスでそれ以外のアクセスを禁止している環境であれば、一般WEB公開や再配布に該当しないので鍋田辞書オリジナルのデータをこのスクリプトで使うことが可能です。
    #nabeta.php パーミッション 755。ftpはアスキーモードで転送。
    #nabeta.cgidat パーミッション はモジュール版PHPセーフモードなら644。そうでないなら600。ftpはバイナリモードで転送。 
    #この行まで書き換えてはいけません。


    class Edict_lib
    {
        var $_result;
        #データのファイル名を指定
        var $file1 ;
        var $cgi_name = "nabeta1.php";

        #表サイズ大
        var $hyou_size_dai = 780;

        #表サイズ小
        var $hyou_size_syou = 400;

        #訳語カットサイズ
        var $yakugo_cut_size = 60;

        #データ認識コード
        #間違って他のデータを検索しないための鍋田辞書CGIデータという印
        var $data_version_code = 654018739;

        var $input_tango = "";


        var $moto_input_tango = '';
        var $oomoji_str = NULL;
        var $ichigyou_str = NULL;
        var $yaku_cut_str = NULL;
        var $hyouji_kensuu_str = '100';
        var $chosakuken = NULL;
        var $tymode = "k_tango";
        var $smode = "kanzen";

        var $link_hyouji = 0;


        public function Edict_lib()
        {   
			$this->file1=(__DIR__."/nabeta.cgidat");
            $this->nabeta_s1 = array();
            $this->nabeta_s2 = array();
            $this->nabeta_s1len = 0;
            $this->nabeta_s2len = 0;

            if(strcmp($this->tymode,"")==0){
                $this->tymode = "k_tango";
            }

            if(strcmp($this->smode,"")==0){
                $this->smode = "sentou";
            }

            if(strcmp($this->oomoji_str,"oomoji")==0){
                $this->oomoji = 1;
            }
            else{
                $this->oomoji = 0;
            }

            if(strcmp($this->ichigyou_str,"ichigyou")==0){
                $this->ichigyou = 1;
            }
            else{
                $this->ichigyou = 0;
            }

            if(strcmp($this->yaku_cut_str,"yaku_cut")==0){
                $this->yaku_cut = 1;
            }
            else{
                $this->yaku_cut = 0;
            }
            $this->moto_yaku_cut = $this->yaku_cut;
            if($this->link_hyouji){
                $this->yaku_cut = 0;
            }

            $this->moto_ichigyou = $this->ichigyou;
            if($this->link_hyouji){
                $this->ichigyou = 0;
            }

            #最大表示件数
            $this->dmax = intval($this->hyouji_kensuu_str);

            $this->moto_oomoji = $this->oomoji;
            if($this->oomoji){
                if(eregi("[a-z]",$this->input_tango)){
                }
                else{
                    #ローマ字が含まれていないので、
                    #大文字小文字同一オプションは不要。検索速度が遅くなるので消す。
                    $this->oomji = 0;
                }
            }

            if($this->oomoji){
                $this->input_tango = strtolower($this->input_tango);
            }

           
        }

        Function table_hyouji(){




            if($this->link_hyouji){
                //print "<table width=\'{$this->hyou_size_syou}\' border=\'1\' cellpadding=\'1\' cellspacing=\'0\'>\n";
                //print "<tr>\n";
                //print "<th>単語/訳語</th>\n";
                //print "</tr>\n";
            }
            else{
                if($this->yaku_cut){
                    //print "<table width=\'{$this->hyou_size_syou}\' border=\'1\' cellpadding=\'2\' cellspacing=\'0\'>\n";
                }
                else{
                    //print "<table width=\'{$this->hyou_size_dai}\' border=\'1\' cellpadding=\'2\' cellspacing=\'0\'>\n";
                }
                //print "<tr>\n";
                //print "<th>単語</th>\n";
                //print "<th>訳語</th>\n";
                //print "</tr>\n";
            }
        }

        Function table_hyouji2(){

            $this->_result=array($this->tangos=>  $this->yakugo);

            if($this->link_hyouji){
                //print "<tr>\n";
                //print "<td>";
                //print "{$this->tangos}<br>{$this->yakugo}</td>\n";
                //print "</tr>\n";
            }
            else{
                //print "<tr>\n";
                //print "<td>";
                //print "{$this->tangos}</td>\n";
                //print "<td>";
                //print "{$this->yakugo}</td>\n";
                //print "</tr>\n";
            }
        }

        Function tangos_syori($tangos){
            $this->$tangos=$tangos;

            //$this->tangos = "<a href=\"$this->cgi_name?link_name=$this->tangos$this->zen_pm\">$this->tangos</a>";
            return $this->tangos;
        }

        Function yakugo_cut_syori_br($yakugo_str){

            $this->yakugo_str=$yakugo_str;
            $this->a = strpos($this->yakugo_str,"<br>",1);
            # ==の二個ではなく、===の三個を使う。
            #二個でもエラーにはならないが期待した動作をしない。
            if($this->a === false){
                #見つからない
            }
            else{
                if($this->a > $this->yakugo_cut_size){
                    $this->yakugo_str = substr($this->yakugo_str,0,$this->a);
                }
                else{
                    $this->a2 = strpos($this->yakugo_str,"<br>",$this->a+4); //$this->a+4
                    if($this->a2 === false){
                        $this->yakugo_str = substr($this->yakugo_str,0,$this->a);
                    }
                    else{
                        if($this->a2 > $this->yakugo_cut_size){
                            $this->yakugo_str = substr($this->yakugo_str,0,$this->a2);
                        }
                        else{
                            $this->a3 = strpos($this->yakugo_str,"<br>",$this->a2+4);
                            if($this->a3 === 0){
                                $this->yakugo_str = substr($this->yakugo_str,0,$this->a2);
                            }
                            else{
                                $this->yakugo_str = substr($this->yakugo_str,0,$this->a3);
                            }
                        }
                    }
                }
            }
            return $this->yakugo_str;
        }

        Function yakugo_cut_syori($yakugo_str){

            $this->yakugo_str=$yakugo_str;
            $this->a = strpos($this->yakugo_str,",",1);
            # ==の二個ではなく、===の三個を使う。
            #二個でもエラーにはならないが期待した動作をしない。
            if($this->a === false){
                #見つからない
            }
            else
                if($this->a > 0){
                    if($this->a > $this->yakugo_cut_size){
                        $this->yakugo_str = substr($this->yakugo_str,0,$this->a);
                    }
                    else{
                        $this->a2 = strpos($this->yakugo_str,",",$this->a+1);
                        if($this->a2 === 0){
                            $this->yakugo_str = substr($this->yakugo_str,0,$this->a);
                        }
                        else{
                            if($this->a2 > $this->yakugo_cut_size){
                                $this->yakugo_str = substr($this->yakugo_str,0,$this->a2);
                            }
                            else{
                                $this->a3 = strpos($this->yakugo_str,",",$this->a2+1);
                                if($this->a3 === 0){
                                    $this->yakugo_str = substr($this->yakugo_str,0,$this->a2);
                                }
                                else{
                                    $this->yakugo_str = substr($this->yakugo_str,0,$this->a3);
                                }
                            }
                        }
                    }
                }
                return $this->yakugo_str;
        }

        Function yakugo_syori($yakugo_str){

            $this->yakugo_str=$yakugo_str;

            $this->htmlf = 0;
            if(eregi("^[ 	\r\n]*<html>",$this->yakugo_str)){
                $this->htmlf = 1;
            }
            if($this->htmlf){
                $this->yakugo_str = eregi_replace("^[ 	\r\n]*<html>","",$this->yakugo_str);
                $this->yakugo_str = eregi_replace("<\/html>","",$this->yakugo_str);
                if($this->yaku_cut){
                    $this->yakugo_str = yakugo_cut_syori_br($this->yakugo_str);
                }
                if($this->ichigyou){
                    $this->yakugo_str = eregi_replace("<br>",",",$this->yakugo_str);
                }
                $this->yakugo_str = eregi_replace("link:\/\/","$this->cgi_name?link_name=",$this->yakugo_str);
                return $this->yakugo_str;
            }
            else
                if($this->ichigyou == 0){
                    $this->yakugo_str = ereg_replace("\r\n","<br>",$this->yakugo_str);
                    $this->yakugo_str = ereg_replace("\n","<br>",$this->yakugo_str);
                    $this->yakugo_str = ereg_replace("\r","<br>",$this->yakugo_str);
                    if($this->yaku_cut){
                        $this->yakugo_str = yakugo_cut_syori_br($this->yakugo_str);
                    }
            }
            else{
                $this->yakugo_str = ereg_replace("\r\n","\,",$this->yakugo_str);
                $this->yakugo_str = ereg_replace("\n","\,",$this->yakugo_str);
                $this->yakugo_str = ereg_replace("\r","\,",$this->yakugo_str);
                if($this->yaku_cut){
                    $this->yakugo_str = yakugo_cut_syori($this->yakugo_str);
                }
            }
            return $this->yakugo_str;
        }

        Function hedda_yomikomi(){
            #先頭にシーク
            #fseek($this->DATA, 0);
            #ファイル識別子読み込み






            $this->buf = fread($this->DATA,4);
            /* if (sizeof($this->buf) != 4) {
            //print("Read ERROR Index file\n");
            fclose($this->DATA); 
            exit(1);
            }*/
            $this->version_code = unpack("V",$this->buf);
            if($this->version_code[1] != $this->data_version_code){
                //print("EROR $this->file1 is not Nabeta CGI data file\n");
                fclose($this->DATA); 
                exit(1);
            }

            #レコード数読み込み(データ数)(見出し数)
            $this->buf = fread($this->DATA,4);
            /* if(strlen($this->buf)!= 4) {
            //print("Read ERROR Index file\n");
            fclose($this->DATA); 
            exit(1);
            }*/
            $this->tmp = unpack("V",$this->buf);
            $this->rec_suu = $this->tmp[1];
            if($this->rec_suu == 0){
                //print("EROR Data size zero.$this->file1\n");
                fclose($this->DATA); 
                exit(1);
            }
            #インデックス開始位置数読み込み
            $this->buf = fread($this->DATA,4);
            /* if(sizeof($this->buf) != 4) {
            //print("Read ERROR Index file\n");
            fclose($this->DATA); 
            exit(1);
            }*/
            $this->tmp = unpack("V",$this->buf);
            $this->index_kaishi_ichi = $this->tmp[1];
            if($this->index_kaishi_ichi == 0){
                //print("EROR Data size zero.$this->file1\n");
                fclose($this->DATA); 
                exit(1);
            }

            #データ開始位置数読み込み
            $this->buf = fread($this->DATA,4);
            /* if(sizeof($this->buf) != 4) {
            //print("Read ERROR Index file\n");
            fclose($this->DATA); 
            exit(1);
            }*/
            $this->tmp = unpack("V",$this->buf);
            $this->data_kaishi_ichi = $this->tmp[1];
            if($this->data_kaishi_ichi == 0){
                //print("EROR Data size zero.$this->file1\n");
                fclose($this->DATA); 
                exit(1);
            }
        }


        #鍋田辞書独自のソート順の文字列比較のための文字列を数字配列に変換。
        #文字列先頭の7ビットのアスキーコードだけ順番が違う。
        #NULLが最も小さく次にa-z,A-Z,、その他の7ビットの記号、後は通常のUnicode順
        #もし、データが他のソート順であればここを変更しないといけない
        Function nabeta_str2s1($sin){

            $this->sin=$sin;
            #$this->tmp = unpack("C*",$this->sin);


            $this->mscd_str = str_split($this->sin);
            #$this->len = sizeof($this->mscd_str);
            $this->len = strlen($this->sin);
            $this->nabeta_s1len = $this->len;
            for($this->i=0;$this->i<$this->len;++$this->i){
                $this->cc = ord($this->mscd_str[$this->i]);
                if($this->mscd_str[$this->i] == ' '){
                    $this->nabeta_s1len = $this->i;
                    return;
                }
                #$this->tmp = unpack("C",$this->mscd_str[$this->i]);これでもよい
                #$this->cc = $this->tmp[1];
                if($this->i != 0){
                    $this->nabeta_s1[$this->i] = $this->cc;
                }
                else
                    if($this->cc >= 0x80){
                        $this->nabeta_s1[$this->i] = $this->cc;
                    }
                    else
                        if($this->cc == 0){
                            #あり得ないパターン
                            $this->nabeta_s1[$this->i] = $this->cc;
                        }
                        else
                            if($this->cc <= 0x40){
                                #記号1
                                #53=ローマ字小文字26+ローマ字大文字26+NULL
                                $this->nabeta_s1[$this->i] = $this->cc + 52 + 1;
                            }
                            else
                                if($this->cc <= 0x5a){
                                    #A-Z
                                    #A=0x41
                                    $this->nabeta_s1[$this->i] = $this->cc - 0x41 + 26 + 1;
                                }
                                else
                                    if($this->cc <= 0x60){
                                        #記号2
                                        #53=ローマ字小文字26+ローマ字大文字26+NULL1
                                        $this->nabeta_s1[$this->i] = $this->cc - 0x5b + 52 + 0x40 + 1;
                                    }
                                    else
                                        if($this->cc <= 0x7a){
                                            #a-z
                                            #a=0x61
                                            $this->nabeta_s1[$this->i] = $this->cc - 0x61 + 1;
                                        }
                                        else
                                            if($this->cc <= 0x7f){
                                                #記号2
                                                #53=ローマ字小文字26+ローマ字大文字26+NULL1
                                                $this->nabeta_s1[$this->i] = $this->cc - 0x7b + 52 + 0x46 + 1;
                                            }
            }
        }

        Function nabeta_str2s2($sin){

            $this->sin=$sin;

            #$this->tmp = unpack("C*",$this->sin);
            $this->mscd_str = str_split($this->sin);
            #$this->len = sizeof($this->mscd_str);
            $this->len = strlen($this->sin);
            $this->nabeta_s2len = $this->len;
            for($this->i=0;$this->i<$this->len;++$this->i){
                $this->cc = ord($this->mscd_str[$this->i]);
                #$this->tmp = unpack("C",$this->mscd_str[$this->i]);これでもよい
                #$this->cc = $this->tmp[1];
                if($this->mscd_str[$this->i] == ' '){
                    $this->nabeta_s1len = $this->i+1;
                    return;
                }
                if($this->i != 0){
                    $this->nabeta_s2[$this->i] = $this->cc;
                }
                else
                    if($this->cc >= 0x80){
                        $this->nabeta_s2[$this->i] = $this->cc;
                    }
                    else
                        if($this->cc == 0){
                            #あり得ないパターン
                            $this->nabeta_s2[$this->i] = $this->cc;
                        }
                        else
                            if($this->cc <= 0x40){
                                #記号1
                                #53=ローマ字小文字26+ローマ字大文字26+NULL
                                $this->nabeta_s2[$this->i] = $this->cc + 52 + 1;
                            }
                            else
                                if($this->cc <= 0x5a){
                                    #A-Z
                                    #A=0x41
                                    $this->nabeta_s2[$this->i] = $this->cc - 0x41 + 26 + 1;
                                }
                                else
                                    if($this->cc <= 0x60){
                                        #記号2
                                        #53=ローマ字小文字26+ローマ字大文字26+NULL1
                                        $this->nabeta_s2[$this->i] = $this->cc - 0x5b + 52 + 0x40 + 1;
                                    }
                                    else
                                        if($this->cc <= 0x7a){
                                            #a-z
                                            #a=0x61
                                            $this->nabeta_s2[$this->i] = $this->cc - 0x61 + 1;
                                        }
                                        else
                                            if($this->cc <= 0x7f){
                                                #記号2
                                                #53=ローマ字小文字26+ローマ字大文字26+NULL1
                                                $this->nabeta_s2[$this->i] = $this->cc - 0x7b + 52 + 0x46 + 1;
                                            }
            }
            #my $this->n_str = pack "C*", $this->mscd; パックできない
        }



        #文字列を鍋田辞書独自のソート順に変換した数値配列の大小を比較
        Function nabeta_strcmp(){




            $this->len1 = $this->nabeta_s1len;
            $this->len2 = $this->nabeta_s2len;


            if($this->len1 > $this->len2){
                $this->lenns = $this->len2;
            }
            else{
                $this->lenns = $this->len1;
            }
            for($this->i=0;$this->i < $this->lenns;++$this->i){
                if($this->nabeta_s1[$this->i] > $this->nabeta_s2[$this->i]){
                    #s1,左が大きい
                    return 1;
                }
                else
                    if($this->nabeta_s1[$this->i] < $this->nabeta_s2[$this->i]){
                        #s1,左が小さい
                        return -1;
                    }
            }

            if($this->len1 > $this->len2){
                #s1,左が大きい
                return 1;
            }
            else
                if($this->len1 < $this->len2){
                    #s1,左が小さい
                    return -1;
                }
                else{
                    #同じ
                    return 0;
            }
        }


        function start($s)
        {
            $this->input_tango=$s;

            $this->zen_pm = "";
            if($this->moto_oomoji){
                $this->zen_pm = "&oomojikomoji=oomoji";
            }
            if($this->moto_ichigyou){
                $this->zen_pm = $this->zen_pm . "&ichigyou=ichigyou";
            }
            if($this->yaku_cut){
                $this->zen_pm = $this->zen_pm . "&yaku_cut=yaku_cut";
            }
            $this->zen_pm = $this->zen_pm . "&hyouji_kensuu={$this->hyouji_kensuu_str}";
          //  $this->zen_pm = $this->zen_pm . "&rb1={$this->tymod}";
            $this->zen_pm = $this->zen_pm . "&rb2={$this->smode}";


            if(strcmp($this->input_tango,"")){
                #もし検索文字が入力されていれば
                if(strcmp($this->tymode,"k_tango")==0){
                    if(strcmp($this->smode,"kanzen")==0){
                        if(($this->DATA = fopen($this->file1,"rb"))==FALSE){
                            //print("Data file {$this->file1} Open Error(read only)\n");
                            exit(1);
                        } 

                        $this->len = strlen($this->input_tango);

                        #ヘッダ読み込み
                        $this->hedda_yomikomi();
                        $this->table_hyouji();

                        #大文字小文字オプション有り且つローマ字の入力有れば全ループ
                        #大文字小文字オプション無しでバイナリサーチ
                        $this->left = 0;
                        $this->right = $this->rec_suu-1;
                        $this->icchi = 0;
                        #kanzen
                        $this->kanzen_cnt = 0;
                        $this->k_cnt = 0;
                        if($this->oomoji == 0){
                            $this->nabeta_str2s2($this->input_tango);
                        }
                        while(1){
                            if($this->oomoji){
                                #大文字小文字オプション有り
                                if($this->kanzen_cnt < $this->rec_suu){
                                }
                                else{
                                    break;
                                }
                            }
                            else{
                                #大文字小文字オプション無し
                                if($this->left <= $this->right){
                                }
                                else{
                                    break;
                                }
                            }
                            if($this->oomoji){
                                $this->mid = $this->kanzen_cnt; 
                            }
                            else{
                                $this->mid = intval(($this->left + $this->right) / 2); 
                            }
                            fseek($this->DATA, $this->mid * 4 + $this->index_kaishi_ichi);
                            $this->buf = fread($this->DATA,4);
                            /* if(sizeof($this->buf) != 4) {
                            //print("Read ERROR Index file\n");
                            fclose($this->DATA); 
                            exit(1);
                            }*/
                            $this->tmp = unpack("V",$this->buf);
                            $this->data_adr = $this->tmp[1];

                            fseek($this->DATA, $this->data_adr + $this->data_kaishi_ichi);
                            $this->tangos = "";
                            while (1){
                                $this->c = fgetc($this->DATA);
                                if(strcmp($this->c,"\0")==0){
                                    break;
                                }       
                                $this->tangos = $this->tangos . $this->c; 
                            }
                            if($this->oomoji == 0){
                                $this->nabeta_str2s1($this->tangos);
                                $this->cmp = $this->nabeta_strcmp();
                            }
                            $this->icchi = 0;
                            if($this->oomoji){
                                #大文字小文字オプション有り
                                $this->komoji_tangos = strtolower($this->tangos);
                                if(strcmp($this->komoji_tangos,$this->input_tango)==0){
                                    #大文字小文字を区別せずデータが一致
                                    $this->icchi = 1;
                                }
                            }
                            else
                                if($this->cmp == 0){
                                    #データが完全一致
                                    $this->icchi = 1;
                                }
                                else
                                    if($this->cmp < 0){
                                        #文字列比較 tangos < input_tango
                                        # midより左側にinput_tangoは存在しない
                                        $this->left = $this->mid + 1;
                                    }
                                    else{
                                        # midより右側にinput_tangoは存在しない
                                        $this->right = $this->mid - 1;
                            }

                            if(strcmp($this->tangos,"")==0){
                            }
                            else
                                if($this->icchi == 1){
                                    $this->yakugo = "";
                                    while (1){
                                        $this->c = fgetc($this->DATA);
                                        if(strcmp($this->c,"\0")==0){
                                            break;
                                        }
                                        $this->yakugo = $this->yakugo . $this->c; 
                                }

                                $this->tangos = $this->tangos_syori($this->tangos);
                                $this->yakugo = $this->yakugo_syori($this->yakugo);
                                $this->table_hyouji2();

                                if($this->oomoji == 0){
                                    break;
                                }
                                ++$this->k_cnt;
                                if($this->k_cnt >= $this->dmax){
                                    break;
                                }
                            }
                            if($this->oomoji){
                                ++$this->kanzen_cnt;
                            }
                        }
                        //print("</table>");
                        if($this->k_cnt == 0){
                            //print("該当無し<br>\n");
                        }
                        fclose($this->DATA);
                        #kanzen end
                    }


                    //fclose($this->DATA); 二重クローズでエラーになる
                }#単語検索処理
            }#検索処理全て終わり
            #データ著作権表示ボタン
        }

    }

  


?>
