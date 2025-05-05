<?php
header('Cache-Control: no-cache, no-store');
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', 900);
ini_set('error_reporting', E_ALL);
include DIR_SYSTEM.'library/product_export_import/PHPExcel.php';
class ControllerExtensionProductimport extends Controller {
	private $error = array();
	
	public function index(){
		$this->load->language('catalog/product');
		
		$this->load->language('extension/product_import');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/product');
		
		$this->load->model('extension/product_import');
		$this->load->model('extension/product_export');
		
		$this->load->model('catalog/manufacturer');

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_form'] = !isset($this->request->get['product_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
		$data['text_default'] = $this->language->get('text_default');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		
		$data['entry_store'] = $this->language->get('entry_store');
		
		$examplefiles = HTTPS_CATALOG.'system/library/product_export_import/example/example.xls';
		$data['entry_import'] = sprintf($this->language->get('entry_import'),$examplefiles);
		
		
		$examplefilesz = HTTPS_CATALOG.'system/library/product_export_import/example/reviewexample.xls';
		$data['text_import_productreview'] = sprintf($this->language->get('text_import_productreview'),$examplefilesz);
		$data['entry_language'] = $this->language->get('entry_language');
		
		$data['help_keyword'] = $this->language->get('help_keyword');
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		$data['cancel'] = $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL');
		$data['button_add'] = $this->language->get('button_add');
		$data['text_importtype'] = $this->language->get('text_importtype');
		$data['text_productid'] = $this->language->get('text_productid');
		$data['text_model'] = $this->language->get('text_model');
		$data['tab_producttab'] = $this->language->get('tab_producttab');
		$data['tab_reviewdata'] = $this->language->get('tab_reviewdata');
		
		$data['productreviewaction'] = $this->url->link('extension/product_import/importproductreview','user_token='.$this->session->data['user_token'],'SSL');
		$data['importproduct'] = $this->url->link('extension/product_import/importproduct','user_token='.$this->session->data['user_token'],'SSL');
		
		$data['user_token'] = $this->session->data['user_token'];
		
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
		
		if(isset($this->error['warning'])){
			$data['error_warning'] = $this->error['warning'];
		}elseif(isset($this->session->data['error_warning'])){
			$data['error_warning'] = $this->session->data['error_warning'];
			unset($this->session->data['error_warning']);
		}else{
			$data['error_warning'] = '';
		}
		
		if(isset($this->session->data['success'])){
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		}else{
			$data['success'] = '';
		}
		
		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		$this->load->model('setting/store');
		$data['stores'] = $this->model_setting_store->getStores();
		
		$data['customfeilds'] = $this->model_extension_product_export->customfeilds();
		
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/product_import', $data));
	}
	
	public function importproduct(){
		$this->load->language('catalog/product');
		
		$this->load->language('extension/product_import');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/product');
		
		$this->load->model('extension/product_import');
		
		$this->load->model('catalog/manufacturer');
		
		if(($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if($this->request->files) {
				if(!empty($this->request->post['store_id'])){
					$store_id = $this->request->post['store_id'];
				}else{
					$store_id = 0;
				}
				
				if(!empty($this->request->post['language_id'])){
					$language_id = $this->request->post['language_id'];
				}else{
					$language_id = $this->config->get('config_langauge_id');
				}
				
			$file = basename($this->request->files['import']['name']);
			move_uploaded_file($this->request->files['import']['tmp_name'], $file);
			$inputFileName = $file;
			$extension = pathinfo($inputFileName);
			if($extension['basename']){
				if($extension['extension']=='xlsx' || $extension['extension']=='xls' || $extension['extension']=='csv'){
					try{
						if($extension['extension']=='csv'){
							$inputFileType = 'CSV';
							$objReader = PHPExcel_IOFactory::createReader($inputFileType);
							$objPHPExcel = $objReader->load($inputFileName);
						}else{
							$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
						}
					}catch(Exception $e){
						die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
					}
					$allDataInSheet = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
				//	print_r($allDataInSheet); die();
					$i=0;
					$updateproduct = 0;
					$newproduct = 0;
					
					$this->load->model('localisation/tax_class');
					foreach($allDataInSheet as $k=> $value){
						if($i!=0){
							if($value['D']){
							//Tax Class
							if(empty($value['T'])){
								$value['T'] = $this->model_extension_product_import->CheckTaxClass($value['U']);
							}
							
							//Stock Status
							if(empty($value['Y'])){
								$value['Y'] = $this->model_extension_product_import->CheckStockStatus($value['Z']);
							}
							
							if(!empty($value['AB'])){
								 $value['AB'] = str_replace(' ','_',$value['AB']);
							}
							
							//manufacture
							$manufacturer_info = array();
							if(!empty($value['AN'])){
								$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($value['AN']);
							}
							if(!$manufacturer_info){
								if(!empty($value['AO'])){
								 $value['AN'] = $this->model_extension_product_import->CheckManufacturer($value['AO'],$store_id);
								}
							}
							
							//Categories
							$categoryids=array();
							if(!empty($value['AP'])){
							  foreach(explode(',',trim($value['AP'])) as $category_id){
								$cquery = $this->db->query("SELECT category_id FROM ".DB_PREFIX."category WHERE category_id = '".(int)$category_id."'");
								if(!empty($cquery->row['category_id'])){
								 $categoryids[]=$cquery->row['category_id'];
								}
							  }
							}
							
							$categoryidsx=array();
							if(!empty($value['AQ'])){
								 $categoryidsx = $this->model_extension_product_import->CheckCategories($value['AQ'],$store_id);
								 foreach($categoryidsx as $cate_id){
									 $categoryids[] = $cate_id;
								 }
							}
							
							//Filter
							$filters=array();
							if(!empty($value['AR'])){
								$filters = $this->model_extension_product_import->checkFilter($value['AR']);
							}
							
							//Download
							$downloads = array();
							if(!empty($value['AS'])){
								$downloads = explode(',',trim($value['AS']));
							}
							
							//Relaled Products
							$relaled_products = array();
							if(!empty($value['AT'])){
								$relaled_products = explode(',',trim($value['AT']));
							}
							
							//Attribute Group
							$attributes = array();
							if(!empty($value['AU'])){
								$attributes = $this->model_extension_product_import->checkAttribute($value['AU']);
							}
							
							
							
							//Discount
							$discounts = array();
							if(!empty($value['AX'])){
								$discounts = $this->model_extension_product_import->checkdiscount($value['AX']);
							}
							
							//Specail
							$specails = array();
							if(!empty($value['AY'])){
								$specails = $this->model_extension_product_import->checkspecial($value['AY']);
							}
							
							//Image
							$imagen = str_replace(' ','_',$value['D']);
							$mainimage = trim($value['K']);
							if(!empty($value['K'])){
							  $value['K'] = str_replace('?dl=0','?raw=1',$value['K']);
							  $mainimage = $this->model_extension_product_import->fetchingimage($value['K'],$imagen);	
							}
							
							
							//Image
							$images = array();
							if(!empty($value['AZ'])){
								$ic=1;
								foreach(explode(';',$value['AZ']) as $imageurl){
									$imageurl = str_replace('?dl=0','?raw=1',$imageurl);
								  $imagename = $imagen.$ic++;
								  $images[] = $this->model_extension_product_import->fetchingimage(trim($imageurl),$imagename);
								}
							}
							
							
							//Options
							$options = array();
							if(!empty($value['AV'])){
								$options = $this->model_extension_product_import->checkoptions($value['AV']);
							}
							
							//Options Required
							$optionsrequired = array();
							if(!empty($value['AW'])){
								$optionsrequired = $this->model_extension_product_import->checkoptionsrequred($value['AW']);
							}
							
							
							//CUSTOM EXTRA FEILD FROM OC_product table 
							$extraproductdata=array();
							if(isset($this->request->post['product'])){
								foreach($this->request->post['product'] as $key => $cell){
									if($cell){
									$extraproductdata[]=array(
										'key'		=> $key,
										'value'	=> (isset($value[$cell]) ? $value[$cell] : ''),
									);
									}
								}
							}
							
							//custom extra feild from oc_product_descrition table
							$extraproductdescriptondata=array();
							if(isset($this->request->post['product_description'])){
								foreach($this->request->post['product_description'] as $key => $cell){
									if($cell){
									$extraproductdescriptondata[]=array(
										'key'		=> $key,
										'value'	=> (isset($value[$cell]) ? $value[$cell] : ''),
									);
									}
								}
							}
							
							
							$importdata=array(
							  'name' 	 			=> $value['D'],
							  'model'  	 			=> $value['E'],
							  'description' 		=> $value['F'],
							  'meta_titile' 		=> $value['G'],
							  'meta_description' 	=> $value['H'],
							  'meta_keyword' 		=> $value['I'],
							  'tag' 				=> $value['J'],
							  'image' 				=> $mainimage,
							  'sku' 				=> $value['L'],
							  'upc' 				=> $value['M'],
							  'ean' 				=> $value['N'],
							  'jan' 				=> $value['O'],
							  'isbn' 				=> $value['P'],
							  'mpn' 				=> $value['Q'],
							  'location' 			=> $value['R'],
							  'price' 				=> $value['S'],
							  'tax_class_id' 		=> $value['T'],
							  'quantity' 			=> $value['V'],
							  'minimum' 			=> $value['W'],
							  'subtract' 			=> $value['X'],
							  'stock_status_id' 	=> $value['Y'],
							  'shipping' 			=> $value['AA'],
							  'keyword' 			=> $value['AB'],
							  'date_available' 		=> ($value['AC'] ? $value['AC'] : date('Y-m-d')),
							  'length' 				=> $value['AD'],
							  'length_class_id' 	=> $value['AE'],
							  'width' 				=> $value['AG'],
							  'height' 				=> $value['AH'],
							  'weight' 				=> $value['AI'],
							  'weight_class_id' 	=> $value['AJ'],
							  'status' 				=> $value['AL'],
							  'sort_order' 			=> $value['AM'],
							  'manufacturer_id' 	=> $value['AN'],
							  'categories'			=> array_unique($categoryids),
							  'filters'				=> array_unique($filters),
							  'downloads' 			=> $downloads,
							  'relaled_products' 	=> $relaled_products,
							  'attributes'			=> $attributes,
							  'options'				=> $options,
							  'discounts'			=> $discounts,
							  'specails'			=> $specails,
							  'images'				=> $images,
							  'optionsrequired'		=> $optionsrequired,
							  'points' 				=> $value['BA'],
							  'viewed' 				=> $value['BB'],
								'extraproductdescriptondata'	=> $extraproductdescriptondata,
								'extraproductdata'	=> $extraproductdata,
							);
							
							if($this->request->post['importtype']==2){
							 $product_id = $this->model_extension_product_import->getproductIDbymodel($value['E']);
								 if($product_id){
									 $this->model_extension_product_import->Editproduct($importdata,$product_id,$language_id,$store_id);
									 $updateproduct++;
								 }else{
									 $this->model_extension_product_import->addproduct($importdata,$language_id,$store_id);
									 $newproduct++;
								 }
							}else{
								if((int)$value['A']){
									$product_info = $this->model_catalog_product->getProduct($value['A']);
									if($product_info){
										$this->model_extension_product_import->Editproduct($importdata,$value['A'],$language_id,$store_id);
										$updateproduct++;
									}else{
										$this->model_extension_product_import->addoldproduct($importdata,$language_id,$store_id,$value['A']);
										$newproduct++;
									}
								}else{
									$this->model_extension_product_import->addproduct($importdata,$language_id,$store_id);
									$newproduct++;
								}
							}
						 }
						}
						$i++;
					}
					if($newproduct || $updateproduct){
						$this->session->data['success'] = sprintf($this->language->get('text_success'),$newproduct,$updateproduct);
					}
				
					if(!$newproduct && !$updateproduct){
						$this->session->data['error_warning'] = $this->language->get('text_no_data');
					}
				} elseif($extension['extension']=='xml'){
					
					try{
						$xml = simplexml_load_file($inputFileName);
					}catch(Exception $e){
						die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
					}

					$i=0;
					$updateproduct = 0;
					$newproduct = 0;					
					$this->load->model('localisation/tax_class');
					foreach($xml->children() as $k=> $value){
						
							if($value->Product_Name){
							//Tax Class
							if(empty($value->Product_Name)){
								$value->Tax_Class_ID = $this->model_extension_product_import->CheckTaxClass($value->Tax_Class_Name);
							}
							
							//Stock Status
							if(empty($value->Stock_Status_ID)){
								$value->Stock_Status_ID = $this->model_extension_product_import->CheckStockStatus(Stock_Status_Name);
							}
							
							if(!empty($value->SEO)){
								 $value->SEO = str_replace(' ','_',$value->SEO);
							}
							
							//manufacture
							$manufacturer_info = array();
							if(!empty($value->Manufacturer_ID)){
								$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($value->Manufacturer_ID);
							}
							if(!$manufacturer_info){
								if(!empty($value->Manufacturer_Name)){
								 $value->Manufacturer_ID = $this->model_extension_product_import->CheckManufacturer($value->Manufacturer_Name,$store_id);
								}
							}
							
							//Categories
							$categoryids=array();
							if(!empty($value->Categories_IDs)){
							  foreach(explode(',',trim($value->Categories_IDs)) as $category_id){
								$cquery = $this->db->query("SELECT category_id FROM ".DB_PREFIX."category WHERE category_id = '".(int)$category_id."'");
								if(!empty($cquery->row['category_id'])){
								 $categoryids[]=$cquery->row['category_id'];
								}
							  }
							}
							
							$categoryidsx=array();
							if(!empty($value->Category_Name)){
								 $categoryidsx = $this->model_extension_product_import->CheckCategories($value->Category_Name,$store_id);
								 foreach($categoryidsx as $cate_id){
									 $categoryids[] = $cate_id;
								 }
							}
							
							//Filter
							$filters=array();
							if(!empty($value->Filter_Data)){
								$filters = $this->model_extension_product_import->checkFilter($value->Filter_Data);
							}
							
							//Download
							$downloads = array();
							if(!empty($value->Downloads)){
								$downloads = explode(',',trim($value->Downloads));
							}
							
							//Relaled Products
							$relaled_products = array();
							if(!empty($value->Related)){
								$relaled_products = explode(',',trim($value->Related));
							}
							
							//Attribute Group
							$attributes = array();
							if(!empty($value->Attribute)){
								$attributes = $this->model_extension_product_import->checkAttribute($value->Attribute);
							}
							
							//Options
							$options = array();
							if(!empty($value->Product_Options)){
								$options = $this->model_extension_product_import->checkoptions($value->Product_Options);
							}
							
							//Discount
							$discounts = array();
							if(!empty($value->Product_Discounts)){
								$discounts = $this->model_extension_product_import->checkdiscount($value->Product_Discounts);
							}
							
							//Specail
							$specails = array();
							if(!empty($value->Product_Specials)){
								$specails = $this->model_extension_product_import->checkspecial($value->Product_Specials);
							}
							
							//Image
							$imagen = str_replace(' ','_',$value->Main_Image);
							$mainimage = $value->Main_Image;
							if(!empty($value->Main_Image)){
							  $value->Main_Image = str_replace('?dl=0','?raw=1',$value->Main_Image);
							  $mainimage = $this->model_extension_product_import->fetchingimage($value->Main_Image,$imagen);	
							}
							
							
							//Image
							$images = array();
							if(!empty($value->Product_Sub_Images)){
								$ic=1;
								foreach(explode(';',trim($value->Product_Sub_Images)) as $imageurl){
								  $imageurl = str_replace('?dl=0','?raw=1',$imageurl);
								  $imagename = $imagen.$ic++;
								  $images[] = $this->model_extension_product_import->fetchingimage($imageurl,$imagename);
								}
							}
							
							//Options Required
							$optionsrequired = array();
							if(!empty($value->Product_Option_Required)){
								$optionsrequired = $this->model_extension_product_import->checkoptionsrequred($value->Product_Option_Required);
							}
							
							$importdata=array(
							  'name' 	 			=> $value->Product_Name,
							  'model'  	 			=> $value->Model,
							  'description' 		=> $value->Product_Description,
							  'meta_titile' 		=> $value->Meta_Title,
							  'meta_description' 	=> $value->Meta_Description,
							  'meta_keyword' 		=> $value->Meta_Keyword,
							  'tag' 				=> $value->Tag,
							  'image' 				=> $mainimage,
							  'sku' 				=> $value->SKU,
							  'upc' 				=> $value->UPC,
							  'ean' 				=> $value->EAN,
							  'jan' 				=> $value->JAN,
							  'isbn' 				=> $value->ISBN,
							  'mpn' 				=> $value->MPN,
							  'location' 			=> $value->Location,
							  'price' 				=> $value->Price,
							  'tax_class_id' 		=> $value->Tax_Class_ID,
							  'quantity' 			=> $value->Quantity,
							  'minimum' 			=> $value->Minimum_Quantity,
							  'subtract' 			=> $value->Subtract,
							  'stock_status_id' 	=> $value->Stock_Status_ID,
							  'shipping' 			=> $value->Shipping,
							  'keyword' 			=> $value->SEO,
							  'date_available' 		=> ($value->Date_Available ? $value->Date_Available : date('Y-m-d')),
							  'length' 				=> $value->length,
							  'length_class_id' 	=> $value->Length_Class_ID,
							  'width' 				=> $value->Width,
							  'height' 				=> $value->Height,
							  'weight' 				=> $value->Weight,
							  'weight_class_id' 	=> $value->Weight_Class_ID,
							  'status' 				=> $value->Status,
							  'sort_order' 			=> $value->Sort_Order,
							  'manufacturer_id' 	=> $value->Manufacturer_ID,
							  'categories'			=> array_unique($categoryids),
							  'filters'				=> array_unique($filters),
							  'downloads' 			=> $downloads,
							  'relaled_products' 	=> $relaled_products,
							  'attributes'			=> $attributes,
							  'options'				=> $options,
							  'discounts'			=> $discounts,
							  'specails'			=> $specails,
							  'images'				=> $images,
							  'optionsrequired'		=> $optionsrequired,
							  'points' 				=> $value->Points,
							  'viewed' 				=> $value->Viewed,
							);
							
							if($this->request->post['importtype']==2){
							 $product_id = $this->model_extension_product_import->getproductIDbymodel($value->Model);
								 if($product_id){
									 $this->model_extension_product_import->Editproduct($importdata,$product_id,$language_id,$store_id);
									 $updateproduct++;
								 }else{
									 $this->model_extension_product_import->addproduct($importdata,$language_id,$store_id);
									 $newproduct++;
								 }
							}else{
								if((int)$value->product_id){
									$product_info = $this->model_catalog_product->getProduct($value->product_id);
									if($product_info){
										$this->model_extension_product_import->Editproduct($importdata,$value->product_id,$language_id,$store_id);
										$updateproduct++;
									}else{
										$this->model_extension_product_import->addoldproduct($importdata,$language_id,$store_id,$value->product_id);
										$newproduct++;
									}
								}else{
									$this->model_extension_product_import->addproduct($importdata,$language_id,$store_id);
									$newproduct++;
								}
							}
						 }
						$i++;						
					}
					if($newproduct || $updateproduct){
						$this->session->data['success'] = sprintf($this->language->get('text_success'),$newproduct,$updateproduct);
					}
				
					if(!$newproduct && !$updateproduct){
						$this->session->data['error_warning'] = $this->language->get('text_no_data');
					}
				}
			}else{
				$this->session->data['error_warning'] = $this->language->get('error_warning');
			}
			if($inputFileName){
				unlink($inputFileName);
			}
			
		  }else{
			$$this->session->data['error_warning'] = $this->language->get('error_warning');
		  }
		}
		
		$this->response->redirect($this->url->link('extension/product_import', 'user_token=' . $this->session->data['user_token'], 'SSL'));
	}
	
	public function importproductreview(){
		$this->load->model('extension/product_import');
		$this->load->language('extension/product_import');
		if(($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()){
						
			if($this->request->files) {
			$file = basename($this->request->files['import']['name']);
			move_uploaded_file($this->request->files['import']['tmp_name'], $file);
			$inputFileName = $file;
			$extension = pathinfo($inputFileName);
			if($extension['basename']){
				if($extension['extension']=='xlsx' || $extension['extension']=='xls' || $extension['extension']=='csv') {
					try{
						if($extension['extension']=='csv'){
							$inputFileType = 'CSV';
							$objReader = PHPExcel_IOFactory::createReader($inputFileType);
							$objPHPExcel = $objReader->load($inputFileName);
						}else{
							$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
						}
					}catch(Exception $e){
						die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
					}
					$allDataInSheet = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
					$i=0;
					$newentry=0;
					$updateentry=0;
					foreach($allDataInSheet as $value){

						if($i!=0){
							$filter_data=array(
								'review_id' 	=> $value['A'],
								'product_id'  	=> $value['B'],
								'customer_id'  	=> $value['C'],
								'author' 	   	=> $value['D'],
								'text'   		=> $value['E'],
								'rating'   		=> $value['F'],
								'status'  		=> $value['G'],
								'date_added' 	=> $value['H'],
								'date_modified'	=> $value['I']
							);
							if((int)$value['A']){
								$return_data = $this->model_extension_product_import->getReview($value['A']);
								if($return_data){
									 $this->model_extension_product_import->editproductreview($filter_data);
									 $updateentry++;
								}else{
									 $this->model_extension_product_import->addexsitproductreview($filter_data);
									 $newentry++;
								}
							}else{
							  $this->model_extension_product_import->addproductreview($filter_data);
							  $newentry++;
							}
						}
						$i++;
					}
					$this->session->data['success'] = sprintf($this->language->get('text_productreviewsuccess'),$newentry,$updateentry);
				}elseif($extension['extension']=='xml'){
					
					try{
						$xml = simplexml_load_file($inputFileName);
					}catch(Exception $e){
						die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
					}
					
					$i=0;
					$newentry=0;
					$updateentry=0;
					foreach($xml->children() as $k=> $value){

						if($i >= 0){
							$filter_data=array(
								'review_id' 	=> $value->Review_ID,
								'product_id'  	=> $value->Product_ID,
								'customer_id'  	=> $value->Customer_ID,
								'author' 	   	=> $value->Author,
								'text'   		=> $value->Text,
								'rating'   		=> $value->Rating,
								'status'  		=> $value->Status,
								'date_added' 	=> $value->Date_Added,
								'date_modified'	=> $value->Date_Modified,
							);
							if((int)$value->Review_ID){
								$return_data = $this->model_extension_product_import->getReview($value->Review_ID);
								if($return_data){
									 $this->model_extension_product_import->editproductreview($filter_data);
									 $updateentry++;
								}else{
									 $this->model_extension_product_import->addexsitproductreview($filter_data);
									 $newentry++;
								}
							}else{
							  $this->model_extension_product_import->addproductreview($filter_data);
							  $newentry++;
							}
						}
						$i++;
					}
					$this->session->data['success'] = sprintf($this->language->get('text_productreviewsuccess'),$newentry,$updateentry);
					
					
				} else{
					$this->session->data['error_warning'] = $this->language->get('error_warning');
				}
			}else{
				$this->session->data['error_warning'] = $this->language->get('error_warning');
			}
			if($inputFileName){
				unlink($inputFileName);
			}
			
		  }else{
			$this->session->data['error_warning'] = $this->language->get('error_warning');
		  }
	  }
	  $this->response->redirect($this->url->link('extension/product_import', 'user_token=' . $this->session->data['user_token'], 'SSL'));
	}
	
	protected function validate(){
		if(!$this->user->hasPermission('modify', 'extension/product_import')){
			$this->error['warning'] = $this->language->get('error_permission');
		}
		return !$this->error;
	}
}