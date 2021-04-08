<?php
  include_once 'config.inc2.php'; 
  set_time_limit(20000);
  error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_WARNING);
  function analiza($a,$pol,$dir){
  	$db=new Conect_MySql2();
  	$a=$dir.'/'.$a; 
                  shell_exec("pdftotext.exe ".'"'.$a.'"');
  	$txt=substr($a,0, strlen($a)-3)."txt";
  	$file=fopen($txt,'r+'); $i=1;$ban=0; $ban2=0; $ban3=0;$aseg=1;$contra=1; $max=0;
                  
    $sql="select poliza from poldoc where poliza='$pol'";
    $query=$db->execute($sql);
    if($db->fetch_row($query))return 0;
    while(!feof($file)){
      $cad=fgets($file); 
      if(strpos($cad, "61875")>1 && $ban==0){
       //echo "<br>".$a;
       
       $poliza=substr($a,8, strlen($a)-12);	
       $ban=1;
      }
      if($ban==1){
       
          if(strpos($cad,"C.P.:")===0){
          echo "<br>$pol - ".$cad;
         $cp=fgets($file);
         //echo "<br>1: ".$cp;
         $cp=fgets($file);
         if(($p=strpos($cp,' '))>=1){
             $cp=substr($cp,0,$p);
         } 
                  $sql="update poldoc set cp='$cp' where poliza='$pol'"; 
                  $db->execute($sql);  
                 echo "<br>2: ".$cp;
        }            
        if(strpos($cad,"NOMBRE")===0){$ban2=1;}   
        if(strpos($cad,"CONTRATANTE:")===0){
                if(strlen($cad)>15){
                    $contra= substr($cad, 12,strlen($cad)-12 );
                 }else {
                    $cad=fgets($file);
                    $cad=fgets($file);
                    if(strlen($cad)<3)$cad=fgets($file);
                    $contra=$cad;
                }
            $sql="insert into poldoc (poliza, contra)"
                  . " values('$pol','$contra')";  
            $db->execute($sql);  
                
        }        
        if(strpos($cad, "MERO DE ASEGURADOS")>1 && $ban3==0) {
            $cad=fgets($file);
            $posp=strpos($cad,' ');
            $Aseg=substr($cad,0,$posp);
            if($Aseg=='')$Aseg=0;
            $sql="insert into polnum (poliza, num)"
                  . " values('$pol', $Aseg )";  
            
            $db->execute($sql);  
            $ban3=1;
        }
      }  
      if($ban2==1){
      
          if(strlen($cad)<80 && strlen($cad)>7
          && strpos($cad, "N ADICIONAL")<1 && strpos($cad, "N DIVIDENDO")<1
          && strpos($cad, "INCLUYEN Y FORMAN PARTE DEL PRESENTE CONTRATO")<1 
          && strpos($cad, "VISO DE PRIVACIDAD")<1 && strpos($cad, "N DE SEGURO: GRUPO SIN")<1
          && strpos($cad, "UMA ASEGURADA")<1 && strpos($cad, "O. SUB CERT. GRUPO")<1
          && strpos($cad, "SICA ACCIDENTE INVALIDEZ")<1 && strpos($cad, "EGURO DE GRUPO")<1
          && strpos($cad, "NOMBRE")!==0 && strpos($cad,"PRIMA NETA")!==0        ){
          $sql="insert into textpol (poliza, renglon)"
                  . " values('$pol','$cad')";
          $db->execute($sql); 
          }
          if(strpos($cad, "COBERTURAS ADICIONALES CONTRATADAS PARA ACCIDENTE E INVALIDEZ")>1)RETURN;
      }
     if(strpos($cad,"copia banco")===0)break; 
    }
   if($ban==0||strpos($a,'(')){unlink ($txt);unlink($a);} 
     $db->close_db();	
   return 1;
  } 
   
/////////////////////////////////////MAIN()////////////////////////////////////////
  $dira="polizas2020";
  $dir=opendir($dira);
  $i=1;
  while($arch=readdir($dir)){
               //$pos=strpos($arch,'.');	
               $ext=substr($arch,$pos,4);
               $pol=substr($arch,0,13);
	  $pos2=strpos($arch,'(');	
	  $pos=strpos($arch,"020000");
                $a="polizas2020/$arch";
                $txt=substr($a,0, strlen($a)-3)."txt";
                //if($pos&&!file_exists($txt))
                if($pos){ 
                     //echo "<br>$i".' '.$dira.'/'.$arch;
                      $i++;
	analiza($arch,$pol,$dira);
                 
  }	
}
?>