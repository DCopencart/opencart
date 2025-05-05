<?php
header('Cache-Control: no-cache, no-store');
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', 900);
ini_set('error_reporting', E_ALL);
include DIR_SYSTEM.'library/product_export_import/PHPExcel.php';
class ControllerExtensionProductexport extends Controller {
	
	private $error = array();
	
	public function __construct($registry){
		 parent::__construct( $registry );
		$path = 'exceltool/';
		if (!is_dir(DIR_UPLOAD . $path)) {
			mkdir(DIR_UPLOAD . $path, 0777);
		}
		define('DIR_EXCEL', DIR_UPLOAD . $path);
	}
	
	public function index(){
		$this->load->language('catalog/product');
		
		$this->load->language('extension/product_export');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/product');

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_form'] = !isset($this->request->get['product_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
		$data['text_default'] = $this->language->get('text_default');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		
		$data['entry_store'] = $this->language->get('entry_store');
		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_price'] = $this->language->get('entry_price');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_quantity'] = $this->language->get('entry_quantity');
		$data['entry_model'] = $this->language->get('entry_model');
		$data['entry_language'] = $this->language->get('entry_language');
		$data['entry_stock_status'] = $this->language->get('entry_stock_status');
		$data['entry_limit'] = $this->language->get('entry_limit');
		$data['entry_categories'] = $this->language->get('entry_categories');
		$data['entry_manufacturer'] = $this->language->get('entry_manufacturer');
		$data['entry_pricerange'] = $this->language->get('entry_pricerange');
		$data['entry_quantityrange'] = $this->language->get('entry_quantityrange');
		$data['tab_producttab'] = $this->language->get('tab_producttab');
		$data['tab_reviewdata'] = $this->language->get('tab_reviewdata');
		$data['entry_approved'] = $this->language->get('entry_approved');
		$data['entry_product_id'] = $this->language->get('entry_product_id');
		$data['select_categories'] = $this->language->get('select_categories');
		$data['select_stock_status'] = $this->language->get('select_stock_status');
		$data['select_manufacture'] = $this->language->get('select_manufacture');
		$data['select_status'] = $this->language->get('select_status');
		$data['product_image'] = $this->language->get('product_image');
		$data['export_format'] = $this->language->get('export_format');
		
		$data['help_keyword'] = $this->language->get('help_keyword');
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		$data['cancel'] = $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL');
		$data['button_export'] = $this->language->get('button_export');
		
		$data['tab_export'] = $this->language->get('tab_export');
		
		$data['user_token'] = $this->session->data['user_token'];
		
		if (isset($this->request->get['filter_name'])) {
			$data['filter_name'] = $this->request->get['filter_name'];
		} else {
			$data['filter_name'] = null;
		}

		if (isset($this->request->get['filter_model'])) {
			$data['filter_model'] = $this->request->get['filter_model'];
		} else {
			$data['filter_model'] = null;
		}

		if (isset($this->request->get['filter_price'])) {
			$data['filter_price'] = $this->request->get['filter_price'];
		} else {
			$data['filter_price'] = null;
		}

		if (isset($this->request->get['filter_quantity'])) {
			$data['filter_quantity'] = $this->request->get['filter_quantity'];
		} else {
			$data['filter_quantity'] = null;
		}

		if (isset($this->request->get['filter_status'])) {
			$data['filter_status'] = $this->request->get['filter_status'];
		} else {
			$data['filter_status'] = null;
		}
		
		if (isset($this->request->get['filter_limit'])) {
			$data['filter_limit'] = $this->request->get['filter_limit'];
		} else {
			$data['filter_limit'] = $this->config->get('config_limit_admin');
		}
		
		if (isset($this->error['warning'])){
			$data['error_warning'] = $this->error['warning'];
		}else{
			$data['error_warning'] = '';
		}
		
		$url = '';
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL')
		);
		
		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		$this->load->model('setting/store');
		$data['stores'] = $this->model_setting_store->getStores();
		
		$this->load->model('localisation/stock_status');
		$data['stock_statuses'] = $this->model_localisation_stock_status->getStockStatuses();
		
		$this->load->model('catalog/category');
		$data['categories'] = $this->model_catalog_category->getCategories();
		
		$this->load->model('catalog/manufacturer');
		$data['manufacturers'] = $this->model_catalog_manufacturer->getManufacturers();
		
		$this->load->model('extension/product_export');
		$data['maxproduct_id'] = $this->model_extension_product_export->getmaxproducts();
		$data['miniproduct_id'] = $this->model_extension_product_export->getminiproducts();
		
		$data['customfeilds'] = $this->model_extension_product_export->customfeilds();
	
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/product_export', $data));
	}
	
	public function export(){
		$this->load->model('catalog/product');
		$this->load->model('extension/product_export');
		$this->load->model('setting/store');
		if(!empty($this->request->post['filter_language_id'])){
			$language_id = $this->request->post['filter_language_id'];
		}else{
			$language_id = $this->config->get('config_language_id');
		}
		
		if (isset($this->request->post['product'])) {
			$filter_product = $this->request->post['product'];
		} else {
			$filter_product = array();
		}

		if (isset($this->request->post['filter_model'])) {
			$filter_model = $this->request->post['filter_model'];
		} else {
			$filter_model = null;
		}

		if (isset($this->request->post['filter_price_to'])) {
			$filter_price_to = $this->request->post['filter_price_to'];
		} else {
			$filter_price_to = null;
		}
		
		if (isset($this->request->post['filter_price_form'])) {
			$filter_price_form = $this->request->post['filter_price_form'];
		} else {
			$filter_price_form = null;
		}

		if (isset($this->request->post['filter_quantity_to'])) {
			$filter_quantity_to = $this->request->post['filter_quantity_to'];
		} else {
			$filter_quantity_to = null;
		}
		
		if (isset($this->request->post['filter_quantity_form'])) {
			$filter_quantity_form = $this->request->post['filter_quantity_form'];
		} else {
			$filter_quantity_form = null;
		}

		if (isset($this->request->post['filter_status'])) {
			$filter_status = $this->request->post['filter_status'];
		} else {
			$filter_status = '*';
		}
		
		if (isset($this->request->post['filter_store'])) {
			$filter_store = $this->request->post['filter_store'];
		} else {
			$filter_store = null;
		}
		
		if (isset($this->request->post['filter_stock_status'])) {
			$filter_stock_status = $this->request->post['filter_stock_status'];
		} else {
			$filter_stock_status = null;
		}
		
		if (isset($this->request->post['product_category'])) {
			$product_category = $this->request->post['product_category'];
		} else {
			$product_category = array();
		}
		
		if (isset($this->request->post['manufacturers'])) {
			$manufacturers = $this->request->post['manufacturers'];
		} else {
			$manufacturers = null;
		}
		
		if (isset($this->request->post['filter_product_id'])) {
			$filter_product_id = $this->request->post['filter_product_id'];
		} else {
			$filter_product_id = null;
		}
		
		if (isset($this->request->post['filter_endproduct_id'])) {
			$filter_endproduct_id = $this->request->post['filter_endproduct_id'];
		} else {
			$filter_endproduct_id = null;
		}
		
		if (isset($this->request->post['filter_pimage'])) {
			$filter_pimage = $this->request->post['filter_pimage'];
		} else {
			$filter_pimage = null;
		}
		
		if (isset($this->request->post['filter_eformat'])) {
			$filter_eformat = $this->request->post['filter_eformat'];
		} else {
			$filter_eformat = null;
		}
		/*New 06-02-2017*/
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
		/*New 06-02-2017*/
		
		if (!empty($this->request->post['filter_limit'])) {
			$filter_limit = $this->request->post['filter_limit'];
		} else {
			$filter_limit = 0;
		}
		
		
		$filter_data1=array(
			'filter_product'	  	=> $filter_product,
			'filter_model'	  		=> $filter_model,
			'filter_price_to'	  	=> $filter_price_to,
			'filter_price_form'	    => $filter_price_form,
			'filter_quantity_to' 	=> $filter_quantity_to,
			'filter_quantity_form'  => $filter_quantity_form,
			'filter_status'   		=> $filter_status,
			'filter_language_id'	=> $language_id,
			'filter_store'			=> $filter_store,
			'product_category'		=> $product_category,
			'manufacturers'			=> $manufacturers,
			'filter_stock_status'   => $filter_stock_status,
			'filter_product_id'     => $filter_product_id,
			'filter_eformat'  		=> $filter_eformat,
			'filter_pimage'  		=> $filter_pimage,
			'filter_endproduct_id'  => $filter_endproduct_id,
		);
		
		
		$product_total = $this->model_extension_product_export->getTotalProducts($filter_data1);
		
		if($filter_limit){
		   $filter_limit = $product_total/$filter_limit;
		}
		
		$filter_data=array(
			'filter_product'	  	=> $filter_product,
			'filter_model'	  		=> $filter_model,
			'filter_price_to'	  	=> $filter_price_to,
			'filter_price_form'	    => $filter_price_form,
			'filter_quantity_to' 	=> $filter_quantity_to,
			'filter_quantity_form'  => $filter_quantity_form,
			'filter_status'   		=> $filter_status,
			'filter_language_id'	=> $language_id,
			'filter_store'			=> $filter_store,
			'product_category'		=> $product_category,
			'manufacturers'			=> $manufacturers,
			/*New 06-02-2017 replace*/
			'start'                 => ($page - 1) * $filter_limit,
			'limit'           		=> $filter_limit,
			/*New 06-02-2017*/
			'filter_stock_status'   => $filter_stock_status,
			'filter_product_id'     => $filter_product_id,
			'filter_eformat'  		=> $filter_eformat,
			'filter_pimage'  		=> $filter_pimage,
			'filter_endproduct_id'  => $filter_endproduct_id,
		);
	
		$products = $this->model_extension_product_export->getProducts($filter_data);
		
		if($filter_eformat == 'xml'){
			$doc = new DOMDocument();
			$doc->formatOutput = true;
			$r = $doc->createElement("PRODUCTS");
			$doc->appendChild( $r );
			$i=0;
			foreach($products as $product){
				$storeinfo = $this->model_setting_store->getStore($product['store_id']);
				if($storeinfo){
					$store = $storeinfo['name'];
				}else{
					$store = 'default';
				}
				$i++;
				$PRODUCT = $doc->createElement("Product");
				
				$PRODUCTID = $doc->createElement("product_id");
				$PRODUCTID->appendChild($doc->createTextNode($product['product_id']));
				$PRODUCT->appendChild($PRODUCTID);
				
				$language = $doc->createElement("Language");
				$language->appendChild($doc->createTextNode($product['language']));
				$PRODUCT->appendChild($language);
				
				$p_store = $doc->createElement("Store");
				$p_store->appendChild($doc->createTextNode($store));
				$PRODUCT->appendChild($p_store);
				
				$name = $doc->createElement("Product_Name");
				$name->appendChild($doc->createTextNode(html_entity_decode($product['name'])));
				$PRODUCT->appendChild($name);
				
				$model = $doc->createElement("Model");
				$model->appendChild($doc->createTextNode($product['model']));
				$PRODUCT->appendChild($model);
				
				$p_description = $doc->createElement("Product_Description");
				$p_description->appendChild($doc->createTextNode(html_entity_decode($product['description'])));
				$PRODUCT->appendChild($p_description);
				
				$meta_title = $doc->createElement("Meta_Title");
				$meta_title->appendChild($doc->createTextNode($product['meta_title']));
				$PRODUCT->appendChild($meta_title);
				
				$meta_description = $doc->createElement("Meta_Description");
				$meta_description->appendChild($doc->createTextNode($product['meta_description']));
				$PRODUCT->appendChild($meta_description);
				
				$meta_keyword = $doc->createElement("Meta_Keyword");
				$meta_keyword->appendChild($doc->createTextNode($product['meta_keyword']));
				$PRODUCT->appendChild($meta_keyword);
				
				$tag = $doc->createElement("Tag");
				$tag->appendChild($doc->createTextNode($product['tag']));
				$PRODUCT->appendChild($tag);
				
				if($filter_pimage == 'yes'){
					if($product['image'] != '' || file_exists(HTTP_CATALOG.'image/'.$product['image'])){
						$main_image = $doc->createElement("Main_Image");
						$main_image->appendChild($doc->createTextNode(HTTP_CATALOG.'image/'.$product['image']));
						$PRODUCT->appendChild($main_image);
					}
					else{
						$main_image = $doc->createElement("Main_Image");
						$main_image->appendChild($doc->createTextNode(''));
						$PRODUCT->appendChild($main_image);
					}
					
				}else{
					$main_image = $doc->createElement("Main_Image");
					$main_image->appendChild($doc->createTextNode($product['image']));
					$PRODUCT->appendChild($main_image);
				}
				
				$sku = $doc->createElement("SKU");
				$sku->appendChild($doc->createTextNode($product['sku']));
				$PRODUCT->appendChild($sku);
				
				$upc = $doc->createElement("UPC");
				$upc->appendChild($doc->createTextNode($product['upc']));
				$PRODUCT->appendChild($upc);
				
				$ean = $doc->createElement("EAN");
				$ean->appendChild($doc->createTextNode($product['ean']));
				$PRODUCT->appendChild($ean);
				
				$jan = $doc->createElement("JAN");
				$jan->appendChild($doc->createTextNode($product['jan']));
				$PRODUCT->appendChild($jan);
				
				$isbn = $doc->createElement("ISBN");
				$isbn->appendChild($doc->createTextNode($product['isbn']));
				$PRODUCT->appendChild($isbn);
				
				$mpn = $doc->createElement("MPN");
				$mpn->appendChild($doc->createTextNode($product['mpn']));
				$PRODUCT->appendChild($mpn);
				
				$location = $doc->createElement("Location");
				$location->appendChild($doc->createTextNode($product['location']));
				$PRODUCT->appendChild($location);
				
				$price = $doc->createElement("Price");
				$price->appendChild($doc->createTextNode($product['price']));
				$PRODUCT->appendChild($price);
				
				$tax_class_id = $doc->createElement("Tax_Class_ID");
				$tax_class_id->appendChild($doc->createTextNode($product['tax_class_id']));
				$PRODUCT->appendChild($tax_class_id);
				
				$tax_class_name = $doc->createElement("Tax_Class_Name");
				$tax_class_name->appendChild($doc->createTextNode($this->model_extension_product_export->getTaxClass($product['tax_class_id'])));
				$PRODUCT->appendChild($tax_class_name);
				
				$quantity = $doc->createElement("Quantity");
				$quantity->appendChild($doc->createTextNode($product['quantity']));
				$PRODUCT->appendChild($quantity);
				
				$minimum = $doc->createElement("Minimum_Quantity");
				$minimum->appendChild($doc->createTextNode($product['minimum']));
				$PRODUCT->appendChild($minimum);
				
				$subtract = $doc->createElement("Subtract");
				$subtract->appendChild($doc->createTextNode($product['subtract']));
				$PRODUCT->appendChild($subtract);
				
				$stock_status_id = $doc->createElement("Stock_Status_ID");
				$stock_status_id->appendChild($doc->createTextNode($product['stock_status_id']));
				$PRODUCT->appendChild($stock_status_id);
				
				$stock_status_name = $doc->createElement("Stock_Status_Name");
				$stock_status_name->appendChild($doc->createTextNode($this->model_extension_product_export->getStockstatus($product['stock_status_id'],$language_id)));
				$PRODUCT->appendChild($stock_status_name);
				
				$shipping = $doc->createElement("Shipping");
				$shipping->appendChild($doc->createTextNode($product['shipping']));
				$PRODUCT->appendChild($shipping);
				
				$SEO = $doc->createElement("SEO");
				$SEO->appendChild($doc->createTextNode($this->model_extension_product_export->getKeyword($product['product_id'])));
				$PRODUCT->appendChild($SEO);
				
				$date_available = $doc->createElement("Date_Available");
				$date_available->appendChild($doc->createTextNode($product['date_available']));
				$PRODUCT->appendChild($date_available);
				
				$length = $doc->createElement("length");
				$length->appendChild($doc->createTextNode($product['length']));
				$PRODUCT->appendChild($length);
				
				$length_class_id = $doc->createElement("Length_Class_ID");
				$length_class_id->appendChild($doc->createTextNode($product['length_class_id']));
				$PRODUCT->appendChild($length_class_id);
				
				$length_class_name = $doc->createElement("Length_Class_Name");
				$length_class_name->appendChild($doc->createTextNode($this->model_extension_product_export->getLengthClass($product['length_class_id'],$language_id)));
				$PRODUCT->appendChild($length_class_name);
				
				$width = $doc->createElement("Width");
				$width->appendChild($doc->createTextNode($product['width']));
				$PRODUCT->appendChild($width);
				
				$height = $doc->createElement("Height");
				$height->appendChild($doc->createTextNode($product['height']));
				$PRODUCT->appendChild($height);
				
				$weight = $doc->createElement("Weight");
				$weight->appendChild($doc->createTextNode($product['weight']));
				$PRODUCT->appendChild($weight);
				
				$weight_class_id = $doc->createElement("Weight_Class_ID");
				$weight_class_id->appendChild($doc->createTextNode($product['weight_class_id']));
				$PRODUCT->appendChild($weight_class_id);
				
				$weight_class_name = $doc->createElement("Weight_Class_Name");
				$weight_class_name->appendChild($doc->createTextNode($this->model_extension_product_export->getWeightClass($product['weight_class_id'],$language_id)));
				$PRODUCT->appendChild($weight_class_name);
				
				$status = $doc->createElement("Status");
				$status->appendChild($doc->createTextNode($product['status']));
				$PRODUCT->appendChild($status);
				
				$sort_order = $doc->createElement("Sort_Order");
				$sort_order->appendChild($doc->createTextNode($product['sort_order']));
				$PRODUCT->appendChild($sort_order);
				
				$manufacturer_id = $doc->createElement("Manufacturer_ID");
				$manufacturer_id->appendChild($doc->createTextNode($product['manufacturer_id']));
				$PRODUCT->appendChild($manufacturer_id);
				
				$manufacturer_name = $doc->createElement("Manufacturer_Name");
				$manufacturer_name->appendChild($doc->createTextNode($this->model_extension_product_export->getManufacturer($product['manufacturer_id'])));
				$PRODUCT->appendChild($manufacturer_name);
				
				$categories = $this->model_catalog_product->getProductCategories($product['product_id']);
				
				$categories_ids = $doc->createElement("Categories_IDs");
				$categories_ids->appendChild($doc->createTextNode(!empty($categories) ? implode(',',$categories) : ''));
				$PRODUCT->appendChild($categories_ids);
				
				// Category Names
				$categoryname = array();
				$this->load->model('catalog/category');
				$this->load->model('catalog/filter');
				foreach($categories as $category_id){
				   $categoryinfo = $this->model_catalog_category->getCategory($category_id);
				   if($categoryinfo){
					$categoryname[] = html_entity_decode(($categoryinfo['path'] ? $categoryinfo['path']. ' &gt; ' . $categoryinfo['name'] : $categoryinfo['name']));
				   }
				}
				$category_name = $doc->createElement("Category_Name");
				$category_name->appendChild($doc->createTextNode(implode(', ',$categoryname)));
				$PRODUCT->appendChild($category_name);
				
				// Filter Data
				$filterdata=array();
				$filteres = $this->model_catalog_product->getProductFilters($product['product_id']);
				foreach($filteres as $filter_id){
					$filter_info = $this->model_catalog_filter->getFilter($filter_id);
					if($filter_info){
						$filterdata[] = html_entity_decode(($filter_info['group'] ? $filter_info['group']. ' :: ' . $filter_info['name'] : $filter_info['name']));
					}
				}
				$pfilter = $doc->createElement("Filter_Data");
				$pfilter->appendChild($doc->createTextNode(implode(', ',$filterdata)));
				$PRODUCT->appendChild($pfilter);
				
				$downloads = $this->model_catalog_product->getProductDownloads($product['product_id']);
				$pdownloads = $doc->createElement("Downloads");
				$pdownloads->appendChild($doc->createTextNode(implode(', ',$downloads)));
				$PRODUCT->appendChild($pdownloads);
				
				$realated = $this->model_catalog_product->getProductRelated($product['product_id']);
				$prealated = $doc->createElement("Related");
				$prealated->appendChild($doc->createTextNode(implode(', ',$realated)));
				$PRODUCT->appendChild($prealated);
				
				//GetAttribute
				$this->load->model('catalog/attribute');
				$this->load->model('catalog/attribute_group');
				$attributes = $this->model_extension_product_export->getProductAttributes($product['product_id'],$language_id);
				$pattributes = $doc->createElement("Attribute");
				$pattributes->appendChild($doc->createTextNode(implode(', ',$attributes)));
				$PRODUCT->appendChild($pattributes);
				
				//options
				$productoptions = $this->model_extension_product_export->getProductOptions($product['product_id'],$language_id);
				$pproductoptions = $doc->createElement("Product_Options");
				$pproductoptions->appendChild($doc->createTextNode(implode(';', $productoptions)));
				$PRODUCT->appendChild($pproductoptions);
				
				//options required
				$productoptionrequired = $this->model_extension_product_export->getProductOptionsrequired($product['product_id'],$language_id);
				$pproductoptionrequired = $doc->createElement("Product_Option_Required");
				$pproductoptionrequired->appendChild($doc->createTextNode(implode('; ',$productoptionrequired)));
				$PRODUCT->appendChild($pproductoptionrequired);
				
				///getDiscount
				$discounts=array();
				$productdiscounts = $this->model_catalog_product->getProductDiscounts($product['product_id']);
				foreach($productdiscounts as $pdiscount){
					$discounts[]= $pdiscount['customer_group_id'].'::'.$pdiscount['quantity'].'::'.$pdiscount['priority'].'::'.$pdiscount['price'].'::'.$pdiscount['date_start'].'::'.$pdiscount['date_end'];
				}
				$pproductdiscounts = $doc->createElement("Product_Discounts");
				$pproductdiscounts->appendChild($doc->createTextNode(implode(', ',$discounts)));
				$PRODUCT->appendChild($pproductdiscounts);
				
				//GetSpecial
				$specials=array();
				$productspecials = $this->model_catalog_product->getProductSpecials($product['product_id']);
				foreach($productspecials as $pspecial){
					$specials[]= $pspecial['customer_group_id'].'::'.$pspecial['priority'].'::'.$pspecial['price'].'::'.$pspecial['date_start'].'::'.$pspecial['date_end'];
				}
				$pproductspecials = $doc->createElement("Product_Specials");
				$pproductspecials->appendChild($doc->createTextNode(implode(', ',$specials)));
				$PRODUCT->appendChild($pproductspecials);
				
				//GET Images
				$images=array();
				$productimages = $this->model_catalog_product->getProductImages($product['product_id']);
				foreach($productimages as $pimage){
					
					if($filter_pimage == 'yes'){
						if($pimage['image'] != '' || file_exists(HTTP_CATALOG.'image/'.$pimage['image'])){
							$images[]= HTTP_CATALOG.'image/'.$pimage['image'];
						}else{
							$images= array();
						}
					}else{
						$images[]= $pimage['image'];
					}
				}
				$pimages = $doc->createElement("Product_Sub_Images");
				$pimages->appendChild($doc->createTextNode(implode(';',$images)));
				$PRODUCT->appendChild($pimages);
				
				$points = $doc->createElement("Points");
				$points->appendChild($doc->createTextNode($product['points']));
				$PRODUCT->appendChild($points);
				
				$viewed = $doc->createElement("Viewed");
				$viewed->appendChild($doc->createTextNode($product['viewed']));
				$PRODUCT->appendChild($viewed);
				$r->appendChild($PRODUCT);
			}
			/*New 06-02-2017*/
			$mask = time();
			$doc->saveXML();
			$doc->save(DIR_EXCEL."product".$mask.".xml");
			/*New 06-02-2017*/
		}else{
		    $objPHPExcel = new PHPExcel();
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()->setTitle("Product");
			$objPHPExcel->getActiveSheet()->getStyle('S')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
			$objPHPExcel->getActiveSheet()
			->getStyle('A1:BB1')
			->applyFromArray(
				array(
					'fill' => array(
						'type' => PHPExcel_Style_Fill::FILL_SOLID,
						'color' => array('rgb' => 'd9d9d9')
					)
				)
			);
			$i=1;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, 'Product ID')->getColumnDimension('A')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, 'Language')->getColumnDimension('B')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Store')->getColumnDimension('C')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, 'Name')->getColumnDimension('D')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, 'Model')->getColumnDimension('E')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, 'Description')->getColumnDimension('F')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, 'Meta Title')->getColumnDimension('G')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, 'Meta Description')->getColumnDimension('H')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('I'.$i, 'Meta Keyword')->getColumnDimension('I')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('J'.$i, 'Tag')->getColumnDimension('J')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('K'.$i, 'Main Image')->getColumnDimension('K')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('L'.$i, 'SKU')->getColumnDimension('L')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('M'.$i, 'UPC')->getColumnDimension('M')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('N'.$i, 'EAN')->getColumnDimension('N')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('O'.$i, 'JAN')->getColumnDimension('O')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('P'.$i, 'ISBN')->getColumnDimension('P')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('Q'.$i, 'MPN')->getColumnDimension('Q')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('R'.$i, 'Location')->getColumnDimension('R')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('S'.$i, 'Price')->getColumnDimension('S')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('T'.$i, 'Tax Class ID')->getColumnDimension('T')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('U'.$i, 'Tax Class')->getColumnDimension('U')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('V'.$i, 'Quantity')->getColumnDimension('V')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('W'.$i, 'Minimum Quantity')->getColumnDimension('W')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('X'.$i, 'Subtract Stock')->getColumnDimension('X')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('Y'.$i, 'Stock Status ID')->getColumnDimension('Y')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('Z'.$i, 'Stock Status')->getColumnDimension('Z')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AA'.$i, 'Shipping')->getColumnDimension('AA')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AB'.$i, 'SEO')->getColumnDimension('AB')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AC'.$i, 'Date Available')->getColumnDimension('AC')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AD'.$i, 'Length')->getColumnDimension('AD')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AE'.$i, 'Length Class ID')->getColumnDimension('AE')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AF'.$i, 'Length Class')->getColumnDimension('AF')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AG'.$i, 'Width')->getColumnDimension('AG')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AH'.$i, 'Height')->getColumnDimension('AH')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AI'.$i, 'Weight')->getColumnDimension('AI')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AJ'.$i, 'Weight Class ID')->getColumnDimension('AJ')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AK'.$i, 'Weight Class')->getColumnDimension('AK')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AL'.$i, 'Status')->getColumnDimension('AL')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AM'.$i, 'Sort Order')->getColumnDimension('AM')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AN'.$i, 'Manufacturer ID')->getColumnDimension('AN')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AO'.$i, 'Manufacturer')->getColumnDimension('AO')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AP'.$i, 'Category ids')->getColumnDimension('AP')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AQ'.$i, 'Categories')->getColumnDimension('AQ')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AR'.$i, 'Filters (Filter Group :: filter Value)')->getColumnDimension('AR')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AS'.$i, 'Download')->getColumnDimension('AS')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AT'.$i, 'Related Products')->getColumnDimension('AT')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AU'.$i, 'Attributes (attribute_Group::attribute::text)')->getColumnDimension('AU')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AV'.$i, 'Options (Option::Type::optionvalue~qty~subtract~priceprefix~price~pointsprefix~points~weightprefix~weight)')->getColumnDimension('AV')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AW'.$i, 'Options Required (optionname:optiontype=0,optionname:optiontype=1)')->getColumnDimension('AW')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AX'.$i, 'Discount (Customer Group id::Quantity::Priority::Price::startdate::Enddate)')->getColumnDimension('AX')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AY'.$i, 'Special (Customer_group_id::Priority::Price::startdate::Enddate)')->getColumnDimension('AY')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('AZ'.$i, 'Sub Images (image1,image2)')->getColumnDimension('AZ')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('BA'.$i, 'Reward')->getColumnDimension('BA')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('BB'.$i, 'Viewed')->getColumnDimension('BB')->setAutoSize(true);
			$column ='BC';
			if(isset($_POST['custom_feilds'])){
				foreach($_POST['custom_feilds'] as $key => $feilds){
					foreach($feilds as $feild){
					$objPHPExcel->getActiveSheet()->setCellValue($column.$i, $feild)->getColumnDimension($column)->setAutoSize(true);	
					$column++;
					}
				}
			}
			
			foreach($products as $product){
				
				$storeinfo = $this->model_setting_store->getStore($product['store_id']);
				if($storeinfo){
					$store = $storeinfo['name'];
				}else{
					$store = 'default';
				}
			
				$i++;
				$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $product['product_id']);
				$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $product['language']);
				$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $store);
				$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, html_entity_decode($product['name']));
				$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $product['model']);
				$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, html_entity_decode($product['description']));
				$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, $product['meta_title']);
				$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, $product['meta_description']);
				$objPHPExcel->getActiveSheet()->setCellValue('I'.$i, $product['meta_keyword']);
				$objPHPExcel->getActiveSheet()->setCellValue('J'.$i, $product['tag']);
				if($filter_pimage == 'yes'){
					if($product['image'] != '' || file_exists(HTTP_CATALOG.'image/'.$product['image'])){
						$objPHPExcel->getActiveSheet()->setCellValue('K'.$i, HTTP_CATALOG.'image/'.$product['image']);
					}
					else{
						$objPHPExcel->getActiveSheet()->setCellValue('K'.$i, '');
					}
				}else{
					$objPHPExcel->getActiveSheet()->setCellValue('K'.$i, $product['image']);
				}
				$objPHPExcel->getActiveSheet()->setCellValue('L'.$i, $product['sku']);
				$objPHPExcel->getActiveSheet()->setCellValue('M'.$i, $product['upc']);
				$objPHPExcel->getActiveSheet()->setCellValue('N'.$i, $product['ean']);
				$objPHPExcel->getActiveSheet()->setCellValue('O'.$i, $product['jan']);
				$objPHPExcel->getActiveSheet()->setCellValue('P'.$i, $product['isbn']);
				$objPHPExcel->getActiveSheet()->setCellValue('Q'.$i, $product['mpn']);
				$objPHPExcel->getActiveSheet()->setCellValue('R'.$i, $product['location']);
				$objPHPExcel->getActiveSheet()->setCellValue('S'.$i, $product['price']);
				$objPHPExcel->getActiveSheet()->setCellValue('T'.$i, $product['tax_class_id']);
				$objPHPExcel->getActiveSheet()->setCellValue('U'.$i, $this->model_extension_product_export->getTaxClass($product['tax_class_id']));
				$objPHPExcel->getActiveSheet()->setCellValue('V'.$i, $product['quantity']);
				$objPHPExcel->getActiveSheet()->setCellValue('W'.$i, $product['minimum']);
				$objPHPExcel->getActiveSheet()->setCellValue('X'.$i, $product['subtract']);
				$objPHPExcel->getActiveSheet()->setCellValue('Y'.$i, $product['stock_status_id']);
				$objPHPExcel->getActiveSheet()->setCellValue('Z'.$i, $this->model_extension_product_export->getStockstatus($product['stock_status_id'],$language_id));
				$objPHPExcel->getActiveSheet()->setCellValue('AA'.$i, $product['shipping']);
				$objPHPExcel->getActiveSheet()->setCellValue('AB'.$i, $this->model_extension_product_export->getKeyword($product['product_id']));
				$objPHPExcel->getActiveSheet()->setCellValue('AC'.$i,  $product['date_available']);
				$objPHPExcel->getActiveSheet()->setCellValue('AD'.$i, $product['length']);
				$objPHPExcel->getActiveSheet()->setCellValue('AE'.$i, $product['length_class_id']);
				$objPHPExcel->getActiveSheet()->setCellValue('AF'.$i, $this->model_extension_product_export->getLengthClass($product['length_class_id'],$language_id));
				$objPHPExcel->getActiveSheet()->setCellValue('AG'.$i, $product['width']);
				$objPHPExcel->getActiveSheet()->setCellValue('AH'.$i, $product['height']);
				$objPHPExcel->getActiveSheet()->setCellValue('AI'.$i, $product['weight']);
				$objPHPExcel->getActiveSheet()->setCellValue('AJ'.$i, $product['weight_class_id']);
				$objPHPExcel->getActiveSheet()->setCellValue('AK'.$i, $this->model_extension_product_export->getWeightClass($product['weight_class_id'],$language_id));
				$objPHPExcel->getActiveSheet()->setCellValue('AL'.$i, $product['status']);
				$objPHPExcel->getActiveSheet()->setCellValue('AM'.$i, $product['sort_order']);
				$objPHPExcel->getActiveSheet()->setCellValue('AN'.$i, $product['manufacturer_id']);
				$objPHPExcel->getActiveSheet()->setCellValue('AO'.$i, $this->model_extension_product_export->getManufacturer($product['manufacturer_id']));
				$categories = $this->model_catalog_product->getProductCategories($product['product_id']);
				$objPHPExcel->getActiveSheet()->setCellValue('AP'.$i, (!empty($categories) ? implode(',',$categories) : ''));
				$categoryname = array();
				$this->load->model('catalog/category');
				$this->load->model('catalog/filter');
				foreach($categories as $category_id){
				   $categoryinfo = $this->model_catalog_category->getCategory($category_id);
				   if($categoryinfo){
					$categoryname[] = html_entity_decode(($categoryinfo['path'] ? $categoryinfo['path']. ' &gt; ' . $categoryinfo['name'] : $categoryinfo['name']));
				   }
				}
				$objPHPExcel->getActiveSheet()->setCellValue('AQ'.$i, implode(', ',$categoryname));
				
				$filterdata=array();
				$filteres = $this->model_catalog_product->getProductFilters($product['product_id']);
				foreach($filteres as $filter_id){
					$filter_info = $this->model_catalog_filter->getFilter($filter_id);
					if($filter_info){
						$filterdata[] = html_entity_decode(($filter_info['group'] ? $filter_info['group']. ' :: ' . $filter_info['name'] : $filter_info['name']));
					}
				}
				
				$objPHPExcel->getActiveSheet()->setCellValue('AR'.$i, implode(', ',$filterdata));
				$downloads = $this->model_catalog_product->getProductDownloads($product['product_id']);
				$objPHPExcel->getActiveSheet()->setCellValue('AS'.$i, implode(', ',$downloads));
				$realated = $this->model_catalog_product->getProductRelated($product['product_id']);
				$objPHPExcel->getActiveSheet()->setCellValue('AT'.$i, implode(', ',$realated));
				
				//GetAttribute
				$this->load->model('catalog/attribute');
				$this->load->model('catalog/attribute_group');
				$attributes = $this->model_extension_product_export->getProductAttributes($product['product_id'],$language_id);
				$objPHPExcel->getActiveSheet()->setCellValue('AU'.$i, implode(', ',$attributes));
				
				//options
				$productoptions = $this->model_extension_product_export->getProductOptions($product['product_id'],$language_id);
				$objPHPExcel->getActiveSheet()->setCellValue('AV'.$i, implode('; ',$productoptions));
				
				//options required
				$productoptionrequired = $this->model_extension_product_export->getProductOptionsrequired($product['product_id'],$language_id);
				
				$objPHPExcel->getActiveSheet()->setCellValue('AW'.$i, implode('; ',$productoptionrequired));
				
				///getDiscount
				$discounts=array();
				$productdiscounts = $this->model_catalog_product->getProductDiscounts($product['product_id']);
				foreach($productdiscounts as $pdiscount){
					$discounts[]= $pdiscount['customer_group_id'].'::'.$pdiscount['quantity'].'::'.$pdiscount['priority'].'::'.$pdiscount['price'].'::'.$pdiscount['date_start'].'::'.$pdiscount['date_end'];
				}
				$objPHPExcel->getActiveSheet()->setCellValue('AX'.$i, implode(', ',$discounts));
				
				//GetSpecial
				$specials=array();
				$productspecials = $this->model_catalog_product->getProductSpecials($product['product_id']);
				foreach($productspecials as $pspecial){
					$specials[]= $pspecial['customer_group_id'].'::'.$pspecial['priority'].'::'.$pspecial['price'].'::'.$pspecial['date_start'].'::'.$pspecial['date_end'];
				}
				$objPHPExcel->getActiveSheet()->setCellValue('AY'.$i, implode(', ',$specials));
				
				//GET Images
				$images=array();
				$productimages = $this->model_catalog_product->getProductImages($product['product_id']);
				foreach($productimages as $pimage){
					
					if($filter_pimage == 'yes'){
						if($pimage['image'] != '' || file_exists(HTTP_CATALOG.'image/'.$pimage['image'])){
							$images[]= HTTP_CATALOG.'image/'.$pimage['image'];
						}else{
							$images= array();
						}
					}else{
						$images[]= $pimage['image'];
					}
				}
				$objPHPExcel->getActiveSheet()->setCellValue('AZ'.$i, implode(';',$images));
				$objPHPExcel->getActiveSheet()->setCellValue('BA'.$i, $product['points']);
				$objPHPExcel->getActiveSheet()->setCellValue('BB'.$i, $product['viewed']);
				$column ='BC';
				if(isset($_POST['custom_feilds'])){
					foreach($_POST['custom_feilds'] as $key => $feilds){
						foreach($feilds as $feild){
						$objPHPExcel->getActiveSheet()->setCellValue($column.$i, $product[$feild]);
						$column++;
						}
					}
				}
			}
			/*NEW 06-02-2017*/
			$mask = time();
			
			if($filter_eformat == 'csv'){
				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
				$filename = 'product'.$mask.'.csv';
			}elseif($filter_eformat == 'xls'){
				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
				$filename = 'product'.$mask.'.xls';
			}elseif($filter_eformat == 'xlsx'){
				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
				$filename = 'product'.$mask.'.xlsx';
			}
			
			$objWriter->save(DIR_EXCEL . $filename);
			/*NEW 06-02-2017*/
		}
		/*NEW 06-02-2017*/
		if($filter_limit){
			//$filter_limit = $product_total/$filter_limit;
			$start = ($page - 1) * $filter_limit;
			$end = $start + $filter_limit;
			if($end < $product_total){
				$json['next'] = str_replace('&amp;', '&', $this->url->link('extension/product_export/export', 'user_token=' . $this->session->data['user_token'] . '&page=' . ($page + 1), true));
			} else {
				$json['next'] = '';
				$json['download'] = $this->url->link('extension/product_export/downloadfile','user_token='.$this->session->data['user_token'].'&filter_eformat='.$filter_eformat.'&filename=product&mask='.$mask.'&batches='.true, true);
			}
		}else{
			$json['download'] = $this->url->link('extension/product_export/downloadfile','user_token='.$this->session->data['user_token'].'&filename=product&filter_eformat='.$filter_eformat.'&mask='.$mask.'&batches='.false, true);
		}
		print_r(json_encode(str_replace('&amp;','&',$json))); 
		/*NEW 06-02-2017*/
	}
	
	public function reviewexport(){
		if (isset($this->request->post['filter_name'])) {
			$filter_name = $this->request->post['filter_name'];
		} else {
			$filter_name = null;
		}
		
		if (isset($this->request->post['filter_status'])) {
			$filter_status = $this->request->post['filter_status'];
		} else {
			$filter_status = '*';
		}
		
		
		
		if (isset($this->request->post['filter_product_id'])) {
			$filter_product_id = $this->request->post['filter_product_id'];
		} else {
			$filter_product_id = null;
		}
		
		if (isset($this->request->post['filter_endproduct_id'])) {
			$filter_endproduct_id = $this->request->post['filter_endproduct_id'];
		} else {
			$filter_endproduct_id = null;
		}
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
		
		if (!empty($this->request->post['filter_limit'])) {
			$filter_limit = $this->request->post['filter_limit'];
		} else {
			$filter_limit = 0;
		}
		
	
		
		if (isset($this->request->post['filter_eformat'])) {
			$filter_eformat = $this->request->post['filter_eformat'];
		} else {
			$filter_eformat = 'xls';
		}
		
		$filter_data1=array(
			'filter_status'   			=> $filter_status,
		    'filter_name'				=> $filter_name,
			'filter_product_id'			=> $filter_product_id,
			'filter_endproduct_id'		=> $filter_endproduct_id,
		);
		
		$this->load->model('extension/product_export');
		$productreview_total = $this->model_extension_product_export->getTotalproductreview($filter_data1);
		
		if($filter_limit){
		  $filter_limit = $productreview_total/$filter_limit;
		}
			
		$filter_data=array(
			'filter_status'   			=> $filter_status,
		    'start'                		=> ($page - 1) * $filter_limit,
			'limit'           			=> $filter_limit,
			'filter_name'				=> $filter_name,
			'filter_product_id'			=> $filter_product_id,
			'filter_endproduct_id'		=> $filter_endproduct_id,
		);
		
		$this->load->model('extension/product_export');
		$productreview = $this->model_extension_product_export->getproductreview($filter_data);
		
		if($filter_eformat == 'xml'){
			$doc = new DOMDocument();
			$doc->formatOutput = true;
			$r = $doc->createElement("REVIEWS");
			$doc->appendChild( $r );
			$i=1;
			foreach($productreview as $productreviews){
				$i++;
				$PRODUCT_REVIEW = $doc->createElement("Review");
				
				$review_id = $doc->createElement("Review_ID");
				$review_id->appendChild($doc->createTextNode($productreviews['review_id']));
				$PRODUCT_REVIEW->appendChild($review_id);
				
				$pproduct_id = $doc->createElement("Product_ID");
				$pproduct_id->appendChild($doc->createTextNode($productreviews['product_id']));
				$PRODUCT_REVIEW->appendChild($pproduct_id);
				
				$customer_id = $doc->createElement("Customer_ID");
				$customer_id->appendChild($doc->createTextNode($productreviews['customer_id']));
				$PRODUCT_REVIEW->appendChild($customer_id);
				
				$author = $doc->createElement("Author");
				$author->appendChild($doc->createTextNode($productreviews['author']));
				$PRODUCT_REVIEW->appendChild($author);
				
				$text = $doc->createElement("Text");
				$text->appendChild($doc->createTextNode($productreviews['text']));
				$PRODUCT_REVIEW->appendChild($text);
				
				$rating = $doc->createElement("Rating");
				$rating->appendChild($doc->createTextNode($productreviews['rating']));
				$PRODUCT_REVIEW->appendChild($rating);
				
				$rstatus = $doc->createElement("Status");
				$rstatus->appendChild($doc->createTextNode($productreviews['status']));
				$PRODUCT_REVIEW->appendChild($rstatus);
				
				$prdate_added = $doc->createElement("Date_Added");
				$prdate_added->appendChild($doc->createTextNode($productreviews['date_added']));
				$PRODUCT_REVIEW->appendChild($prdate_added);
				
				$prdate_modified = $doc->createElement("Date_Modified");
				$prdate_modified->appendChild($doc->createTextNode($productreviews['date_modified']));
				$PRODUCT_REVIEW->appendChild($prdate_modified);
				
				$r->appendChild($PRODUCT_REVIEW);
			}
			$mask = time();
			$doc->saveXML();
			$doc->save(DIR_EXCEL."productreview".$mask.".xml");
		}else{
			$objPHPExcel = new PHPExcel();
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()->setTitle("Product Reviews");
			$i=1;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, 'Review ID')->getColumnDimension('A')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, 'Product ID')->getColumnDimension('B')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, 'Customer ID')->getColumnDimension('C')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, 'Author')->getColumnDimension('D')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, 'Text')->getColumnDimension('E')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, 'Rating')->getColumnDimension('F')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, 'Status')->getColumnDimension('G')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, 'Date Added')->getColumnDimension('H')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->setCellValue('I'.$i, 'Date Modified')->getColumnDimension('I')->setAutoSize(true);
			
			foreach($productreview as $productreviews){
				$i++;
				$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $productreviews['review_id']);
				$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $productreviews['product_id']);
				$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $productreviews['customer_id']);
				$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $productreviews['author']);
				$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $productreviews['text']);
				$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $productreviews['rating']);
				$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, $productreviews['status']);
				$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, $productreviews['date_added']);
				$objPHPExcel->getActiveSheet()->setCellValue('I'.$i, $productreviews['date_modified']);
			}
			
			$mask = time();
			if($filter_eformat == 'csv'){
				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
				$filename = 'productreview'.$mask.'.csv';
			}elseif($filter_eformat == 'xls'){
				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
				$filename = 'productreview'.$mask.'.xls';
			}elseif($filter_eformat == 'xlsx'){
				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
				$filename = 'productreview'.$mask.'.xlsx';
			}
			
			$objWriter->save(DIR_EXCEL . $filename);
		}
		
		
		if($filter_limit){
		    $start = ($page - 1) * $filter_limit;
			$end = $start + $filter_limit;
			
			if($end < $productreview_total){
				$json['next'] = str_replace('&amp;', '&', $this->url->link('extension/product_export/reviewexport', 'user_token=' . $this->session->data['user_token'] . '&page=' . ($page + 1), true));
			} else {
				$json['next'] = '';
				$json['download'] = $this->url->link('extension/product_export/downloadfile','user_token='.$this->session->data['user_token'].'&filter_eformat='.$filter_eformat.'&mask='.$mask.'&filename=productreview&batches='.true, true);
			}
		}else{
			$json['download'] = $this->url->link('extension/product_export/downloadfile','user_token='.$this->session->data['user_token'].'&filter_eformat='.$filter_eformat.'&mask='.$mask.'&filename=productreview&batches='.false, true);
		}
		print_r(json_encode(str_replace('&amp;','&',$json))); 
	}
	/*NEW 06-02-2017*/
	public function create_zip($files = array(),$destination = '',$overwrite = false){
		//if the zip file already exists and overwrite is false, return false
		if(file_exists($destination) && !$overwrite) { return false; }
	
		//vars
		$valid_files = array();
		//if files were passed in...
		if(is_array($files)) {
			//cycle through each file
			foreach($files as $file) {
				//make sure the file exists
				if(file_exists($file)) {
					$valid_files[] = $file;
				}
			}
		}
		
		
		//if we have good files...
		if(count($valid_files)) {
			//create the archive
			$zip = new ZipArchive();
			if($zip->open($destination,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
				return false;
			}
			//add the files
			foreach($valid_files as $file){
				$zip->addFile($file,basename($file));
			}
			
			$zip->close();
			
			header('Content-Type: application/zip'); 
			header('Content-Disposition: attachment;filename='.basename($destination)); 
			header('Cache-Control: max-age=0'); 
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Content-Length: ' . filesize($destination));
			readfile($destination, 'rb');
			unlink($destination);
			foreach($valid_files as $file){
				unlink($file);
			}
			exit();
		}
		else
		{
			return false;
		}
	}
	
	public function downloadfile(){
		if($this->request->get['batches']){
			$nom_zip = DIR_EXCEL.$this->request->get['filename'].'-'.time().'.zip';
			$files = array_diff(scandir(DIR_EXCEL), array('..', '.'));
			foreach($files as $file){
			 $tmpfile[]=DIR_EXCEL.$file;
			}
			
			$this->create_zip($tmpfile,$nom_zip);
		}else{
			$file = DIR_EXCEL . $this->request->get['filename'].$this->request->get['mask'].'.'.$this->request->get['filter_eformat'];
			if(is_file($file)){
				if($this->request->get['filter_eformat'] != 'xml'){
						header('Content-Type: application/vnd.ms-excel'); 
						header('Content-Disposition: attachment;filename='.basename($file)); 
						header('Cache-Control: max-age=0'); 
						header('Expires: 0');
						header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
						header('Pragma: public');
						header('Content-Length: ' . filesize($file));
						readfile($file, 'rb');
						unlink($file);
						exit();
				}else{
					header('Content-type: text/xml');
					header('Content-Disposition: attachment; filename='.basename($file));
					header('Expires: 0');
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
					header('Pragma: public');
					header('Content-Length: ' . filesize($file));
					readfile($file, 'rb');
					unlink($file);
					exit();
				}
			}
		}
	}
	/*NEW 06-02-2017*/
}