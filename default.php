<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php
 
$itemid = JoomdleHelperContent::getMenuItem();

$linkstarget = $this->params->get( 'linkstarget' );
if ($linkstarget == "new")
    $target = " target='_blank'";
else $target = "";
?>

<?php
$jump_url =  JoomdleHelperContent::getJumpURL ();
$user = JFactory::getUser();
$username = $user->username;
$session                = JFactory::getSession();
$token = md5 ($session->getId());
$course_id = $this->course_info['remoteid'];
$direct_link = 1;
$show_summary = $this->params->get( 'course_show_summary');
$show_topics_numbers = $this->params->get( 'course_show_numbers');

if ($this->course_info['guest'])
    $this->is_enroled = true;

//skip intro
//array_shift ($this->mods);

        include_once( JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php' );
        $cbUser    =&  CBuser::getInstance( $user->id );

//this->mods sao as aulas vindo do moodle
//retira a primeira 'aula'
$aulaBiblioteca = array_shift($this->mods);
//trabalha a view da biblioteca

//pega os arquivos da 'aula'
$resources = $aulaBiblioteca['mods'];
$bibliotecaCurso =  array();
foreach ($resources as $id => $resource) {
	if($resource['mod']=='forum')
		continue;
	if (($this->is_enroled) && ($resource['available'])){
		$direct_link = JoomdleHelperSystem::get_direct_link ($resource['mod'], $course_id, $resource['id'], $resource['type']);
		if ($direct_link){
			if ($direct_link != 'none')
				array_push($bibliotecaCurso, "<li><a href=\"".$direct_link."\" target=\"_blank\">".$resource['name']."</a></li>");
		}else{
			$mtype = JoomdleHelperSystem::get_mtype ($resource['mod']);
			array_push($bibliotecaCurso, "<li><a $target href=\"".$jump_url."&mtype=$mtype&id=".$resource['id']."&course_id=$course_id&create_user=0&Itemid=$itemid&redirect=$direct_link\" target=\"_blank\">".$resource['name']."</a></li>");
		}
	}
}

//retira a segunda 'aula'
$aulaCertificacao = array_shift($this->mods);
//trabalha view da certificacao

//pega os arquivos da 'aula'
$resources = $aulaCertificacao['mods'];
$certificacaoCurso =  array();
foreach ($resources as $id => $resource) {
	//pula o componente quiz (questionario) porque ele vai ir por iframe num label
	if($resource['mod'] == 'quiz' || $resource['mod'] == 'scorm')
		continue;
	//só pra fazer o link do certificado
	if (($this->is_enroled) && ($resource['available'])){
		$direct_link = JoomdleHelperSystem::get_direct_link ($resource['mod'], $course_id, $resource['id'], $resource['type']);
		if ($direct_link){
			if ($direct_link != 'none')
				$certificacaoCurso['certificado'] = "<p class=\"text-center \"> <a target=\"_blank\" href=\"".$direct_link."\"><img src=\"http://telelab.aids.gov.br/images/icon-certificado-on.jpg\" alt=\"Certificado\"></a></p>";
				//$certificacaoCurso['certificado'] = "<a class='btn btn-info btn-block' href=\"".$direct_link."\">".$resource['name']."</a>";
		}else{
			$mtype = JoomdleHelperSystem::get_mtype ($resource['mod']);
			$certificacaoCurso['certificado'] = "<a class='btn btn-info btn-block' $target href=\"".$jump_url."&mtype=$mtype&id=".$resource['id']."&course_id=$course_id&create_user=0&Itemid=$itemid&redirect=$direct_link\">".$resource['name']."</a>";
		}
	}
	//para pegar o frame da prova
if ($resource['mod'] == 'label'){
	$frameProva = JoomdleHelperContent::call_method ('get_label', $resource['id']);
	$certificacaoCurso['frame'] = $frameProva['content'];
}
}
//resto das aulas
$conteudo = '';
$testeee = array();
$youtubeNoMenu = '';
$menuAulas = '';
if (is_array ($this->mods)) {
	foreach ($this->mods as  $tema){ 

		//logica da URL (aula e youtube)
		if ( isset($_GET["youtube"]))
			$youtubeNoMenu = "&youtube=true";
        $menuAulas .=  "<li class='aula".$tema['section']."'><a href='?aula=".$tema['section'].$youtubeNoMenu."'>".$tema['name']."</a></li>";

        //compara a aula que esta na url com a section do $tema caso for a aula errada pula e caso não tiver aula selecionada carrega a primeira.
	if(!isset($_GET["aula"])&&$tema['section']!=2)
	    continue;
        elseif(isset($_GET["aula"])&&($_GET["aula"])!=$tema['section'])
            continue;
 
        //unset para tirar a primeira aula
        unset($conteudo);
        //estrai as informações da aula
        $conteudo["titulo"] = $tema['name'];
		$conteudo["sumario"] = $tema['summary'];
		$conteudo["secao"] = $tema['section'];
		$resources = $tema['mods'];
		//var_dump($resources);
		//carrega arquivos/atividades da aula
		foreach ($resources as $id => $resource) {
			//resource sao os conteudos videos, atividades, prova. da pra ler o 'mod' e ver o que é
			//é usado o recurso "label" no moodle para passar o video e aqui é dado tratamento especial
			if ($resource['mod'] == 'label'){
				if (isset($_GET["youtube"])==true){
					include_once( JPATH_ROOT . '/components/com_joomdle/models/cursosTelelab.php' );
					//die(var_dump(getLocalVideo($course_id, $tema['section'])));
					$conteudo["video"]["content"] = "<div class=\"text_to_html\"><p><video controls> <source src=\"".getLocalVideo($course_id, $tema['section'])."\" type=\"video/mp4\"> your browser does not support the video tag. </video></p></div>";
					$conteudo["linkYoutube"] = "<a href='?aula=".$tema['section']."' class='trocar-player'>Para voltar ao player do Youtube, Clique aqui.</a>";
				}else{
					$conteudo["video"] = JoomdleHelperContent::call_method ('get_label', $resource['id']);
                    $testeee = $conteudo["video"];
					$conteudo["linkYoutube"] = "<a href='?aula=".$tema['section']."&youtube=true' class='trocar-player'>Problemas para visualizar o video?</a>";
				}
			}
        }

    }
    $conteudoAula = $conteudo;
    unset($conteudo);
}

//inicializa varivel de script
$scriptJS;
?>
<div class="parteEsquerda span9">
<div class="span12 cabecalho-aula">
<h1><span>Curso: </span><?php echo $this->course_info['fullname']; ?></h1>
<?php /*h2><?php echo $conteudoAula["titulo"]; ?></h2 */ ?>
</div>
<div class="span12">
<div class="topico">
<div class="video" id="videoYT">
<?php

$cpfTeste = $cbUser->getField( 'cb_cpf', $defaultValue = null, $output = 'html', $formatting = 'none', $reason = 'profile', $list_compare_types = 0 );
$cpfTeste2 = strlen(preg_replace("/[^0-9]/","",$cpfTeste));

if (isset($_GET["aula"])&&($_GET["aula"]=="prova")){
            if(($cbUser->getField( 'cb_pais', $defaultValue = null, $output = 'html', $formatting = 'none', $reason = 'profile', $list_compare_types = 0 )=="BRASIL")&&($cpfTeste2==11))
                echo $certificacaoCurso["frame"]; 
              

    $scriptJS = "<script> 

        jQuery( document ).ready(function() {
            console.log( 'ready!' );
            document.getElementById('tab-1').style.display= 'none';
            document.getElementById('tab-3').style.display= 'block';
        });

    </script>";

}else{
    echo $conteudoAula["video"]["content"];
    //CASO curso seja SCORM (falciforme), não precisa do LINK 
    if($course_id!=15)
    echo $conteudoAula["linkYoutube"]; 
}
?>
</div>
</div>
</div>
</div>


<div class="span3" id="tabs-container">
<ul class="tabs-menu">
<li class="aulas"><a href="#tab-1">Aulas</a></li>
<li class="biblioteca"><a href="#tab-2">Materiais</a></li>
<li class="certificacao"><a href="#tab-3" >Certificado</a></li>
</ul>
<div class="tab">
<div id="tab-1" class="tab-content">
<ul>
<?php echo $menuAulas; ?>
</ul>	
</div>
<div id="tab-2" class="tab-content">
<ul>
<?php foreach($bibliotecaCurso as $umArquivo) echo $umArquivo; ?>
</ul>
</div>
<div id="tab-3" class="tab-content">
<ul>
<!-- Aba de certificação -->
<!-- 2 conteudos, para logados e nao logados -->
<?php $user = JFactory::getUser();
///////////////////IF
if ( $user->get('guest')  ) { //$user->get('guest') retorna '1' caso seja visitante ?>

<li>
<div class="orientacao visitante">
<h4>Olá Visitante</h4><p> <br > Nossa certificação é online e gratuíta. Para mais informações você deve entrar no site, 
  caso você não tenha cadastro <a href="http://telelab.aids.gov.br/index.php/cadastro">clique aqui</a></p>
        <div class="alert alert-success" style="padding-bottom: 1cm;">
        <h4 class="texto-certificacao text-center">Já sou cadastrado</h4>
<?php
    $zone = "login";
    $modules =& JModuleHelper::getModules($zone);
    foreach ($modules as $module){
        echo JModuleHelper::renderModule($module);
    }
?> </li></div></div>

    <?php } else {
	$cpfTeste = $cbUser->getField( 'cb_cpf', $defaultValue = null, $output = 'html', $formatting = 'none', $reason = 'profile', $list_compare_types = 0 );
	$cpfTeste2 = strlen(preg_replace("/[^0-9]/","",$cpfTeste));
        if($course_id==8||$course_id==12||$course_id==17)
            echo "<p>Este curso não dispõe de avaliação e certificação.</p>";
        else
                        if(($cbUser->getField( 'cb_pais', $defaultValue = null, $output = 'html', $formatting = 'none', $reason = 'profile', $list_compare_types = 0 )!="BRASIL")||($cpfTeste2!=11)){
//var_dump($cbUser->getField( 'cb_pais', $defaultValue = null, $output = 'html', $formatting = 'none', $reason = 'profile', $list_compare_types = 0 ));
//var_dump(strlen($cbUser->getField( 'cb_cpf', $defaultValue = null, $output = 'html', $formatting = 'none', $reason = 'profile', $list_compare_types = 0 )));
//die();
?>
    <li><div class="orientacao cadastrado" >
     <div class="alert alert-danger usuario" >
                <h4 class="titulo-certificacao text-center"><strong>  Atenção</strong></h4><p class="texto-certificacao">A certificação está disponível apenas para brasileiros com CPF válido. Você pode conferir seu cadastro <a href="http://telelab.aids.gov.br/index.php/component/comprofiler/userdetails">clicando aqui.</a></a></p>
                </div></div></li>
    <li><div class="orientacao cadastrado" >
     <div class="alert alert-danger usuario" >
                <h4 class="titulo-certificacao text-center"><strong>  Attention</strong></h4><p class="texto-certificacao"> Only Brazilians are allowed to see this page. </p>
                </div></div></li>
     <?php             
                        }else{
                        
            ?>
                      
                <li><div>
                <!-- um link para ir pra prova -->
                <p>O primeiro passo para obter a certificação é realizar a prova. Se você quiser saber mais acesse nossa <a href="http://telelab.aids.gov.br/index.php/component/k2/item/122">página de dúvidas sobre a certificação.</a> </p>
                <a class='btn btn-info btn-block' href='?aula=prova'>Abrir prova</a></div>
                <!-- aqui fica null quando o certificado não está visivel, equivalente a quando o usuario não possui a nota suficientes -->
                </li>
<?php 
    if(is_null($certificacaoCurso["certificado"]))
        echo "<li><p>Para concluir a certificação você precisa de nota 7 ou superior na prova.</p><p class=\"text-center \"> <img src=\"http://telelab.aids.gov.br/images/icon-certificado-off.jpg\" alt=\"Certificado\"></p>";
    else
        echo "<li><div><p class='certificado'>Parabéns! Na imagem abaixo você encontra uma cópia do seu certificado.</p>";
    echo $certificacaoCurso["certificado"]; 
     ?> </div></li>
                <?php  }} ?>

</div>
</ul>
    <div class="clear"></div>
</div>
</div>
<?php
if(isset($conteudoAula["titulo"])){
unset($conteudoAula["titulo"]);
unset($conteudoAula["sumario"]);
unset($conteudoAula["video"]); 
unset($conteudoAula["linkYoutube"]);
unset($conteudoAula["secao"]);
}
/*
//para ver se o nome está incompleto
$isIncompleto = false;

if($isProva)
if(verificarCadastro($cbUser))
echo "Atenção, verifique se seu nome está correto: \"".$cbUser->getField( 'firstname', $defaultValue = null, $output = 'html', $formatting = 'none', $reason = 'profile', $list_compare_types = 0 )." ".$cbUser->getField( 'lastname', $defaultValue = null, $output = 'html', $formatting = 'none', $reason = 'profile', $list_compare_types = 0 )."\".";
else
$isIncompleto = true;
if($isIncompleto)
echo "Seu cadastro está incompleto, você não pode fazer a prova";
else
?>
<?php
foreach($conteudoAula as $arquivos)
echo "<li>" . $arquivos. "</li>";
?>
</div>
 */

if (isset($_GET["aula"])&&$_GET["aula"]=="prova") 
	echo $scriptJS;
function getUsuarioNomeCertificado($cbUser){




}

function verificarCadastro($cbUser){
	$isCompleto = true;

	if(strlen($cbUser->getField( 'cb_cpf', $defaultValue = null, $output = 'html', $formatting = 'none', $reason = 'profile', $list_compare_types = 0 ))<9)
		$isCompleto = false;

	if(strlen($cbUser->getField( 'cb_telefone', $defaultValue = null, $output = 'html', $formatting = 'none', $reason = 'profile', $list_compare_types = 0 ))<7)
		$isCompleto = false;

	if(strlen($cbUser->getField( 'cb_sexo', $defaultValue = null, $output = 'html', $formatting = 'none', $reason = 'profile', $list_compare_types = 0))<5)
		$isCompleto = false;

	if(strlen($cbUser->getField( 'cb_nascimento', $defaultValue = null, $output = 'html', $formatting = 'none', $reason = 'profile', $list_compare_types = 0))<5)
		$isCompleto = false;

	if(strlen($cbUser->getField( 'cb_pais', $defaultValue = null, $output = 'html', $formatting = 'none', $reason = 'profile', $list_compare_types = 0 ))<5)
		$isCompleto = false;

	if(strlen($cbUser->getField( 'cb_cep', $defaultValue = null, $output = 'html', $formatting = 'none', $reason = 'profile', $list_compare_types = 0 ))<5)
		$isCompleto = false;

	if(strlen($cbUser->getField( 'cb_estado', $defaultValue = null, $output = 'html', $formatting = 'none', $reason = 'profile', $list_compare_types = 0 ))<5)
		$isCompleto = false;
	if(strlen($cbUser->getField( 'cb_municipio', $defaultValue = null, $output = 'html', $formatting = 'none', $reason = 'profile', $list_compare_types = 0 ))<3)
		$isCompleto = false;

	if(strlen($cbUser->getField( 'cb_bairro', $defaultValue = null, $output = 'html', $formatting = 'none', $reason = 'profile', $list_compare_types = 0 ))<3)
		$isCompleto = false;

	if(strlen($cbUser->getField( 'cb_endereco', $defaultValue = null, $output = 'html', $formatting = 'none', $reason = 'profile', $list_compare_types = 0 ))<3)
		$isCompleto = false;

	if(strlen($cbUser->getField( 'cb_estado', $defaultValue = null, $output = 'html', $formatting = 'none', $reason = 'profile', $list_compare_types = 0 ))<2)
		$isCompleto = false;

	return $isCompleto;
}

?>


