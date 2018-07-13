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


if (!($this->course_info["enroled"])&&$user->id!=0){
	JoomdleHelperContent::call_method ('enrol_user', $username, (int) $course_id, 5);
}

$objetoAulas = array();
$conteudo = "";
$todoConteudo = "";
$bibliotecaCurso =  array();
$arrayDadosDoCurso = array();


//metodo para descobrir se é um curso para o novo modelo
function isNovoCurso($param2){
	if($param2 == 9)
		return true;
}
$cont01 = 0;
$cont02 = 1;
$cont01aux = 0;
//separa os topicos do curso geral
//CONSTRUCAO DO OBJETO

if(isNovoCurso($course_id)==true){
	foreach ($this->mods as $todasAsAulas){
		//var_dump($todasAsAulas);
		$arrayDadosDoCurso["numeroDeTopicos"]++;
		//separa os topicos em aulas
		foreach ($todasAsAulas as $id1 => $aulaCompleta) {
			$cont02 = 0;
			$cont01aux = 0;
			//separa as aulas em atividades
			//die(var_dump($todasAsAulas));
			if(is_array($aulaCompleta))
			foreach ($aulaCompleta as $id6 => $resource) {
				if (($this->is_enroled) && ($resource['available'])){
					if (($resource['mod'] == 'label')&&(!strpos($direct_link, 'mod/certificate'))){
						//coloca label do video
						$arrayLabel = JoomdleHelperContent::call_method ('get_label', $resource['id']);
						//adicionado arquivo
						$cont01aux++;
						//$objetoAulas[$cont01]['titulo'] = $aulaCompleta;
						$objetoAulas[$cont01][$cont02++] = $arrayLabel['content'];
						if(!(strpos($arrayLabel['content'], 'mod/quiz')))
						
		$objetoAulas[$cont01][$cont02++] = "/media/joomdle/videos/".$course_id."/aula".$cont01.".mp4";
					}else{
						//analiza o link pra saber o que é
						$direct_link = JoomdleHelperSystem::get_direct_link ($resource['mod'], $course_id, $resource['id'], $resource['type']);
						if ($direct_link){
							if ($direct_link == 'none'){
								$mtype = JoomdleHelperSystem::get_mtype ($resource['mod']);
								$direct_link = "<li><a $target href=\"".$jump_url."&mtype=$mtype&id=".$resource['id']."&course_id=$course_id&create_user=0&Itemid=$itemid&redirect=$direct_link\" target=\"_blank\">".$resource['name']."</a></li>";
							}
							if (strpos($direct_link, 'mod/certificate')){
								$direct_link = JoomdleHelperSystem::get_direct_link($resource['mod'], $course_id, $resource['id'], $resource['type']);
								
								//adicionado certificado
								$cont01aux++;
								$objetoAulas[$cont01][$cont02++] = $direct_link;
							}
							if (strpos($direct_link, 'mod/resource')){
								if(!isset($objetoAulas[$cont01]['titulo']))
									$objetoAulas[$cont01]['titulo'] = $todasAsAulas['name'];
								//adicionado arquivo
								$contaux01++;
								
								$objetoAulas[$cont01][$cont02++] = "<li><a href='".$direct_link."' download>".$resource['name']."</a></li>";
								$objetoAulas[$cont01][$cont02++] = $direct_link;
							}}
						//coloca o link direto pro pdf
					}}
			}
		}
		$cont01++;
	}        
	//die(var_dump($objetoAulas));
	//die();
	//////RECORTES PARA FAZER O FOREACH ABAIXO
	//tira o array da biblioteca
	$bibliotecaCurso = array_shift($objetoAulas);
	$conteudoCertificado = array_pop($objetoAulas);
	//echo "XXXXXXXXXXXXXXXX";
	//var_dump($bibliotecaCurso);
	//var_dump($conteudoCertificado);die();
	//echo "XXXXXXXXXXXXXXXX";
	//var_dump($objetoAulas);die();
	//echo "XXXXXXXXXXXXXXXX";

	//CONSTRUCAO DOS ELEMENTOS DA INTERFACE
	$conteudoInterface = array();
	$menuAulas = "";
	if ( isset($_GET["youtube"]))
		$youtubeNoMenu = "&youtube=true";
	foreach ($objetoAulas as $id1 => $umaAula) {
		$seSelecionado = "";
		$manSelecionado = "";
		$vidSelecionado = "";
		$id1aux = $id1+1;
		if($_GET["aula"]==$id1)
			$seSelecionado = "selecionado";
		if(!isset($_GET["aula"])&&($id1aux==0)){
			$seSelecionado = "selecionado";
		}

		if(($seSelecionado=="selecionado")&&(($_GET["conteudo"]=="vid"||!isset($_GET["conteudo"]))))
			$vidSelecionado = "<i style='left: 6%' class='icon-chevron-left icon-white'></i><i style='left: 8%'class='icon-chevron-left icon-white'></i>";


		if(($seSelecionado=="selecionado")&&($_GET["conteudo"]=="man"))
			$manSelecionado = "<i style='left: 6%' class='icon-chevron-left icon-white'></i><i style='left: 8%'class='icon-chevron-left icon-white'></i>";


		$menuAulas .=  "
			<div class='panel panel-default ".$seSelecionado."'>  
			<div class='panel-heading'><h4 class='panel-title'>  ".$umaAula['titulo']."</h4></div>
			<div class='panel-body'> 
			<div class='btn-group' style='min-width:100%; text-align:center;'>

			<a href='?aula=".$id1.$youtubeNoMenu."&conteudo=vid'><button class='btn btn-info' style='min-width:85%;'>".$vidSelecionado."<i class='icon-play icon-white'></i> Assistir vídeo</button></a>  
			<a title='Baixar vídeo' href='".$umaAula[1]."' download><button class='btn btn-success''>
			<i class='icon-download-alt icon-white'></i></button></a> 
			</div>
			<br />

			<div class='btn-group' style='min-width:100%; text-align:center;'>
			<a href='?aula=".$id1.$youtubeNoMenu."&conteudo=man'> <button class='btn btn-info' style='min-width:85%; margin-top: 5px;'>".$manSelecionado."<i class=' icon-file icon-white'></i> Abrir manual</button></a> 
			<a title='Baixar manual' href='".$umaAula[3]."' download><button class='btn btn-success' style='margin-top: 5px;'>
			<i class='icon-download-alt icon-white'></i></button></a></div></div></div>";

	}
	//var_dump($bibliotecaCurso);die();


	if (isset($_GET["aula"])&&$_GET["aula"]=="prova") 
		echo $scriptJS;
	function getUsuarioNomeCertificado($cbUser){}


	//Selecionar os elementos da Pagina
	$elementosDaPagina = array();
	//if (!isset($_GET["aula"]))
	//	$_GET["aula"] = 1;
	if (!isset($_GET["conteudo"]))
		$_GET["conteudo"] = "vid";
	//ESCOLHE QUAL O VIDEO
	if (!isset($_GET["aula"])||(strlen($_GET["aula"])!="1"))
		$elementosDaPagina["conteudo"] = $objetoAulas[0][0];
	elseif($_GET["conteudo"]=="vid"&&$_GET["youtube"]==true)
		$elementosDaPagina["conteudo"] = "<video controls=''> <source src='".$objetoAulas[$_GET["aula"]][1]."' type='video/mp4'> your browser does not support the video tag. </video>";
	elseif($_GET["conteudo"]!=man)
		$elementosDaPagina["conteudo"] = $objetoAulas[$_GET["aula"]][0];
	else
		$elementosDaPagina["conteudo"] = "<div id='embeddedPdfContainer'><object data='".$objetoAulas[$_GET['aula']][3]."' id='embeddedPdf'  style='min-width: 98%; height:82vh;' type='application/pdf'></object></div>";

	//SE O CONTEUDO FOR PROVA
	if($_GET["aula"]=="prova")
		$elementosDaPagina["conteudo"] = $conteudoCertificado[0];


	//var_dump($elementosDaPagina);die();


	//COMEÇA INTERFACE
	?>


		<div class="parteEsquerda span9">
		<div class="span12 cabecalho-aula">
		<h2><?php echo $this->course_info['fullname']; ?>: Módulo <?php echo ($_GET["aula"]+1); ?></h2>
		<div class="span12">
		<div class="topico">
		<div class="video" id="videoYT">

		<!- CONTEUDO CENTRAL ->

		<?php
		if(!isset($_GET["aula"]))
			$_GET["aula"]=0;
	echo $elementosDaPagina["conteudo"];

	if($_GET["conteudo"]=="vid"||!isset($_GET["conteudo"]))
		if(isset($_GET["youtube"])){
			echo "<a href='?aula=".$_GET['aula']."&conteudo=".$_GET['conteudo']."' class='trocar-player'>Para voltar ao player do Youtube, Clique aqui.</a>";
		}else{
			echo "<a href='?aula=".$_GET['aula']."&conteudo=".$_GET['conteudo']."&youtube=true' class='trocar-player'>Problemas para visualizar o video?</a>";
		}
	?>



		</div>
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
		<?php foreach($bibliotecaCurso as $umArquivo) 
		if (strpos(''.$umArquivo, 'href')==true ) 
			echo $umArquivo; ?>
				</ul>
				</div>
				<div id="tab-3" class="tab-content">
				<ul>
				<!-- Aba de certificação -->
				<!-- 2 conteudos, para logados e nao logados -->
				<?php $user = JFactory::getUser();
	///////////////////IF
	if ( $user->get('guest')  ) { //$user->get('guest') retorna '1' caso seja visitante ?>

		<div class="panel panel-default">
			<div class="panel-heading"><h4 class="panel-title">Olá Visitante</h4></div>
			<div class="panel-body">
			<!-- um link para ir pra prova -->
			<p>Nossa certificação é online e gratuíta. Para mais informações você deve entrar no site, caso você não tenha cadastro <a href="http://telelab.aids.gov.br/index.php/cadastro">clique aqui</a> </p>
			<div class="alert alert-success" style="padding-bottom: 1cm;">
			<h4 class="texto-certificacao text-center">Já sou cadastrado</h4>
			<?php
			$zone = "login";
		$modules =& JModuleHelper::getModules($zone);
		foreach ($modules as $module){
			echo JModuleHelper::renderModule($module);
		}
		?> </div></div></div>

			<?php } else {
				$cpfTeste = $cbUser->getField( 'cb_cpf', $defaultValue = null, $output = 'html', $formatting = 'none', $reason = 'profile', $list_compare_types = 0 );
				$cpfTeste2 = strlen(preg_replace("/[^0-9]/","",$cpfTeste));
				if($course_id==8||$course_id==12||$course_id==17||$course_id==18||$course_id==19)
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
							<div class="panel panel-default">
							<div class="panel-heading"><h4 class="panel-title">Verifique seus dados</h4></div>
							<div class="panel-body">Antes de solicitar o certificado confira os seus dados.  No cadastro consta que seu nome completo é <b>"<? echo $cbUser->getField( 'firstname', $defaultValue = null, $output = 'html', $formatting = 'none', $reason = 'profile', $list_compare_types = 0 )." ".$cbUser->getField( 'lastname', $defaultValue = null, $output = 'html', $formatting = 'none', $reason = 'profile', $list_compare_types = 0 ); ?>"</b> e seu CPF <b>"<?echo $cbUser->getField( 'cb_cpf', $defaultValue = null, $output = 'html', $formatting = 'none', $reason = 'profile', $list_compare_types = 0 ); ?>"</b>. Preenchimento incorreto pode <b>invalidar seu certificado</b>. Você pode <a href='/index.php/cb-profile-edit'>corrigir estes dados clicando aqui.</a>  </div>
							</div>

							<div class="panel panel-default">
							<div class="panel-heading"><h4 class="panel-title">Avaliação</h4></div>
							<div class="panel-body">
							<!-- um link para ir pra prova -->
							<p>O primeiro passo para obter a certificação é realizar a avaliação. Se você quiser saber mais, acesse nossa <a href="http://telelab.aids.gov.br/index.php/2013-11-14-17-44-09/item/122">página de dúvidas sobre a certificação.</a> </p>
							<a class='btn btn-info btn-block' href='?aula=prova'><i class='icon-pencil icon-white'></i> Realizar avaliação</a></div>
							<!-- aqui fica null quando o certificado não está visivel, equivalente a quando o usuario não possui a nota suficientes -->
							</div>
							<div class="panel panel-default">
							<div class="panel-heading"><h4 class="panel-title">Certificado</h4></div>
							<div class="panel-body">
							<?php
							if(is_null($conteudoCertificado[1]))
								echo "<p>Para concluir a certificação você precisa de uma nota igual ou superior a 7,00 na avaliação.</p><p class=\"text-center \"> <img src=\"http://devtelelab.sites.ufsc.br/images/icon-certificado-off.jpg\" alt=\"Certificado\"></p>";
							else
								if($course_id==15)
									echo "<p>Parabéns por ser aprovado no curso! Clicando na imagem abaixo você encontra uma cópia do seu certificado.</p> <p>Nos ajude a melhorar, responda nossa <a href='https://goo.gl/forms/Ssm0XixBFsm2DPlF2'>pesquisa de satisfação</a>, sua opinião é muito importante!</p>"."<p class=\"text-center \"> <a target=\"_blank\" href=\"".$conteudoCertificado[1]."\"><img src=\"http://devtelelab.sites.ufsc.br/images/icon-certificado-on.jpg\" alt=\"Certificado\"></a></p>";
								else
									echo "<div><p class='certificado'>Parabéns! Na imagem abaixo você encontra uma cópia do seu certificado.</p>"."<p class=\"text-center \"> <a target=\"_blank\" href=\"".$conteudoCertificado[1]."\"><img src=\"http://devtelelab.sites.ufsc.br/images/icon-certificado-on.jpg\" alt=\"Certificado\"></a></p>"; 


						?> </div>  </div>
							</div>
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


}else{
	//this->mods sao as aulas vindo do moodle
	//retira a primeira 'aula'
	$aulaBiblioteca = array_shift($this->mods);
	$aulaCertificacao = array_shift($this->mods);
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
	//trabalha view da certificacao

	//pega os arquivos da 'aula'
	$resources = $aulaCertificacao['mods'];
	$certificacaoCurso =  array();
	foreach ($resources as $id => $resource) {
		//pula o componente quiz (questionario) porque ele vai ir por iframe num label
		if($resource['mod'] == 'quiz' || $resource['mod'] == 'scorm')
			continue;
		//só pra fazer o link do certificado
		if (($this->course_info["enroled"]) && ($resource['available'])){
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

		<div class="panel panel-default">
			<div class="panel-heading"><h4 class="panel-title">Olá Visitante</h4></div>
			<div class="panel-body">
			<!-- um link para ir pra prova -->
			<p>Nossa certificação é online e gratuíta. Para mais informações você deve entrar no site, caso você não tenha cadastro <a href="http://telelab.aids.gov.br/index.php/cadastro">clique aqui</a> </p>
			<div class="alert alert-success" style="padding-bottom: 1cm;">
			<h4 class="texto-certificacao text-center">Já sou cadastrado</h4>
			<?php
			$zone = "login";
		$modules =& JModuleHelper::getModules($zone);
		foreach ($modules as $module){
			echo JModuleHelper::renderModule($module);
		}
		?> </div></div></div>

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
							<div class="panel panel-default">
							<div class="panel-heading"><h4 class="panel-title">Verifique seus dados</h4></div>
							<div class="panel-body">Antes de solicitar o certificado confira os seus dados.  No cadastro consta que seu nome completo é <b>"<? echo $cbUser->getField( 'firstname', $defaultValue = null, $output = 'html', $formatting = 'none', $reason = 'profile', $list_compare_types = 0 )." ".$cbUser->getField( 'lastname', $defaultValue = null, $output = 'html', $formatting = 'none', $reason = 'profile', $list_compare_types = 0 ); ?>"</b> e seu CPF <b>"<?echo $cbUser->getField( 'cb_cpf', $defaultValue = null, $output = 'html', $formatting = 'none', $reason = 'profile', $list_compare_types = 0 ); ?>"</b>. Preenchimento incorreto pode <b>invalidar seu certificado</b>. Você pode <a href='/index.php/cb-profile-edit'>corrigir estes dados clicando aqui.</a>  </div>
							</div>

							<div class="panel panel-default">
							<div class="panel-heading"><h4 class="panel-title">Avaliação</h4></div>
							<div class="panel-body">
							<!-- um link para ir pra prova -->
							<p>O primeiro passo para obter a certificação é realizar a avaliação. Se você quiser saber mais, acesse nossa <a href="http://telelab.aids.gov.br/index.php/2013-11-14-17-44-09/item/122">página de dúvidas sobre a certificação.</a> </p>
							<a class='btn btn-info btn-block' href='?aula=prova'>Abrir prova</a></div>
							<!-- aqui fica null quando o certificado não está visivel, equivalente a quando o usuario não possui a nota suficientes -->
							</div>
							<div class="panel panel-default">
							<div class="panel-heading"><h4 class="panel-title">Certificado</h4></div>
							<div class="panel-body">
							<?php
							if(is_null($certificacaoCurso["certificado"]))
								echo "<p>Para concluir a certificação você precisa de uma nota igual ou superior a 7,00 na avaliação.</p><p class=\"text-center \"> <img src=\"http://telelab.aids.gov.br/images/icon-certificado-off.jpg\" alt=\"Certificado\"></p>";
							else
								if($course_id==15)
									echo "<p>Parabéns por ser aprovado no curso! Clicando na imagem abaixo você encontra uma cópia do seu certificado.</p> <p>Nos ajude a melhorar, responda nossa <a href='https://goo.gl/forms/Ssm0XixBFsm2DPlF2'>pesquisa de satisfação</a>, sua opinião é muito importante!</p>";
								else
									echo "<div><p class='certificado'>Parabéns! Na imagem abaixo você encontra uma cópia do seu certificado.</p>"; 

						echo $certificacaoCurso["certificado"];
						?> </div>  </div>
							</div>
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
						}}

?>

