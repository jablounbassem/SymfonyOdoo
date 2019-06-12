<?php

namespace Odoo\ClientBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class InvoiceController extends Controller
{
    public function indexAction()
    {
        return $this->render('OdooClient::index.html.twig');
    }

    public function listInvoiceAction()
    {
       // $invoice = $odooService->search('account.invoice');
        $p=$odooService->search('account.invoice');
        $opt=array(
            'invoice_status' => 'invoiced'
        );
        $z=$odooService->update('purchase.order',1,$opt);
        dump($p).die();
        return $this->render('@OdooClient/Purchase/list.html.twig', array(
            "purchases" => $purchases
        ));
    }
    public function CreateInvoiceFormAction(Request $request)
    {
        $odooService = $this->get('odoo_service');
        $data = $request->request->all();
        if (isset($data['id'])){
            $purchaseOrderService = $this->get('purchase_order_service');
            $purchase=$purchaseOrderService->getPurchase($data['id']);
            if (count($purchase) != 0) {
                $purchaseOrderService = $this->get('purchase_order_service');
                if (count($purchase) == 0) {
                    return $this->render('@OdooClient/Invoice/provider/add.html.twig');
                } else {
                    $commandeLines = $purchaseOrderService->getPurchaseOrder($data['id']);
                    if (count($commandeLines)==0){
                        return $this->redirect($this->generateUrl('odoo_connector_purchaseOrderLists'));
                    }else{

                        $option[0] = array('supplier', '=', true);
                        $vendors = $odooService->search('res.partner', $option);
                        $commercialPartnerId = $purchase[0]['partner_id'][0];
                        $option[0] = array('partner_id', '=', $commercialPartnerId);
                        $bankAccount = $odooService->search('res.partner.bank',$option);
                       // dump($bankAccount).die();
                        $articles = $odooService->search('product.product');
                        $option[0] = array('type_tax_use', '=', 'purchase');
                        $taxes = $odooService->search('account.tax', $option);
                        return $this->render('@OdooClient/Invoice/provider/add_with_origin_document.html.twig', array(
                            "purchase"=>$purchase[0],
                            "articles" => $articles,
                            "taxes" => $taxes,
                            "vendors" => $vendors,
                            "bankAccount" => $bankAccount,
                            "commandeLines"=>$commandeLines
                        ));

                    }
        }

            }

        }else{
            $option[0] = array('supplier', '=', true);
            $vendors = $odooService->search('res.partner', $option);
            $articles = $odooService->search('product.product');
            $option[0] = array('type_tax_use', '!=', 'none');
            $taxes = $odooService->search('account.tax', $option);
            return $this->render('@OdooClient/Invoice/provider/add.html.twig',array(
                "vendors" => $vendors,
                "articles" => $articles,
                "taxes" => $taxes,

            ));
        }

    }

    public function testAction(Request $request)
    {
        $data = $request->request->all();
        dump($data).die();
    }



}
