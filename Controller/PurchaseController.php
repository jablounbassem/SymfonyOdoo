<?php

namespace Odoo\ClientBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class PurchaseController extends Controller
{
    public function indexAction()
    {
        return $this->render('@OdooClient:index.html.twig');
    }

    public function listPurchaseAction()
    {
        $odooService = $this->get('odoo_service');
        $purchases = $odooService->search('purchase.order');
        return $this->render('@OdooClient/Purchase/list.html.twig', array(
            "purchases" => $purchases
        ));
    }

    public function addPurchaseFormAction()
    {

        $odooService = $this->get('odoo_service');
        $option[0] = array('supplier', '=', true);
        $vendor = $odooService->search('res.partner', $option);
        $articles = $odooService->search('product.product');
        $opt[0] = array('type_tax_use', '=', 'purchase');
        $taxes = $odooService->search('account.tax', $opt);
        return $this->render('@OdooClient/Purchase/add.html.twig', array(
            "vendors" => $vendor,
            "articles" => $articles,
            "taxes" => $taxes,
        ));
    }

    public function addPurchaseAction(Request $request)
    {
        $purchaseOrderService = $this->get('purchase_order_service');
        $data = $request->request->all();
        $odooService = $this->get('odoo_service');
        $purchase=$purchaseOrderService->addPurchase($data);
        return $this->redirectToRoute('odoo_connector_getPurchase', ['id' => $purchase]);

    }

    public function getPurchaseAction($id)
    {
        $odooService = $this->get('odoo_service');
        $purchaseOrderService = $this->get('purchase_order_service');
        $purchase = $purchaseOrderService->getPurchase($id);
        if (count($purchase) == 0) {
            return $this->redirect($this->generateUrl('odoo_connector_purchaseOrderLists'));
        } else {
            $odooService = $this->get('odoo_service');
            $commandeLines = $purchaseOrderService->getPurchaseOrder($id);
            $company=$purchaseOrderService->getCompanyInformations();
            $option[0]=array('id',"=",$purchase[0]['partner_id'][0]);
            $vendor = $odooService->search('res.partner', $option);
        }
        return $this->render("@OdooClient/Purchase/purchase.html.twig", array(
            'purchase' => $purchase[0],
            'commandeLines' => $commandeLines,
            'company'=>$company,
            'vendor'=>$vendor[0]
        ));


    }

    public function updatePurchaseFormAction($id)
    {
        $odooService = $this->get('odoo_service');
        $purchaseOrderService = $this->get('purchase_order_service');
        $purchase = $purchaseOrderService->getPurchase($id);
        if (count($purchase) == 0) {
            return $this->redirect($this->generateUrl('odoo_connector_purchaseOrderLists'));
        } else {
            $commandeLines = $purchaseOrderService->getPurchaseOrderCommandeLine($id);
            $option[0] = array('supplier', '=', true);
            $vendors = $odooService->search('res.partner', $option);
            $articles = $odooService->search('product.product');
            $opt[0] = array('type_tax_use', '=', 'purchase');
            $taxes = $odooService->search('account.tax', $opt);
            return $this->render("@OdooClient/Purchase/update.html.twig", array(
                'purchase' => $purchase[0],
                'vendors' => $vendors,
                'commandeLines' => $commandeLines,
                "articles" => $articles,
                "taxes" => $taxes,
            ));
        }

    }

    public function updatePurchaseAction(Request $request)
    {

        $purchaseService = $this->get('purchase_order_service');
        $odooService = $this->get('odoo_service');
        $data = $request->request->all();
        if (isset($data['purchase_id'])) {
            $purchaseService->updatePurchaseOrder($data);
            return $this->redirectToRoute('odoo_connector_getPurchase', ['id' => $data['purchase_id']]);

        }

        return $this->forward('OdooClientBundle:Purchase:listPurchase');


    }

    public function getProvidersAction()
    {
        $odooService = $this->get('odoo_service');
        $option[0] = array('is_company', '=', true);
        $option[1] = array('supplier', '=', true);
        $providers = $odooService->search('res.partner', $option);
        $tab = array();
        for ($i = 0; $i < count($providers); $i++) {
            if (count($providers[$i]['category_id']) > 0) {
                for ($j = 0; $j < count($providers[$i]['category_id']); $j++) {
                    $id = (int)$providers[$i]['category_id'][$j];
                    $opt[0] = array('id', '=', $id);
                    $cat = $odooService->search('res.partner.category', $opt)[0]['display_name'];
                    $providers[$i]['categories'][$j] = $cat;
                }
            }

            // array_push($tab, $cl);
        }
        //dump($providers).die();
        //dump($odooService->fields('res.partner.category')).die();
        return $this->render('OdooConnectorBundle:Contact:Provider.html.twig',
            array(
                "providers" => $providers
            )
        );
    }

    public function updatePurchaseOrderStateAction($id,$state)
    {
        $purchaseService = $this->get('purchase_order_service');
        $purchase = $purchaseService->getPurchase($id);
        if (count($purchase) == 0) {
            return $this->redirect($this->generateUrl('odoo_connector_purchaseOrderLists'));
        } else {
            $purchaseService->updatePurchaseOrderState($id,$state);
            return $this->redirectToRoute('odoo_connector_getPurchase', ['id' => $id]);

        }

    }
    public function CreateInvoiceFormAction($id)
    {
        $purchaseService = $this->get('purchase_order_service');
        $purchase = $purchaseService->getPurchase($id);
        if (count($purchase) == 0) {
            return $this->redirect($this->generateUrl('odoo_connector_purchaseOrderLists'));
        } else {
            $purchaseService->updatePurchaseOrderState($id,$state);
            return $this->redirectToRoute('odoo_connector_getPurchase', ['id' => $id]);

        }

    }


}
