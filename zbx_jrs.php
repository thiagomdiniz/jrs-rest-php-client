<?php

require_once dirname(__FILE__).'/include/config.inc.php';
#require_once dirname(__FILE__).'/include/hostgroups.inc.php';
#require_once dirname(__FILE__).'/include/hosts.inc.php';
#require_once dirname(__FILE__).'/include/forms.inc.php';
require_once dirname(__FILE__).'/jasper/jrs-rest-client/autoload.dist.php';

$page['title'] = 'Jasper';
$page['file'] = 'zbx_jrs.php';

/*
 * Permissions
 */
if (getRequest('groupid') && !isReadableHostGroups([getRequest('groupid')])) {
	access_deny();
}
if (getRequest('hostid') && !isReadableHosts([getRequest('hostid')])) {
	access_deny();
}

/*
 * Jasper Server Connection
 */
use Jaspersoft\Client\Client;
try {
    $c = new Client(
                "http://192.168.100.5:8080/jasperserver",
                "jasperadmin",
                "jasperadmin"
        );

    $info = $c->serverInfo();
}
catch (Exception $e) {
    echo "<p>Ocorreu um problema na conexão com o JasperServer: <br>" . $e->getMessage() . "</p>";
}

// Get Parameters for generate reports
if (getRequest('uri')) {
    $uri = getRequest('uri');
    $output = getRequest('output');
    #echo "<p>$uri - $output</p>";

    $controls = $_GET;
    unset($controls['uri']);
    unset($controls['output']);

    try {
        if($controls) {
#echo "com controle";
            $report = $c->reportService()->runReport($uri, $output, null, null, $controls);
        } else {
#echo "sem contrle";
            $report = $c->reportService()->runReport($uri, $output);
        }
    }
    catch (Exception $e) {
        echo "Ocorreu um problema na execução do relatório:<br>" . $e->getMessage();
        exit();
    }

    if($output == "pdf") {
#echo "pdf";
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename=report.pdf');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . strlen($report));
        header('Content-Type: application/pdf');
    }

    echo $report;
    exit();
} else {
    require_once dirname(__FILE__).'/include/page_header.php';
}

// Page header
#echo "<h1>Relatórios personalizados</h1><hr>";
echo "<div class=\"header-title table\"><div class=\"cell\"><h1>Jasper Reports</h1></div></div>";

use Jaspersoft\Service\RepositoryService;
use Jaspersoft\Service\Criteria\RepositorySearchCriteria;

// Search for specific items in repository
$criteria = new RepositorySearchCriteria();
#$criteria->q = "Zabbix";
$criteria->folderUri = "/Zabbix_Reports";
$criteria->type = "reportUnit";

$results = $c->repositoryService()->searchResources($criteria);

foreach ($results->items as $res) {
    echo "<p><form method=\"get\" action=\"zbx_jrs.php\" target=\"_blank\"><table class=\"list-table\">";
    echo "<tr><td width=\"100px\">Relatório:</td><td>" . $res->label . "</td></tr>";
    #echo "<tr><td width=\"100px\">Path:</td><td> $res->uri </td><tr>";
    echo "<tr><td width=\"100px\">Descrição:</td><td> $res->description </td></tr>";
    $input_controls = $c->reportService()->getReportInputControls($res->uri);
    if($input_controls) { echo "<tr><td width=\"100px\">Parametros:</td><td>"; }
    foreach($input_controls as $ic) {
	    #printf('Key: %s <br />', $ic->id);
	    #echo "<td>Nome: $ic->label , Tipo: $ic->type , Obrigatório: $ic->mandatory , Tipo de dado: $ic->dataType</td>";
        echo $ic->label . (($ic->mandatory == 1) ? "*: " : ": ") . 
            (($ic->dataType == "text") ? "<input type=\"text\" name=\"$ic->label\" value=\"texto\">" : "") .
            (($ic->dataType == "date") ? "<input type=\"text\" name=\"$ic->label\" value=\"data\">" : "");
    }
    if($input_controls) { echo "</td></tr>"; }
    echo "<tr><td colspan=\"2\"><select name=\"output\"><option value=\"html\">HTML</option><option value=\"pdf\">PDF</option></select>   <button type=\"submit\">Executar relatório</button></td></tr></table>";
    echo "<input type=\"hidden\" value=\"" . $res->uri . "\" name=\"uri\">";
    echo "</form></p>";
}

// Display JasperServer connection info footer
echo "<div class=\"grey\"><p>Versão do JasperServer: " . $info['version'] . " " . $info['edition'] . ". (" . $c->serverUrl . ")</p>";
echo "<p>Formato de data: " . $info['dateFormatPattern'] . "</p>";
echo "<p>Formato de data/hora: " . $info['datetimeFormatPattern'] . "</p></div>";

require_once dirname(__FILE__).'/include/page_footer.php';
