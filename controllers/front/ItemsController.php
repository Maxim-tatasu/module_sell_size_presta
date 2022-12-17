<?php 
//namespace Controllers/Front/ItemsController;
class CountItemsModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $db = \Db::getInstance();
        $sql = new DbQuery();
        $sql->select('COUNT(id_product)');
        $sql->from('product', 'p');
        $sql->where('price >= 10 AND price <= 20');
        return $db->getValue($sql);
    }
}

?>