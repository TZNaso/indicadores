<?php

class OrganogramaController extends Zend_Controller_Action
{

    public function init()
    {}

    public function indexAction()
    {}

    public function getCoordenacoesAction()
    {
        $objMapper = new Application_Model_OrganogramaMapper();
        $arrRetorno['legends'] = $objMapper->countTotal();
        $arrRetorno['coords'] = $objMapper->getCoords();
        $arrRetorno['legRoot'] = $objMapper->legRoot();
        echo json_encode($arrRetorno);
        die();
    }

    public function getSubCoordenacoesAction()
    {
        $coord = $this->_request->getParam('coord');
        $objMapper = new Application_Model_OrganogramaMapper();
        $arrRetorno['root'] = $objMapper->getRoot($coord);
        $tmp = $objMapper->getNuarea($coord);
        $arrRetorno['legends'] = $objMapper->getLegend($tmp[0]['nu_area']);
        $arrRetorno['coords'] = $objMapper->getSubCoords($coord);
        $arrRetorno['funcionarios'] = $objMapper->getFuncCoord($coord);
        echo json_encode($arrRetorno);
        die();
    }
}
