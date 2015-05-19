<?php

class Request extends Doctrine_Record {

	public function setTableDefinition() {
		$this -> hasColumn('request_id', 'varchar', 20);
		$this -> hasColumn('client_id', 'varchar', 11);
		$this -> hasColumn('sample_qty', 'int', 11);
		$this -> hasColumn('product_name', 'varchar', 30);
		$this -> hasColumn('label_claim', 'varchar', 255);
		$this -> hasColumn('active_ing', 'varchar', 200);
		$this -> hasColumn('Dosage_Form', 'varchar', 30);
		$this -> hasColumn('Manufacturer_Name', 'varchar', 50);
		$this -> hasColumn('Manufacturer_add', 'varchar', 50);
		$this -> hasColumn('Batch_no', 'varchar', 20);
		$this -> hasColumn('exp_date', 'date');
		$this -> hasColumn('Manufacture_date', 'date');
		$this -> hasColumn('Designator_Name', 'varchar', 50);
		$this -> hasColumn('Designation', 'varchar', 30);
		$this -> hasColumn('Designation_date', 'date');
		$this -> hasColumn('Urgency', 'int', 11);
		$this -> hasColumn('edit_notes', 'varchar', 255 );
		$this -> hasColumn('presentation', 'varchar', 255);
		$this -> hasColumn('description', 'varchar', 255);
		$this -> hasColumn('product_lic_no', 'varchar', 35);
		$this -> hasColumn('country_of_origin', 'varchar', 35);
		$this -> hasColumn('dateformat', 'varchar', 10);
		$this -> hasColumn('clientsampleref', 'varchar' , 30);
		$this -> hasColumn('moa', 'varchar', 30);
		$this -> hasColumn('crs', 'varchar', 30);
		$this -> hasColumn('dsgntr', 'varchar', 30);
		$this -> hasColumn('dsgntn', 'varchar', 30);
		$this -> hasColumn('packaging', 'int', 11);
		$this -> hasColumn('full_details_status', 'int', 11);
		$this -> hasColumn('assign_status', 'int', 11);
		$this -> hasColumn('payment_status', 'int', 11);
		$this -> hasColumn('split_status', 'int', 11);
		$this -> hasColumn('label_status', 'int', 11);
		$this -> hasColumn('quotation_status', 'int', 11);
		$this -> hasColumn('component_status', 'int', 11);
		$this -> hasColumn('coa_collection_status', 'int', 11);
		$this -> hasColumn('coa_done_status', 'int', 11);
		$this -> hasColumn('invoice_print_status', 'int', 11);
		$this -> hasColumn('invoice_status', 'int', 11);
		$this -> hasColumn('proforma_no_status', 'int', 11);
		$this -> hasColumn('proforma_no','varchar', 50);
		$this -> hasColumn('client_agent_id','int', 11);
	}
	
	
	public function setUp() {
		$this->actAs('Timestampable');
		$this -> setTableName('request');
		$this -> hasOne('Clients', array(
			'local' => 'client_id',
			'foreign' => 'id'
		));
		$this -> hasOne('Packaging', array(
			'local' => 'packaging',
			'foreign' => 'id'
			));
		$this -> hasMany('Split',array(
			'local' => 'request_id',
			'foreign' => 'request_id'			
		));

		$this -> hasOne('Sample_issuance',array(
			'local' => 'request_id',
			'foreign' => 'lab_ref_no'			
		));

		$this -> hasOne('Worksheet_tracking',array(
			'local' => 'request_id',
			'foreign' => 'labref'			
		));

		$this -> hasOne('Dispatch_register',array(
			'local' => 'request_id',
			'foreign' => 'request_id'			
		));

		$this -> hasOne('Request_details',array(
			'local' => 'request_id',
			'foreign' => 'request_id'			
		));

		$this -> hasOne('Coa_number',array(
			'local' => 'request_id',
			'foreign' => 'request_id'			
		));

		$this -> hasOne('Coa_body', array(
			'local' => 'request_id',
			'foreign' => 'labref'
		));

	}

	public function getProformaInfo($c, $d){
		$query = Doctrine_Query::create()
		-> select("designation_date, client_agent_id, proforma_no")
		-> from("request")
		-> where("client_id = ?", $c)
		-> andWhere("designation_date = ?", $d)
		-> groupBy("proforma_no");
		$componentData = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $componentData;
	}


	public function getProformaCountPerClient($c, $d){
		$query = Doctrine_Query::create()
		-> select("count(*)")
		-> from("request")
		-> where("client_id = ?", $c)
		-> andWhere("designation_date = ?", $d)
		-> andWhere("proforma_no_status = 1")
		-> groupBy("proforma_no");
		$componentData = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $componentData;
	}

	public function getProformaNo($r){
		$query = Doctrine_Query::create()
		-> select("proforma_no, designation_date")
		-> from("request")
		-> andWhere("request_id = ?", $r);
		$componentData = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $componentData;
	}

	public function getClientId($reqid){
		$query = Doctrine_Query::create()
		-> select("client_id")
		-> from("request")
		-> where("request_id = ?", $reqid);
		$componentData = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $componentData;
	}
	
	public function getComponentName($reqid){
		$query = Doctrine_Query::create()
		-> select("product_name")
		-> from("request")
		-> where("request_id = ?", $reqid);
		$componentData = $query -> execute();
		return $componentData;	
	}

	public function getInvoiceDetails($r){
		$query = Doctrine_Query::create()
		-> select("r.request_id as LABORATORY_REF_NO, c.Name as Client_Name, c.Address as Client_Address, c.email as Client_Email, r.clientsampleref as CLIENT_REF_NO, r.id,r.product_name as PRODUCT, r.batch_no as BATCH_NO, coa.full_number as CERTIFICATE_NO, , 
			 rq.id, t.Name")
		-> from("request r")
		-> leftJoin("r.Clients c")
		-> leftJoin("r.Coa_number coa")
		-> leftJoin("r.Request_details rq")
		-> leftJoin("rq.Tests t")
		-> where("r.request_id =?", $r);
		$invoiceData = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $invoiceData;
	}

	public function getInvoiceDetailsPerClient($client_id, $date_received, $proforma_no){
		$query = Doctrine_Query::create()
		-> select("r.request_id as LABORATORY_REF_NO,c.Name as Client_Name, c.Address as Client_Address, r.clientsampleref as CLIENT_REF_NO, r.id,r.product_name as PRODUCT, r.batch_no as BATCH_NO, coa.full_number as CERTIFICATE_NO, , 
			 rq.id, t.Name, d.amount, d.discount")
		-> from("request r")
		-> leftJoin("r.Clients c")
		-> leftJoin("r.Coa_number coa")
		-> leftJoin("r.Request_details rq")
		-> leftJoin("r.Dispatch_register d")
		-> leftJoin("rq.Tests t")
		-> where("d.client_id =?", $client_id)
		-> andWhere("r.designation_date =?", $date_received)
		-> andWhere("r.proforma_no =?", $proforma_no);
		$invoiceData = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $invoiceData;
	}



	public function getRequestInfoSimple($reqid){
		$query = Doctrine_Query::create()
		-> select("r.product_name, c.name")
		-> from("request r")
		-> leftJoin("r.Clients c")
		-> where("r.request_id = ?", $reqid);
		$componentData = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $componentData;	
	}

	public function getManufacturerDetail($ref){
		$query = Doctrine_Query::create() 
		-> select("Manufacturer_add, country_of_origin") 
		-> from("request")
		-> where("Manufacturer_Name = ?", str_replace('_', ' ', $ref))
		-> orderBy("id DESC")
		-> limit(1);
		$requestData = $query -> execute() -> toArray();
		return $requestData;
	}


	public function getAuthorizerDetail($ref){
		$query = Doctrine_Query::create() 
		-> select("dsgntn") 
		-> from("request")
		-> where("dsgntr = ?", str_replace('_', ' ', $ref))
		-> orderBy("id DESC")
		-> limit(1);
		$requestData = $query -> execute() -> toArray();
		return $requestData;
	}


	public function getProductDetail($ref){
		$query = Doctrine_Query::create() 
		-> select("r.label_claim, r.Dosage_Form, r.active_ing, r.packaging") 
		-> from("request r")
		-> where("r.product_name = ?", str_replace('_', ' ', $ref))
		-> orderBy("id DESC")
		-> limit(1);
		$requestData = $query -> execute() -> toArray();
		return $requestData;
	}
	
	
	public function getHistory($reqid) {
		$query = Doctrine_Query::create() 
		-> select("*") 
		-> from("request")
		-> where("request_id = ?", $reqid)
		-> orderBy("id DESC")
		-> limit(3); 
		$requestData = $query -> execute();
		return $requestData;
	}//end getall
	
	public function getAll7() {
		$query = Doctrine_Query::create() 
		-> select("*") 
		-> from("request");
		$requestData = $query -> execute();
		return $requestData;
	}

	public function getIfSampleSplit($lab_ref_no) {
		$query = Doctrine_Query::create()
		-> select("split_status")
		-> from("request")
		-> where("request_id = ?", $lab_ref_no);
		$splitData = $query -> execute();
		return $splitData;
	}

	public function getAll() {
		$query = Doctrine_Query::create() 
		-> select("*") 
		-> from("request");
		$requestData = $query -> execute();
		return $requestData;
	}//end getall

	 public static function get_value($delivery){
  	
		$query=Doctrine_Query::create()-> select("*")->from("request")-> where("id=$delivery");
		$order=$query->execute();
		return $order[0];
	}

	public function getSingleHydrated($reqid) {
		$query = Doctrine_Query::create() 
		-> select("r.*, c.*, p.*, sp.*")
		-> from("request r")
		-> where("request_id =?", $reqid)
		-> leftJoin("r.Clients c")
		-> innerJoin("r.Packaging p")
		-> leftJoin("r.Split sp");
		$requestData = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $requestData;
	}

	public function getAllHydrated() {
		$query = Doctrine_Query::create() 
		-> select("r.*, c.*, p.*, sp.*")
		-> from("request r")
		-> leftJoin("r.Clients c")
		-> leftJoin("r.Packaging p")
		-> leftJoin("r.Split sp");
		$requestData = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $requestData;
	}

	public function getRequestsPerClient($uname) {
		$query = Doctrine_Query::create() 
		-> select("r.*, c.*, p.*, d.*, w.*")
		-> from("request r")
		-> innerJoin("r.Clients c")
		-> innerJoin("r.Packaging p")
		-> leftJoin("r.Worksheet_tracking w")
		-> leftJoin("r.Dispatch_register d")
		-> where("c.email = ?", $uname);
		$requestData = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $requestData;
	}

	public function getRequest($id) {
		$query = Doctrine_Query::create() -> select("*") -> from("request") -> where("Request_id = $id");
		$requestData = $query -> execute();
		return $requestData;
	}//end getRequest

	
	public function getRequestId() {
		$query = Doctrine_Query::create() 
		-> select('*')
		-> from("request");
		$requestData = $query -> execute() -> getLast();
		return $requestData;
	}//end getRequest
	
	
	public function getProducts($lab_ref_no) {
		$query = Doctrine_Query::create() 
		-> select('*')
		-> from("request")
		-> where('request_id =?', $lab_ref_no);
		$requestData = $query -> execute();
		return $requestData;
	}

	public function getProdInfo($lab_ref_no) {
		$query = Doctrine_Query::create() 
		-> select('*')
		-> from("request")
		-> where('request_id =?', $lab_ref_no);
		$requestData = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $requestData;
	}

	public function getSample($reqid) {
		$query = Doctrine_Query::create() 
		-> select('*')
		-> from("request")
		-> where('request_id =?', $reqid);
		$requestData = $query -> execute() -> toArray();
		return $requestData;
	}
	
	
	public function getAll5($reqid) {
		$query = Doctrine_Query::create() 
		-> select("r.*, c.*, p.*, sp.*")
		-> from("request r")
		-> leftJoin("r.Clients c")
		-> innerJoin("r.Packaging p")
		-> leftJoin("r.Split sp")
		-> andWhere("r.assign_status = '2' or r.assign_status = '0'");
		$requestData = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $requestData;
	}


	public function getAll6($reqid) {
		$query = Doctrine_Query::create() 
		-> select("r.*, c.*, p.*, sp.*")
		-> from("request r")
		-> leftJoin("r.Clients c")
		-> innerJoin("r.Packaging p")
		-> leftJoin("r.Split sp")
		-> where('r.request_id =?', $reqid);
		$requestData = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $requestData;
	}

	public function getLastRequestId(){
		$query = Doctrine_Query::create()
		-> select('max(id)')
		-> from("request");
		$lastreqid = $query -> execute() -> toArray();
		return $lastreqid;
	}
	
}
?>